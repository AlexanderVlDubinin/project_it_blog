<div class="comment-card {{ $comment->is_deleted ? 'deleted-muted' : '' }} mt-4 mb-4 {{ $comment->parent_id ? 'ml-7.5' : '' }} border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-800 rounded-lg px-4 py-2 h-full flex flex-col justify-between">

    <div class="comment-header">
        <div class="flex items-center justify-between w-full">
            <div>
                <!-- user?->name will return "Anonymous" if the user is deleted from the database -->
                <strong class="{{ $comment->is_deleted ? 'text-gray-600 dark:text-gray-400' : 'text-indigo-400' }}">
                    {{ $comment->user?->name ?? 'Anonymous' }} {{-- $comment->is_deleted ? 'Moderator' --}}
                </strong>
                <small class="text-gray-600 dark:text-gray-400">
                    {{ $comment->created_at->format('Y-m-d H:i:s') }} ({{ $comment->created_at->diffForHumans() }})
                </small>
            </div>

            <div class="mt-2 flex items-center justify-between w-auto">
                <!-- The answer is possible only on live comments -->
                @if(!$comment->is_deleted && auth()->check() && $comment->user_id)
                    @if($comment->user_id === auth()->id())
                        <button
                            type="button"
                            onclick="prepareEdit({{ $comment->id }}, '{{ addslashes($comment->getRawOriginal('body')) }}')"
                            class="btn-link border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-sky-900 rounded-lg px-2 py-0.5 cursor-pointer"
                        >
                            Edit
                        </button>
                    @else
                        <button
                            type="button"
                            onclick="prepareReply({{ $comment->id }}, '{{ $comment->user?->name ?? 'Anonymous' }}')"
                            class="btn-link border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-700 rounded-lg px-2 py-0.5 cursor-pointer"
                        >
                            Reply
                        </button>
                    @endif
                @endif

                @can('manage-site')
                    <div class="sm:flex sm:items-center sm:ms-2 ">
                        <x-dropdown align="right" width="auto" modal="true">
                            <x-slot name="trigger">
                                <button class="border border-border border-gray-700 dark:border-gray-300 rounded-lg inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <svg class="fill-current h-5 w-5" viewBox="0 0 20 20">
                                        <path d="M5 7h10l-5 6z" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <!-- The deletion form for the admin (shown only to the admin) -->
                                @include('partials.admin_moderate_form', ['comment' => $comment])
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Automatically outputs a stub with the reason if is_deleted = true -->
    <div class="comment-body mt-4">
        <p class="{{ $comment->is_deleted ? 'text-gray-600 dark:text-gray-400' : '' }}">
            {{ $comment->body }}
        </p>
    </div>

    <!-- RECURSION: The output of the children of the current comment -->
    @if($comment->children->isNotEmpty())
        <div class="comment-replies">
            @foreach($comment->children as $child)
                @include('partials.comment_item', ['comment' => $child])
            @endforeach
        </div>
    @endif
</div>
