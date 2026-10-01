<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE customer_projects MODIFY COLUMN status ENUM('Draft', 'On Schedule', 'Delayed', 'Waiting Approval', 'Completed') DEFAULT 'Draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE customer_projects MODIFY COLUMN status ENUM('On Schedule', 'Delayed', 'Waiting Approval', 'Completed') DEFAULT 'On Schedule'");
    }
};
