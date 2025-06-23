<?php

namespace App\Http\Controllers;

use App\Models\Call;
use Illuminate\Http\Request;
use App\Imports\CallsImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class CallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Call::query();

        // Filter by numero_chiamato if provided
        if ($request->has('numero_chiamato') && $request->numero_chiamato !== '') {
            $query->where('numero_chiamato', 'like', '%' . $request->numero_chiamato . '%');
        }

        // Filter by stato_chiamata if provided
        if ($request->has('stato_chiamata') && $request->stato_chiamata !== '') {
            $query->where('stato_chiamata', $request->stato_chiamata);
        }

        // Filter by esito if provided
        if ($request->has('esito') && $request->esito !== '') {
            $query->where('esito', 'like', '%' . $request->esito . '%');
        }

        // Filter by utente if provided
        if ($request->has('utente') && $request->utente !== '') {
            $query->where('utente', 'like', '%' . $request->utente . '%');
        }

        // Filter by date range if provided
        if ($request->has('data_from') && $request->data_from !== '') {
            try {
                $dataFrom = Carbon::createFromFormat('Y-m-d', $request->data_from)->startOfDay();
                $query->where('data_inizio', '>=', $dataFrom);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        if ($request->has('data_to') && $request->data_to !== '') {
            try {
                $dataTo = Carbon::createFromFormat('Y-m-d', $request->data_to)->endOfDay();
                $query->where('data_inizio', '<=', $dataTo);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'data_inizio');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort parameters
        $allowedSortBy = ['numero_chiamato', 'data_inizio', 'durata', 'stato_chiamata', 'esito', 'utente', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'data_inizio';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);

        $calls = $query->paginate(15)->withQueryString();

        // Get unique values for filter dropdowns
        $statoChiamataOptions = Call::distinct()->pluck('stato_chiamata')->filter()->sort()->values();
        $esitoOptions = Call::distinct()->pluck('esito')->filter()->sort()->values();
        $utenteOptions = Call::distinct()->pluck('utente')->filter()->sort()->values();

        return view('calls.index', compact('calls', 'statoChiamataOptions', 'esitoOptions', 'utenteOptions', 'sortBy', 'sortDirection'));
    }

    public function import(Request $request)
    {
        try {
            // Check if file was uploaded
            if (!$request->hasFile('file')) {
                return redirect()->route('calls.index')->with('error', 'No file was selected. Please choose a file to import.');
            }

            $file = $request->file('file');

            // Check if file is valid
            if (!$file->isValid()) {
                return redirect()->route('calls.index')->with('error', 'The uploaded file is not valid. Please try again.');
            }

            // Validate file
            $request->validate([
                'file' => 'required|file|max:2048', // 2MB max to match PHP config
            ], [
                'file.required' => 'Please select a file to import.',
                'file.file' => 'The uploaded file is not valid.',
                'file.max' => 'The file size must not exceed 2MB.',
            ]);

            // Custom validation for file type
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['csv', 'xlsx', 'xls'];

            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->route('calls.index')->with('error', 'The file must be a CSV, XLSX, or XLS file. Detected extension: ' . $extension);
            }

            Excel::import(new CallsImport, $file);

            return redirect()->route('calls.index')->with('success', 'Calls imported successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('calls.index')->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->route('calls.index')->with('error', 'Error importing calls: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('calls.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_chiamato' => 'nullable|string|max:20',
            'data_inizio' => 'nullable|date',
            'durata' => 'nullable|string|max:10',
            'stato_chiamata' => 'nullable|string|max:50',
            'esito' => 'nullable|string|max:100',
            'utente' => 'nullable|string|max:255',
        ]);

        Call::create($data);
        return redirect()->route('calls.index')->with('success', 'Call created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Call $call)
    {
        return view('calls.show', compact('call'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Call $call)
    {
        return view('calls.edit', compact('call'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Call $call)
    {
        $data = $request->validate([
            'numero_chiamato' => 'nullable|string|max:20',
            'data_inizio' => 'nullable|date',
            'durata' => 'nullable|string|max:10',
            'stato_chiamata' => 'nullable|string|max:50',
            'esito' => 'nullable|string|max:100',
            'utente' => 'nullable|string|max:255',
        ]);

        $call->update($data);
        return redirect()->route('calls.index')->with('success', 'Call updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Call $call)
    {
        $call->delete();
        return redirect()->route('calls.index')->with('success', 'Call deleted successfully.');
    }

    /**
     * Display the calls dashboard with statistics and date filtering.
     */
    public function dashboard(Request $request)
    {
        $query = Call::query();

        // Apply date filters
        if ($request->has('date_from') && $request->date_from !== '') {
            try {
                $dateFrom = Carbon::createFromFormat('Y-m-d', $request->date_from)->startOfDay();
                $query->where('data_inizio', '>=', $dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            try {
                $dateTo = Carbon::createFromFormat('Y-m-d', $request->date_to)->endOfDay();
                $query->where('data_inizio', '<=', $dateTo);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        // Get filtered calls
        $filteredCalls = $query->get();

        // Calculate statistics
        $totalCalls = $filteredCalls->count();
        $answeredCalls = $filteredCalls->where('stato_chiamata', 'ANSWER')->count();
        $busyCalls = $filteredCalls->where('stato_chiamata', 'BUSY')->count();
        $noAnswerCalls = $filteredCalls->where('stato_chiamata', 'Non Risposto')->count();
        $otherStatusCalls = $totalCalls - $answeredCalls - $busyCalls - $noAnswerCalls;

        // Calculate total duration
        $totalDuration = $filteredCalls->sum(function($call) {
            return $call->getDurationInSeconds();
        });

        // Get top operators
        $topOperators = $filteredCalls->groupBy('utente')
            ->map(function($calls) {
                return [
                    'count' => $calls->count(),
                    'total_duration' => $calls->sum(function($call) {
                        return $call->getDurationInSeconds();
                    })
                ];
            })
            ->sortByDesc('count')
            ->take(10);

        // Get calls by hour
        $callsByHour = $filteredCalls->groupBy(function($call) {
            return $call->data_inizio ? $call->data_inizio->format('H') : '00';
        })
        ->map(function($calls) {
            return $calls->count();
        })
        ->sortKeys();

        // Get calls by day
        $callsByDay = $filteredCalls->groupBy(function($call) {
            return $call->data_inizio ? $call->data_inizio->format('Y-m-d') : '0000-00-00';
        })
        ->map(function($calls) {
            return $calls->count();
        })
        ->sortKeys();

        // Get top outcomes
        $topOutcomes = $filteredCalls->groupBy('esito')
            ->map(function($calls) {
                return $calls->count();
            })
            ->sortByDesc(function($count) {
                return $count;
            })
            ->take(10);

        // Recent calls for the table
        $recentCalls = $query->orderBy('data_inizio', 'desc')->limit(10)->get();

        return view('calls.dashboard', compact(
            'totalCalls',
            'answeredCalls',
            'busyCalls',
            'noAnswerCalls',
            'otherStatusCalls',
            'totalDuration',
            'topOperators',
            'callsByHour',
            'callsByDay',
            'topOutcomes',
            'recentCalls'
        ));
    }

    /**
     * Show the form for importing calls.
     */
    public function showImportForm()
    {
        return view('calls.import');
    }
}
