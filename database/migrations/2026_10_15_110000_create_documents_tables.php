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
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->string('id')->primary(); // e.g. DOC-2026-00418
                $table->string('file_name');
                $table->text('file_url')->nullable();
                $table->string('module')->index();
                $table->string('category')->index();
                $table->string('related_request')->nullable()->index();
                $table->string('related_title')->nullable();
                $table->string('unit')->index();
                $table->string('file_type', 10)->index(); // PDF, XLSX, DOCX, JPG, PNG, etc.
                $table->string('size', 50)->default('0 KB');
                $table->string('uploaded_by');
                $table->date('uploaded_date')->index();
                $table->string('period', 50)->nullable()->index();
                $table->string('version', 20)->default('v1.0');
                $table->string('status', 30)->default('Active')->index();
                $table->date('expires_at')->nullable();
                $table->text('description')->nullable();
                $table->boolean('can_upload_version')->default(true);
                $table->boolean('can_delete')->default(true);
                $table->boolean('locked')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('document_versions')) {
            Schema::create('document_versions', function (Blueprint $table) {
                $table->id();
                $table->string('document_id');
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
                $table->string('version', 20);
                $table->string('file_name')->nullable();
                $table->text('file_url')->nullable();
                $table->string('size', 50)->nullable();
                $table->string('uploaded_by');
                $table->date('uploaded_date');
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'version']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
