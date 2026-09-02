<?php

namespace Database\Seeders;

use App\Enum\NotificationTypes;
use App\Models\DatabaseNotification;
use App\Models\User;
use App\Notifications\CommentReplied;
use App\Notifications\CustomUserNotification;
use Guava\IconPicker\Icons\IconManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ADMINISTRATIVE NOTIFICATIONS
        $userIds = User::query()->pluck('id');
        if ($userIds->isEmpty()) {
            return;
        }
        $countToTake = ceil($userIds->count() * 0.20); // 20% of users
        $userIds = $userIds->random($countToTake); // Randomly select 20% of users

        foreach ($userIds as $userId) {
            $baseNow = now()->toImmutable();
            $randomDays = mt_rand(1, 30);
            $createdDate = $baseNow->subDays($randomDays); // date of creation

            $readAt = null;
            $isRead = (mt_rand(1, 100) <= 80); // 80% chance of being read
            if ($isRead) {
                $randomReadDays = mt_rand(0, $randomDays - 1);
                $readAt = $createdDate->addDays($randomReadDays)->toDateTimeString(); // date of reading
            }

            // random icon for notification
            $randomIcons = app(IconManager::class)->getIcons('heroicons');
            $randomIcon = collect($randomIcons)->values()->random()->id;

            DatabaseNotification::factory()
                ->read($readAt) // string with date or null
                ->filamentData([
                'status' => $this->getRandomTypeBy(),
                'icon' => $randomIcon,
            ])
                ->create([
                    'type' => CustomUserNotification::class,
                    'notifiable_id' => $userId,
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate,
                ]);
        }

        // NOTIFICATIONS OF REPLIES TO COMMENTS
        // Get all replies to comments
        $commentReplies = DB::table('comments as child')
            ->leftJoin('comments as parent', 'child.parent_id', '=', 'parent.id')
            ->leftJoin('users as responder', 'child.user_id', '=', 'responder.id')
            ->where('child.created_at', '>=', now()->subDays(30))
            ->whereNotNull('child.parent_id')
            ->whereNotNull('parent.user_id')
            ->select([
                //'child.*',
                'child.id',
                'child.post_id',
                'child.body',
                'child.created_at',
                'parent.user_id as parent_user_id', // ID of the author of the parent comment
                'responder.name as responder_name', // Name of the responder
            ])
            ->get();

        foreach ($commentReplies as $comment) {
            $readAt = null;
            $isRead = (mt_rand(1, 100) <= 80); // 80% chance of being read
            if ($isRead) {
                $randomReadDate = fake()->dateTimeBetween($comment->created_at, now());
                $readAt = Carbon::parse($comment->created_at)->addDays($randomReadDate)->toDateTimeString(); // date of reading
            }

            // data for comment reply
            $data = [
                'type' => 'comment_reply',
                'post_id' => $comment->post_id,
                'comment_id' => $comment->id,
            ];

            DatabaseNotification::factory()
                ->read($readAt) // string with date or null
                ->filamentData([
                    'title' => 'A new response to your comment',
                    'body' => $comment->responder_name . ' answered: "' . $comment->body. '"',
                    'icon' => 'heroicon-o-chat-bubble-left-right',
                    'data' => $data,
                ])
                ->create([
                    'type' => CommentReplied::class,
                    'notifiable_id' => $comment->parent_user_id,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->created_at,
                ]);
        }
    }

    /**
     * Returns a random notification type.
     */
    private function getRandomTypeBy(): string
    {
        $weights = [
            NotificationTypes::INFO->value   => 70, // 70% chance
            NotificationTypes::WARNING->value => 20, // 20% chance
            NotificationTypes::DANGER->value => 5, // 5% chance
            NotificationTypes::SUCCESS->value  => 5,  // 5% chance
        ];

        $roll = mt_rand(1, array_sum($weights));
        $currentSum = 0;

        foreach ($weights as $key => $weight) {
            $currentSum += $weight;
            if ($roll <= $currentSum) {
                return $key;
            }
        }

        return NotificationTypes::INFO->value; // return 'info' by default
    }
}
