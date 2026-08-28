<div id="comment-{{ $comment->id }}" class="comment-card {{ $comment->is_deleted ? 'deleted-muted' : '' }} mt-4 mb-4 {{ $comment->parent_id ? 'ml-7.5' : '' }} border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-800 rounded-lg px-4 py-2 h-full flex flex-col justify-between">

    <div class="comment-header">
        <div class="flex items-center justify-between w-full">
            <div>
                <!-- user?->name will return "Anonymous" if the user is deleted from the database -->
                <strong class="{{ $comment->is_deleted ? 'text-gray-600 dark:text-gray-400' : 'text-indigo-400' }}">
                    {{ $comment->user?->name }} {{-- $comment->is_deleted ? 'Moderator' --}}
                </strong>
                <small class="text-gray-600 dark:text-gray-400">
                    {{ $comment->created_at->format('Y-m-d H:i:s') }} ({{ $comment->created_at->diffForHumans() }})
                </small>
            </div>

            <div class="mt-2 flex items-center justify-between w-auto">
                @if(!$comment->is_deleted && $comment->user_id !== auth()->id())
                <div class="mr-4 reaction-block comment-reaction-block flex items-center gap-4 border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-800 rounded-lg px-2 py-1 text-gray-500 text-sm select-none" data-type="comment" data-id="{{ $comment->id }}">
                    @php
                        $likes = $comment->likes_count ?? 0;
                        $dislikes = $comment->dislikes_count ?? 0;
                        $rating = $likes - $dislikes;

                        $hasLiked = $comment->userReaction?->is_like === true;
                        $hasDisliked = $comment->userReaction?->is_like === false;
                    @endphp
                    <!-- Like button -->
                    <button type="button"
                            class="js-reaction-btn flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-green-600 {{ $hasLiked ? 'text-green-600' : '' }}"
                            data-is-like="1">
                        <!-- Contour thumbs up -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="js-icon-outline {{ $hasLiked ? 'hidden' : '' }}">
                            <path d="M7 10v12" /><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z" />
                        </svg>
                        <!-- Filled thumbs up -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="js-icon-solid text-green-600 {{ $hasLiked ? '' : 'hidden' }}">
                            <path d="M7 10v12" /><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z" />
                        </svg>
                        <span class="js-likes-count">{{ $comment->likes_count ?? 0 }}</span>
                    </button>

                    <!-- Total score (Rating) -->
                    <span class="js-comment-rating font-bold text-sm min-w-4 text-center {{ $rating > 0 ? 'text-green-600' : ($rating < 0 ? 'text-red-500' : 'text-gray-400') }}">
                        {{ $rating > 0 ? '+' . $rating : $rating }}
                    </span>

                    <!-- Dislike button -->
                    <button type="button"
                            class="js-reaction-btn flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-red-600 {{ $hasDisliked ? 'text-red-600' : '' }}"
                            data-is-like="0">
                        <span class="js-dislikes-count">{{ $comment->dislikes_count ?? 0 }}</span>
                        <!-- Contour thumbs down -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="js-icon-outline {{ $hasDisliked ? 'hidden' : '' }}">
                            <path d="M17 14V2" /><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22h0a3.13 3.13 0 0 1-3-3.88Z" />
                        </svg>
                        <!-- Filled thumbs down -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="js-icon-solid text-red-600 {{ $hasDisliked ? '' : 'hidden' }}">
                            <path d="M17 14V2" /><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22h0a3.13 3.13 0 0 1-3-3.88Z" />
                        </svg>
                    </button>
                </div>
                @endif

                <!-- The answer is possible only on live comments -->
                @if(!$comment->is_deleted && auth()->check() /*&& $comment->user_id*/)
                    @if($comment->user_id === auth()->id())
                        <button
                            type="button"
                            onclick="prepareEdit({{ $comment->id }}, '{{ addslashes($comment->getRawOriginal('body')) }}')"
                            data-test="btn-edit-comment-{{ $comment->id }}"
                            class="btn-link border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-sky-900 rounded-lg px-2 py-0.5 cursor-pointer"
                        >
                            Edit
                        </button>
                    @else
                        <button
                            type="button"
                            onclick="prepareReply({{ $comment->id }}, '{{ $comment->user?->name }}')"
                            data-test="btn-reply-comment-{{ $comment->id }}"
                            class="btn-link border border-border border-gray-700 dark:border-gray-300 bg-white dark:bg-gray-700 rounded-lg px-2 py-0.5 cursor-pointer"
                        >
                            Reply
                        </button>
                    @endif
                @endif

                @can('change-comment-action', $comment)
                    @if($comment->is_deleted && $comment->user_id === auth()->id())
                        {{-- DO NOT SHOW ANYTHING --}}
                    @else
                    <div class="sm:flex sm:items-center sm:ms-2 ">
                        <x-dropdown align="right" width="auto" modal="true">
                            <x-slot name="trigger">
                                <button data-test="admin-actions-trigger-btn-{{ $comment->id }}" class="border border-border border-gray-700 dark:border-gray-300 rounded-lg inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
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
                    @endif
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
