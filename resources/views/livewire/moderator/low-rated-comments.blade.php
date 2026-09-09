<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-white">
                        Low-rated comments:
                    </h2>
                </div>
            </div>

            <div class="overflow-x-auto my-6 w-full">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-t border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                        <th class="w-1/100 px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Comment</th>
                        <th class="px-4 py-2 text-center">Rating</th>
                        <th class="px-4 py-2 text-left">Author</th>
                        {{--
                        <th class="px-4 py-2 text-left">Post</th>
                        --}}
                        <th class="px-4 py-2 text-center">Created at</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($lowRatedComments as $index => $comment)
                        <tr class="border-b border-t border-gray-600 /*hover:bg-gray-700*/">
                            <td class="w-1/100 px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ Str::words($comment->body, 10) }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <span class="text-red-500">
                                            {{ $comment->likes_count - $comment->dislikes_count }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $comment->user?->name ?? 'Anonymous' }}
                                    </div>
                                </div>
                            </td>
                            {{--
                            <td class="px-4 py-3 align-middle">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $comment->post?->title }}
                                    </div>
                                </div>
                            </td>
                            --}}
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $comment->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </td>
                            @php
                                $isPostDeleted = !is_null($comment->post?->deleted_at);
                            @endphp
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <a href="{{ $isPostDeleted ? \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $comment->post]) : $comment->page_url }}" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-border border-blue-400 text-blue-700 bg-blue-50 hover:bg-blue-200 transition">
                                            {{ $isPostDeleted ? 'Moderate' : 'Open' }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border border-gray-300 px-4 py-4 text-center text-gray-500">
                                There are no low-rated comments at the moment.
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
                        Total: {{ $lowRatedCommentsCount }}
                    </span>
                    <button
                        wire:click="loadMore"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md text-sm disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="loadMore">Show more</span>
                        <span wire:loading wire:target="loadMore">Loading...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
