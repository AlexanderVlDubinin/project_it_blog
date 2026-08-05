<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('List of Notifications') }}
            </h2>

            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2 px-3 py-0.5 border border-gray-500 rounded-lg shadow-sm">
                    <label for="ttl" class="text-sm text-gray-200 font-medium whitespace-nowrap mt-1">Delete notifications you read via: </label>

                    <form action="{{ route('notifications.updateSettings') }}" method="POST" class="m-0">
                        @csrf
                        <select id="ttl" name="notifications_ttl_days" onchange="this.form.submit()"
                                class="w-full border border-none text-white bg-white dark:bg-gray-800 rounded-lg px-4 py-2 text-sm pr-8 cursor-pointer">
                            @foreach($notifications_ttl_days as $days => $label)
                                <option value="{{ $days }}" {{ auth()->user()->notifications_ttl_days == $days ? 'selected' : '' }} class="w-full">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if(auth()->user()->unreadNotifications()->exists())
                    <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="ml-4">
                        @csrf
                        <button type="submit" class="flex items-center justify-between text-gray-800 dark:text-gray-200 bg-indigo-500 rounded-lg px-4 py-2 button-back cursor-pointer">
                            <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M5 7l4 4 6-6"></path>
                            </svg>
                            <span>Mark all as read</span>
                        </button>
                    </form>
                @endif

                @if(auth()->user()->readNotifications()->exists())
                        <form action="{{ route('notifications.deleteAllRead') }}"
                              method="POST"
                              class="ml-4"
                              x-data
                              @submit.prevent="if (confirm('Are you sure you want to permanently delete all the notifications you have already read?')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center justify-between text-gray-800 dark:text-gray-200 bg-red-700 rounded-lg px-4 py-2 button-back cursor-pointer">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                <span>Clear all read</span>
                            </button>
                        </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="shadow-sm overflow-hidden">
            @forelse($notifications as $notification)
                @php
                    $isCommentReply = ($notification->data['data']['type'] ?? 'default_notification') === 'comment_reply';
                    $icon = $notification->data['icon'] ?? 'heroicon-o-bell';
                    $status = $notification->data['status'] ?? 'blue';

                    $colors = [
                        'warning' => ['bg' => 'bg-amber-100', 'bgExtra' => 'bg-amber-500', 'bgBtn' => 'bg-amber-50 hover:bg-amber-200', 'border' => 'border-amber-400', 'mainText' => 'text-amber-500', 'headerText' => 'text-amber-700', 'subText' => 'text-amber-400'],
                        'success' => ['bg' => 'bg-green-100', 'bgExtra' => 'bg-green-500', 'bgBtn' => 'bg-green-50 hover:bg-green-200', 'border' => 'border-green-400', 'mainText' => 'text-green-500', 'headerText' => 'text-green-700', 'subText' => 'text-green-400'],
                        'danger'  => ['bg' => 'bg-red-100', 'bgExtra' => 'bg-red-500', 'bgBtn' => 'bg-red-50 hover:bg-red-200', 'border' => 'border-red-400', 'mainText' => 'text-red-500', 'headerText' => 'text-red-700', 'subText' => 'text-red-400'],
                        'info'    => ['bg' => 'bg-blue-100', 'bgExtra' => 'bg-blue-500', 'bgBtn' => 'bg-blue-50 hover:bg-blue-200', 'border' => 'border-blue-400', 'mainText' => 'text-blue-500', 'headerText' => 'text-blue-700', 'subText' => 'text-blue-400'],
                        //'gray'    => ['bg' => 'bg-gray-100', 'bgExtra' => 'bg-gray-500', 'bgBtn' => 'bg-gray-50 hover:bg-gray-200', 'border' => 'border-gray-400', 'mainText' => 'text-gray-500', 'headerText' => 'text-gray-700', 'subText' => 'text-gray-400'],
                    ][$status] ?? ['bg' => 'bg-blue-100', 'bgExtra' => 'bg-blue-500', 'bgBtn' => 'bg-blue-50 hover:bg-blue-200', 'border' => 'border-blue-400', 'mainText' => 'text-blue-500', 'headerText' => 'text-blue-700', 'subText' => 'text-blue-400'];//'blue';
                @endphp

                <div class="mt-2 p-4 border border-2 {{ $notification->unread() ? $colors['border'] : $colors['border'].' saturate-40' }} rounded-lg flex items-start justify-between {{ $colors['bg'] }}">

                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <!-- The unread label -->
                            @if($notification->unread())
                                <span class="w-2 h-2 rounded-full {{ $colors['bgExtra'] }} shrink-0"></span>
                            @endif

                            <!-- Icon -->
                            @svg($icon, 'w-6 h-6 ' . ($notification->unread() ? $colors['headerText'] : $colors['headerText'].' saturate-40'), )

                            <h3 class="font-bold text-2xl {{ $notification->unread() ? $colors['headerText'] : $colors['headerText'].' saturate-40' }}">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </h3>
                        </div>

                        <p class="{{ $notification->unread() ? $colors['mainText'] : $colors['mainText'].' saturate-40' }} text-lg mt-1">
                            {{ $notification->data['body'] ?? '...' }}
                        </p>

                        <span class="text-sm {{ $notification->unread() ? $colors['subText'] : $colors['subText'].' saturate-40' }} block mt-2">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>

                    @if($notification->unread() || $isCommentReply)
                    <!-- Action Button (Smart Link) -->
                    <div class="ml-4">
                        <a href="{{ route('notifications.read', $notification->id) }}"
                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-border {{ $notification->unread() ? $colors['border'] : $colors['border'].' saturate-40' }} {{ $notification->unread() ? $colors['headerText'] : $colors['headerText'].' saturate-40' }} {{ $notification->unread() ? $colors['bgBtn'] : $colors['bgBtn'].' saturate-40' }} transition">
                            {{ $notification->unread() ? ($isCommentReply ? 'Mark as read & Open' : 'Mark as read') : 'Open' }}
                        </a>
                    </div>
                    @endif
                </div>
            @empty
                <div class="p-8 font-bold text-center text-2xl text-gray-500">
                    You don't have any notifications yet.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>

</x-app-layout>
