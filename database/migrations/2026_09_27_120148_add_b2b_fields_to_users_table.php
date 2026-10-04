<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->text('billing_address')->nullable()->after('phone');
            $table->text('shipping_address')->nullable()->after('billing_address');
            $table->string('tax_id')->nullable()->after('shipping_address');
            $table->string('role')->default('client')->after('password'); // admin vs client
            $table->string('credit_status')->default('pending')->after('role'); // pending, approved, suspended
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'phone', 'billing_address', 'shipping_address', 'tax_id', 'role', 'credit_status']);
        });
    }
};

