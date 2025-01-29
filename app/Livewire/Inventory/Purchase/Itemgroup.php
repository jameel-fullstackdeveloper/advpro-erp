<?php

namespace App\Livewire\Inventory\Purchase;

use Livewire\Component;
use App\Models\CustomerDetail;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountGroup;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class Itemgroup extends Component
{
    use WithPagination;

    public function mount()
    {
        abort_if(!auth()->user()->can('inventory view'), 403);
    }

    public function render()
    {
        return view('livewire.inventory.purchase.itemgroup');
    }


}
