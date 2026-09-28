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
        Schema::create('legal_requests', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. "1", "2", "LGL-..."
            $table->string('request_code')->unique();
            $table->string('submitted_at');
            $table->string('unit');
            $table->string('category');
            $table->string('title');
            $table->string('applicant');
            $table->string('applicant_email');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('target_date');
            $table->string('current_stage');
            $table->enum('status', ['draft', 'waiting_review', 'in_process', 'completed', 'rejected'])->default('draft');
            $table->text('description')->nullable();
            $table->string('counterparty')->nullable();
            $table->string('estimated_value')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_attachments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('request_id');
            $table->foreign('request_id')->references('id')->on('legal_requests')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_type');
            $table->integer('file_size_kb')->default(0);
            $table->string('uploaded_by');
            $table->string('uploaded_at');
            $table->timestamps();
        });

        Schema::create('legal_timeline_events', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('request_id');
            $table->foreign('request_id')->references('id')->on('legal_requests')->onDelete('cascade');
            $table->string('stage');
            $table->string('actor');
            $table->string('occurred_at')->nullable();
            $table->enum('state', ['done', 'current', 'pending'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_notes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('request_id');
            $table->foreign('request_id')->references('id')->on('legal_requests')->onDelete('cascade');
            $table->string('author');
            $table->string('created_at_str');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('legal_output_documents', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('request_id');
            $table->foreign('request_id')->references('id')->on('legal_requests')->onDelete('cascade');
            $table->string('doc_number');
            $table->string('title');
            $table->string('doc_type');
            $table->string('issued_at');
            $table->timestamps();
        });

        Schema::create('legal_approvals', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('request_id');
            $table->foreign('request_id')->references('id')->on('legal_requests')->onDelete('cascade');
            $table->string('approver');
            $table->string('role');
            $table->enum('decision', ['approved', 'rejected', 'pending'])->default('pending');
            $table->string('decided_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_approvals');
        Schema::dropIfExists('legal_output_documents');
        Schema::dropIfExists('legal_notes');
        Schema::dropIfExists('legal_timeline_events');
        Schema::dropIfExists('legal_attachments');
        Schema::dropIfExists('legal_requests');
    }
};
