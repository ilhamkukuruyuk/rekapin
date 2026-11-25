<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BEM FILKOM UB</title>
    <meta name="description" content="Login ke Sistem Kas & Presensi BEM FILKOM Universitas Brawijaya">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }

        .demo-content {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 300ms cubic-bezier(0.22, 1, 0.36, 1);
        }

        .demo-content.is-open {
            grid-template-rows: 1fr;
        }

        .demo-content-inner {
            min-height: 0;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-0.35rem);
            transition: opacity 200ms ease, transform 300ms cubic-bezier(0.22, 1, 0.36, 1);
        }

        .demo-content.is-open .demo-content-inner {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .demo-content {
                transition: none;
            }

            .demo-content-inner {
                transition: none;
            }
        }
    </style>
</head>
<body class="min-h-screen flex bg-[#2F3336]">

    {{-- Left Panel --}}
    <div class="sticky top-0 h-screen self-start shrink-0 w-[45%] bg-[#2F3336] flex flex-col justify-between p-10 text-white relative overflow-hidden">
        {{-- Logo --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#1A8A8A] rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <span class="text-white font-bold text-lg">BEM FILKOM UB</span>
        </div>

        {{-- Hero Content --}}
        <div class="my-auto py-10">
            <span class="inline-block text-[#5AADAD] text-xs border border-[#5AADAD]/40 rounded-full px-3.5 py-1.5 mb-8 font-medium">
                Sistem Manajemen Organisasi
            </span>
            <h1 class="text-4xl font-extrabold text-white leading-tight mb-6 tracking-tight">
                Kelola kas &<br>kehadiran dengan<br>lebih efisien.
            </h1>
            <p class="text-[#9CA3AF] text-sm leading-relaxed max-w-sm">
                Platform terpusat untuk manajemen kas, iuran, denda, dan presensi anggota BEM FILKOM Universitas Brawijaya.
            </p>
        </div>

        {{-- Role Tersedia --}}
        <div>
            <div class="text-[11px] font-semibold text-gray-400 tracking-wider uppercase mb-3">Role Tersedia</div>
            <p class="text-sm text-gray-300">Anggota Biasa · ADKEU · PresBEM · PSDI · Admin</p>
        </div>

        {{-- Decorative gradient --}}
        <div class="absolute bottom-0 right-0 w-40 h-40 bg-[#1A8A8A]/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    {{-- Right Panel --}}
    <div class="flex-1 min-h-screen bg-[#F0EDE8] flex flex-col justify-between p-12">
        <div></div>

        <div class="w-full max-w-md mx-auto my-auto">
            <div class="mb-8">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-1.5 tracking-tight">Masuk ke akun Anda</h2>
                <p class="text-sm text-gray-500">Kas & Presensi BEM FILKOM UB</p>
            </div>

            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L3.07 16.5c-.77.833.193 2.5 1.732 2.5z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="nama@organisasi.id"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/40 focus:border-[#1A8A8A] transition-all"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••••"
                            class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1A8A8A]/40 focus:border-[#1A8A8A] transition-all pr-12"
                            required
                        >
                        <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg id="eyeOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember & Forgot --}}
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                        <input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember')) class="w-4 h-4 rounded border-gray-300 text-[#1A8A8A] focus:ring-[#1A8A8A]/30 cursor-pointer">
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-[#1A8A8A] hover:text-[#157070] font-semibold transition-colors">Lupa password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit" id="btn-masuk"
                    class="w-full bg-[#1A8A8A] hover:bg-[#157070] text-white font-semibold py-3.5 rounded-xl transition-all duration-200 text-sm shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                    Masuk
                </button>

                @if ($showDemoAccounts)
                    {{-- Demo Accounts Accordion --}}
                    <div class="border border-dashed border-gray-300 rounded-xl bg-white/60 overflow-hidden">
                    <button type="button" id="toggleDemo" aria-controls="demoContent" aria-expanded="false" class="w-full px-4 py-3 flex items-center justify-between text-xs font-semibold text-gray-600 hover:bg-gray-50/50 transition">
                        <span>Akun Demo Cepat</span>
                        <svg id="demoChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="demoContent" class="demo-content" aria-hidden="true" inert>
                        <div class="demo-content-inner">
                            <div class="px-4 pb-3 pt-1 border-t border-gray-200/60 text-xs text-gray-600 space-y-2 max-h-72 overflow-y-auto">
                                {{-- 1. ADKEU (Universal Institusional) --}}
                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#1A8A8A] pt-1">Biro Administrasi Keuangan (ADKEU)</div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('adkeu@bem-filkom.ub.ac.id', 'adkeu123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Administrasi Keuangan (ADKEU) <span class="text-[10px] text-teal-700 bg-teal-100/70 px-1.5 py-0.5 rounded font-semibold">Utama</span></div>
                                        <div class="text-[11px] text-gray-500">adkeu@bem-filkom.ub.ac.id • sandi: adkeu123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>

                                {{-- 2. PSDI (Universal Institusional) --}}
                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#1A8A8A] pt-2">Pengembangan Sumber Daya Internal (PSDI)</div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('psdi@bem-filkom.ub.ac.id', 'psdi123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Pengembangan Sumber Daya Internal (PSDI) <span class="text-[10px] text-teal-700 bg-teal-100/70 px-1.5 py-0.5 rounded font-semibold">Utama</span></div>
                                        <div class="text-[11px] text-gray-500">psdi@bem-filkom.ub.ac.id • sandi: psdi123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>

                                {{-- 3. Presidium BEM --}}
                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#1A8A8A] pt-2">Presiden BEM</div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('daffa@bem-filkom.ub.ac.id', 'presbem123')">
                                    <div>
                                        <div class="font-bold text-gray-800">M. Daffa Riyadlussalam <span class="text-[10px] text-teal-700 bg-teal-100/70 px-1.5 py-0.5 rounded font-semibold">Ketua BEM</span></div>
                                        <div class="text-[11px] text-gray-500">daffa@bem-filkom.ub.ac.id • sandi: presbem123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('ahmad@bem-filkom.ub.ac.id', 'wapresbem123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Ahmad Fauzi <span class="text-[10px] text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded font-semibold">Wakil Ketua BEM</span></div>
                                        <div class="text-[11px] text-gray-500">ahmad@bem-filkom.ub.ac.id • sandi: wapresbem123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>

                                {{-- 4. Anggota Fungsionaris --}}
                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#1A8A8A] pt-2">Anggota Fungsionaris</div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('claudio@bem-filkom.ub.ac.id', 'anggota123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Claudio Amadeus <span class="text-[10px] text-gray-400">Staff PSDM</span></div>
                                        <div class="text-[11px] text-gray-500">claudio@bem-filkom.ub.ac.id • sandi: anggota123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('budi@student.ub.ac.id', 'anggota123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Budi Santoso <span class="text-[10px] text-gray-400">Staff Medinkraf</span></div>
                                        <div class="text-[11px] text-gray-500">budi@student.ub.ac.id • sandi: anggota123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('citra@student.ub.ac.id', 'anggota123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Citra Lestari <span class="text-[10px] text-gray-400">Staff Advokesma</span></div>
                                        <div class="text-[11px] text-gray-500">citra@student.ub.ac.id • sandi: anggota123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('dimas@student.ub.ac.id', 'anggota123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Dimas Setiawan <span class="text-[10px] text-gray-400">Staff Sosling</span></div>
                                        <div class="text-[11px] text-gray-500">dimas@student.ub.ac.id • sandi: anggota123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('fajar@student.ub.ac.id', 'anggota123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Fajar Nugroho <span class="text-[10px] text-gray-400">Staff Kastrat</span></div>
                                        <div class="text-[11px] text-gray-500">fajar@student.ub.ac.id • sandi: anggota123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-teal-50/70 border border-transparent hover:border-teal-200 cursor-pointer transition" onclick="setDemo('gita@student.ub.ac.id', 'anggota123')">
                                    <div>
                                        <div class="font-bold text-gray-800">Gita Savitri <span class="text-[10px] text-gray-400">Staff PIT</span></div>
                                        <div class="text-[11px] text-gray-500">gita@student.ub.ac.id • sandi: anggota123</div>
                                    </div>
                                    <span class="text-xs text-[#1A8A8A] font-semibold">Pilih</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-8">© {{ now()->year }} BEM FILKOM Universitas Brawijaya</p>
    </div>

    <script>
        const toggleDemo = document.getElementById('toggleDemo');
        const demoContent = document.getElementById('demoContent');
        const demoChevron = document.getElementById('demoChevron');

        if (toggleDemo && demoContent) {
            toggleDemo.addEventListener('click', () => {
                const isOpen = toggleDemo.getAttribute('aria-expanded') === 'true';

                toggleDemo.setAttribute('aria-expanded', String(!isOpen));
                demoContent.setAttribute('aria-hidden', String(isOpen));
                demoContent.inert = isOpen;
                demoContent.classList.toggle('is-open', !isOpen);

                if (demoChevron) {
                    demoChevron.classList.toggle('rotate-180', !isOpen);
                }
            });
        }

        function setDemo(email, password = '') {
            document.getElementById('email').value = email;
            if (password && document.getElementById('password')) {
                document.getElementById('password').value = password;
            }
        }

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                if (eyeOpen && eyeClosed) {
                    eyeOpen.classList.toggle('hidden', isPassword);
                    eyeClosed.classList.toggle('hidden', !isPassword);
                }
            });
        }
    </script>
</body>
</html>
