<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Post') }}
        </h2>
    </x-slot>

    <div class="py-12 text-gray-800 dark:text-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('posts.index') }}">
                <button class="flex items-center justify-between border border-border border-gray-700 dark:border-gray-300 rounded-lg px-4 py-2 button-back cursor-pointer">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6 mr-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <span>Return To All Posts</span>
                </button>
            </a>

            <h1 class="mt-6 text-head text-indigo-400 font-bold text-4xl">
                {{ $post->title }}
            </h1>

            <div class="mt-4 w-full gap-4 text-gray-600 dark:text-gray-400">
                <b>Authored by</b>
                <i>{{ $post->user->name }} ({{ $post->user->email }})</i>
            </div>
            <div class="w-full gap-4 text-gray-600 dark:text-gray-400">
                <b>Published on</b>
                <i>{{ $post->created_at->format('Y-m-d H:i:s') }}</i>
                (<i>{{ $post->created_at->diffForHumans() }}</i>)
            </div>

            @if ($post->image)
                <div class="mt-4 space-y-2">
                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}"
                         class="w-full h-auto object-cover rounded-lg">
                </div>
            @endif

            {{-- raw text with html --}}
            {{-- nl2br --- \n ---> <br/>, e - safe text (htmlentities) --}}
            <div class="mt-5">{!! nl2br(e($post->content)) !!}</div>
        </div>
    </div>
</x-app-layout>
