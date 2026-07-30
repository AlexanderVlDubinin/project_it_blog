<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('List of Posts') }}
            </h2>

            <a href="{{ route('posts.create') }}">
                <button class="flex items-center justify-between text-gray-800 dark:text-gray-200 bg-indigo-500 rounded-lg px-4 py-2 button-back cursor-pointer">
                    <span>New Post</span>
                </button>
            </a>
        </div>
    </x-slot>

    <div class="py-12 text-gray-800 dark:text-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('posts.index') }}" method="GET" class="flex w-full gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search (ID, title, content)"
                    class="flex-1 border border-border bg-white dark:bg-gray-800 rounded-lg px-4 py-2">
                <button type="submit" class="border border-border border-gray-700 dark:border-gray-300 rounded-lg px-4 py-2 button-back">Search</button>
            </form>

            <div class="grid grid-cols-2">
                @forelse ($posts as $post)
                    <div class="p-2">
                        <div class="mt-4 border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-800 rounded-lg px-4 py-2 h-full flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between w-full items-start">
                                    <h2 class="mt-6 text-head text-indigo-400 font-bold text-4xl underline">
                                        <a href="/posts/{{ $post->id }}">{{ $post->title }}</a>
                                    </h2>

                                    <div class="hidden sm:flex sm:items-center sm:ms-2 mt-6">
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

                                                    <x-dropdown-link :href="route('posts.destroy', $post)"
                                                                     onclick="
                                                                         event.preventDefault();
                                                                         if (confirm('Are you sure you want to delete this post?')) {
                                                                             document.getElementById('remove-post-form_{{$post->id}}').submit();
                                                                         }
                                                                     ">
                                                        <button form="remove-post-form_{{$post->id}}" class="mt-1 w-full cursor-pointer flex inline-flex text-nowrap">
                                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                            <span class="w-full">Delete Post</span>
                                                        </button>
                                                    </x-dropdown-link>

                                                    <form id="remove-post-form_{{$post->id}}"
                                                          action="{{ route('posts.destroy', $post) }}"
                                                          method="POST"
                                                          class="hidden">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @else
                                                    <x-dropdown-link :href="route('register')">
                                                        {{ __('Register') }}
                                                    </x-dropdown-link>

                                                    <x-dropdown-link :href="route('login')">
                                                        {{ __('Log In') }}
                                                    </x-dropdown-link>
                                                @endauth
                                            </x-slot>
                                        </x-dropdown>
                                    </div>
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
                            <div class="flex items-center justify-between w-full text-gray-600 dark:text-gray-400">
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
                        </div>
                    </div>
                @empty
                    <h2 class="text-4xl font-bold text-indigo-600 flex item-center">
                        No posts at this time
                    </h2>
                @endforelse
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
