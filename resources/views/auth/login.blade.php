<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-body text-slate-700 antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 rounded-2xl mb-4">
                    <span class="text-3xl">🏥</span>
                </div>
                <h1 class="font-heading font-bold text-2xl text-slate-900">{{ config('app.name') }}</h1>
                <p class="text-sm text-slate-500 mt-1">Sistem Informasi Posyandu</p>
            </div>

            {{-- Login Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <h2 class="font-heading font-semibold text-lg text-slate-800 mb-6">Masuk ke akun Anda</h2>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- [START] Toggle Role Admin/Kader (Bisa dihapus jika tidak diperlukan) --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Masuk Sebagai</label>
                        <input type="hidden" name="role" id="role-input" value="{{ old('role', 'admin') }}">
                        <div class="grid grid-cols-2 p-1 bg-slate-100/90 rounded-xl border border-slate-200/80 gap-1">
                            <button type="button" id="btn-role-admin" onclick="switchRole('admin')"
                                    class="flex items-center justify-center gap-2 py-2 px-3 text-sm rounded-lg transition-all duration-150">
                                <span>🛡️</span>
                                <span>Admin</span>
                            </button>
                            <button type="button" id="btn-role-kader" onclick="switchRole('kader')"
                                    class="flex items-center justify-center gap-2 py-2 px-3 text-sm rounded-lg transition-all duration-150">
                                <span>👩‍⚕️</span>
                                <span>Kader</span>
                            </button>
                        </div>
                    </div>
                    {{-- [END] Toggle Role Admin/Kader --}}

                    {{-- Username --}}
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                               placeholder="Masukkan username" required autofocus>
                    </div>

                    {{-- Password --}}
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <input type="password" id="password" name="password"
                               class="w-full h-11 px-3.5 border-[1.5px] border-slate-300 rounded-[10px] text-sm text-slate-800 placeholder:text-slate-400 focus:border-primary-500 focus:ring-[3px] focus:ring-primary-500/20 outline-none transition-all"
                               placeholder="Masukkan password" required>
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center mb-6">
                        <input type="checkbox" id="remember" name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-primary-500 focus:ring-primary-500/20">
                        <label for="remember" class="ml-2 text-sm text-slate-600">Ingat saya</label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full h-11 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-[10px] transition-all active:scale-[0.97]">
                        Masuk
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>

    {{-- [START] Script Toggle Role (Bisa dihapus jika toggle dihapus) --}}
    <script>
        function switchRole(role) {
            const roleInput = document.getElementById('role-input');
            const btnAdmin = document.getElementById('btn-role-admin');
            const btnKader = document.getElementById('btn-role-kader');

            if (!roleInput || !btnAdmin || !btnKader) return;
            roleInput.value = role;

            const activeClasses = ['bg-white', 'text-primary-600', 'font-semibold', 'shadow-xs', 'border', 'border-slate-200/80'];
            const inactiveClasses = ['text-slate-500', 'hover:text-slate-700', 'font-medium', 'border-transparent'];

            if (role === 'admin') {
                btnAdmin.classList.remove(...inactiveClasses);
                btnAdmin.classList.add(...activeClasses);

                btnKader.classList.remove(...activeClasses);
                btnKader.classList.add(...inactiveClasses);
            } else {
                btnKader.classList.remove(...inactiveClasses);
                btnKader.classList.add(...activeClasses);

                btnAdmin.classList.remove(...activeClasses);
                btnAdmin.classList.add(...inactiveClasses);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const currentRole = document.getElementById('role-input')?.value || 'admin';
            switchRole(currentRole);
        });
    </script>
    {{-- [END] Script Toggle Role --}}
</body>
</html>
