<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tablesAndColumns = [
            'approval_attachments' => ['file_url', 'file_path'],
            'documents' => ['file_url'],
            'document_versions' => ['file_url'],
            'customer_project_attachments' => ['file_url'],
            'construction_attachments' => ['file_url'],
        ];

        foreach ($tablesAndColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                DB::statement("UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, 'http://localhost:8000/storage/', '/storage/') WHERE `{$column}` LIKE '%localhost:8000/storage/%'");
                DB::statement("UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, 'http://127.0.0.1:8000/storage/', '/storage/') WHERE `{$column}` LIKE '%127.0.0.1:8000/storage/%'");
                DB::statement("UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, 'http://localhost:8080/storage/', '/storage/') WHERE `{$column}` LIKE '%localhost:8080/storage/%'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tetap menggunakan path relatif /storage/
    }
};
