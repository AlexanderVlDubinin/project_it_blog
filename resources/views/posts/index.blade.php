<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('List of Posts') }}
            </h2>

            @can('can-be-author')
                <a href="{{ route('posts.create') }}">
                    <button class="flex items-center justify-between text-gray-800 dark:text-gray-200 bg-indigo-500 rounded-lg px-4 py-2 button-back cursor-pointer">
                        <span>New Post</span>
                    </button>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12 text-gray-800 dark:text-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('posts.index') }}" method="GET" class="flex w-full gap-2">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full items-start">
                    <div class="flex flex-col gap-1 md:col-span-4">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Search by</label>
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="ID, title, content"
                            class="w-full border border-border bg-white dark:bg-gray-800 rounded-lg px-4 py-2 h-9.5">

                        @error('q')
                        <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Tag</label>
                        <select name="tag_id" class="w-full border border-border bg-white dark:bg-gray-800 rounded-lg px-4 py-2 text-sm h-9.5">
                            <option value="">All Tags</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ request('user_id') == $tag->id ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('tag_id')
                        <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Author</label>
                        <select name="user_id" class="w-full border border-border bg-white dark:bg-gray-800 rounded-lg px-4 py-2 text-sm h-9.5">
                            <option value="">All Authors</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ request('user_id') == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }} ({{ $author->email }})
                                </option>
                            @endforeach
                        </select>

                        @error('user_id')
                        <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Date from</label>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            placeholder="Select date from..."
                            class="w-full border border-border bg-white dark:bg-gray-800 rounded-lg px-4 py-2 text-sm">

                        @error('date_from')
                        <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Date to</label>
                        <input
                            type="date"
                            name="date_to"
                            value="{{ request('date_to') }}"
                            placeholder="Select date to..."
                            class="w-full border border-border bg-white dark:bg-gray-800 rounded-lg px-4 py-2 text-sm">

                        @error('date_to')
                        <span class="text-xs text-red-500 mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="{{ request()->hasAny(['user_id']) }} flex flex-col">
                    <button type="submit" class="max-h-9.5 mt-5 border border-border border-gray-700 dark:border-gray-300 rounded-lg px-4 py-2 button-back cursor-pointer">Search</button>

                    @if(request()->filled('q') || request()->filled('user_id') || request()->filled('date_from') || request()->filled('date_to') || request()->filled('tag_id')/*request()->hasAny(['q', 'user_id', 'date_from', 'date_to'])*/)
                        <a href="{{ route('posts.index') }}"
                           class="mt-7.5 border border-red-500 text-red-500 rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-50 dark:hover:bg-gray-800 flex items-center transition-colors cursor-pointer">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <div class="grid grid-cols-2">
                @forelse ($posts as $post)
                    <div class="p-2">
                        <div class="mt-4 border border-border {{ $post->is_published ? 'border-gray-700 dark:border-gray-300' : 'border-red-500' }} bg-white dark:bg-gray-800 rounded-lg px-4 py-2 h-full flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between w-full items-start">
                                    <h2 class="mt-6 text-head text-indigo-400 font-bold text-4xl underline">
                                        <a href="/posts/{{ $post->id }}">{{ $post->title }}</a>
                                    </h2>

                                    @canany(['manage-site', 'owner-action'], $post)
                                    <div class="sm:flex sm:items-center sm:ms-2 mt-6">
                                        <x-dropdown align="right" width="auto">
                                            <x-slot name="trigger">
                                                <button class="border border-border border-gray-700 dark:border-gray-300 rounded-lg inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                                    <svg class="fill-current h-6 w-6" viewBox="0 0 20 20">
                                                        <path d="M5 7h10l-5 6z" />
                                                    </svg>
                                                </button>
                                            </x-slot>

                                            <x-slot name="content">
                                                @auth()
                                                    <x-dropdown-link :href="route('posts.edit', $post->id)">
                                                        <div class="flex items-center justify-between w-full">
                                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 mr-2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                            </svg>
                                                            <span class="w-full">Edit Post</span>
                                                        </div>
                                                    </x-dropdown-link>

                                                    <x-dropdown-link href="#"
                                                                     data-url="{{ route('posts.destroy', $post) }}"
                                                                     @click.prevent="if (confirm('Are you sure you want to delete this post?')) { $dispatch('set-post-delete-url', $el.dataset.url) }">
                                                        <div class="flex items-center w-full text-nowrap">
                                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                            <span class="w-full text-left">Delete Post</span>
                                                        </div>
                                                    </x-dropdown-link>
                                                @endauth
                                            </x-slot>
                                        </x-dropdown>
                                    </div>
                                    @endcanany
                                </div>

                                <div class="flex items-center justify-between w-full text-gray-600 dark:text-gray-400 mt-2">
                                    <i>
                                        <b>Authored by: </b>
                                        {{ $post->user->name }} ({{ $post->user->email }})
                                    </i>
                                </div>
                                <div class="flex items-center justify-between w-full text-gray-600 dark:text-gray-400">
                                    <i>
                                        <b>Created at: </b>
                                        {{ $post->created_at->format('Y-m-d H:i:s') }} ({{ $post->created_at->diffForHumans() }})
                                    </i>
                                </div>

                                <div class="mt-4">
                                    <p>{{ Str::words($post->content, 20) }}</p>
                                </div>
                            </div>
                            @if($post->image)
                                <div class="mt-4 flex justify-center w-full">
                                    <img src="{{ $post->image_url }}"
                                         alt="{{ $post->title }}"
                                         class="w-1/2 h-auto object-cover rounded-md">
                                </div>
                            @endif
                            @if($post->comments_count)
                                <div class="flex items-center justify-between w-full text-gray-600 dark:text-gray-400">
                                    <i>
                                        <b>Comments: </b>
                                        {{ $post->total_comments_count }}
                                    </i>
                                </div>
                            @endif
                            @if($post->tags->isNotEmpty())
                                <div class="flex flex-wrap gap-2 min-h-8 p-2 rounded-xl">
                                    <b class="text-gray-600 dark:text-gray-400">Tags: </b>
                                    @foreach ($post->tags as $tag)
                                        <a href="{{ route('posts.index', array_merge(request()->query(), ['tag_id' => $tag->id])) }}">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-semibold">
                                                {{ $tag->name }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <h2 class="text-4xl font-bold text-indigo-600 flex item-center">
                        No posts at this time
                    </h2>
                @endforelse

                <form id="post-delete-form"
                      x-data="{ action: '' }"
                      :action="action"
                      method="POST"
                      class="hidden"
                      @set-post-delete-url.window="action = $event.detail; $nextTick(() => $el.submit())">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            {{-- Pagination --}}
            @if($posts->hasPages())
                <div class="custom-pagination mt-6 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
