<?php

namespace App\Exports;

use App\Models\ChartOfAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ChartOfAccountsExport implements FromCollection, WithHeadings
{
    protected $selectedCategory;
    protected $searchTerm;

    public function __construct($selectedCategory, $searchTerm)
    {
        $this->selectedCategory = $selectedCategory;
        $this->searchTerm = $searchTerm;
    }

    public function collection()
    {
        $query = ChartOfAccount::query();

        // Apply filters
        if ($this->selectedCategory) {
            $query->whereHas('chartOfAccountGroup', function ($q) {
                $q->whereHas('chartOfAccountsType', function ($q) {
                    $q->where('category', $this->selectedCategory);
                });
            });
        }

        if ($this->searchTerm) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Group ID', 'Balance', 'DR/CR', 'Created At', 'Updated At'
        ];
    }
}

