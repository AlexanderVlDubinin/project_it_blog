<div class="admin-moderation-zone {{ $comment->is_deleted ? 'py-1' : 'mt-2.5 p-2.5' }}  /*border border-dashed border-gray-500 rounded*/">
    @if($comment->is_deleted)
        {{-- Restore --}}
        <x-dropdown-link href="#"
                         data-url="{{ route('admin.comments.restore', $comment->id) }}"
                         @click.prevent="$dispatch('set-comment-restore-url', $el.dataset.url)">
            <div class="flex items-center justify-between w-full text-nowrap">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-2 size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span class="w-full">Restore Comment</span>
            </div>
        </x-dropdown-link>

        {{-- Delete --}}
        <x-dropdown-link href="#"
                         data-url="{{ route('admin.comments.destroy', $comment) }}"
                         @click.prevent="if (confirm('Are you sure you want to permanently delete this comment with all its descendants?')) { $dispatch('set-comment-delete-url', $el.dataset.url) }">
            <div class="flex items-center w-full text-nowrap">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <span class="w-full">Complete Delete</span>
            </div>
        </x-dropdown-link>
    @else
        {{-- SoftDelete form --}}
        <form action="{{ route('admin.comments.delete', $comment) }}" method="POST" class="m-0 {{ $comment->is_deleted }}">
            @csrf
            @method('PUT')

            <div class="flex gap-2.5 items-center flex-wrap">
                <!-- The reason selection selector -->
                <div class="flex-1 w-[50vw] min-w-[200px]">
                    <select
                        name="reason_key"
                        class="w-full py-1 px-2 border border-solid border-[#cbd5e0] bg-white dark:bg-gray-800 rounded text-[14px]"
                        onchange="toggleCustomReasonInput(this, {{ $comment->id }})"
                        required
                    >
                        <option value="">-- Reason for deletion --</option>
                        @foreach(\App\Enum\CommentDeletionReason::labels() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Confirmation button -->
                <button type="submit" class="bg-red-700 text-white border-none py-2 px-3 rounded cursor-pointer text-sm">
                    Delete comment
                </button>
            </div>

            <!-- Hidden field for manual input (shown when "other" is selected) -->
            <div id="custom_reason_container_{{ $comment->id }}" class="hidden mt-2">
                <input
                    type="text"
                    id="custom_reason_input_{{ $comment->id }}"
                    name="custom_reason"
                    placeholder="Specify your reason for deletion..."
                    class="w-full py-1 px-2 border border-gray-300 bg-white dark:bg-gray-800 rounded text-sm"
                >
            </div>
        </form>

        <script>
            /**
             * Shows or hides the text field for manually entering the reason.
             */
            function toggleCustomReasonInput(selectElement, commentId) {
                const container = document.getElementById(`custom_reason_container_${commentId}`);
                const input = document.getElementById(`custom_reason_input_${commentId}`);

                if (!container || !input) return;

                console.log(selectElement.value);

                if (selectElement.value === 'other') {
                    console.log(selectElement.value);
                    container.classList.remove('hidden');
                    //container.style.display = 'block';
                    input.setAttribute('required', 'required');
                    input.focus();
                } else {
                    container.classList.add('hidden');
                    //container.style.display = 'none';
                    input.removeAttribute('required');
                    input.value = ''; // Resetting the text when switching
                }
            }
        </script>
    @endif
</div>
