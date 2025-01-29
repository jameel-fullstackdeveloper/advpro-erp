<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
       // Add all the seeders here
       $this->call([
        ChartOfAccountsTypesSeeder::class,
        ChartOfAccountsGroupsSeeder::class,
        ChartOfAccountsSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Enable foreign key checks

        DB::table('roles')->insert([
            'name' => 'Super Admin',
            'guard_name' =>  'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a new user with the password '123456'
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => Hash::make('12345678'), // Hashing the password for security
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'name' => 'Administrator',
            'guard_name' =>  'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a new user with the password '123456'
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'), // Hashing the password for security
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add other seeders as needed
        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => 'App\Models\User',
            'model_id' =>  1,
        ]);

        // Add other seeders as needed
        DB::table('companies')->insert([
            'name' => 'ABC Feeds',
            'address' => 'Plot # 852, Near Main Chowk, Hyderabad.',
            'email' => 'info@abcfeeds.com',
        ]);

        // Add other seeders as needed
        DB::table('sales_products_groups')->insert([
            'name' => 'Control',
            'company_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // Add other seeders as needed
        DB::table('sales_products')->insert([
            'product_name' => 'Layer 5-C',
            'group_id' => 1,
            'quantity' => 0,
            'price' => 5000,
            'balance' => 0,
            'company_id' => 1,
            'created_by' => 1,
            'created_at' => now(),
        ]);

         // Add other seeders as needed
         DB::table('purchase_items_groups')->insert([
            'name' => 'Raw Material',
            'company_id' => 1,
            'created_by' => 1,
            'created_at' => now(),
        ]);

        // Add other seeders as needed
        DB::table('user_company')->insert([
            'user_id' => 1,
            'company_id' => 1,
            'created_at' => now(),
        ]);

        // Add admin to company
        DB::table('user_company')->insert([
            'user_id' => 2,
            'company_id' => 1,
            'created_at' => now(),
        ]);

    }
}
