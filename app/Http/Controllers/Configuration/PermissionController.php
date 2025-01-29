<?php

namespace App\Http\Controllers\Configuration;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;


class PermissionController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:permissions view')->only('index');
        $this->middleware('permission:permissions edit')->only('edit');
        $this->middleware('permission:permissions create')->only('create');
        $this->middleware('permission:permissions delete')->only('destroy');
    }


    public function index()
    {
        $permissions = Permission::all()->groupBy('module');

        return view('configuration.permissions.index', [
            'groupedPermissions' => $permissions
        ]);
    }

    public function create()
    {
        return view('configuration.permissions.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'module' => 'required|string',
            'permissions' => 'required|array',
        ]);

        $errors = [];

        foreach ($request->permissions as $permission) {
            $permissionName = "{$request->module} {$permission}";

            // Check if the permission already exists
            if (Permission::where('name', $permissionName)->where('guard_name', 'web')->exists()) {
                // Add error message for the specific permission
                $errors[] = "Permission '{$permissionName}' already exists.";
            } else {
                // If permission doesn't exist, create it
                Permission::create([
                    'name' => $permissionName,
                    'module' => $request->module,
                    'guard_name' => 'web',
                ]);
            }
        }

        if (!empty($errors)) {
            // If there are validation errors, return them back with the input
            throw ValidationException::withMessages([
                'permissions' => $errors,
            ]);
        }

        return redirect()->route('permissions.index')->with('success', 'Permissions created successfully.');
    }

    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return view('configuration.permissions.show', compact('permission'));
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        // Extract the module and associated permissions without the module prefix
        $module = $permission->module;
        $selectedPermissions = Permission::where('module', $module)
                                        ->pluck('name')
                                        ->map(function ($name) use ($module) {
                                            return str_replace("{$module} ", '', $name);
                                        })
                                        ->toArray();

        return view('configuration.permissions.edit', compact('permission', 'selectedPermissions', 'module'));
    }


    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'module' => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        // Get the original module name to identify existing permissions
        $permission = Permission::findOrFail($id);
        $originalModule = $permission->module;

        // Get all existing permissions for the module
        $existingPermissions = Permission::where('module', $originalModule)
                                          ->where('guard_name', 'web')
                                          ->get()
                                          ->keyBy('name');

        // Loop through the provided permissions and either update or create them
        foreach ($request->permissions as $permissionType) {
            $permissionName = "{$request->module} {$permissionType}";

            if (isset($existingPermissions[$permissionName])) {
                // If the permission already exists, update the module if needed
                $existingPermissions[$permissionName]->update([
                    'module' => $request->module,
                ]);
                // Remove from the collection to keep track of what's left
                unset($existingPermissions[$permissionName]);
            } else {
                // If the permission doesn't exist, create it
                Permission::create([
                    'name' => $permissionName,
                    'module' => $request->module,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Check if any remaining permissions are associated with roles
        foreach ($existingPermissions as $permission) {
            if ($permission->roles()->count() > 0) {
                // If the permission is associated with any roles, return with an error
                return redirect()->route('permissions.index')->with('error', "Cannot delete permission '{$permission->name}' because it is associated with one or more roles.");
            } else {
                // If the permission is not associated with any roles, delete it
                $permission->delete();
            }
        }

        // Clear permission cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();



        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Check if the permission is associated with any roles
        if ($permission->roles()->exists()) {
            return redirect()->route('permissions.index')
                ->with('error', 'Permission cannot be deleted because it is associated with one or more roles.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');

    }
}
