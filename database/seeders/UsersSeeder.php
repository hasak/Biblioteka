<?php
/**
 * Created by hasak on 29.11.25 @ 18:01
 **/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Two full-privilege admins
        $admin1 = User::firstOrCreate(
            ['username' => 'Aida'],
            [
                'name' => 'Aida',
                'email' => 'aida@hasak',
                'password' => bcrypt('07031997'),
            ]
        );
        $admin1->assignRole('admin');

        $admin2 = User::firstOrCreate(
            ['username' => 'Hasak'],
            [
                'name' => 'Hasak',
                'email' => 'himzo@hasak.ba',
                'password' => bcrypt('gnomegnome'),
            ]
        );
        $admin2->assignRole('admin');

        // One ordinary user
        $user = User::firstOrCreate(
            ['username' => 'user'],
            [
                'name' => 'Normal Demo User',
                'email' => 'demo@hasak.ba',
                'password' => bcrypt('user'),
            ]
        );
        $user->assignRole('user');
    }
}
