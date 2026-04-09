<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('manager_id')->nullable()->after('activa')->constrained('users')->nullOnDelete();
            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['deleted_at', 'manager_id']);
        });
    }
};
