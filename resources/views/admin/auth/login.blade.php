<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Alert Messages */
        .alert-message {
            transition: all 0.3s ease-in-out;
            animation: slideIn 0.3s ease-out;
        }
        
        .alert-message.hiding {
            opacity: 0;
            transform: translateY(-10px);
            margin-bottom: 0 !important;
            padding-top: 0;
            padding-bottom: 0;
            max-height: 0;
            overflow: hidden;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-close-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: transparent;
            border: none;
            font-size: 1.25rem;
            font-weight: bold;
            line-height: 1;
            color: inherit;
            opacity: 0.7;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            transition: opacity 0.2s;
        }
        
        .alert-close-btn:hover {
            opacity: 1;
        }
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Admin Login
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Sign in to access the admin panel
                </p>
            </div>
            
            <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-xl" method="POST" action="{{ route('admin.login') }}">
                @csrf

                @if(session('success'))
                    <div class="alert-message bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative pr-10" role="alert">
                        <button type="button" class="alert-close-btn" onclick="closeAlert(this)" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative pr-10" role="alert">
                        <button type="button" class="alert-close-btn" onclick="closeAlert(this)" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative pr-10" role="alert">
                        <button type="button" class="alert-close-btn" onclick="closeAlert(this)" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror" 
                               placeholder="Email address" value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror" 
                               placeholder="Password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                        Sign in
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Alert Messages Auto-dismiss and Close functionality
        function closeAlert(button) {
            const alert = button.closest('.alert-message');
            if (alert) {
                alert.classList.add('hiding');
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }
        }
        
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-message');
            alerts.forEach(function(alert) {
                // Set timeout to auto-dismiss after 5 seconds
                const timeout = setTimeout(function() {
                    alert.classList.add('hiding');
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
                
                // Clear timeout if user manually closes the alert
                const closeBtn = alert.querySelector('.alert-close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        clearTimeout(timeout);
                    });
                }
            });
        });
    </script>
</body>
</html>

