<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_url')->nullable()->after('status')->comment('Checkout URL from Payment Gateway');
            $table->string('payment_reference_id')->nullable()->after('payment_url')->comment('External ID from Gateway');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_url', 'payment_reference_id']);
        });
    }
};
