@props(['unreadNotificationsCount' => 0])

<div class="flex items-center" style="margin-right: 16px;">
    <x-filament::icon-button
        tag="a"
        href="/notifications"
        icon="heroicon-o-bell"
        color="gray"
        size="lg"
        badge=""
        badge-color="danger"
        class="relative"
    >
    @if($unreadNotificationsCount > 0)
        <x-slot name="badge">
            <span
                style="
                background-color: rgb(239, 68, 68);
                color: rgb(255, 255, 255);
                font-size: 10px;
                font-weight: 700;
                line-height: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 18px;
                height: 18px;
                border-radius: 50%;
                padding: 0 4px;
                margin-right: 2px;
            "
            >
                {{ $unreadNotificationsCount <= 99 ? $unreadNotificationsCount : '99+' }}
            </span>
        </x-slot>
    @endif
    <!-- Примечание: Если нужно вывести количество уведомлений, передайте число в параметр badge="5" -->
    </x-filament::icon-button>
</div>
