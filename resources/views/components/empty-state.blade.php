@props(['message', 'actionLabel' => null, 'actionUrl' => null])

<div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-neutral-300 px-6 py-12 dark:border-neutral-600">
    <flux:text class="text-center">{{ $message }}</flux:text>
    @if ($actionLabel && $actionUrl)
        <flux:button variant="primary" :href="$actionUrl" wire:navigate class="mt-4">
            {{ $actionLabel }}
        </flux:button>
    @endif
</div>
