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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('eula_agreed')->default(false)->after('authorities');
            $table->timestamp('eula_agreed_at')->nullable()->after('eula_agreed');
            $table->string('eula_version', 20)->default('1.0')->after('eula_agreed_at');
        });

        // Set existing users to already agreed so they are not blocked immediately
        \Illuminate\Support\Facades\DB::table('users')->update([
            'eula_agreed' => true,
            'eula_agreed_at' => now(),
            'eula_version' => '1.0',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['eula_agreed', 'eula_agreed_at', 'eula_version']);
        });
    }
};
