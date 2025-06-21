<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceImportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\FornitoriController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Mfcompenso;

// Redirect root to login if not authenticated
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::get('login/microsoft', function () {
        return Socialite::driver('microsoft')->redirect();
    })->name('login.microsoft');

    Route::get('login/microsoft/callback', function () {
        $microsoftUser = Socialite::driver('microsoft')->user();
        $user = User::firstOrCreate([
            'email' => $microsoftUser->getEmail(),
        ], [
            'name' => $microsoftUser->getName() ?? $microsoftUser->getNickname() ?? $microsoftUser->getEmail(),
            'password' => bcrypt(str()->random(16)),
        ]);
        Auth::login($user, true);
        return redirect('/dashboard');
    });
});

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/home', function () {
        return view('dashboard');
    })->name('home');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Roles and Permissions Routes
    Route::middleware(['permission:role_management'])->group(function () {
        Route::resource('roles', RoleController::class);
    });

    Route::middleware(['permission:permission_management'])->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    // User Management Routes
    Route::middleware(['permission:user_management'])->group(function () {
        Route::get('users/import', [UserController::class, 'import'])->name('users.import');
        Route::post('users/import', [UserController::class, 'importStore'])->name('users.import.store');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('users', UserController::class);
        Route::get('users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    });

    // Company routes
    Route::middleware(['auth'])->group(function () {
        Route::get('companies/trashed', [CompanyController::class, 'trashed'])->name('companies.trashed');
        Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
        Route::delete('companies/{id}/force-delete', [CompanyController::class, 'forceDelete'])->name('companies.force-delete');
        Route::resource('companies', CompanyController::class);
    });

    // Invoice Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/import', [InvoiceImportController::class, 'index'])->name('invoices.import');
        Route::post('invoices/import', [InvoiceImportController::class, 'import'])->name('invoices.import.store');
    });

    // Fornitori routes
    Route::middleware(['permission:fornitori_management'])->group(function () {
        Route::resource('fornitoris', FornitoriController::class);
    });

    // MFCompensos routes
    Route::get('mfcompensos/proforma-summary', [App\Http\Controllers\MfcompensoController::class, 'proformaSummary'])->name('mfcompensos.proformaSummary');
    Route::post('mfcompensos/send-proforma-email', [App\Http\Controllers\MfcompensoController::class, 'sendProformaEmail'])->name('mfcompensos.sendProformaEmail');
    Route::post('mfcompensos/send-all-proforma-emails', [App\Http\Controllers\MfcompensoController::class, 'sendAllProformaEmails'])->name('mfcompensos.sendAllProformaEmails');
    Route::post('mfcompensos/mark-as-received', [App\Http\Controllers\MfcompensoController::class, 'markAsReceived'])->name('mfcompensos.markAsReceived');
    Route::post('mfcompensos/mark-as-paid', [App\Http\Controllers\MfcompensoController::class, 'markAsPaid'])->name('mfcompensos.markAsPaid');
    Route::post('mfcompensos/sync-denominazioni', [App\Http\Controllers\MfcompensoController::class, 'syncDenominazioniToFornitori'])->name('mfcompensos.syncDenominazioni');
    Route::get('mfcompensos/check-emails', [App\Http\Controllers\MfcompensoController::class, 'checkSentEmails'])->name('mfcompensos.checkSentEmails');
    Route::post('mfcompensos/import', [App\Http\Controllers\MfcompensoController::class, 'import'])->name('mfcompensos.import');
    Route::post('mfcompensos/bulk-update-to-proforma', [App\Http\Controllers\MfcompensoController::class, 'bulkUpdateToProforma'])->name('mfcompensos.bulkUpdateToProforma');
    Route::resource('mfcompensos', App\Http\Controllers\MfcompensoController::class);

    // Calls routes
    Route::post('calls/import', [App\Http\Controllers\CallController::class, 'import'])->name('calls.import');
    Route::resource('calls', App\Http\Controllers\CallController::class);

    // Leads routes
    Route::post('leads/import', [App\Http\Controllers\LeadController::class, 'import'])->name('leads.import');
    Route::resource('leads', App\Http\Controllers\LeadController::class);

    // Clienti routes
    Route::resource('clientis', App\Http\Controllers\ClientiController::class);

    // Customertypes routes
    Route::resource('customertypes', App\Http\Controllers\CustomertypeController::class);

    // Employroles routes
    Route::resource('employroles', App\Http\Controllers\EmployroleController::class);
});

// Test route for debugging filters (outside auth middleware)
Route::get('test-filter', function(Request $request) {
    $query = Mfcompenso::query();

    if ($request->has('denominazione_riferimento') && $request->denominazione_riferimento !== '') {
        $query->where('denominazione_riferimento', 'like', '%' . $request->denominazione_riferimento . '%');
    }

    $results = $query->limit(5)->get();

    return response()->json([
        'total' => $results->count(),
        'results' => $results->map(function($item) {
            return [
                'id' => $item->id,
                'denominazione_riferimento' => $item->denominazione_riferimento,
                'cognome' => $item->cognome,
                'istituto_finanziario' => $item->istituto_finanziario
            ];
        })
    ]);
});

// Home route
Route::get('/', [HomeController::class, 'index'])->name('home');

require __DIR__.'/auth.php';
