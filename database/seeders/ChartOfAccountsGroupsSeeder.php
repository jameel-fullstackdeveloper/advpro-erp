<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('chart_of_accounts_groups')->truncate();


        DB::table('chart_of_accounts_groups')->insert([
            [
                'name' => 'Cash',
                'type_id' => 1,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bank',
                'type_id' => 1,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Debtors',
                'type_id' => 1,
                'company_id' => 1,
                'is_customer_vendor' => 'customer',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Creditors',
                'type_id' => 3,
                'company_id' => 1,
                'is_customer_vendor' => 'vendor',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operating Expenses',
                'type_id' => 6,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrative Expenses',
                'type_id' => 6,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Owners Equity',
                'type_id' => 4,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Sales of Goods',
                'type_id' => 5,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accrued Expenses',
                'type_id' => 3,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Sales Brokers',
                'type_id' => 3,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Purchase Brokers',
                'type_id' => 3,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Taxes Payable',
                'type_id' => 3,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Cost of Goods Sold',
                'type_id' => 6,
                'company_id' => 1,
                'is_customer_vendor' => null,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],





        ]);
    }
}
