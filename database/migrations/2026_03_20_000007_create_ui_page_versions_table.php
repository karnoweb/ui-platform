<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_page_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('ui_pages')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('payload');
            $table->string('checksum', 64);
            $table->string('published_by', 128)->nullable();
            $table->timestamp('published_at');
            $table->timestamps();

            $table->unique(['page_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_page_versions');
    }
};
