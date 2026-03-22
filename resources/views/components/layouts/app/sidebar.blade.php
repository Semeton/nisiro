<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Overview')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Stock')" class="grid">
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

                <flux:sidebar.group :heading="__('Money')" class="grid">
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

                <flux:sidebar.group :heading="__('Reports')" class="grid">
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.profit-and-loss')" :current="request()->routeIs('reports.profit-and-loss')" wire:navigate>
                        {{ __('Profit & Loss') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="currency-dollar" :href="route('reports.cash-flow')" :current="request()->routeIs('reports.cash-flow')" wire:navigate>
                        {{ __('Cash Flow') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shield-check" :href="route('reports.tax-visibility')" :current="request()->routeIs('reports.tax-visibility')" wire:navigate>
                        {{ __('Tax Visibility') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
