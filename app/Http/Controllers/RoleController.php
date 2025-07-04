<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleController extends Controller
{


    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
            'company_id' => 'nullable|string|max:36',
        ]);
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
            'company_id' => $data['company_id'] ?? null,
        ]);
        $role->syncPermissions($data['permissions']);
        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
            'company_id' => 'nullable|string|max:36',
        ]);
        $role->update([
            'name' => $data['name'],
            'company_id' => $data['company_id'] ?? null,
        ]);
        $role->syncPermissions($data['permissions']);
        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role that is assigned to users');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }

    public function companyRoles($company_id)
    {
        // Get all roles for this company or global roles (company_id is null)
        $roles = \Spatie\Permission\Models\Role::where(function($q) use ($company_id) {
            $q->where('company_id', $company_id)->orWhereNull('company_id');
        })->with(['users' => function($q) use ($company_id) {
            $q->where('company_id', $company_id);
        }])->orderBy('name')->get();

        $company = \App\Models\Company::find($company_id);
        return view('roles.company_roles', compact('roles', 'company'));
    }
}
