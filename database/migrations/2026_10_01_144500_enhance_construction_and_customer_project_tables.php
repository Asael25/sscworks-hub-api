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
        // 1. Tambah kolom notes di construction_works
        if (Schema::hasTable('construction_works') && !Schema::hasColumn('construction_works', 'notes')) {
            Schema::table('construction_works', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('budget');
            });
        }

        // 2. Tambah kolom quantity & unit_of_measure di construction_scopes
        if (Schema::hasTable('construction_scopes')) {
            Schema::table('construction_scopes', function (Blueprint $table) {
                if (!Schema::hasColumn('construction_scopes', 'quantity')) {
                    $table->string('quantity')->nullable()->after('scope');
                }
                if (!Schema::hasColumn('construction_scopes', 'unit_of_measure')) {
                    $table->string('unit_of_measure')->nullable()->after('quantity');
                }
            });
        }

        // 3. Buat tabel construction_attachments
        if (!Schema::hasTable('construction_attachments')) {
            Schema::create('construction_attachments', function (Blueprint $table) {
                $table->id();
                $table->string('work_id');
                $table->foreign('work_id')->references('id')->on('construction_works')->onDelete('cascade');
                $table->string('file_name');
                $table->string('file_type')->nullable();
                $table->integer('file_size_kb')->default(0);
                $table->string('uploaded_by')->nullable();
                $table->string('uploaded_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tambah kolom notes di customer_projects
        if (Schema::hasTable('customer_projects') && !Schema::hasColumn('customer_projects', 'notes')) {
            Schema::table('customer_projects', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('retention');
            });
        }

        // 5. Buat tabel customer_project_attachments
        if (!Schema::hasTable('customer_project_attachments')) {
            Schema::create('customer_project_attachments', function (Blueprint $table) {
                $table->id();
                $table->string('project_id');
                $table->foreign('project_id')->references('id')->on('customer_projects')->onDelete('cascade');
                $table->string('file_name');
                $table->string('file_type')->nullable();
                $table->integer('file_size_kb')->default(0);
                $table->string('uploaded_by')->nullable();
                $table->string('uploaded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_project_attachments');
        if (Schema::hasTable('customer_projects') && Schema::hasColumn('customer_projects', 'notes')) {
            Schema::table('customer_projects', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        Schema::dropIfExists('construction_attachments');
        if (Schema::hasTable('construction_scopes')) {
            Schema::table('construction_scopes', function (Blueprint $table) {
                $table->dropColumn(['quantity', 'unit_of_measure']);
            });
        }
        if (Schema::hasTable('construction_works') && Schema::hasColumn('construction_works', 'notes')) {
            Schema::table('construction_works', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
