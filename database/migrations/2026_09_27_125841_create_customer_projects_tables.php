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
        Schema::create('customer_projects', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. CPR-2026-00101
            $table->string('customer');
            $table->string('name');
            $table->string('contract_number');
            $table->string('po_number');
            $table->string('unit');
            $table->string('location');
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->decimal('target', 5, 2)->default(0);
            $table->decimal('actual', 5, 2)->default(0);
            $table->string('deadline');
            $table->string('start_date');
            $table->string('period');
            $table->enum('status', ['On Schedule', 'Delayed', 'Waiting Approval', 'Completed'])->default('On Schedule');
            $table->string('pic');
            $table->string('payment_term');
            $table->string('retention');
            $table->timestamps();
        });

        Schema::create('customer_project_scopes', function (Blueprint $table) {
            $table->id();
            $table->string('project_id');
            $table->foreign('project_id')->references('id')->on('customer_projects')->onDelete('cascade');
            $table->string('scope');
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('target', 5, 2)->default(0);
            $table->decimal('actual', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('customer_project_weekly_progresses', function (Blueprint $table) {
            $table->id();
            $table->string('project_id');
            $table->foreign('project_id')->references('id')->on('customer_projects')->onDelete('cascade');
            $table->string('period');
            $table->decimal('target', 5, 2)->default(0);
            $table->decimal('actual', 5, 2)->default(0);
            $table->text('note')->nullable();
            $table->string('reported_by');
            $table->timestamps();
        });

        Schema::create('customer_project_issues', function (Blueprint $table) {
            $table->id();
            $table->string('project_id');
            $table->foreign('project_id')->references('id')->on('customer_projects')->onDelete('cascade');
            $table->string('title');
            $table->enum('severity', ['Low', 'Medium', 'High'])->default('Medium');
            $table->string('action');
            $table->string('owner');
            $table->string('date');
            $table->enum('status', ['Open', 'In Progress', 'Closed'])->default('Open');
            $table->timestamps();
        });

        Schema::create('customer_project_photos', function (Blueprint $table) {
            $table->id();
            $table->string('project_id');
            $table->foreign('project_id')->references('id')->on('customer_projects')->onDelete('cascade');
            $table->string('caption');
            $table->string('date');
            $table->timestamps();
        });

        Schema::create('customer_project_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('project_id');
            $table->foreign('project_id')->references('id')->on('customer_projects')->onDelete('cascade');
            $table->string('stage');
            $table->string('approver');
            $table->string('date')->nullable();
            $table->enum('state', ['Approved', 'Waiting', 'Pending', 'Returned'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_project_approvals');
        Schema::dropIfExists('customer_project_photos');
        Schema::dropIfExists('customer_project_issues');
        Schema::dropIfExists('customer_project_weekly_progresses');
        Schema::dropIfExists('customer_project_scopes');
        Schema::dropIfExists('customer_projects');
    }
};

