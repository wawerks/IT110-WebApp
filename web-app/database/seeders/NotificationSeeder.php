<?php

// database/seeders/NotificationSeeder.php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        Notification::create([
            'user_id' => 1,
            'type' => 'order_shipped',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => 1,
            'data' => json_encode(['message' => 'Your order has been shipped.']),
        ]);

        Notification::create([
            'user_id' => 1,
            'type' => 'friend_request',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => 1,
            'data' => json_encode(['message' => 'You have a new friend request.']),
        ]);

        Notification::create([
            'user_id' => 1,
            'type' => 'post_comment',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => 1,
            'data' => json_encode(['message' => 'John Doe commented on your post.']),
        ]);
    }
}
