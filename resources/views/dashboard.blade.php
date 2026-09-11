<x-app-layout>
    <x-slot name="header">
        <h2 class="dashboard-page-title font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @if(auth()->user()->hasRole(App\Enum\UserRole::USER))
        @include('dashboard.partials.user-stats')
        @include('dashboard.partials.recommendations')
    @elseif(auth()->user()->hasRole(App\Enum\UserRole::AUTHOR))
        @include('dashboard.partials.user-stats')
        @include('dashboard.partials.recommendations')
        @include('dashboard.partials.my-posts')
    @elseif(auth()->user()->hasRole(App\Enum\UserRole::MODERATOR))
        <livewire:moderator.recent-deleted-posts />
        <livewire:moderator.recent-deleted-comments />
        <livewire:moderator.low-rated-comments />
    @elseif(auth()->user()->hasRole(App\Enum\UserRole::ADMIN))
        <livewire:admin.latest-registered-users />
        <livewire:admin.recent-imported-posts />
    @endif
</x-app-layout>
