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
        DB::statement("ALTER TABLE construction_weekly_progresses MODIFY COLUMN status VARCHAR(50) NULL DEFAULT 'Waiting Approval'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE construction_weekly_progresses MODIFY COLUMN status ENUM('Draft', 'Waiting Approval') NULL");
    }
};
