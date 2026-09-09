<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="py-4 px-20 border-b border-gray-200 dark:border-gray-800">
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-white">
                        Personal activity statistics:
                    </h2>
                </div>
            </div>

            <div class="flex justify-between p-3 px-20 text-gray-900 dark:text-gray-100">
                <span>{{ __("Posts liked: ") }}</span>
                {{ $postLikesCount }}
            </div>
            <div class="flex justify-between p-3 px-20 text-gray-900 dark:text-gray-100">
                <span>{{ __("Comments left: ") }}</span>
                {{ $commentsCount }}
            </div>
            <div class="flex justify-between p-3 px-20 text-gray-900 dark:text-gray-100">
                <span>{{ __("Received likes for comments: ") }}</span>
                <span class="text-green-600">{{ $commentLikesCount }}</span>
            </div>
            <div class="flex justify-between p-3 px-20 text-gray-900 dark:text-gray-100">
                <span>{{ __("Received dislikes for comments: ") }}</span>
                <span class="text-red-600">{{ $commentDislikesCount }}</span>
            </div>
            <div class="flex justify-between p-3 px-20 text-gray-900 dark:text-gray-100">
                <span>{{ __("Total rating: ") }}</span>
                <span class="{{ $totalRating ? ($totalRating > 0 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' }}">
                    {{ $totalRating ? ($totalRating > 0 ? '+' : '-').$totalRating : 0 }}
                </span>
            </div>
        </div>
    </div>
</div>
