<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanySwitcher extends Component
{
    public $companies;
    public $selectedCompany;


    public function mount()
    {

       // Check if the user is a super admin
       if (Auth::user()->hasRole('Super Admin')) {
            // If the user is a super admin, get all companies
            $this->companies = Company::select('id', 'name', 'abv', 'avatar')->get();

            //dd( $this->companies);

        } else {
            // If the user is not a super admin, get companies associated with the user
            $this->companies = Company::join('user_company', 'companies.id', '=', 'user_company.company_id')
                ->where('user_company.user_id', Auth::id()) // Ensure you only get companies linked to the user
                ->select('companies.id', 'companies.name', 'companies.abv', 'companies.avatar')
                ->get();
        }


        // Set the default selected company (first one or session)
        $this->selectedCompany = session('company_id', $this->companies->first()->id ?? null);
    }

    public function switchCompany($companyId)
    {
        // Store selected company in session
        session(['company_id' => $companyId]);
        $this->selectedCompany = $companyId;

        // Instead of redirect, trigger a re-render
        $this->dispatch('companySwitched');

    }

    public function render()
    {
        $this->selectedCompany = Company::find(session('company_id', $this->companies->first()->id ?? null));

        return view('livewire.company-switcher');
    }
}

