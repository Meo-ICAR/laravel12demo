<?php

namespace App\Http\Controllers;

use App\Models\Mfcompenso;
use Illuminate\Http\Request;
use App\Imports\MfcompensosImport;
use Maatwebsite\Excel\Facades\Excel;

class MfcompensoController extends Controller
{
    public function index()
    {
        $mfcompensos = Mfcompenso::paginate(15);
        return view('mfcompensos.index', compact('mfcompensos'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);
        Excel::import(new MfcompensosImport, $request->file('file'));
        return redirect()->route('mfcompensos.index')->with('success', 'Import completed!');
    }
    // ... other CRUD methods ...
}
