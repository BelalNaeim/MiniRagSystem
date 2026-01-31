<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->integer('chunk_index');
            $table->integer('section_number')->nullable();
            $table->integer('sort_order')->nullable();
            $table->string('guid')->unique();
            $table->string('vector_id')->unique();
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'pdf_id']);
            $table->index('guid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chunks');
    }
};
