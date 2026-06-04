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
        Schema::create('links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('page_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('url');
            $table->string('icon')->nullable();

            $table->enum('type', [
                'social',
                'phone',
                'whatsapp',
                'email',
                'map',
                'website',
                'custom'
            ])->default('custom');

            $table->integer('display_order')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);

            $table->timestamps();

            $table->index(['page_id', 'type']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
