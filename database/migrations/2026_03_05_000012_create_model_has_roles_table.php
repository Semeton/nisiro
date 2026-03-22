<?php

declare(strict_types=1);

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
        Schema::create('model_has_roles', function (Blueprint $blueprint): void {
            $blueprint->uuid('role_id');
            $blueprint->uuid('tenant_id')->index();
            $blueprint->string('model_type');
            $blueprint->uuid('model_id');

            $blueprint->primary(['role_id', 'model_id', 'model_type', 'tenant_id'], 'model_has_roles_primary');

            $blueprint->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_roles');
    }
};
