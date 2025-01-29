<?php

namespace App\Http\Controllers\Configuration;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;


class RoleController extends Controller
{


    public function __construct()
    {
        $this->middleware('permission:roles view')->only('index');
        $this->middleware('permission:roles edit')->only('edit');
        $this->middleware('permission:roles create')->only('create');
        $this->middleware('permission:roles delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->where('name', '!=', 'Super Admin')->get();  // Exclude "Super Admin" role
        return view('configuration.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('configuration.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
        ]);

        if ($validatedData['name'] === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'Super Admin role cannot be created.');
        }

        $role = Role::create([
            'name' => $validatedData['name'],
            'guard_name' => 'web',
        ]);

        if(!empty($request->permissions)){
            foreach($request->permissions as $name) {
                $permission = Permission::where('name', $name)->where('guard_name', 'web')->first();
                if ($permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'Access to Super Admin role is restricted.');
        }
        return view('configuration.roles.show', compact('role'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'Super Admin role cannot be edited.');
        }
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('configuration.roles.edit', compact('role', 'permissions', 'rolePermissions'));
     }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'Super Admin role cannot be edited.');
        }

        $validatedData = $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'required|array',
        ]);

        $role->update(['name' => $validatedData['name']]);
        $role->syncPermissions($validatedData['permissions']);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
      }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'Super Admin role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Role cannot be deleted because it is associated with users.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');

    }
}
