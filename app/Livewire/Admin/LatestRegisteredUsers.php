<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire component for listing recently registered users in the admin dashboard.
 */
class LatestRegisteredUsers extends Component
{
    /**
     * The number of users to display per page.
     */
    public int $perPage;

    /**
     * Mount the component - set the initial value of perPage.
     */
    public function mount(): void
    {
        // Dynamic retrieval of a perPage from the Post model (if not specified there, Laravel returns the default 15)
        $this->perPage = new User()->getPerPage();
    }

    /**
     * Load more users after the user clicks the "Show More" button.
     */
    public function loadMore(): void
    {
        // Increase by a step equal to the base pagination of this model
        $this->perPage += new User()->getPerPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // time period that is being investigated for new users
        $period = now()->subDays(3);
        // Getting users registered in the period of time
        $users = User::query()
            ->where('created_at', '>=', $period)
            ->latest('id')
            ->limit($this->perPage)
            ->get();

        // If there are less than 10 users in the period of time, get just last 10 users
        if ($users->count() < 10) {
            $users = User::query()
                ->latest('id')
                ->limit(10)
                ->get();
        }

        // Count the total number of users registered in the period of time
        $usersCount = User::query()->where('created_at', '>=', $period)->count();
        // Check if there are more users to load (more than the current perPage)
        $hasMore = $usersCount > $this->perPage;

        return view('livewire.admin.latest-registered-users', [
            'latestRegisteredUsers' => $users,
            'latestRegisteredUsersCount' => $usersCount,
            'hasMore' => $hasMore
        ]);
    }
}
