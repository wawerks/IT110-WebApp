<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LostItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('lost_items')->insert([
            [
                'item_name' => 'asd',
                'lost_date' => Carbon::create('2024', '12', '15'),
                'facebook_link' => 'https://www.facebook.com/lostitem1',
                'contact_number' => '09123456789',
                'description' => 'A black leather wallet with a few ID cards inside.',
                'category' => 'Wallet',
                'location' => 'Butuan City Park',
                'image_url' => 'images/lost-items/wallet1.jpg',
                'user_id' => 1,  // Assuming the user with ID 1 exists
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'item_name' => 'asd',
                'lost_date' => Carbon::create('2024', '12', '14'),
                'facebook_link' => 'https://www.facebook.com/lostitem2',
                'contact_number' => '09876543210',
                'description' => 'A silver ring with a small engraving.',
                'category' => 'Jewelry',
                'location' => 'Butuan City Mall',
                'image_url' => 'images/lost-items/ring1.jpg',
                'user_id' => 1 ,  // Assuming the user with ID 2 exists
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'item_name' => 'asd',
                'lost_date' => Carbon::create('2024', '12', '13'),
                'facebook_link' => 'https://www.facebook.com/lostitem3',
                'contact_number' => '09211223344',
                'description' => 'A pair of brown sunglasses, slightly scratched.',
                'category' => 'Sunglasses',
                'location' => 'Butuan City Square',
                'image_url' => 'images/lost-items/sunglasses1.jpg',
                'user_id' => 1,  // Assuming the user with ID 3 exists
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
