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
                <b>Authored by: </b>
                <i>{{ $post->user->name }} ({{ $post->user->email }})</i>
            </div>
            <div class="w-full gap-4 text-gray-600 dark:text-gray-400">
                <b>Created at: </b>
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

            <hr class="mt-8"/>

            {{-- COMMENTS --}}
            <div class="comments-section my-4">
                <h3>Comments ({{ $post->comments->count() }})</h3>

                <div class="mt-4 main-comment-form-container border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-800 rounded-lg px-4 py-2 h-full flex flex-col justify-between" >
                    <!-- Dynamic form header -->
                    <h3 id="form_title">Please comment on this post</h3>

                    <!-- Information bar, to whom reply (initially hidden) -->
                    <div id="reply_target_info" class="hidden text-gray-600 dark:text-gray-400">
                        You reply to the user: <strong id="reply_author_name"></strong>
                    </div>

                    <form action="{{ route('comments.store', $post) }}" method="POST" id="global_comment_form">
                        @csrf

                        <!-- This field will be activated via JS only when editing -->
                        <input type="hidden" name="_method" id="form_method" value="POST" disabled>
                        <!-- Hidden field for the parent's ID (null/empty by default) -->
                        <input type="hidden" name="parent_id" id="form_parent_id" value="">

                        <div class="mb-2.5">
                            <textarea
                                id="form_body"
                                name="body"
                                rows="4"
                                class="border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-800 rounded-lg w-full p-2"
                                placeholder="Write a text..."
                                required
                            >{{ old('body') }}</textarea>
                        </div>

                        <div class="flex gap-2.5">
                            <!-- The main send button -->
                            <button type="submit" id="form_submit_btn" class="flex items-center justify-between text-gray-800 dark:text-gray-200 bg-indigo-500 rounded-lg px-4 py-2 button-back cursor-pointer">
                                Send comment
                            </button>

                            <!-- Cancel button (initially hidden) -->
                            <button type="button" id="form_cancel_btn" onclick="resetCommentForm()" class="hidden bg-[#e53e3e] text-white border-none px-4 py-2 rounded cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <hr class="my-8"/>

                <!-- Tree output -->
                @if($post->comments->count() > 0)
                    <div class="comments-tree">
                        @foreach($post->comments as $comment)
                            @include('partials.comment_item', ['comment' => $comment])
                        @endforeach

                        {{-- Restore form --}}
                        <form id="comment-restore-form"
                              x-data="{ action: '' }"
                              :action="action"
                              method="POST"
                              class="hidden"
                              @set-comment-restore-url.window="action = $event.detail; $nextTick(() => $el.submit())">
                            @csrf
                            @method('PUT')
                        </form>

                        {{-- Delete form --}}
                        <form id="comment-delete-form"
                              x-data="{ action: '' }"
                              :action="action"
                              method="POST"
                              class="hidden"
                              @set-comment-delete-url.window="action = $event.detail; $nextTick(() => $el.submit())">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>

                    <script>
                        // Saving the base URL for creating comments so that we can return to it when resetting
                        const BASE_STORE_URL = "{{ route('comments.store', $post) }}";
                        // Generating an update route template on the server.
                        // The output in JS will be a string like: "/comments/COMMENT_ID"
                        const BASE_UPDATE_URL_TEMPLATE = "{{ route('comments.update', ['comment' => 'COMMENT_ID']) }}";

                        /**
                         * Switches the main form to comment response mode.
                         * @param {number} commentId - ID of the parent comment
                         * @param {string} authorName - The name of the author being answered
                         */
                        function prepareReply(commentId, authorName) {
                            const formTitle = document.getElementById('form_title');
                            const parentIdInput = document.getElementById('form_parent_id');
                            const submitBtn = document.getElementById('form_submit_btn');
                            const cancelBtn = document.getElementById('form_cancel_btn');
                            const targetInfo = document.getElementById('reply_target_info');
                            const authorNameSpan = document.getElementById('reply_author_name');
                            const textarea = document.getElementById('form_body');

                            // 1. Changing the texts and headings
                            formTitle.innerText = 'Write a reply to the comment';
                            authorNameSpan.innerText = authorName;
                            submitBtn.innerText = 'Reply';
                            textarea.value = '';

                            // 2. Writing the parent's ID in the hidden field
                            parentIdInput.value = commentId;

                            // 3. Show the bar with the info and the Cancel button
                            targetInfo.classList.remove('hidden');
                            cancelBtn.classList.remove('hidden');

                            // 4. Scroll to the form and put the focus on the text field
                            focusAndScrollForm();
                        }

                        /**
                         * Editing own comment
                         */
                        function prepareEdit(commentId, currentBody) {
                            resetCommentFormFields(); // Pre-reset

                            const form = document.getElementById('global_comment_form');
                            const methodInput = document.getElementById('form_method');
                            const cancelBtn = document.getElementById('form_cancel_btn');

                            // 1. Changing the visual
                            document.getElementById('form_title').innerText = 'Edit your comment';
                            document.getElementById('form_submit_btn').innerText = 'Save changes';
                            cancelBtn.classList.remove('hidden');

                            // 2. Substituting the old text in the textarea
                            document.getElementById('form_body').value = currentBody;

                            // 3. DYNAMIC ROUTE: Changing the COMMENT_ID stub to the real comment ID
                            form.action = BASE_UPDATE_URL_TEMPLATE.replace('COMMENT_ID', commentId);

                            // 4. Enabling the field of the PUT method so that Laravel understands the request.
                            methodInput.value = 'PUT';
                            methodInput.removeAttribute('disabled');

                            focusAndScrollForm();
                        }

                        /**
                         * Complete reset of the form to its original state (Creating a new comment)
                         */
                        function resetCommentForm() {
                            resetCommentFormFields();

                            // Returning the default texts
                            document.getElementById('form_title').innerText = 'Please comment on this post';
                            document.getElementById('form_submit_btn').innerText = 'Send comment';
                        }

                        /**
                         * Auxiliary technical reset of all fields
                         */
                        function resetCommentFormFields() {
                            const form = document.getElementById('global_comment_form');
                            const methodInput = document.getElementById('form_method');

                            form.action = BASE_STORE_URL;
                            methodInput.value = 'POST';
                            methodInput.setAttribute('disabled', 'disabled'); // Disabling the PUT method

                            document.getElementById('form_parent_id').value = '';
                            document.getElementById('form_body').value = '';

                            document.getElementById('reply_target_info').classList.add('hidden');
                            document.getElementById('form_cancel_btn').classList.add('hidden');
                        }

                        function focusAndScrollForm() {
                            const textarea = document.getElementById('form_body');
                            textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            textarea.focus();
                        }
                    </script>
                @else
                    <h2 class="text-4xl font-bold text-indigo-600 flex item-center">
                        No comments yet.
                    </h2>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
