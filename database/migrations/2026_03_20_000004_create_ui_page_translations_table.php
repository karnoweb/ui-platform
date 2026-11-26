<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_page_translations', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_key', 128)->index();
            $table->foreignId('page_id')->constrained('ui_pages')->cascadeOnDelete();
            $table->string('locale', 20);
            $table->string('title', 255);
            $table->string('slug', 255)->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->text('seo_description')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_page_translations');
    }
};
