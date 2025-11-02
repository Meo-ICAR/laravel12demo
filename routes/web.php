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
use App\Http\Controllers\InvoiceinImportController;
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

    // Microsoft/Azure AD OAuth Routes
    Route::get('login/microsoft', [\App\Http\Controllers\Auth\AzureAuthController::class, 'redirect'])->name('microsoft.login');
    Route::get('login/microsoft/callback', [\App\Http\Controllers\Auth\AzureAuthController::class, 'callback']);

    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
});

// All other routes require authentication
Route::middleware(['auth', 'verified'])->group(function () {


    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });

    // Dashboard routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/home', function () {
        return view('dashboard');
    })->name('home');
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
    Route::get('invoices/dashboard', [App\Http\Controllers\InvoiceController::class, 'dashboard'])->name('invoices.dashboard');
    Route::delete('invoices/{id}', [App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Fornitori routes
  //  Route::middleware(['permission:fornitori_management'])->group(function () {
        Route::post('fornitoris/import', [FornitoriController::class, 'import'])->name('fornitoris.import');
        Route::post('fornitoris/import-invoiceins-to-invoices', [FornitoriController::class, 'importInvoiceinsToInvoices'])->name('fornitoris.importInvoiceinsToInvoices');
        Route::resource('fornitoris', FornitoriController::class);
  //  });

    // ENASARCO routes
    Route::resource('enasarco', \App\Http\Controllers\EnasarcoController::class);

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
    Route::get('provvigioni/dashboard', [App\Http\Controllers\ProvvigioneController::class, 'dashboard'])->name('provvigioni.dashboard');
    Route::put('provvigioni/{id}/stato', [App\Http\Controllers\ProvvigioneController::class, 'updateStato'])->name('provvigioni.updateStato');
    Route::put('provvigioni/{id}/toggle-stato', [App\Http\Controllers\ProvvigioneController::class, 'toggleStato'])->name('provvigioni.toggleStato');
    Route::resource('provvigioni', App\Http\Controllers\ProvvigioneController::class);
    Route::post('provvigioni/create-proforma-from-summary', [App\Http\Controllers\ProvvigioneController::class, 'createProformaFromSummary'])->name('provvigioni.createProformaFromSummary');

    // Test route for debugging
    Route::get('provvigioni/test-create-proforma/{denominazione}', function($denominazione) {
        \Log::info('Test route called with denominazione: ' . $denominazione);
        return response()->json(['success' => true, 'denominazione' => $denominazione]);
    })->name('provvigioni.testCreateProforma');

    // Calls routes
    Route::get('calls/import', [App\Http\Controllers\CallController::class, 'showImportForm'])->name('calls.import.form');
    Route::post('calls/import', [App\Http\Controllers\CallController::class, 'import'])->name('calls.import');
    Route::post('calls/import/sidial', [App\Http\Controllers\CallController::class, 'importFromSidial'])->name('calls.import.sidial');
    Route::get('calls/dashboard', [App\Http\Controllers\CallController::class, 'dashboard'])->name('calls.dashboard');
    Route::get('calls', [App\Http\Controllers\CallController::class, 'index'])->name('calls.index');
    Route::resource('calls', App\Http\Controllers\CallController::class);

    // Leads routes
    Route::get('leads/dashboard', [App\Http\Controllers\LeadController::class, 'dashboard'])->name('leads.dashboard');
    Route::get('leads/export', [App\Http\Controllers\LeadController::class, 'export'])->name('leads.export');
    Route::get('leads/analytics', [App\Http\Controllers\LeadController::class, 'analytics'])->name('leads.analytics');
    Route::post('leads/import', [App\Http\Controllers\LeadController::class, 'import'])->name('leads.import');
    Route::post('leads/import/sidial', [App\Http\Controllers\LeadController::class, 'importFromSidial'])->name('leads.import.sidial');
    Route::resource('leads', App\Http\Controllers\LeadController::class);

    // Clienti routes
    Route::resource('clientis', App\Http\Controllers\ClientiController::class);
    Route::get('clientis-invoices/{id}', [App\Http\Controllers\ClientiInvoiceController::class, 'show'])->name('clientis.invoices.show');
    Route::post('clientis/import-invoiceins-to-invoices', [\App\Http\Controllers\ClientiController::class, 'importInvoiceinsToInvoicesByClienti'])->name('clientis.importInvoiceinsToInvoicesByClienti');

    // Pratiche routes - accessible by all authenticated users
 //   Route::middleware('auth')->group(function () {
        Route::resource('pratiches', 'App\Http\Controllers\pratichecontroller');
  //  });

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

    // Proformas routes
    Route::post('proformas/{proforma}/send-email', [App\Http\Controllers\ProformaController::class, 'sendEmail'])->name('proformas.sendEmail');
    Route::post('proformas/{proforma}/send-proforma-email', [App\Http\Controllers\ProformaController::class, 'sendProformaEmail'])->name('proformas.sendProformaEmail');
    Route::post('proformas/{proforma}/send-proforma-preview', [App\Http\Controllers\ProformaController::class, 'sendProformaPreview'])->name('proformas.sendProformaPreview');
    Route::post('proformas/send-bulk-emails', [App\Http\Controllers\ProformaController::class, 'sendBulkEmails'])->name('proformas.sendBulkEmails');
    Route::resource('proformas', App\Http\Controllers\ProformaController::class);

    // Invoiceins routes
    Route::get('invoiceins/import', [InvoiceinImportController::class, 'index'])->name('invoiceins.import');
    Route::post('invoiceins/import', [InvoiceinImportController::class, 'import']);
    Route::post('invoiceins/import-custom', [InvoiceinImportController::class, 'importCustom'])->name('invoiceins.import.custom');
    Route::resource('invoiceins', App\Http\Controllers\InvoiceinController::class);

    // Pratiche routes
    Route::resource('pratiches', App\Http\Controllers\PraticheController::class);

    // Import API routes
    Route::get('pratiches/import-api', [App\Http\Controllers\PraticheController::class, 'showImportApiForm'])
        ->name('pratiches.import.api.form');

    // New import routes
    Route::post('pratiche/import', [App\Http\Controllers\PraticheCrudController::class, 'import'])
        ->name('pratiche.import');

    // Keep the old route for backward compatibility
    Route::resource('pratiches-crud', App\Http\Controllers\PraticheCrudController::class)->parameters([
        'pratiches-crud' => 'pratiche'
    ]);


    // FornitoriInvoice routes
    Route::get('fornitoris-invoices', [App\Http\Controllers\FornitoriInvoiceController::class, 'index'])->name('fornitoris.invoices.index');
    Route::get('fornitoris-invoices/{id}', [App\Http\Controllers\FornitoriInvoiceController::class, 'show'])->name('fornitoris.invoices.show');

        Route::get('/', function () {
        return redirect()->route('provvigioni/proforma-summary');
    });
});

require __DIR__.'/auth.php';

// Test email route (temporary for debugging)
Route::get('/test-email', [\App\Http\Controllers\ProformaController::class, 'testEmail'])->name('test.email');
