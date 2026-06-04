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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('slug', 120)->unique();
            $table->json('title');
            $table->string('logo')->nullable();
            $table->json('slogan')->nullable();

            $table->enum('type', ['bio', 'website'])->default('website');
            $table->string('template')->default('resto-1');
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->string('domain')->nullable()->unique();
            $table->json('settings')->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'suspended'
            ])->default('draft');
            $table->unsignedSmallInteger('settings_version')->default(1);
            $table->index(['user_id']);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
