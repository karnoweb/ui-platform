<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_page_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('ui_pages')->cascadeOnDelete();
            $table->uuid('instance_key')->unique();
            $table->string('type', 128);
            $table->string('variant_key', 128);
            $table->unsignedInteger('position');
            $table->boolean('is_enabled')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_page_sections');
    }
};
