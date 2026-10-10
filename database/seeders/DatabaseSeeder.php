<?php


namespace Database\Seeders;
 
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
 
class LicenseSeeder extends Seeder
{
    /** Issues one new unused key (valid 1 year to activate). */
    public function run(): void
    {
        $key = implode('-', str_split(strtoupper(Str::random(20)), 5));
 
        License::create(['license_key' => $key, 'expires_at' => now()->addYear()]);
 
        $this->command->info("License key: {$key}");
    }
}
 

// namespace Database\Seeders;

// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

// class DatabaseSeeder extends Seeder
// {
//     use WithoutModelEvents;
//     public function run(): void
//     {
//         User::factory()->create([
//             'name' => 'Test User',
//             'email' => 'test@example.com',
//         ]);
//     }
// }
