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
        Schema::table('legal_requests', function (Blueprint $table) {
            $table->text('purpose')->nullable()->after('description');
            $table->text('notes_text')->nullable()->after('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_requests', function (Blueprint $table) {
            $table->dropColumn(['purpose', 'notes_text']);
        });
    }
};
