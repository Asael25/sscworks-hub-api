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
        Schema::table('legal_attachments', function (Blueprint $table) {
            $table->string('file_url')->nullable();
        });
        Schema::table('construction_attachments', function (Blueprint $table) {
            $table->string('file_url')->nullable();
        });
        Schema::table('customer_project_attachments', function (Blueprint $table) {
            $table->string('file_url')->nullable();
        });
        Schema::table('approval_attachments', function (Blueprint $table) {
            $table->string('file_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_attachments', function (Blueprint $table) {
            $table->dropColumn('file_url');
        });
        Schema::table('construction_attachments', function (Blueprint $table) {
            $table->dropColumn('file_url');
        });
        Schema::table('customer_project_attachments', function (Blueprint $table) {
            $table->dropColumn('file_url');
        });
        Schema::table('approval_attachments', function (Blueprint $table) {
            $table->dropColumn('file_url');
        });
    }
};
