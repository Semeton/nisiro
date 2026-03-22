<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('tenant_id')->index();
            $blueprint->date('date');
            $blueprint->string('description');
            $blueprint->string('reference')->nullable();
            $blueprint->timestamp('posted_at')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
