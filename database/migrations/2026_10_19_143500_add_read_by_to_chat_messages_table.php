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
        if (Schema::hasTable('chat_messages') && !Schema::hasColumn('chat_messages', 'read_by')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->json('read_by')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chat_messages') && Schema::hasColumn('chat_messages', 'read_by')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropColumn('read_by');
            });
        }
    }
};
