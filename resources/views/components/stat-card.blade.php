@props(['label', 'value'])

<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
    <flux:text class="text-sm">{{ $label }}</flux:text>
    <flux:heading size="xl" class="mt-2">{{ $value }}</flux:heading>
</div>
