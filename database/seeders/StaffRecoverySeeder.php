<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StaffRecoverySeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('seeders/staff_backup.json'));
        $staff = json_decode($json, true);

        foreach ($staff as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                $account
            );
        }
    }
}
