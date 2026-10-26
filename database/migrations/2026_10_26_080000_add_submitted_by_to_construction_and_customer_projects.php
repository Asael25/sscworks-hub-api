<?php

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
        if (Schema::hasTable('construction_works')) {
            Schema::table('construction_works', function (Blueprint $table) {
                if (!Schema::hasColumn('construction_works', 'submitted_by')) {
                    $table->string('submitted_by')->nullable()->after('pic');
                }
                if (!Schema::hasColumn('construction_works', 'submitted_by_id')) {
                    $table->string('submitted_by_id')->nullable()->after('submitted_by');
                }
            });
        }

        if (Schema::hasTable('customer_projects')) {
            Schema::table('customer_projects', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_projects', 'submitted_by')) {
                    $table->string('submitted_by')->nullable()->after('pic');
                }
                if (!Schema::hasColumn('customer_projects', 'submitted_by_id')) {
                    $table->string('submitted_by_id')->nullable()->after('submitted_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('construction_works')) {
            Schema::table('construction_works', function (Blueprint $table) {
                if (Schema::hasColumn('construction_works', 'submitted_by_id')) {
                    $table->dropColumn('submitted_by_id');
                }
                if (Schema::hasColumn('construction_works', 'submitted_by')) {
                    $table->dropColumn('submitted_by');
                }
            });
        }

        if (Schema::hasTable('customer_projects')) {
            Schema::table('customer_projects', function (Blueprint $table) {
                if (Schema::hasColumn('customer_projects', 'submitted_by_id')) {
                    $table->dropColumn('submitted_by_id');
                }
                if (Schema::hasColumn('customer_projects', 'submitted_by')) {
                    $table->dropColumn('submitted_by');
                }
            });
        }
    }
};
