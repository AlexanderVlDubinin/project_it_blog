<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="h-full shrink-0 flex items-center">
                    <a href="@auth() {{ route('dashboard') }} @else {{ route('login') }} @endauth" class="h-full flex items-center">
                        {{--<x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />--}}
                        <img src="/images/geralt-quantum-computing-9427290_1920.png" class="h-full w-auto object-contain shrink-0" alt="Logo">
                    </a>
                </div>

                @auth()
                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    </div>

                    @can('manage-site')
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <x-nav-link :href="route('filament.admin.pages.dashboard')" target="blank">
                                {{ __('Admin Panel') }}
                            </x-nav-link>
                        </div>
                    @endcan

                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index')">
                            {{ __('List of Posts') }}
                        </x-nav-link>
                    </div>
                @endauth
            </div>

            <div class="flex justify-between items-center">
                @auth
                    <!-- A bell container for managing the drop-down list -->
                    <div class="flex items-center" >
                        <a href="{{ route('notifications.index') }}">
                        <button class="relative p-2 text-gray-400 hover:text-gray-300 focus:outline-none">
                            <!-- Icon Heroicons (Bell) -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>

                            <!-- Badge with a number (shown only if there are unread ones) -->
                            @if($unreadNotificationsCount > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full transform translate-x-1/3 -translate-y-1/3">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </button>
                        </a>

                        {{-- Alternative: With x-dropdown - dropdown list of latest notifications --}}
                        {{--
                        <x-dropdown align="right" width="auto" contentClasses="w-80 py-1 bg-white dark:bg-gray-700">
                            <x-slot name="trigger">
                                <!-- Bell button -->
                                <button class="relative p-2 text-gray-400 hover:text-gray-300 focus:outline-none">
                                    <!-- Icon Heroicons (Bell) -->
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>

                                    <!-- Badge with a number (shown only if there are unread ones) -->
                                    @if($unreadNotificationsCount > 0)
                                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full transform translate-x-1/3 -translate-y-1/3">
                                        {{ $unreadNotificationsCount }}
                                    </span>
                                    @endif
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <!-- Drop-down preview window -->
                                <div class="px-4 py-2 font-semibold text-gray-300 border-b border-gray-100 flex justify-between items-center">
                                    <span>Notifications</span>
                                    @if($unreadNotificationsCount > 0)
                                        <span class="text-xs text-indigo-500 bg-gray-200 px-2 py-0.5 rounded-full">{{ $unreadNotificationsCount }} new</span>
                                    @endif
                                </div>

                                <!-- List of the last 5 notifications -->
                                <div class="max-h-64 overflow-y-auto">
                                    @forelse($latestNotifications as $notification)
                                        <div class="px-4 py-3  text-sm">
                                            <p class="text-indigo-400">
                                                <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                            </p>
                                            <p class="text-white text-xs mt-0.5">
                                                {{ \Illuminate\Support\Str::limit($notification->data['body'] ?? '', 60) }}
                                            </p>
                                            <span class="text-[10px] text-gray-300 block mt-1">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="px-4 py-6 text-center text-gray-300 text-sm">
                                            There are no new notifications
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Link to the notification page -->
                                <a href="{{ route('notifications.index') }}" class="block text-center text-xs text-blue-400 font-medium py-2 border-t border-gray-100 hover:underline">
                                    View all notifications
                                </a>
                            </x-slot>
                        </x-dropdown>
                        --}}
                    </div>
                @endauth

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                @auth()
                                    <div>{{ Auth::user()->name }}</div>
                                @else
                                    <div>{{ __('Hello, guest') }}</div>
                                @endauth

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @auth()
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            @else
                                <x-dropdown-link :href="route('register')">
                                    {{ __('Register') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('login')">
                                    {{ __('Log In') }}
                                </x-dropdown-link>
                            @endauth
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        @auth()
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            </div>

            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index')">
                    {{ __('List of Posts') }}
                </x-responsive-nav-link>
            </div>
        @endauth

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                @auth()
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                @else
                    <div class="font-medium text-sm text-gray-500">{{ __('Hello, guest') }}</div>
                @endauth
            </div>

            <div class="mt-3 space-y-1">
                @auth()
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                @endauth

                @auth()
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                @else
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Log In') }}
                    </x-responsive-nav-link>
                @endauth
            </div>
        </div>
    </div>
</nav>
