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
        Schema::create('construction_works', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. KI-2026-0011
            $table->string('name');
            $table->string('unit');
            $table->string('location');
            $table->string('vendor');
            $table->string('contract_number');
            $table->string('pic');
            $table->string('start_date');
            $table->string('target_date');
            $table->string('period');
            $table->decimal('target', 5, 2)->default(0);
            $table->decimal('actual', 5, 2)->default(0);
            $table->enum('status', ['Draft', 'On Schedule', 'Delayed', 'Waiting Approval', 'Completed'])->default('Draft');
            $table->decimal('budget', 15, 2)->default(0);
            
            // Quality
            $table->integer('quality_checked')->default(0);
            $table->integer('quality_passed')->default(0);
            $table->integer('quality_reworks')->default(0);
            $table->text('quality_note')->nullable();

            // Safety
            $table->integer('safety_manhours')->default(0);
            $table->integer('safety_incidents')->default(0);
            $table->integer('safety_near_miss')->default(0);
            $table->integer('safety_toolbox')->default(0);

            $table->timestamps();
        });

        Schema::create('construction_scopes', function (Blueprint $table) {
            $table->id();
            $table->string('work_id');
            $table->foreign('work_id')->references('id')->on('construction_works')->onDelete('cascade');
            $table->string('scope');
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('target', 5, 2)->default(0);
            $table->decimal('actual', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('construction_weekly_progresses', function (Blueprint $table) {
            $table->id();
            $table->string('work_id');
            $table->foreign('work_id')->references('id')->on('construction_works')->onDelete('cascade');
            $table->string('period');
            $table->decimal('target', 5, 2)->default(0);
            $table->decimal('actual', 5, 2)->default(0);
            $table->string('report_id')->nullable();
            $table->enum('status', ['Draft', 'Waiting Approval'])->nullable();
            $table->timestamps();
        });

        Schema::create('construction_issues', function (Blueprint $table) {
            $table->id();
            $table->string('work_id');
            $table->foreign('work_id')->references('id')->on('construction_works')->onDelete('cascade');
            $table->string('title');
            $table->enum('severity', ['Low', 'Medium', 'High'])->default('Medium');
            $table->string('raised_by');
            $table->string('date');
            $table->enum('status', ['Open', 'In Progress', 'Closed'])->default('Open');
            $table->timestamps();
        });

        Schema::create('construction_photos', function (Blueprint $table) {
            $table->id();
            $table->string('work_id');
            $table->foreign('work_id')->references('id')->on('construction_works')->onDelete('cascade');
            $table->string('caption');
            $table->string('date');
            $table->enum('tone', ['site', 'quality', 'safety'])->default('site');
            $table->timestamps();
        });

        Schema::create('construction_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('work_id');
            $table->foreign('work_id')->references('id')->on('construction_works')->onDelete('cascade');
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
        Schema::dropIfExists('construction_approvals');
        Schema::dropIfExists('construction_photos');
        Schema::dropIfExists('construction_issues');
        Schema::dropIfExists('construction_weekly_progresses');
        Schema::dropIfExists('construction_scopes');
        Schema::dropIfExists('construction_works');
    }
};

