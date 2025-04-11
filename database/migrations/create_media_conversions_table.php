<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TheJawker\Mediaux\Models\MediaItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_conversions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(MediaItem::class);
            $table->string('filename')->unique();
            $table->string('original_filename');
            $table->string('disk');
            $table->string('hash');
            $table->json('specifications');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_conversions');
    }
};
