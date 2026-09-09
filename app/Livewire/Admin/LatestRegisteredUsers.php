<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class LatestRegisteredUsers extends Component
{
    public int $perPage;

    public function mount(): void
    {
        // Dynamic retrieval of a perPage from the Post model (if not specified there, Laravel returns the default 15)
        $this->perPage = new User()->getPerPage();
    }

    public function loadMore(): void
    {
        // Increase by a step equal to the base pagination of this model
        $this->perPage += new User()->getPerPage();
    }

    public function render(): View
    {
        $period = now()->subMonth();
        // Get the last 10 users registered in the period of time
        $users = User::query()
            ->where('created_at', '>=', now()->subMonth())
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

        $usersCount = User::query()->where('created_at', '>=', $period)->count();
        $hasMore = $usersCount > $this->perPage;

        return view('livewire.admin.latest-registered-users', [
            'latestRegisteredUsers' => $users,
            'latestRegisteredUsersCount' => $usersCount,
            'hasMore' => $hasMore
        ]);
    }
}
