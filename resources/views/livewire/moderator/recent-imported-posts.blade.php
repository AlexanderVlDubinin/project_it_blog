<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-white">
                        Last imported posts:
                    </h2>
                </div>
            </div>

            <div class="overflow-x-auto my-6 w-full">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-t border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Post title</th>
                        <th class="px-4 py-2 text-center">Likes</th>
                        <th class="px-4 py-2 text-center">Image</th>
                        <th class="px-4 py-2 text-center">Is published</th>
                        <th class="px-4 py-2 text-center">Created at</th>
                        <th class="px-4 py-2 text-center">Deleted at</th>
                        <th class="px-4 py-2 text-left">Source</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentImportedPosts as $index => $post)
                        <tr class="border-b border-t border-gray-600 /*hover:bg-gray-700*/">
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $post->id }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <a href="{{ route('posts.show', $post->id) }}" class="hover:underline" target="_blank">
                                            {{ $post->title }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="flex items-center justify-center">
                                        <span class="inline-flex items-center gap-x-1 rounded-md bg-red-50 px-2 py-1 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-400/10 dark:text-red-200 dark:ring-red-400/20">
                                            <svg aria-hidden="true" class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"></path>
                                            </svg>
                                            {{ $post->likes_count }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="flex shrink-0 justify-center">
                                        <img class="h-10 w-10 rounded-lg object-cover object-center ring-1 ring-gray-950/5 dark:ring-white/10"
                                             alt="{{ $post->title }}"
                                             src="{{ $post->image_url }}">
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="flex items-center justify-center">
                                        @if($post->is_published)
                                            <svg class="h-6 w-6 shrink-0 text-emerald-600 dark:text-emerald-500"
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5"
                                                 stroke="currentColor"
                                                 aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                            </svg>
                                            <span class="sr-only">Yes</span>
                                        @else
                                            <svg class="h-6 w-6 shrink-0 text-red-600 dark:text-red-500"
                                                 xmlns="http://w3.org"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5"
                                                 stroke="currentColor"
                                                 aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                            </svg>
                                            <span class="sr-only">No</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $post->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $post->deleted_at?->format('d.m.Y H:i') ?? '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $post->source_type }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex flex-col justify-center">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $post]) }}" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-border border-blue-400 text-blue-700 bg-blue-50 hover:bg-blue-200 transition">
                                            Moderate
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="border-b border-t border-gray-600 px-4 py-4 text-center text-gray-500 w-full">
                                No imported posts.
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
                        Total: {{ $recentImportedPostsCount }}
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
