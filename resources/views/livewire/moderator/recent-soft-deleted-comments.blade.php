<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-white">
                        Soft deleted comments:
                    </h2>
                </div>
            </div>

            <div class="overflow-x-auto my-6 w-full">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-t border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Comment</th>
                        <th class="px-4 py-2 text-left">Deletion reason</th>
                        <th class="px-4 py-2 text-left">Author</th>
                        {{--
                        <th class="px-4 py-2 text-left">Post</th>
                        <th class="px-4 py-2 text-center">Parent</th>
                        <th class="px-4 py-2 text-center">👍</th>
                        <th class="px-4 py-2 text-center">Rating</th>
                        <th class="px-4 py-2 text-center">👎</th>
                        --}}
                        <th class="px-4 py-2 text-center">Created at</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentSoftDeletedComments as $index => $comment)
                        <tr class="border-b border-t border-gray-600 /*hover:bg-gray-700*/ recent-soft-deleted-comments-tr">
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ Str::words($comment->body, 10) }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $comment->deletion_reason }}
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
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $comment->parent_id ?? '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $comment->likes_count }}
                                    </div>
                                </div>
                            </td>
                            @php
                                $rating = $comment->likes_count - $comment->dislikes_count;
                            @endphp
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm">
                                        @if($rating > 0)
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-1.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                                            {{ $rating }}
                                        </span>
                                        @elseif($rating < 0)
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-1.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20">
                                            {{ $rating }}
                                        </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                            0
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $comment->dislikes_count }}
                                    </div>
                                </div>
                            </td>
                            --}}
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col justify-center">
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
                                        <a href="{{ $isPostDeleted ? \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $comment->post]) : $comment->page_url }}"
                                           data-test="{{ $isPostDeleted ? 'recent-soft-deleted-comments-moderate-post-' . $comment->id . '-btn' : 'recent-soft-deleted-comments-open-post-' . $comment->id . '-btn' }}"  target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-border border-blue-400 text-blue-700 bg-blue-50 hover:bg-blue-200 transition">
                                            {{ $isPostDeleted ? 'Moderate' : 'Open' }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border-b border-t border-gray-600 px-4 py-4 text-center text-gray-500 w-full"> {{-- colspan="11" --}}
                                Soft deleted comments are missing.
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
                        Total: {{ $recentSoftDeletedCommentsCount }}
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
