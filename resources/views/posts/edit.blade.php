@php
    $isEdit = $post->exists;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __($isEdit ? 'Edit Post' : 'Create Post') }}
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

            <form action="{{ $isEdit ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($isEdit)
                    @method('PATCH')
                @endif

                <div class="space-y-2 mt-4">
                    <label for="title" class="label">Title</label>
                    @error('title')<p class="error">{{ $message }}</p>@enderror
                    <input
                        type="text"
                        class="input flex-1 border border-border bg-white dark:bg-gray-800 rounded-lg w-full" id="title"
                        name="title"
                        value="{{ old('title', $post->title) }}"
                        placeholder="Enter title"
                        required
                    />
                </div>

                <div class="space-y-2 mt-4">
                    <label for="content" class="label">Content</label>
                    @error('content')<p class="error">{{ $message }}</p>@enderror
                    <textarea
                        name="content"
                        id="content"
                        class="textarea flex-1 border border-border bg-white dark:bg-gray-800 rounded-lg w-full h-100"
                        placeholder="Enter the content of the post"
                        required
                    >{{ old('content', $post->content) ?? '' }}</textarea>
                </div>

                <!-- A small script so that when you select a file, the text changes to the name of the selected file. -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const fileInput = document.getElementById('image_upload');
                        const statusText = document.getElementById('file_status');

                        if (fileInput && statusText) {
                            fileInput.addEventListener('change', function () {
                                // Inside the input event handler.files[0] contains information about the file
                                if (this.files && this.files.length > 0) {
                                    const fileName = this.files[0].name;
                                    statusText.innerHTML = `<span class="text-green-600 font-bold">The file is selected: </span> ${fileName}`;
                                } else {
                                    statusText.innerHTML = `<span class="text-blue-600 hover:underline">Select an image</span> or drag it here`;
                                }
                            });
                        }
                    });
                </script>

                <div class="space-y-2 mt-4">
                    <label for="image" class="label">Post Image</label>
                    @error('image')<p class="error">{{ $message }}</p>@enderror
                    @if ($isEdit && $post->image)
                        <div class="space-y-2">
                            {{-- getImageUrlAttribute() makes it equal to {{ asset('storage/' . $post->image) }} --}}
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}"
                                 class="w-full h-auto object-cover rounded-lg">

                            <div class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="remove_image" value="1" {{ old('remove_image') ? 'checked' : '' }} class="rounded"/>
                                Remove Image
                            </div>
                        </div>
                    @endif

                    <div class="relative">
                        <!-- A hidden input that covers the entire click area for accessibility -->
                        <input type="file"
                               id="image_upload"
                               name="image"
                               accept="image/*"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        >

                        <!-- A visual custom block (Label) that is stylized -->
                        <label for="image_upload"
                               class="flex flex-col items-center justify-center w-full h-32 p-4 border-2 border-dashed border-gray-300 rounded-xl bg-white dark:bg-gray-800 transition-colors text-center">

                            <!-- Large bold download icon (24px, stroke-2.5) -->
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8 text-gray-400 mb-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>

                            <!-- Hint text -->
                            <p id="file_status" class="text-sm font-semibold text-gray-500">
                                <span class="text-blue-600 hover:underline">Select an image</span> or drag it here
                            </p>
                            <p class="text-xs text-gray-400 mt-1">PNG, JPG, JPEG, WEBP, GIF up to 2MB</p>
                        </label>
                    </div>
                </div>

                <div class="space-y-2 mt-4 flex items-center justify-between w-full gap-4">
                    @error('is_published')<p class="error">{{ $message }}</p>@enderror
                    <div class="inline-flex rounded-lg bg-gray-400 p-1 border border-gray-200">
                        <label for="is_published1" class="relative block cursor-pointer select-none">
                            <input type="radio" id="is_published1" name="is_published" value="1" class="peer sr-only" {{ old('is_published', $post->is_published) == '1' ? 'checked' : '' }}>
                            <span class="block px-6 py-2 text-sm font-medium text-gray-600 rounded-md transition-all duration-200 peer-checked:bg-[oklch(0.65_0.15_160)] peer-checked:text-white peer-checked:shadow-md hover:text-gray-900">
                        Publish
                    </span>
                        </label>

                        <label for="is_published0" class="relative block cursor-pointer select-none">
                            <input type="radio" id="is_published0" name="is_published" value="0" class="peer sr-only" {{ old('is_published', $post->is_published ?? '0') == '0' ? 'checked' : '' }}>
                            <span class="block px-6 py-2 text-sm font-medium text-gray-600 rounded-md transition-all duration-200 peer-checked:bg-[oklch(0.45_0.25_25)] peer-checked:text-white peer-checked:shadow-md hover:text-gray-900">
                        Unpublish
                    </span>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="px-8 py-4 text-md font-semibold text-white bg-[oklch(0.65_0.15_160)] rounded-xl cursor-pointer border border-gray-200">
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
