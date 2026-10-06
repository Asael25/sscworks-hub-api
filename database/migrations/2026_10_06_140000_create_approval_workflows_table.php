<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. WFL-001
            $table->string('module');        // Budgeting, Cashflow, Project Customer, etc.
            $table->string('process');       // Budget Plan, Transaction Verification, Site Report, etc.
            $table->string('mode')->default('Layered'); // Single, Layered
            $table->string('status')->default('Active'); // Active, Draft
            $table->timestamps();
        });

        Schema::create('approval_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_id');
            $table->foreign('workflow_id')->references('id')->on('approval_workflows')->onDelete('cascade');
            $table->integer('order')->default(1);
            $table->string('role');
            $table->string('approver')->nullable();
            $table->string('unit_scope')->default('All units');
            $table->decimal('threshold', 15, 2)->default(0);
            $table->boolean('required')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_stages');
        Schema::dropIfExists('approval_workflows');
    }
};
