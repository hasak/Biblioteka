<?php
/**
 * Created by hasak on 29.11.25 @ 18:01
 **/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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
                'password' => bcrypt($this->password('SEED_AIDA_PASSWORD', 'Aida')),
            ]
        );
        $admin1->assignRole('admin');

        $admin2 = User::firstOrCreate(
            ['username' => 'Hasak'],
            [
                'name' => 'Hasak',
                'email' => 'himzo@hasak.ba',
                'password' => bcrypt($this->password('SEED_HASAK_PASSWORD', 'Hasak')),
            ]
        );
        $admin2->assignRole('admin');

        // One ordinary user
        $user = User::firstOrCreate(
            ['username' => 'user'],
            [
                'name' => 'Normal Demo User',
                'email' => 'demo@hasak.ba',
                'password' => bcrypt($this->password('SEED_DEMO_PASSWORD', 'user')),
            ]
        );
        $user->assignRole('user');
    }

    /**
     * Read a seed password from the environment, or generate one and print it
     * once. Passwords must never be committed to the repository.
     */
    private function password(string $envKey, string $label): string
    {
        $password = env($envKey);

        if (filled($password)) {
            return $password;
        }

        $password = Str::password(20);
        $this->command?->warn("Generated password for {$label}: {$password} — set {$envKey} in .env to choose your own.");

        return $password;
    }
}
