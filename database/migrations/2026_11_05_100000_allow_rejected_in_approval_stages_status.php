<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('approval_stages')) {
            try {
                // Change status from restricted ENUM to VARCHAR(50) so 'Rejected', 'Waiting', 'Approved', 'Pending', 'Returned' are all valid
                DB::statement("ALTER TABLE approval_stages MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Waiting'");
            } catch (\Throwable $e) {
                \Log::warning("Could not alter status column on approval_stages: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('approval_stages')) {
            try {
                DB::statement("ALTER TABLE approval_stages MODIFY COLUMN status ENUM('Waiting', 'Approved', 'Pending', 'Returned') NOT NULL DEFAULT 'Waiting'");
            } catch (\Throwable $e) {}
        }
    }
};

