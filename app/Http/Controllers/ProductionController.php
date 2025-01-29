<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\ProductionDetail;
use App\Models\SalesProduct;
use App\Models\PurchaseItem;
use App\Models\PurchaseItemGroup;
use App\Models\ProductionBag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Items;
use App\Models\ItemGroup;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['finishedProduct', 'details.rawMaterial'])->get();
        return view('productions.index', compact('productions'));
    }

    public function create()
    {
        $finishedProducts = Items::where('item_type', 'sale')->get();

        // Fetch purchase item groups with their items, excluding groups with ID 9 and 10
        $purchaseItemGroups = ItemGroup::with(['items' => function ($query) {
            $query->where('item_type', 'purchase'); // Only include purchasable items
        }])
        ->whereNotIn('id', [8,9, 10]) // Exclude item groups with ID 9 and 10
        ->whereHas('items', function ($query) {
            $query->where('item_type', 'purchase'); // Ensure item groups have purchasable items
        })
        ->get();


        //dd($purchaseItemGroups);

       // $purchaseItemGroups = PurchaseItemGroup::with('purchaseItems')->get();
        $today = date('Y-m-d'); // Get today's date in the required format

        return view('productions.create', compact('purchaseItemGroups','finishedProducts','today'));

    }

    public function store(Request $request)
    {

        // Get current year
        $currentYear = date('Y');

        // Define your maximum allowed back year (e.g., 2 years ago)
        $allowedYear = $currentYear - 2;


        $request->validate([
            'production_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
            'finished_product' => 'required',
            'lots' => 'required|numeric|min:1',
            'perlots' => 'required|numeric|min:1',
            'defaultbags_perlot' => 'required|numeric|min:1',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.quantity_used' => 'required|numeric|min:0',
          //  'comments' => 'required|string|max:1000',

        ]);

        /*
        // Custom validation to check that at least one finished product has a quantity > 0
        $hasFinishedProduct = collect($request->finished_products)->contains(function ($product) {
            return $product['quantity'] > 0;
        });



        // If no finished product has a quantity greater than 0, return with an error
        if (!$hasFinishedProduct) {
            return redirect()->back()->withErrors(['finished_products' => 'You must fill in at least one Finished Product.']);
        }*/


         // Custom validation to check that at least one finished product has a quantity > 0
         $hasRawProduct = collect($request->raw_materials)->contains(function ($product) {
            return $product['quantity_used'] > 0;
        });

        // If no finished product has a quantity greater than 0, return with an error
        if (!$hasRawProduct) {
            return redirect()->back()->withErrors(['raw_products' => 'You must fill in at least one Material Used']);
        }


        DB::beginTransaction();

        try {
            // Create the production record, now including finished_product_id
            $production = Production::create([
                'production_date' => $request->production_date,
                'product_id' => $request->finished_product,
                'lots' => $request->lots,
                'perlots' => $request->perlots,
                'defaultbags_perlot' => $request->defaultbags_perlot,
                'short_perlot' => $request->short_perlot,
                'excess_perlot' => $request->excess_perlot,
                'actual_produced'=> $request->actual_produced,
                'comments' => $request->comments ? $request->comments : 'Nil',
                'company_id' => session('company_id'),
                'financial_year_id' => 1,
                'created_by' => auth()->id(),
            ]);


           /* foreach ($request->finished_products as $productId => $finishedProduct) {
                // Only insert products with a quantity greater than 0
                if ($finishedProduct['quantity'] > 0) {
                    ProductionBag::create([
                        'production_id' => $production->id,
                        'product_id' => $productId,
                        'quantity' => $finishedProduct['quantity'],
                    ]);
                }
            }*/

            foreach ($request->raw_materials as $productId => $raw_material) {
                // Only insert products with a quantity greater than 0
                if ($raw_material['quantity_used'] > 0) {
                    ProductionDetail::create([
                        'production_id' => $production->id,
                        'raw_material_id' => $productId,
                        'quantity_used' => $raw_material['quantity_used'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('productions.index')->with('success', 'Production recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Return an error message and show exception details for debugging
            return redirect()->back()->with('error', 'Error recording production: ' . $e->getMessage());

        }
    }

    public function edit($id)
    {

        $finished_Products = Items::where('item_type', 'sale')->get();


        // Retrieve the production record along with the finished products and their pivot quantity
        $production = Production::with(['finishedProduct' => function ($query) {
            $query->withPivot('quantity');
        }, 'details.rawMaterial'])->findOrFail($id);

        // Pre-process finished products to make it easier for the view
        $finishedProductsData = [];
        foreach ($production->finishedProduct as $finishedProduct) {
            $finishedProductsData[$finishedProduct->id] = $finishedProduct->pivot->quantity ?? 0;
        }

        // Fetch finished products and purchase item groups for raw materials
        $finishedProducts = Items::where('item_type', 'sale')->get();;
        $purchaseItemGroups = ItemGroup::with('purchaseItems')->get();

        // Return the edit view with the production and related data
        return view('productions.edit', compact('production', 'finishedProducts', 'purchaseItemGroups', 'finishedProductsData','finished_Products'));
    }

    public function update(Request $request, $id)
    {

        // Get current year
        $currentYear = date('Y');

        // Define your maximum allowed back year (e.g., 2 years ago)
        $allowedYear = $currentYear - 2;

        $request->validate([
            'production_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . $allowedYear . '-01-01|before_or_equal:' . $currentYear . '-12-31',
            'finished_product' => 'required',
            'lots' => 'required|numeric|min:1',
            'perlots' => 'required|numeric|min:1',
            'defaultbags_perlot' => 'required|numeric|min:1',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.quantity_used' => 'required|numeric|min:0',
        ]);

        // Custom validation to check that at least one raw material has a quantity used > 0
        $hasRawProduct = collect($request->raw_materials)->contains(function ($product) {
            return $product['quantity_used'] > 0;
        });

        // If no raw material has a quantity used greater than 0, return with an error
        if (!$hasRawProduct) {
            return redirect()->back()->withErrors(['raw_products' => 'You must fill in at least one Raw Material']);
        }

        DB::beginTransaction();

        try {
            // Retrieve the production record
            $production = Production::findOrFail($id);

            // Update the production record
            $production->update([
                'production_date' => $request->production_date,
                'product_id' => $request->finished_product,
                'lots' => $request->lots,
                'perlots' => $request->perlots,
                'defaultbags_perlot' => $request->defaultbags_perlot,
                'short_perlot' => $request->short_perlot,
                'excess_perlot' => $request->excess_perlot,
                'actual_produced'=> $request->actual_produced,
                'comments' => $request->comments,
                'company_id' => session('company_id'),
                'financial_year_id' => 1,
                'updated_by' => auth()->id(),
            ]);


            // Update the raw materials (ProductionDetail)
            foreach ($request->raw_materials as $productId => $rawMaterial) {
                if ($rawMaterial['quantity_used'] > 0) {
                    // Update existing or create new record for raw material
                    ProductionDetail::updateOrCreate(
                        ['production_id' => $production->id, 'raw_material_id' => $productId],
                        ['quantity_used' => $rawMaterial['quantity_used']]
                    );
                } else {
                    // Remove the record if quantity used is zero
                    ProductionDetail::where('production_id', $production->id)
                        ->where('raw_material_id', $productId)
                        ->delete();
                }
            }

            DB::commit();

            return redirect()->route('productions.index')->with('success', 'Production updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

                 // Return an error message and show exception details for debugging
                 return redirect()->back()->with('error', 'Error recording production: ' . $e->getMessage());
        }
    }




    public function print($id)
    {
       // Retrieve the production record with necessary relationships
        /*$production = Production::with([
            'details.rawMaterial',
        ])->findOrFail($id);*/

        $production = Production::with([
            'details.rawMaterial.itemGroup', // Corrected to use the itemGroup relationship
            'product'                        // Eager load the finished product
        ])->findOrFail($id);

        $groupedMaterials = $production->details->groupBy(function($detail) {
            // Ensure rawMaterial and its group are not null before trying to access the group
            return $detail->rawMaterial && $detail->rawMaterial->itemGroup
                ? $detail->rawMaterial->itemGroup->name
                : 'Unknown';
        });


        // Return the view with production data and grouped materials
        return view('productions.print', compact('production', 'groupedMaterials'));


    }






}
