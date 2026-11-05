<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->string('user_unit')->nullable();
                $table->string('user_role')->nullable();
                $table->string('module');
                $table->string('action');
                $table->string('action_type')->nullable();
                $table->string('entity_type')->nullable();
                $table->string('entity_id')->nullable();
                $table->string('entity_title')->nullable();
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

