<?php

namespace Database\Factories;

use App\Models\DatabaseNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

// FOR TESTS
class DatabaseNotificationFactory extends Factory
{
    protected $model = DatabaseNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'Filament\Notifications\Notification',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(), // It will create a user if it hasn’t been passed.
            'data' => [
                'id' => Str::uuid()->toString(),
                'title' => $this->faker->sentence(),
                'body' => $this->faker->paragraph(),
                'status' => 'info', // info by default
                'icon' => 'heroicon-o-bell', // by default
                'actions' => [],
                'data' => [], // custom metadata
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * A universal method for changing ANY internal Filament parameters.
     */
    public function filamentData(array $customData): self
    {
        return $this->state(function (array $attributes) use ($customData) {
            // Deep merge to avoid overwriting the default title/body if we’re only changing the status.
            $newFilamentData = array_replace_recursive($attributes['data'], $customData);

            return [
                'data' => $newFilamentData,
            ];
        });
    }

    /**
     * Quick status for a read notification
     */
    public function read($readAt = null): self
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $readAt ?? now(),
        ]);
    }

    // Custom helper state for responding to comments.
    public function commentReply(int $postId, int $commentId): self
    {
        return $this->state(function (array $attributes) use ($postId, $commentId) {
            return [
                'data' => array_merge($attributes['data'], [
                    'data' => [
                        'type' => 'comment_reply',
                        'post_id' => $postId,
                        'comment_id' => $commentId,
                    ]
                ])
            ];
        });
    }
}
