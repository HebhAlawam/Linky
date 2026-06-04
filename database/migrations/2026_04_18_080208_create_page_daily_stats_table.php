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
        Schema::create('page_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')
                ->constrained('pages')
                ->cascadeOnDelete();

            $table->date('date');

            $table->unsignedBigInteger('visits')->default(0);
            $table->unsignedBigInteger('unique_visits')->default(0);

            $table->unsignedBigInteger('item_views')->default(0);
            $table->unsignedBigInteger('item_clicks')->default(0);

            $table->unsignedBigInteger('link_clicks')->default(0);

            $table->timestamps();

            $table->unique(['page_id', 'date']);

            $table->index('date');
            $table->index(['page_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_daily_stats');
    }
};
