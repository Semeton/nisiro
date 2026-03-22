<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item icon="archive-box" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                    {{ __('Products') }}
                </flux:navbar.item>
                <flux:navbar.item icon="banknotes" :href="route('accounts.index')" :current="request()->routeIs('accounts.*')" wire:navigate>
                    {{ __('Accounts') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Overview')">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Stock')">
                    <flux:sidebar.item icon="archive-box" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                        {{ __('Products') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-down-tray" :href="route('stock.purchase')" :current="request()->routeIs('stock.purchase')" wire:navigate>
                        {{ __('Stock In') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-up-tray" :href="route('stock.sell')" :current="request()->routeIs('stock.sell')" wire:navigate>
                        {{ __('Stock Out') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('stock.history')" :current="request()->routeIs('stock.history')" wire:navigate>
                        {{ __('Stock History') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Money')">
                    <flux:sidebar.item icon="banknotes" :href="route('accounts.index')" :current="request()->routeIs('accounts.*')" wire:navigate>
                        {{ __('Accounts') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('transactions.index')" :current="request()->routeIs('transactions.index') || request()->routeIs('transactions.show')" wire:navigate>
                        {{ __('Transactions') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="plus-circle" :href="route('transactions.create')" :current="request()->routeIs('transactions.create')" wire:navigate>
                        {{ __('New Transaction') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
