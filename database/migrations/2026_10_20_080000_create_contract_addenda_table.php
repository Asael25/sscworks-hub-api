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
        if (!Schema::hasTable('contract_addenda')) {
            Schema::create('contract_addenda', function (Blueprint $table) {
                $table->id();
                $table->string('addendum_id')->unique();
                $table->string('module'); // 'Internal Construction' or 'Customer Project'
                $table->string('target_item_id'); // work_id or project_id
                $table->integer('addendum_number')->default(1);
                $table->string('approval_id')->nullable()->index();
                $table->decimal('previous_amount', 15, 2)->default(0);
                $table->decimal('revised_amount', 15, 2)->default(0);
                $table->string('previous_target_date')->nullable();
                $table->string('revised_target_date')->nullable();
                $table->text('reason');
                $table->string('document_name')->nullable();
                $table->text('document_url')->nullable();
                $table->string('status')->default('Waiting Approval'); // Waiting Approval, Approved, Rejected
                $table->string('submitted_by')->nullable();
                $table->string('submitted_date')->nullable();
                $table->string('approved_by')->nullable();
                $table->string('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_addenda');
    }
};
