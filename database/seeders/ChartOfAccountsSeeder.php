<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('chart_of_accounts')->truncate();

        DB::table('chart_of_accounts')->insert([
            [
                'name' => 'Cash on Hand',
                'group_id' => 1,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Meezan Bank',
                'group_id' => 2,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Discount',
                'group_id' => 5,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Sales',
                'group_id' => 8,
                'balance' => 0,
                'drcr' => 'Cr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Freight-Out Payable',
                'group_id' => 9,
                'balance' => 0,
                'drcr' => 'Cr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Freight-Out Exp',
                'group_id' => 5,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Freight-In Payable',
                'group_id' => 9,
                'balance' => 0,
                'drcr' => 'Cr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Freight-In Exp',
                'group_id' => 5,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Sales Commission Expense',
                'group_id' => 5,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Purchase Commission Expense',
                'group_id' => 5,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Sales Tax Payable',
                'group_id' => 12,
                'balance' => 0,
                'drcr' => 'Cr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Purchases',
                'group_id' => 13,
                'balance' => 0,
                'drcr' => 'Dr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Further Tax Payable',
                'group_id' => 12,
                'balance' => 0,
                'drcr' => 'Cr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'name' => 'Advance WHT Payable',
                'group_id' => 12,
                'balance' => 0,
                'drcr' => 'Cr.',
                'company_id' => 1,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),

            ],
        ]);
    }
}
