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
        Schema::create('role_has_permissions', function (Blueprint $blueprint): void {
            $blueprint->uuid('permission_id');
            $blueprint->uuid('role_id');
            $blueprint->uuid('tenant_id')->index();

            $blueprint->primary(['permission_id', 'role_id', 'tenant_id'], 'role_has_permissions_primary');

            $blueprint->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

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
        Schema::dropIfExists('role_has_permissions');
    }
};
