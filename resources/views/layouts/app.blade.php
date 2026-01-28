<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') ?? 'Project Management App' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if (!empty(env('ONESIGNAL_APP_ID')))
        {{-- OneSignalSDKWorker.js must be served with content-type: application/javascript --}}
        <script src="{{ asset('OneSignalSDKWorker.js') }}" type="application/javascript"></script>
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
        <script>
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            OneSignalDeferred.push(async function(OneSignal) {
                await OneSignal.init({
                    appId: "{{ env('ONESIGNAL_APP_ID') }}",
                });
                OneSignal.Debug.setLogLevel("trace");

                @if (Auth::check())
                    OneSignal.User.PushSubscription.optIn();
                    await OneSignal.login("{{ (string) Auth::id() }}");
                @endif
            });
        </script>
        {{-- <div class='onesignal-customlink-container'></div> --}}
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                {{-- Logo and Desktop Nav --}}
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('tasks.index') }}"
                            class="text-xl font-bold text-indigo-600 tracking-tight flex items-center gap-2">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                </path>
                            </svg>
                            ProMan
                        </a>
                    </div>
                    {{-- Desktop Links --}}
                    <div class="hidden sm:-my-px sm:ml-10 sm:flex sm:space-x-8">
                        <a href="{{ route('tasks.index') }}"
                            class="{{ request()->routeIs('tasks.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            Tasks
                        </a>
                        <a href="{{ route('credentials.index') }}"
                            class="{{ request()->routeIs('credentials.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            Credentials
                        </a>
                        <a href="{{ route('notes.index') }}"
                            class="{{ request()->routeIs('notes.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            Notes
                        </a>
                        <a href="{{ route('chat.index') }}"
                            class="{{ request()->routeIs('chat.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            Chat
                        </a>
                        <a href="{{ route('drive.index') }}"
                            class="{{ request()->routeIs('drive.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            Shared Drive
                        </a>
                        @if (Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}"
                                class="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                                Admin
                            </a>
                            <a href="{{ route('admin.activity_logs.index') }}"
                                class="{{ request()->routeIs('admin.activity_logs.index') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                                Activities
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}"
                            class="{{ request()->routeIs('profile.edit') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                            Profile
                        </a>
                    </div>
                </div>

                <!-- Desktop Profile Dropdown (simplified as standard flex for now) -->
                <div class="hidden sm:flex items-center">
                    <div class="ml-3 relative flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="text-sm text-red-600 hover:text-red-800 font-medium transition-colors">Logout</button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Hamburger -->
                <div class="-mr-2 flex items-center sm:hidden">
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-colors"
                        aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="hidden sm:hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('tasks.index') }}"
                    class="{{ request()->routeIs('tasks.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Tasks</a>
                <a href="{{ route('credentials.index') }}"
                    class="{{ request()->routeIs('credentials.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Credentials</a>
                <a href="{{ route('notes.index') }}"
                    class="{{ request()->routeIs('notes.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Notes</a>
                <a href="{{ route('chat.index') }}"
                    class="{{ request()->routeIs('chat.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Chat</a>
                <a href="{{ route('drive.index') }}"
                    class="{{ request()->routeIs('drive.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Shared
                    Drive</a>
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Admin
                        / Users</a>
                    <a href="{{ route('admin.activity_logs.index') }}"
                        class="{{ request()->routeIs('admin.activity_logs.index') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Activity
                        Logs</a>
                @endif
                <a href="{{ route('profile.edit') }}"
                    class="{{ request()->routeIs('profile.edit') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Profile</a>
            </div>
            <div class="pt-4 pb-4 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <!-- User Avatar/Initial could go here -->
                        <div
                            class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>

</html>
