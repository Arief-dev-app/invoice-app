<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tambahkan favicon -->
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
   <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex">

            <!-- Sidebar -->
            <aside class="w-64 bg-white shadow-md h-screen fixed overflow-hidden">
                <div class="h-full flex flex-col">
                    <!-- Logo / Header -->
                    <div class="p-6 text-xl font-bold border-b">
                        <a href="{{ route('dashboard') }}">
                            <x-application-logo class="h-6 w-auto fill-current text-gray-800" />
                        </a>
                    </div>

                    <!-- Navigasi Scrollable -->
                    <nav class="flex-1 overflow-y-auto px-4 mt-4">
                        <ul class="space-y-2">
                            @if (auth()->user()->is_admin == 1)
                                {{-- Static Menu: Auth --}}
                                <li x-data="{ open: {{ request()->is('group-user*') || request()->is('menu*') || request()->is('role-user*') || request()->is('user*') ? 'true' : 'false' }} }" class="relative">
                                    <button @click="open = !open"
                                        class="w-full text-left py-2 px-2 flex justify-between items-center rounded hover:bg-gray-200 transition
                                        {{ request()->is('group-user*') || request()->is('menu*') || request()->is('role-user*') || request()->is('user*') ? 'text-blue-600 font-bold bg-gray-100' : 'text-gray-700' }}">
                                        <span>Auth</span>
                                        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <ul x-show="open" x-cloak class="ml-4 mt-1 space-y-1">
                                        <!-- <li>
                                            <a href="{{ route('group-user.index') }}" class="block px-2 py-1 rounded hover:bg-gray-200 transition {{ request()->routeIs('group-user.index') ? 'text-blue-600 font-bold bg-gray-100' : 'text-gray-700' }}">
                                                User Group
                                            </a>
                                        </li> -->
                                        <li>
                                            <a href="{{ route('menu.index') }}" class="block px-2 py-1 rounded hover:bg-gray-200 transition {{ request()->routeIs('menu.index') ? 'text-blue-600 font-bold bg-gray-100' : 'text-gray-700' }}">
                                                Menu
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('role-user.index') }}" class="block px-2 py-1 rounded hover:bg-gray-200 transition {{ request()->routeIs('role-user.index') ? 'text-blue-600 font-bold bg-gray-100' : 'text-gray-700' }}">
                                                Role User
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.index') }}" class="block px-2 py-1 rounded hover:bg-gray-200 transition {{ request()->routeIs('user.index') ? 'text-blue-600 font-bold bg-gray-100' : 'text-gray-700' }}">
                                                User
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                            {{-- Dynamic Menus --}}
                            @foreach ($menu_header as $menu)
                                @php
                                    $children = $menu_detail->where('parent_id', $menu->id);
                                    $isActive = $children->pluck('slug')->filter()->contains(function ($slug) {
                                        return request()->is(ltrim($slug, '/') . '*');
                                    });
                                @endphp

                                <li x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="relative">
                                    <button @click="open = !open"
                                        class="w-full text-left py-2 px-2 flex justify-between items-center rounded hover:bg-gray-200 transition
                                        {{ $isActive ? 'text-blue-600 font-bold bg-gray-100' : 'text-gray-700' }}">
                                        <span>{{ $menu->name }}</span>
                                        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <ul x-show="open" x-cloak class="ml-4 mt-1 space-y-1">
                                        @foreach ($children as $child)
                                            <li>
                                                <a href="{{ url($child->slug) }}"
                                                class="block px-2 py-1 rounded hover:bg-gray-200 transition 
                                                {{ request()->is(ltrim($child->slug, '/') . '*') ? 'text-blue-600 font-bold bg-gray-100' : 'text-gray-700' }}">
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach

                            {{-- Logout --}}
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left py-2 px-2 rounded text-red-500 hover:text-red-700 hover:bg-red-100 transition">Logout</button>
                                </form>
                            </li>

                        </ul>
                    </nav>
                </div>
            </aside>

            

            <!-- Main content -->
            <div class="flex-1 ml-64">
                @include('layouts.navigation')

                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="p-0">
                    @yield('content')
                </main>
            </div>

        </div>
        <!-- Modal Script -->            
        @stack('scripts')
    </body>

</html>
