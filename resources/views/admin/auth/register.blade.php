<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-3xl w-full">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900">Create an Account</h1>
                <p class="mt-2 text-sm text-gray-600">Register your company and create the first admin account.</p>
            </div>

            <div class="max-w-md mx-auto">
                <a href="{{ route('admin.register.company') }}" class="block bg-white rounded-xl shadow-xl p-8 hover:shadow-2xl transition border border-transparent hover:border-indigo-200">
                    <div class="h-12 w-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Register as Company</h2>
                    <p class="mt-2 text-sm text-gray-600">Company list maa add huncha, ani first admin account create huncha.</p>
                </a>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">Already have an account? Sign in</a>
            </div>
        </div>
    </div>
</body>
</html>
