<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_page_routes', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_key', 128)->index();
            $table->foreignId('page_id')->constrained('ui_pages')->cascadeOnDelete();
            $table->foreignId('page_version_id')
                ->constrained('ui_page_versions')
                ->cascadeOnDelete();

            $table->string('locale', 20);
            $table->string('slug', 255);
            $table->timestamps();

            $table->unique(['tenant_key', 'locale', 'slug']);
            $table->index(['page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_page_routes');
    }
};
