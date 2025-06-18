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
            <aside class="w-64 bg-white shadow-md h-screen fixed">
                <div class="p-6 text-xl font-bold border-b">  
                    <a href="{{ route('dashboard') }}">
                       <x-application-logo class="h-6 w-auto fill-current text-gray-800" />
                    </a>
                </div>
                <nav class="mt-4 px-4">
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('company.index') }}" class="{{ request()->routeIs('company.index') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                Company
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('product.index') }}" class="{{ request()->routeIs('product.index') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                Product
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('invoice.index') }}" class="{{ request()->routeIs('invoice.index') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                Invoice
                            </a>
                        </li>
                        <li x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="w-full text-left py-2 {{ request()->is('group-user*') || request()->is('menu*') || request()->is('role-user*') || request()->is('user*') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                Auth
                            </button>
                            <ul x-show="open" class="ml-4 mt-1 space-y-1" x-cloak>
                                <li>
                                    <a href="{{ route('group-user.index') }}" class="{{ request()->routeIs('group-user.index') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        User Group
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('menu.index') }}" class="{{ request()->routeIs('menu.index') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        Menu
                                    </a>
                                </li>
                                 {{--<li>
                                    <a href="{{ route('role-user.index') }}" class="{{ request()->routeIs('role-user.index') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        Role User
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.index') }}" class="{{ request()->routeIs('user.index') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        User
                                    </a>
                                </li> --}}
                            </ul>
                        </li>
                        {{-- Dinamis Menu dari DB --}}
                        @foreach ($menus as $menu)
                            @if ($menu->children->isNotEmpty())
                                <li x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="w-full text-left py-2 {{ request()->is(strtolower($menu->slug) . '*') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        {{ $menu->name }}
                                    </button>
                                    <ul x-show="open" class="ml-4 mt-1 space-y-1" x-cloak>
                                        @foreach ($menu->children as $child)
                                            <li>
                                                <a href="{{ url($child->slug) }}" class="{{ request()->is(strtolower($child->slug) . '*') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                <li>
                                    <a href="{{ url($menu->slug) }}" class="{{ request()->is(strtolower($menu->slug) . '*') ? 'text-blue-600 font-bold' : 'text-gray-700' }}">
                                        {{ $menu->name }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left py-2 text-red-500 hover:text-red-700">Logout</button>
                            </form>
                        </li>
                    </ul>
                </nav>
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
