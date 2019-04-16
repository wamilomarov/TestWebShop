<?php

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        set_time_limit(2000);
        $path = 'storage/data/customers.csv';
        $customers = Customer::parseCSV($path);

        foreach ($customers as $customer) {
            // switching the datetime format
            $created_at = date("Y-m-d H:i:s", strtotime($customer[4]));


            // to avoid repeating emails, phones can be the same
            $newCustomer = Customer::firstOrNew(
                // make emails lowercase in database
                ['email' => strtolower($customer[2])],
                ['id' => $customer[0], 'job_title' => $customer[1], 'full_name' => $customer[3],
                    'created_at' => $created_at, 'phone' => $customer[5]]
            );

            $newCustomer->save();
        }
    }
}
