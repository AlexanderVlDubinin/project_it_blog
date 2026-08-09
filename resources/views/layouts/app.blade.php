<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            @if (session('success'))
                <div id="toast-success" class="fixed top-24 left-1/2 -translate-x-1/2 bg-emerald-600 text-white px-6 py-3 rounded-xl shadow-lg border border-emerald-500/30 z-50 transition-opacity duration-1000 whitespace-nowrap">
                    <span class="text-sm font-semibold">
                        {{ session('success') }}
                    </span>
                </div>
            @endif

            @if (session('error'))
                <div id="toast-error" class="fixed top-24 left-1/2 -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-xl shadow-lg border border-red-500/30 z-50 transition-opacity duration-1000 whitespace-nowrap">
                    <span class="text-sm font-semibold">
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            @if (session('success') || session('error'))
                <script>
                    // Looking for all toasts (both success and error)
                    document.querySelectorAll('[id^="toast-"]').forEach(toast => {
                        setTimeout(() => {
                            toast.classList.add('opacity-0'); // Dissolving it smoothly in 1 second
                            setTimeout(() => toast.remove(), 1000); // Completely removing it from the DOM
                        }, 5000);
                    });
                </script>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @if(auth()->check() && request()->routeIs(['posts.index', 'posts.show']))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // Sets of button styles
                const classes = {
                    post: {
                        like: ['bg-red-50', 'border-red-500', 'text-red-600']
                    },
                    comment: {
                        like: ['text-green-600'],
                        dislike: ['text-red-600']
                    }
                };

                // Helper for switching the visibility of contoured/filled SVGs
                function toggleSvgIcon(button, isNowActive) {
                    const outlineSvg = button.querySelector('.js-icon-outline');
                    const solidSvg = button.querySelector('.js-icon-solid');

                    if (isNowActive) {
                        outlineSvg?.classList.add('hidden');
                        solidSvg?.classList.remove('hidden');
                    } else {
                        outlineSvg?.classList.remove('hidden');
                        solidSvg?.classList.add('hidden');
                    }
                }

                document.body.addEventListener('click', async (event) => {
                    const btn = event.target.closest('.js-reaction-btn');
                    if (!btn) return;

                    event.preventDefault();

                    const container = btn.closest('.reaction-block');
                    const type = container.dataset.type; // 'post' or 'comment'
                    const id = container.dataset.id;
                    const isLike = btn.dataset.isLike;

                    btn.classList.add('pointer-events-none', 'opacity-70');

                    try {
                        const response = await fetch('/reactions/toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ type, id, is_like: isLike })
                        });

                        if (response.status === 401) {
                            alert('Please log in.');
                            return;
                        }
                        if (!response.ok) throw new Error('Network error');

                        const data = await response.json();

                        // 1. Updating the text of the counters
                        const likesSpan = container.querySelector('.js-likes-count');
                        if (likesSpan) likesSpan.textContent = data.likes_count;

                        const dislikesSpan = container.querySelector('.js-dislikes-count');
                        if (dislikesSpan) dislikesSpan.textContent = data.dislikes_count;

                        // 2. Calculating the total balance for comments
                        const ratingSpan = container.querySelector('.js-comment-rating');
                        if (ratingSpan && type === 'comment') {
                            const rating = parseInt(data.likes_count) - parseInt(data.dislikes_count);

                            // Formatting the output (adding a plus sign for positive ones)
                            ratingSpan.textContent = rating > 0 ? `+${rating}` : rating;

                            // Resetting the old Tailwind color classes
                            ratingSpan.classList.remove('text-green-600', 'text-red-500', 'text-gray-400');

                            // Assigning the current color
                            if (rating > 0) {
                                ratingSpan.classList.add('text-green-600');
                            } else if (rating < 0) {
                                ratingSpan.classList.add('text-red-500');
                            } else {
                                ratingSpan.classList.add('text-gray-400');
                            }
                        }

                        // Finding the elements of the buttons
                        const likeBtn = container.querySelector('.js-reaction-btn[data-is-like="1"]');
                        const dislikeBtn = container.querySelector('.js-reaction-btn[data-is-like="0"]');

                        // Completely reset the button styles and hide the solid icons
                        if (likeBtn) {
                            likeBtn.classList.remove(...(classes[type].like || []));
                            toggleSvgIcon(likeBtn, false);
                        }
                        if (dislikeBtn) {
                            dislikeBtn.classList.remove(...(classes[type].dislike || []));
                            toggleSvgIcon(dislikeBtn, false);
                        }

                        // Enabling active styles according to the DB response
                        if (data.user_reaction === 'like' && likeBtn) {
                            likeBtn.classList.add(...classes[type].like);
                            toggleSvgIcon(likeBtn, true);
                        } else if (data.user_reaction === 'dislike' && dislikeBtn) {
                            dislikeBtn.classList.add(...classes[type].dislike);
                            toggleSvgIcon(dislikeBtn, true);
                        }

                    } catch (error) {
                        console.error(error);
                    } finally {
                        btn.classList.remove('pointer-events-none', 'opacity-70');
                    }
                });
            });
        </script>
        @endif
    </body>
</html>
