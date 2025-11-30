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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->foreignId('series_id')->nullable()->constrained();
            $table->unsignedTinyInteger('part_number')->nullable();
            $table->string('publisher')->nullable();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->foreignId('language_id')->nullable()->constrained();
            $table->foreignId('genre_id')->nullable()->constrained();
            $table->string('original_title')->nullable();
            $table->year('year')->nullable();
            $table->geometry('position','point')->nullable();
            $table->string('isbn')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();

            $table->unique(['series_id', 'part_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
