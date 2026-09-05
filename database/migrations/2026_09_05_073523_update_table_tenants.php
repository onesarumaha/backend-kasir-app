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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('logo')->nullable()->after('address');
            $table->string('footer_receipt_text')->default('Terima kasih telah berbelanja!')->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['email', 'logo', 'footer_receipt_text']);
        });
    }
};
