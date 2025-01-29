<?php

namespace App\Exports;

use App\Models\CustomerDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, WithHeadings
{
        /**
        * @return \Illuminate\Support\Collection
        */
        public function collection()
        {
            // Fetch all customers with necessary relations
            return CustomerDetail::with('coaTitle')
                ->whereHas('coaTitle', function ($query) {
                    $query->where('is_customer_vendor', 'customer')
                        ->where('company_id', session('company_id'));
                })
                ->get()
                ->map(function ($customer) {
                    return [
                        'Customer ID' => $customer->id?? '',
                        'Customer Name' => $customer->coaTitle->name ?? '',
                        'Group ID' => $customer->coaTitle->group_id ?? '',
                        'Balance' => $customer->coaTitle->balance ?? 0,
                        'CNIC' => $customer->cnic ?? '',
                        'STRN' => $customer->strn ?? '',
                        'NTN' => $customer->ntn ?? '',
                        'Discount' => $customer->discount ?? 0,
                        'Bonus' => $customer->bonus ?? 0,
                        'Credit Limit' => $customer->credit_limit ?? 0,
                        'Payment Terms' => $customer->payment_terms ?? 0,
                        'Address' => $customer->address ?? '',
                        'Phone' => $customer->phone ?? '',
                        'Email' => $customer->email ?? '',
                    ];
                });
        }

        public function headings(): array
        {
            return [
                'Customer ID',
                'Customer Name',
                'Group ID',
                'Balance',
                'CNIC',
                'STRN',
                'NTN',
                'Discount',
                'Bonus',
                'Credit Limit',
                'Payment Terms',
                'Address',
                'Phone',
                'Email',
            ];
        }
}
