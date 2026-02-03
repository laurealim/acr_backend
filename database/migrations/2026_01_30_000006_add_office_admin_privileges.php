<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds office_admin role and required privileges
     */
    public function up(): void
    {
        // Create office_admins pivot table for managing office admins
        Schema::create('office_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            $table->boolean('can_assign_dossier')->default(true);
            $table->boolean('can_manage_employees')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'office_id']);
        });

        // Insert office_admin role if using Tyro Dashboard
        // This will be done via seeder for proper integration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_admins');
    }
};
