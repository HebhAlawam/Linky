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
        Schema::table('page_daily_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('whatsapp_order_attempts')->default(0);
            $table->unsignedBigInteger('copied_order_attempts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_daily_stats', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_order_attempts',
                'copied_order_attempts',
            ]);
        });
    }
};
