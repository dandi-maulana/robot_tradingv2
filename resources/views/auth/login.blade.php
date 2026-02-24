<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        body {
            background-image: url("{{ asset('assets/images/Jkw1.jpeg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow-x: hidden;
            /* 🔥 Hilangkan geser kiri kanan */
        }

        /* Matikan fixed background di HP (biang scroll) */
        @media (max-width: 768px) {
            body {
                background-attachment: scroll;
            }
        }

        @media (min-width: 769px) {
            body {
                background-attachment: fixed;
            }
        }

        .login-box {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
        }
    </style>
</head>

<body class="min-h-screen relative overflow-x-hidden">

    <!-- BUTTON LOGIN -->
    <div class="fixed top-3 right-3 sm:top-4 sm:right-4 z-40">
        <button onclick="openLogin()"
            class="bg-black text-white px-4 py-2 text-sm sm:text-base rounded-lg hover:bg-gray-800 transition shadow">
            Login
        </button>
    </div>

    <!-- MODAL LOGIN -->
    <div id="loginModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center px-4">

        <div class="w-full max-w-md p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200 login-box relative">

            <!-- CLOSE BUTTON -->
            <button onclick="closeLogin()"
                class="absolute top-2 right-3 text-gray-600 hover:text-black text-xl font-bold">
                ✕
            </button>

            <h2 class="text-xl sm:text-2xl font-bold mb-6 text-center">Login</h2>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        class="w-full p-2 sm:p-2.5 border border-gray-300 rounded focus:ring-1 focus:ring-black outline-none bg-white/90"
                        required autofocus>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" name="password"
                        class="w-full p-2 sm:p-2.5 border border-gray-300 rounded focus:ring-1 focus:ring-black outline-none bg-white/90"
                        required>
                </div>

                <div class="mb-4 flex justify-center scale-90 sm:scale-100 overflow-hidden">
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                </div>

                <button type="submit"
                    class="w-full bg-black text-white p-2.5 rounded-lg hover:bg-gray-800 transition font-bold shadow-md">
                    Log In
                </button>
            </form>
        </div>
    </div>

    <script>
        function openLogin() {
            document.getElementById('loginModal').classList.remove('hidden');
        }

        function closeLogin() {
            document.getElementById('loginModal').classList.add('hidden');
        }
    </script>

</body>

</html>