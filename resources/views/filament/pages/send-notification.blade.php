<x-filament-panels::page>
    {{-- Page content --}}
    <form wire:submit.prevent="send" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Send Notification
        </x-filament::button>
    </form>
</x-filament-panels::page>
