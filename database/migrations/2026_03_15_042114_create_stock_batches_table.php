<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('tenant_id')->index();
            $blueprint->uuid('stock_item_id')->index();
            $blueprint->decimal('quantity', 10, 2);
            $blueprint->decimal('quantity_remaining', 10, 2);
            $blueprint->decimal('unit_cost', 15, 2);
            $blueprint->timestamps();

            $blueprint->foreign('stock_item_id')
                ->references('id')
                ->on('stock_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
