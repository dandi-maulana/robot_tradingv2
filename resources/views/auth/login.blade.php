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
            background-image: url('{{ asset('assets/images/naruto-bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .login-box {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full p-8 rounded-lg shadow-lg border border-gray-200 login-box">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}"
                    class="w-full p-2 border border-gray-300 rounded focus:ring-1 focus:ring-black outline-none bg-white/90"
                    required autofocus>
                @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password"
                    class="w-full p-2 border border-gray-300 rounded focus:ring-1 focus:ring-black outline-none bg-white/90"
                    required>
            </div>
            <div class="mb-4 flex justify-center">
                <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
            </div>
            @error('g-recaptcha-response')
                <p class="text-red-500 text-xs text-center mb-4">{{ $message }}</p>
            @enderror
            <button type="submit"
                class="w-full bg-black text-white p-2 rounded hover:bg-gray-800 transition font-bold shadow-md">
                Log In
            </button>
        </form>
    </div>
</body>

</html>
