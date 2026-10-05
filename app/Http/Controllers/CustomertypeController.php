<?php

namespace App\Http\Controllers;

use App\Models\Customertype;
use Illuminate\Http\Request;

class CustomertypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customertypes = Customertype::all();
        return view('customertypes.index', compact('customertypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customertypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        Customertype::create($data);
        return redirect()->route('customertypes.index')->with('success', 'Customer type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customertype $customertype)
    {
        return view('customertypes.show', compact('customertype'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customertype $customertype)
    {
        return view('customertypes.edit', compact('customertype'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customertype $customertype)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $customertype->update($data);
        return redirect()->route('customertypes.index')->with('success', 'Customer type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customertype $customertype)
    {
        $customertype->delete();
        return redirect()->route('customertypes.index')->with('success', 'Customer type deleted successfully.');
    }
}
