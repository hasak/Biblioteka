<?php

use Illuminate\Support\Facades\DB;
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
            $table->string('cover')->nullable();
            $table->string('title');
            $table->string('author');
            $table->foreignId('series_id')->nullable()->constrained();
            $table->double('part_number')->nullable();
            $table->string('publisher');
            $table->unsignedInteger('year');
            $table->foreignId('country_id')->constrained();
            $table->foreignId('language_id')->constrained();
            $table->string('original_title')->nullable();
            $table->foreignId('genre_id')->constrained();
            $table->unsignedTinyInteger('shelf_x');
            $table->unsignedTinyInteger('shelf_y');
            $table->boolean('is_read')->default(false);
            $table->date('read_date')->nullable();
            $table->foreignId('purchased_country_id')->nullable()->constrained('countries');
            $table->string('purchased_city')->nullable();
            $table->date('purchased_date')->nullable();
            $table->string('isbn')->unique();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            //$table->unique(['series_id', 'part_number']);
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
