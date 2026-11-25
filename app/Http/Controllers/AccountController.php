<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    private const ROLES = [
        'anggota' => 'Anggota Biasa',
        'bendahara' => 'ADKEU / Bendahara',
        'presbem' => 'PresBEM',
        'psdi' => 'PSDI',
    ];

    public function create(): View
    {
        return view('admin.accounts.create', [
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(self::ROLES))],
            'nim' => ['nullable', 'string', 'max:20'],
            'departemen' => ['nullable', 'string', 'max:50'],
            'angkatan' => ['nullable', 'string', 'max:10'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'email.unique' => 'Email tersebut sudah digunakan.',
            'role.in' => 'Role akun tidak valid.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create($validated);

        return redirect()
            ->route('admin.accounts.create')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['nullable', 'string', 'max:20'],
            'angkatan' => ['nullable', 'string', 'max:10'],
            'departemen' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ], [
            'name.required' => 'Nama fungsionaris wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'nim' => $validated['nim'] ?? $user->nim,
            'angkatan' => $validated['angkatan'] ?? $user->angkatan,
            'departemen' => $validated['departemen'] ?? $user->departemen,
            'jabatan' => $validated['jabatan'] ?? $user->jabatan,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
