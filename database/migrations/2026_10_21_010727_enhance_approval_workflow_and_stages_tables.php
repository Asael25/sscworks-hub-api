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
        // 1. Table approval_workflows
        Schema::table('approval_workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_workflows', 'workflow_type')) {
                $table->string('workflow_type')->default('submission')->after('process');
            }
            if (!Schema::hasColumn('approval_workflows', 'is_customized')) {
                $table->boolean('is_customized')->default(false)->after('status');
            }
        });

        // 2. Table approval_workflow_stages (Templates)
        Schema::table('approval_workflow_stages', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_workflow_stages', 'stage_level')) {
                $table->integer('stage_level')->default(1)->after('order');
            }
            if (!Schema::hasColumn('approval_workflow_stages', 'stage_name')) {
                $table->string('stage_name')->nullable()->after('stage_level');
            }
            if (!Schema::hasColumn('approval_workflow_stages', 'is_sequential')) {
                $table->boolean('is_sequential')->default(false)->after('stage_name');
            }
            if (!Schema::hasColumn('approval_workflow_stages', 'sub_order')) {
                $table->integer('sub_order')->default(1)->after('is_sequential');
            }
        });

        // 3. Table approval_stages (Runtime Instances per Ticket)
        Schema::table('approval_stages', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_stages', 'stage_level')) {
                $table->integer('stage_level')->default(1)->after('order');
            }
            if (!Schema::hasColumn('approval_stages', 'stage_name')) {
                $table->string('stage_name')->nullable()->after('stage_level');
            }
            if (!Schema::hasColumn('approval_stages', 'is_sequential')) {
                $table->boolean('is_sequential')->default(false)->after('stage_name');
            }
            if (!Schema::hasColumn('approval_stages', 'sub_order')) {
                $table->integer('sub_order')->default(1)->after('is_sequential');
            }
            if (!Schema::hasColumn('approval_stages', 'status')) {
                $table->enum('status', ['Waiting', 'Approved', 'Pending', 'Returned'])->default('Waiting')->after('unit_scope');
            }
            if (!Schema::hasColumn('approval_stages', 'approved_by')) {
                $table->string('approved_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('approval_stages', 'approved_at')) {
                $table->string('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_stages', function (Blueprint $table) {
            $table->dropColumn([
                'stage_level',
                'stage_name',
                'is_sequential',
                'sub_order',
                'status',
                'approved_by',
                'approved_at'
            ]);
        });

        Schema::table('approval_workflow_stages', function (Blueprint $table) {
            $table->dropColumn([
                'stage_level',
                'stage_name',
                'is_sequential',
                'sub_order'
            ]);
        });

        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->dropColumn([
                'workflow_type',
                'is_customized'
            ]);
        });
    }
};
