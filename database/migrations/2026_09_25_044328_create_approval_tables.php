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
        Schema::create('approvals', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. CF-2026-00312
            $table->string('module'); // Cashflow, Contract, Legal, Budgeting, Konstruksi Internal, Project Customer
            $table->string('request');
            $table->text('summary');
            $table->string('unit');
            $table->string('submitted_by');
            $table->string('submitted_by_id');
            $table->string('submitted_date');
            $table->string('period');
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('amount_label');
            $table->integer('current_stage')->default(1);
            $table->enum('priority', ['High', 'Medium', 'Low'])->default('Medium');
            $table->enum('status', ['Waiting', 'In Review', 'Approved', 'Returned', 'Rejected', 'Overdue'])->default('Waiting');
            $table->string('due_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_stages', function (Blueprint $table) {
            $table->id();
            $table->string('approval_id');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade');
            $table->integer('order');
            $table->string('name');
            $table->string('role');
            $table->string('approver')->nullable();
            $table->string('unit_scope')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('approval_id');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade');
            $table->string('name');
            $table->string('size');
            $table->string('type');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->string('approval_id');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade');
            $table->string('actor');
            $table->string('role');
            $table->string('action');
            $table->text('note')->nullable();
            $table->string('at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('approval_attachments');
        Schema::dropIfExists('approval_stages');
        Schema::dropIfExists('approvals');
    }
};
