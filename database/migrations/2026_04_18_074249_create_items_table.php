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
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->json('title');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();

            $table->string('slug');
            $table->unique(['page_id', 'slug']);
            $table->enum('action_type', ['internal', 'external'])->default('internal');
            $table->string('external_url')->nullable();

            $table->json('price')->nullable();
            $table->string('image')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_visible')->default(true);

            $table->integer('display_order')->default(0);

            $table->unsignedBigInteger('clicks')->default(0); // counter

            $table->timestamps();

            $table->index(['page_id', 'category_id']);
            $table->index(['page_id', 'is_visible']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
