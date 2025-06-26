<?php

namespace App\Http\Controllers;

use App\Models\Fornitori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Imports\FornitoriImport;
use Maatwebsite\Excel\Facades\Excel;

class FornitoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Fornitori::query();

        // Filter by search term if provided - search across multiple fields case-insensitively
        if ($request->filled('name')) {
            $searchTerm = $request->name;
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(codice) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(coge) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(piva) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(regione) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(citta) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            });
        }

        // Filter by coordinatore if provided
        if ($request->filled('coordinatore')) {
            $coordinatoreTerm = strtolower($request->coordinatore);
            $query->whereRaw('LOWER(coordinatore) LIKE ?', ['%' . $coordinatoreTerm . '%']);
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Validate sort parameters
        $allowedSortBy = ['name', 'codice', 'coge', 'piva', 'email', 'anticipo', 'contributo', 'regione', 'citta', 'coordinatore', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'asc';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Build query parameters for pagination (only include non-empty values)
        $queryParams = [];
        if ($request->filled('name')) {
            $queryParams['name'] = $request->name;
        }
        if ($request->filled('coordinatore')) {
            $queryParams['coordinatore'] = $request->coordinatore;
        }
        if ($request->filled('sort_by')) {
            $queryParams['sort_by'] = $request->sort_by;
        }
        if ($request->filled('sort_direction')) {
            $queryParams['sort_direction'] = $request->sort_direction;
        }

        $fornitoris = $query->paginate(10)->appends($queryParams);
        return view('fornitoris.index', compact('fornitoris', 'sortBy', 'sortDirection'));
    }

    public function import(Request $request)
    {
        try {
            // Check if file was uploaded
            if (!$request->hasFile('file')) {
                return redirect()->route('fornitoris.index')->with('error', 'No file was selected. Please choose a file to import.');
            }

            $file = $request->file('file');

            // Check if file is valid
            if (!$file->isValid()) {
                return redirect()->route('fornitoris.index')->with('error', 'The uploaded file is not valid. Please try again.');
            }

            // Validate file
            $request->validate([
                'file' => 'required|file|max:2048', // 2MB max
            ], [
                'file.required' => 'Please select a file to import.',
                'file.file' => 'The uploaded file is not valid.',
                'file.max' => 'The file size must not exceed 2MB.',
            ]);

            // Custom validation for file type
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['csv', 'tsv', 'xlsx', 'xls'];

            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->route('fornitoris.index')->with('error', 'The file must be a CSV, TSV, XLSX, or XLS file. Detected extension: ' . $extension);
            }

            // Determine delimiter based on file extension
            $delimiter = ($extension === 'tsv') ? "\t" : ',';

            Excel::import(new FornitoriImport($delimiter), $file);

            return redirect()->route('fornitoris.index')->with('success', 'Fornitori imported successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('fornitoris.index')->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->route('fornitoris.index')->with('error', 'Error importing fornitori: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('fornitoris.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codice' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'piva' => 'nullable|string|max:16',
            'email' => 'nullable|string|max:255',
            'anticipo' => 'nullable|numeric|min:0|max:999999999.99',
            'contributo' => 'nullable|numeric|min:0|max:999999999.99',
            'contributo_description' => 'nullable|string|max:255',
            'anticipo_description' => 'nullable|string|max:255',
            'issubfornitore' => 'nullable|boolean',
            'operatore' => 'nullable|string|max:255',
            'iscollaboratore' => 'nullable|boolean',
            'isdipendente' => 'nullable|boolean',
            'regione' => 'nullable|string|max:255',
            'citta' => 'nullable|string|max:255',
            'coordinatore' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
        ]);
        $data['id'] = (string) Str::uuid();
        Fornitori::create($data);
        return redirect()->route('fornitoris.index')->with('success', 'Fornitore created successfully.');
    }

    public function show(Fornitori $fornitori)
    {
        return view('fornitoris.show', compact('fornitori'));
    }

    public function edit(Fornitori $fornitori)
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('fornitoris.edit', compact('fornitori', 'companies'));
    }

    public function update(Request $request, Fornitori $fornitori)
    {
        $data = $request->validate([
            'codice' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'piva' => 'nullable|string|max:16',
            'email' => 'nullable|string|max:255',
            'anticipo' => 'nullable|numeric|min:0|max:999999999.99',
            'contributo' => 'nullable|numeric|min:0|max:999999999.99',
            'contributo_description' => 'nullable|string|max:255',
            'anticipo_description' => 'nullable|string|max:255',
            'issubfornitore' => 'nullable|boolean',
            'operatore' => 'nullable|string|max:255',
            'iscollaboratore' => 'nullable|boolean',
            'isdipendente' => 'nullable|boolean',
            'regione' => 'nullable|string|max:255',
            'citta' => 'nullable|string|max:255',
            'coordinatore' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
        ]);
        $fornitori->update($data);
        return redirect()->route('fornitoris.index')->with('success', 'Fornitore updated successfully.');
    }

    public function destroy(Fornitori $fornitori)
    {
        $fornitori->delete();
        return redirect()->route('fornitoris.index')->with('success', 'Fornitore deleted successfully.');
    }
}
