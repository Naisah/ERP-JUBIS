<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('user_id');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('subtotal');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('user_id');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'vat_amount']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'vat_amount']);
        });
    }
};
