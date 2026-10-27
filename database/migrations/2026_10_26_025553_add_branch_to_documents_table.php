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
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'branch')) {
                $table->string('branch', 100)->nullable()->after('unit')->index();
            }
        });

        // Bersihkan data unit lama dan isi kolom branch
        $docs = \Illuminate\Support\Facades\DB::table('documents')->get();
        foreach ($docs as $doc) {
            $unit = $doc->unit ?? '';
            $branch = 'Head Office';
            $newUnit = $unit;

            if (stripos($unit, 'Balikpapan') !== false) {
                $branch = 'Cabang Balikpapan';
                $newUnit = 'Workshop Fabrikasi';
            } elseif (stripos($unit, 'Surabaya') !== false) {
                $branch = 'Cabang Surabaya';
                $newUnit = 'Gudang & Logistik';
            } elseif (stripos($unit, 'Palu') !== false || stripos($unit, 'Morowali') !== false) {
                $branch = 'Cabang Palu';
                $newUnit = 'Project Morowali';
            } elseif (stripos($unit, 'Samarinda') !== false) {
                $branch = 'Cabang Samarinda';
                $newUnit = 'Operasional Proyek';
            } elseif (stripos($unit, 'Jakarta') !== false) {
                $branch = 'Head Office';
                $newUnit = 'Engineering & Desain';
            } elseif (stripos($unit, 'Head Office') !== false || $unit === 'All Units') {
                $branch = 'Head Office';
                if ($doc->category === 'Cashflow' || stripos($doc->file_name, 'Pajak') !== false) {
                    $newUnit = 'Finance & Accounting';
                } elseif ($doc->category === 'Legal' || stripos($doc->file_name, 'Legal') !== false) {
                    $newUnit = 'Legal & Compliance';
                } elseif (stripos($doc->file_name, 'Progress') !== false || $doc->category === 'Construction') {
                    $newUnit = 'Engineering & Konstruksi';
                } else {
                    $newUnit = 'Operasional Head Office';
                }
            }

            \Illuminate\Support\Facades\DB::table('documents')
                ->where('id', $doc->id)
                ->update([
                    'branch' => $branch,
                    'unit' => $newUnit,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'branch')) {
                $table->dropColumn('branch');
            }
        });
    }
};
