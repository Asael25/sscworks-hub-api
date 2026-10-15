<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_units', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. UNT-001
            $table->string('code')->unique(); // e.g. HO, BPN, SBY
            $table->string('name')->unique(); // e.g. SSC Works — Head Office
            $table->string('type')->default('Branch'); // Head Office, Branch, Unit, Project
            $table->string('parent')->nullable()->default('—');
            $table->string('status')->default('Active'); // Active, Inactive
            $table->timestamps();
        });

        // Seed initial default units
        $now = now();
        $initialUnits = [
            [
                'id' => 'UNT-001',
                'code' => 'HO',
                'name' => 'SSC Works — Head Office',
                'type' => 'Head Office',
                'parent' => '—',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 'UNT-002',
                'code' => 'BPN',
                'name' => 'SSC Works — Balikpapan',
                'type' => 'Branch',
                'parent' => 'SSC Works — Head Office',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 'UNT-003',
                'code' => 'PLW',
                'name' => 'SSC Works — Palu',
                'type' => 'Branch',
                'parent' => 'SSC Works — Head Office',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 'UNT-004',
                'code' => 'SBY',
                'name' => 'SSC Works — Surabaya',
                'type' => 'Branch',
                'parent' => 'SSC Works — Head Office',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 'UNT-005',
                'code' => 'SMR',
                'name' => 'SSC Works — Samarinda',
                'type' => 'Unit',
                'parent' => 'SSC Works — Balikpapan',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('system_units')->insert($initialUnits);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_units');
    }
};
