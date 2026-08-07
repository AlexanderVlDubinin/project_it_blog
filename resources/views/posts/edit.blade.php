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

                <div class="mb-4 mt-4">
                    <label class="block text-sm font-medium mb-2">Tags (maximum 7)</label>

                    {{-- Поле ввода с подсказками из datalist --}}
                    <div class="flex gap-2 mb-3">
                        <input type="text" id="tag-input" list="existing-tags" placeholder="Enter the tags (separated by commas) and click Add"
                               class="rounded-xl border-gray-300 bg-white dark:bg-gray-800 flex-1 text-sm shadow-sm">
                        <button type="button" id="add-tag-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl transition shadow-sm">
                            Add
                        </button>
                    </div>

                    {{-- A list of existing tags in the database for auto-completion --}}
                    <datalist id="existing-tags">
                        {{--
                        @foreach(\App\Models\Tag::all() as $tag)
                            <option value="{{ $tag->name }}"></option>
                        @endforeach
                        --}}
                    </datalist>

                    {{-- Container for displaying selected badges --}}
                    <div id="tags-container" class="flex flex-wrap gap-2 min-h-8 p-2 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-200">
                        @if(old('tags'))
                            @foreach(old('tags') as $tagName)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-semibold">
                                    {{ $tagName }}
                                    <button type="button" onclick="this.parentElement.remove()" class="hover:text-red-600 font-bold">×</button>
                                    <input type="hidden" name="tags[]" value="{{ $tagName }}">
                                </span>
                            @endforeach
                        @elseif(isset($post) && $post->tags->isNotEmpty())
                            @foreach($post->tags as $tag)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-semibold">
                                    {{ $tag->name }}
                                    <button type="button" onclick="this.parentElement.remove()" class="hover:text-red-600 font-bold">×</button>
                                    <input type="hidden" name="tags[]" value="{{ $tag->name }}">
                                </span>
                            @endforeach
                        @endif
                    </div>

                    @error('tags')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-2 mt-4 flex items-center justify-between w-full gap-4">
                    @error('is_published')<p class="error">{{ $message }}</p>@enderror
                    <div class="inline-flex rounded-lg bg-gray-400 p-1 border border-gray-200">
                        <label for="is_published1" class="relative block cursor-pointer select-none">
                            <input type="radio" id="is_published1" name="is_published" value="1" class="peer sr-only" {{ old('is_published', $post->is_published) == '1' ? 'checked' : '' }}>
                            <span class="block px-6 py-2 text-sm font-medium text-gray-600 rounded-md transition-all duration-200 peer-checked:bg-primary peer-checked:text-white peer-checked:shadow-md hover:text-gray-900">
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
                        <button type="submit" class="px-8 py-4 text-md font-semibold text-white bg-primary rounded-xl cursor-pointer border border-gray-200">
                            {{ $isEdit ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>

                <script>
                    function addTags() {
                        const input = document.getElementById('tag-input');
                        const container = document.getElementById('tags-container');

                        const tagsArray = input.value.split(',');

                        tagsArray.forEach(function(item) {
                            const tagText = item.trim().toLowerCase();

                            if (!tagText) return;

                            // Checking the limit of 7 tags
                            if (container.querySelectorAll('span').length >= 7) {
                                alert('7 tags maximum!');
                                return;
                            }

                            // Checking for duplicates among those already added to the page.
                            const existingValues = Array.from(container.querySelectorAll('input[type="hidden"]')).map(i => i.value);
                            if (existingValues.includes(tagText)) {
                                return;
                            }

                            // Creating a beautiful badge
                            const badge = document.createElement('span');
                            badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold";
                            badge.innerHTML = `
                                ${tagText}
                                <button type="button" class="hover:text-red-600 font-bold">×</button>
                                <input type="hidden" name="tags[]" value="${tagText}">
                            `;

                            // Deleting by clicking on a cross
                            badge.querySelector('button').addEventListener('click', () => badge.remove());

                            container.appendChild(badge);
                        });

                        input.value = '';
                    }

                    document.getElementById('add-tag-btn').addEventListener('click', addTags);

                    document.getElementById('tag-input').addEventListener('keydown', function(event) {
                        if (event.key === 'Enter') {
                            event.preventDefault(); // Blocking the sending of the entire form by pressing Enter
                            addTags(); // Calling the addition of tags
                        }
                    });

                    const bigDatabase = [];
                    @foreach(\App\Models\Tag::all() as $tag)
                        bigDatabase.push('{{ $tag->name }}');
                    @endforeach

                    const input = document.getElementById('tag-input');
                    const datalist = document.getElementById('existing-tags');

                    input.addEventListener('input', (e) => {
                        const query = e.target.value.toLowerCase();
                        datalist.innerHTML = ''; // Очищаем старые варианты

                        if (query.length < 2) return; // Ищем только от 2 символов

                        // Фильтруем и ограничиваем результат первыми 5 совпадениями
                        const filtered = bigDatabase
                            .filter(item => item.toLowerCase().includes(query))
                            .slice(0, 5);

                        // Добавляем отфильтрованные подсказки в datalist
                        filtered.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item;
                            datalist.appendChild(option);
                        });
                    });
                </script>
            </form>
        </div>
    </div>
</x-app-layout>
