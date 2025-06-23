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
use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Provvigione;

// Redirect root to login if not authenticated
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes (public)
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

// All other routes require authentication
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/home', function () {
        return view('dashboard');
    })->name('home');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Help Routes
    Route::get('help/{page?}', [App\Http\Controllers\HelpController::class, 'show'])->name('help.show');
    Route::post('help/upload-screenshot', [App\Http\Controllers\HelpController::class, 'uploadScreenshot'])->name('help.uploadScreenshot');
    Route::post('help/capture-screenshot', [App\Http\Controllers\HelpController::class, 'captureScreenshot'])->name('help.captureScreenshot');

    // Admin Help Management Routes
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('help-admin', [App\Http\Controllers\HelpController::class, 'index'])->name('help.admin.index');
        Route::get('help-admin/{page}/edit', [App\Http\Controllers\HelpController::class, 'edit'])->name('help.admin.edit');
        Route::put('help-admin/{page}', [App\Http\Controllers\HelpController::class, 'update'])->name('help.admin.update');
    });

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
    Route::get('companies/trashed', [CompanyController::class, 'trashed'])->name('companies.trashed');
    Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::delete('companies/{id}/force-delete', [CompanyController::class, 'forceDelete'])->name('companies.force-delete');
    Route::resource('companies', CompanyController::class);
    Route::get('companies/{company}/roles', [\App\Http\Controllers\RoleController::class, 'companyRoles'])->name('companies.roles');

    // Invoice Routes
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/reconciliation', [InvoiceController::class, 'reconciliation'])->name('invoices.reconciliation');
    Route::post('invoices/reconcile', [InvoiceController::class, 'reconcile'])->name('invoices.reconcile');
    Route::get('invoices/test-reconciliation', [InvoiceController::class, 'testReconciliation'])->name('invoices.testReconciliation');
    Route::get('invoices/{id}/xml-data', [InvoiceController::class, 'getXmlData'])->name('invoices.xml');
    Route::get('invoices/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('invoices/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::get('invoices/{id}/check', [InvoiceController::class, 'check'])->name('invoices.check');
    Route::post('invoices/{id}/reconcile-checked', [InvoiceController::class, 'reconcileChecked'])->name('invoices.reconcileChecked');
    Route::get('invoices/import', [InvoiceImportController::class, 'index'])->name('invoices.import');
    Route::post('invoices/import', [InvoiceImportController::class, 'import'])->name('invoices.import.store');

    // Fornitori routes
    Route::middleware(['permission:fornitori_management'])->group(function () {
        Route::post('fornitoris/import', [FornitoriController::class, 'import'])->name('fornitoris.import');
        Route::resource('fornitoris', FornitoriController::class);
    });

    // Provvigioni routes
    Route::get('provvigioni/proforma-summary', [App\Http\Controllers\ProvvigioneController::class, 'proformaSummary'])->name('provvigioni.proformaSummary');
    Route::post('provvigioni/send-proforma-email', [App\Http\Controllers\ProvvigioneController::class, 'sendProformaEmail'])->name('provvigioni.sendProformaEmail');
    Route::post('provvigioni/send-all-proforma-emails', [App\Http\Controllers\ProvvigioneController::class, 'sendAllProformaEmails'])->name('provvigioni.sendAllProformaEmails');
    Route::post('provvigioni/mark-as-received', [App\Http\Controllers\ProvvigioneController::class, 'markAsReceived'])->name('provvigioni.markAsReceived');
    Route::post('provvigioni/mark-as-paid', [App\Http\Controllers\ProvvigioneController::class, 'markAsPaid'])->name('provvigioni.markAsPaid');
    Route::post('provvigioni/mark-as-sent', [App\Http\Controllers\ProvvigioneController::class, 'markAsSent'])->name('provvigioni.markAsSent');
    Route::post('provvigioni/sync-denominazioni', [App\Http\Controllers\ProvvigioneController::class, 'syncDenominazioniToFornitori'])->name('provvigioni.syncDenominazioni');
    Route::get('provvigioni/check-emails', [App\Http\Controllers\ProvvigioneController::class, 'checkSentEmails'])->name('provvigioni.checkSentEmails');
    Route::match(['GET', 'POST'], 'provvigioni/import', [App\Http\Controllers\ProvvigioneController::class, 'import'])->name('provvigioni.import');
    Route::post('provvigioni/bulk-update-to-proforma', [App\Http\Controllers\ProvvigioneController::class, 'bulkUpdateToProforma'])->name('provvigioni.bulkUpdateToProforma');
    Route::put('provvigioni/{id}/stato', [App\Http\Controllers\ProvvigioneController::class, 'updateStato'])->name('provvigioni.updateStato');
    Route::resource('provvigioni', App\Http\Controllers\ProvvigioneController::class);

    // Calls routes
    Route::get('calls/import', [App\Http\Controllers\CallController::class, 'showImportForm'])->name('calls.import.form');
    Route::post('calls/import', [App\Http\Controllers\CallController::class, 'import'])->name('calls.import');
    Route::get('calls/dashboard', [App\Http\Controllers\CallController::class, 'dashboard'])->name('calls.dashboard');
    Route::get('calls', [App\Http\Controllers\CallController::class, 'index'])->name('calls.index');
    Route::resource('calls', App\Http\Controllers\CallController::class);

    // Leads routes
    Route::get('leads/dashboard', [App\Http\Controllers\LeadController::class, 'dashboard'])->name('leads.dashboard');
    Route::get('leads/export', [App\Http\Controllers\LeadController::class, 'export'])->name('leads.export');
    Route::get('leads/analytics', [App\Http\Controllers\LeadController::class, 'analytics'])->name('leads.analytics');
    Route::post('leads/import', [App\Http\Controllers\LeadController::class, 'import'])->name('leads.import');
    Route::resource('leads', App\Http\Controllers\LeadController::class);

    // Clienti routes
    Route::resource('clientis', App\Http\Controllers\ClientiController::class);

    // Customertypes routes
    Route::resource('customertypes', App\Http\Controllers\CustomertypeController::class);

    // Test and debug routes (should be protected or removed in production)
    Route::get('test-filter', function(Request $request) {
        $query = Provvigione::query();

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

    Route::post('test-upload', function(Request $request) {
        \Log::info('Test upload request', [
            'has_file' => $request->hasFile('file'),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
            'all_input' => $request->all()
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // ... handle file upload ...
        }
        return response()->json(['success' => true]);
    })->name('test.upload');
});

require __DIR__.'/auth.php';
