<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('tracking_url')->nullable()->after('tracking_number')->comment('Live tracking link from Courier API');
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('tracking_url')->comment('Calculated delivery fee from Courier API');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['tracking_url', 'delivery_fee']);
        });
    }
};
