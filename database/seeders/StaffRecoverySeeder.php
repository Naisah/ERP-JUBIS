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

        if (is_array($staff)) {
            foreach ($staff as $account) {
                unset($account['id']);
                User::updateOrCreate(
                    ['email' => $account['email']],
                    $account
                );
            }
        }
    }
}
