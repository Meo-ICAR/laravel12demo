<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::with('updatedByUser')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'piva' => 'nullable|string|max:255',
            'crm' => 'nullable|string|max:255',
            'callcenter' => 'nullable|string|max:255',
        ]);

        $company = Company::create($validated + [
            'updated_by' => auth()->id()
        ]);

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'piva' => 'nullable|string|max:255',
            'crm' => 'nullable|string|max:255',
            'callcenter' => 'nullable|string|max:255',
        ]);

        $company->update($validated + [
            'updated_by' => auth()->id()
        ]);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->update(['deleted_by' => auth()->id()]);
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    public function trashed()
    {
        $companies = Company::onlyTrashed()
            ->with('deletedByUser')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('companies.trashed', compact('companies'));
    }

    public function restore($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();

        return redirect()->route('companies.trashed')
            ->with('success', 'Company restored successfully.');
    }

    public function forceDelete($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->forceDelete();

        return redirect()->route('companies.trashed')
            ->with('success', 'Company permanently deleted.');
    }
}
