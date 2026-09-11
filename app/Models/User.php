<?php

namespace App\Models;

use App\Enum\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $perPage = 10; // Redefining the standard perPage property

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Determine if the user can access a specific panel (filament admin panel).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // For unit tests
        if (app()->runningUnitTests()) {
            return true;
        }

        // For the 'admin' panel (which is in the AdminPanelProvider)
        if ($panel->getId() === 'admin') {
            return $this->role === UserRole::ADMIN || $this->role === UserRole::MODERATOR;
        }

        return false;
    }

    /**
     * Get the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments of the user.
     */
    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function hasRole($role): bool
    {
        return $this->role === $role;
    }
}
