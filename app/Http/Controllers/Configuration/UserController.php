<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:users view')->only('index');
        $this->middleware('permission:users edit')->only('edit');
        $this->middleware('permission:users create')->only('create');
        $this->middleware('permission:users delete')->only('destroy');
    }

    public function index()
    {
        // Get the logged-in user
        $currentUser = auth()->user();

        // Check if the current user has the super admin role
        if ($currentUser->hasRole('Super Admin')) {
            // Super admin can see all users, including other super admins
            $users = User::with(['roles', 'companies'])->get();
        } else {
            // Non-super admin users should not see super admin users
            $users = User::with(['roles', 'companies'])
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'Super Admin');
                })->get();
        }

        return view('configuration.users.index', compact('users'));
    }


    public function create()
    {
        //$roles = Role::where('guard_name', 'web')->get();
        // Exclude the "Super Admin" role from the list of roles
        $roles = Role::where('guard_name', 'web')->where('name', '!=', 'Super Admin')->get();
        $companies = Company::all(); // Assuming you have a Company model

        return view('configuration.users.create', compact('roles','companies'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'companies' => 'required|array|min:1', // Ensure at least one company is selected

        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        $user->syncRoles($validatedData['roles']);

         // Assign companies
        $user->companies()->sync($validatedData['companies']); // Sync the selected companies


        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::where('guard_name', 'web')->where('name', '!=', 'Super Admin')->get();
        $companies = Company::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        $userCompanies = $user->companies->pluck('id')->toArray(); // Get the assigned companies

        return view('configuration.users.edit', compact('user', 'roles', 'userRoles', 'companies', 'userCompanies'));
    }


    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'companies' => 'required|array|min:1', // Ensure at least one company is selected
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the user's name and email
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        // If a new password is provided, hash and update it
        if (!empty($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }

        // Save the user
        $user->save();

        // Sync the selected roles
        $user->syncRoles($validatedData['roles']);

        // Sync the selected companies
        $user->companies()->sync($validatedData['companies']); // Sync the companies with the user

        // Redirect back to the users index page with a success message
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Check if the user is associated with any companies
        if ($user->companies()->exists()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user because they are associated with companies.');
        }

        // Check if the user is associated with data in the sales module
        if ($user->sales()->exists()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user because they are associated with sales data.');
        }

        // Check if the user is associated with data in the purchase module
        if ($user->purchases()->exists()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user because they are associated with purchase data.');
        }

        // Check if the user is associated with data in the accounting module
        if ($user->accountingEntries()->exists()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user because they are associated with accounting entries.');
        }

        // Check if the user is associated with data in the inventory module
        if ($user->inventoryEntries()->exists()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user because they are associated with inventory data.');
        }

        // Check if the user is associated with data in the weighbridge module
        if ($user->weighbridgeEntries()->exists()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user because they are associated with weighbridge data.');
        }

        // If no associations found, proceed to delete the user
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

}

