<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun - BEM FILKOM UB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#F0EDE8]">
    <header class="bg-[#2F3336] px-6 py-4 text-white sm:px-10">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4">
            <div>
                <p class="font-bold">BEM FILKOM UB</p>
                <p class="text-sm text-[#A0AEC0]">Admin · Manajemen akun</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-white/20 px-4 py-2 text-sm font-medium hover:bg-white/10">
                    Keluar
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-10 sm:px-10">
        <div class="mb-7">
            <p class="text-sm font-semibold uppercase tracking-wide text-[#1A8A8A]">Administrasi</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-gray-900">Tambah akun</h1>
            <p class="mt-2 text-sm text-gray-600">Buat akun pengguna untuk mengakses sistem. Admin hanya memiliki akses ke fitur ini.</p>
        </div>

        @if (session('success'))
            <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Ada data yang belum valid. Periksa kembali isian yang ditandai.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.accounts.store') }}" class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-gray-700">Nama lengkap</label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="role" class="mb-1.5 block text-sm font-semibold text-gray-700">Role</label>
                    <select id="role" name="role" required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                        <option value="">Pilih role</option>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="nim" class="mb-1.5 block text-sm font-semibold text-gray-700">NIM <span class="font-normal text-gray-400">(opsional)</span></label>
                    <input id="nim" name="nim" value="{{ old('nim') }}" maxlength="20"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                    @error('nim')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="departemen" class="mb-1.5 block text-sm font-semibold text-gray-700">Departemen <span class="font-normal text-gray-400">(opsional)</span></label>
                    <input id="departemen" name="departemen" value="{{ old('departemen') }}" maxlength="50"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                    @error('departemen')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="angkatan" class="mb-1.5 block text-sm font-semibold text-gray-700">Angkatan <span class="font-normal text-gray-400">(opsional)</span></label>
                    <input id="angkatan" name="angkatan" value="{{ old('angkatan') }}" maxlength="10"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                    @error('angkatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="jabatan" class="mb-1.5 block text-sm font-semibold text-gray-700">Jabatan <span class="font-normal text-gray-400">(opsional)</span></label>
                    <input id="jabatan" name="jabatan" value="{{ old('jabatan') }}" maxlength="100"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                    @error('jabatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-gray-700">Password awal</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter, dengan huruf dan angka.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-gray-700">Konfirmasi password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-[#1A8A8A] focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/20">
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="rounded-xl bg-[#1A8A8A] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#157070]">
                    Tambah akun
                </button>
            </div>
        </form>
    </main>
</body>
</html>
