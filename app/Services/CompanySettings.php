<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanySettings
{
    public function all(): array
    {
        $defaults = config('company.defaults');
        // Retain known legacy fields only. SMTP credentials must never be public props.
        $legacy = array_intersect_key(Cache::get('global_settings', []), $defaults);
        $stored = Schema::hasTable('app_settings') ? DB::table('app_settings')->where('key', 'company')->value('value') : null;
        $values = $stored ? json_decode($stored, true, flags: JSON_THROW_ON_ERROR) : $legacy;

        return array_replace($defaults, array_intersect_key($values, $defaults));
    }

    public function save(array $validated): void
    {
        $values = array_replace($this->all(), array_intersect_key($validated, config('company.defaults')));
        DB::table('app_settings')->updateOrInsert(['key' => 'company'], [
            'value' => json_encode($values, JSON_THROW_ON_ERROR), 'updated_at' => now(),
        ]);
        Cache::forever('global_settings', $values);
    }
}
