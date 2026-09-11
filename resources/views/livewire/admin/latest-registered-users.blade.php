<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-white">
                        Latest registered users:
                    </h2>
                </div>
            </div>

            <div class="overflow-x-auto my-6 w-full">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-t border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-center">Registered at</th>
                        <th class="px-4 py-2 text-center">Role</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($latestRegisteredUsers as $index => $user)
                        <tr class="border-b border-t border-gray-600 /*hover:bg-gray-700*/ latest-registered-users-tr">
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $user->id }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $user->name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $user->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10 dark:bg-indigo-400/10 dark:text-indigo-200 dark:ring-indigo-400/30">
                                            {{ $user->role }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $user]) }}" data-test="latest-registered-users-moderate-user-{{ $user->id }}-btn" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-border border-blue-400 text-blue-700 bg-blue-50 hover:bg-blue-200 transition">
                                            Moderate
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border-b border-t border-gray-600 px-4 py-4 text-center text-gray-500 w-full">
                                No users registered.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Show more Button --}}
            @if($hasMore)
                <div class="my-4 text-center">
                    <span class="text-gray-500 dark:text-gray-400">
                        Total: {{ $latestRegisteredUsersCount }}
                    </span>
                    <button
                        wire:click="loadMore"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md text-sm disabled:opacity-50"
                        data-test="latest-registered-users-show-more-btn"
                    >
                        <span wire:loading.remove wire:target="loadMore">Show more</span>
                        <span wire:loading wire:target="loadMore">Loading...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
