<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allMenus = [
            "Dashboard",
            "Approval Center",
            "Legal Operations",
            "Internal Construction",
            "Project Customer",
            "Cashflow",
            "Budgeting",
            "Document Center",
            "Chat & Collaboration",
            "System Settings",
            "Help Center"
        ];

        $allAuthorities = [
            "Submit",
            "Edit",
            "Delete",
            "Approval",
            "Export",
            "Master Data",
            "manage_cashflow_master"
        ];

        $users = [
            [
                'identifier' => 'virga@sscworks.com',
                'username' => 'Virga',
                'password' => Hash::make('adminap'), // Hashed password
                'name' => 'Virga Damayanti',
                'role' => 'Account Payable',
                'primary_unit' => 'SSC Works — Head Office',
                'additional_units' => json_encode([]),
                'hub_access' => 'Active',
                'menu_access' => json_encode($allMenus),
                'authorities' => json_encode($allAuthorities),
            ],
            [
                'identifier' => 'sultan@sscworks.com',
                'username' => 'Sultan',
                'password' => Hash::make('user123'),
                'name' => 'Andi Sultan',
                'role' => 'Staff Commercial',
                'primary_unit' => 'SSC Works — Head Office',
                'additional_units' => json_encode([]),
                'hub_access' => 'Active',
                'menu_access' => json_encode([
                    "Dashboard",
                    "Approval Center",
                    "Project Customer",
                    "Budgeting",
                    "Document Center",
                    "Chat & Collaboration",
                    "Help Center"
                ]),
                'authorities' => json_encode(["Submit", "Edit", "Approval", "Export"]),
            ],
            [
                'identifier' => 'hilman@sscworks.com',
                'username' => 'Hilman',
                'password' => Hash::make('user123'),
                'name' => 'Lazuardhi Hilman Lathif',
                'role' => 'General Manager',
                'primary_unit' => 'SSC Works — Head Office',
                'additional_units' => json_encode([]),
                'hub_access' => 'Active',
                'menu_access' => json_encode($allMenus),
                'authorities' => json_encode(["Submit", "Edit", "Approval", "Export"]),
            ],
            [
                'identifier' => 'rudi@sscworks.com',
                'username' => 'Rudi',
                'password' => Hash::make('admin123'),
                'name' => 'Fachrudi Putra Pamuji',
                'role' => 'IT & Bussiness System Support',
                'primary_unit' => 'SSC Works — Head Office',
                'additional_units' => json_encode(["SSC Works - Surabaya"]),
                'hub_access' => 'Active',
                'menu_access' => json_encode($allMenus),
                'authorities' => json_encode($allAuthorities),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['identifier' => $user['identifier']],
                array_merge($user, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
