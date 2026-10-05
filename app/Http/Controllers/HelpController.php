<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class HelpController extends Controller
{
    public function show($page = 'home')
    {
        $helpContent = $this->getHelpContent($page);
        return view('help.show', compact('helpContent', 'page'));
    }

    public function index()
    {
        $pages = $this->getAllPages();
        $allHelpContent = $this->getAllHelpContent();
        return view('help.index', compact('pages', 'allHelpContent'));
    }

    public function edit($page)
    {
        $helpContent = $this->getHelpContent($page);
        $allPages = $this->getAllPages();
        return view('help.edit', compact('helpContent', 'page', 'allPages'));
    }

    public function update(Request $request, $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sections' => 'required|array',
            'sections.*' => 'required|string',
            'screenshot' => 'nullable|string'
        ]);

        // Get current content
        $content = $this->getAllHelpContent();

        // Update the specific page
        $content[$page] = [
            'title' => $request->input('title'),
            'screenshot' => $request->input('screenshot') ?: '/images/help/' . $page . '.png',
            'sections' => $request->input('sections')
        ];

        // Save to file (you could also use database)
        $this->saveHelpContent($content);

        return redirect()->route('help.edit', $page)
            ->with('success', 'Help content updated successfully!');
    }

    public function uploadScreenshot(Request $request)
    {
        $request->validate([
            'page' => 'required|string',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $page = $request->input('page');
        $file = $request->file('screenshot');

        // Generate filename
        $filename = $page . '.' . $file->getClientOriginalExtension();

        // Store in public/images/help directory
        $path = $file->storeAs('images/help', $filename, 'public');

        return response()->json([
            'success' => true,
            'path' => '/storage/' . $path,
            'message' => 'Screenshot uploaded successfully'
        ]);
    }

    public function captureScreenshot(Request $request)
    {
        $request->validate([
            'page' => 'required|string',
            'url' => 'required|url'
        ]);

        $page = $request->input('page');
        $url = $request->input('url');

        try {
            // Generate filename
            $filename = $page . '.png';
            $path = storage_path('app/public/images/help/' . $filename);

            // Ensure directory exists
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            // Capture screenshot
            Browsershot::url($url)
                ->windowSize(1920, 1080)
                ->waitUntilNetworkIdle()
                ->save($path);

            return response()->json([
                'success' => true,
                'path' => '/storage/images/help/' . $filename,
                'message' => 'Screenshot captured successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to capture screenshot: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getAllPages()
    {
        return [
            'home' => 'Dashboard',
            'users.index' => 'Users',
            'roles.index' => 'Roles',
            'permissions.index' => 'Permissions',
            'companies.index' => 'Companies',
            'provvigioni.index' => 'Provvigioni',
            'provvigioni.proformaSummary' => 'Proforma Summary',
            'invoices.reconciliation' => 'Invoice Reconciliation',
            'fornitoris.index' => 'Fornitori',
            'calls.index' => 'Calls',
            'leads.index' => 'Leads',
            'clientis.index' => 'Clienti'
        ];
    }

    public function getAllHelpContent()
    {
        return [
            'home' => [
                'title' => 'Dashboard',
                'screenshot' => '/images/help/dashboard.png',
                'sections' => [
                    'Overview' => 'The dashboard provides a quick summary of your application, including recent activity and key metrics.',
                    'Actions' => 'Use the dashboard to navigate to other sections or review important notifications.'
                ]
            ],
            'users.index' => [
                'title' => 'Users',
                'screenshot' => '/images/help/users.png',
                'sections' => [
                    'Overview' => 'This page lists all users in the system. You can search, filter, and sort users.',
                    'Actions' => 'Click "Create User" to add a new user, or use the Edit/Delete buttons to manage existing users.'
                ]
            ],
            'roles.index' => [
                'title' => 'Roles',
                'screenshot' => '/images/help/roles.png',
                'sections' => [
                    'Overview' => 'Roles define groups of permissions for users. Each user can have one or more roles.',
                    'Actions' => 'Create, edit, or delete roles. Assign permissions to roles as needed.'
                ]
            ],
            'permissions.index' => [
                'title' => 'Permissions',
                'screenshot' => '/images/help/permissions.png',
                'sections' => [
                    'Overview' => 'Permissions control access to specific features and actions within the application.',
                    'Actions' => 'Create, edit, or delete permissions. Assign permissions to roles.'
                ]
            ],
            'companies.index' => [
                'title' => 'Companies',
                'screenshot' => '/images/help/companies.png',
                'sections' => [
                    'Overview' => 'This page lists all companies. You can view, create, and manage company records.',
                    'Actions' => 'Click "Create Company" to add a new company, or use the Edit/Delete buttons to manage existing companies.'
                ]
            ],
            'provvigioni.index' => [
                'title' => 'Provvigioni (Commissions)',
                'screenshot' => '/images/help/provvigioni.png',
                'sections' => [
                    'Overview' => 'This page manages commission records. You can view, filter, and update commission status.',
                    'Filters' => 'Use the filter section to search by status, denominazione, institute, surname, source, or sent date.',
                    'Status Management' => 'Change commission status directly in the table using the dropdown menus.',
                    'Actions' => 'Use "Import Provvigioni" to upload new records, or "Proforma Summary" to view grouped records.'
                ]
            ],
            'provvigioni.proformaSummary' => [
                'title' => 'Proforma Summary',
                'screenshot' => '/images/help/proforma-summary.png',
                'sections' => [
                    'Overview' => 'This page shows commissions with "Inserito" status grouped by denominazione.',
                    'Email Management' => 'Send emails to suppliers with commission details. Records are automatically updated to "Proforma" status.',
                    'Sync Denominazioni' => 'Synchronize denominazioni with the fornitori table to ensure email addresses are available.',
                    'Sorting' => 'Sort by denominazione, total amount, or number of records in ascending or descending order.'
                ]
            ],
            'invoices.reconciliation' => [
                'title' => 'Invoice Reconciliation',
                'screenshot' => '/images/help/invoice-reconciliation.png',
                'sections' => [
                    'Overview' => 'Match sent commissions (Proforma status) with invoices for reconciliation.',
                    'Process' => '1. Select a commission group using the link button. 2. Click reconcile on an invoice to match them.',
                    'Status Updates' => 'Reconciled commissions are updated to "Fatturato" status and linked to the invoice number.'
                ]
            ],
            'fornitoris.index' => [
                'title' => 'Fornitori (Suppliers)',
                'screenshot' => '/images/help/fornitori.png',
                'sections' => [
                    'Overview' => 'Manage supplier information including contact details and email addresses.',
                    'Import' => 'Upload supplier data using CSV or Excel files.',
                    'Email Sync' => 'Email addresses are used for sending commission notifications.'
                ]
            ],
            'calls.index' => [
                'title' => 'Calls',
                'screenshot' => '/images/help/calls.png',
                'sections' => [
                    'Overview' => 'Track and manage call records and activities.',
                    'Dashboard' => 'View call statistics and analytics.',
                    'Import' => 'Upload call data from external sources.'
                ]
            ],
            'leads.index' => [
                'title' => 'Leads',
                'screenshot' => '/images/help/leads.png',
                'sections' => [
                    'Overview' => 'Manage lead information and track conversion progress.',
                    'Dashboard' => 'View lead analytics and performance metrics.',
                    'Export' => 'Export lead data for external analysis.'
                ]
            ],
            'clientis.index' => [
                'title' => 'Clienti (Customers)',
                'screenshot' => '/images/help/clienti.png',
                'sections' => [
                    'Overview' => 'Manage customer information and relationships.',
                    'Actions' => 'Create, edit, and maintain customer records.'
                ]
            ],
        ];
    }

    public function getHelpContent($page)
    {
        $content = $this->getAllHelpContent();
        return $content[$page] ?? ['title' => 'Help', 'sections' => ['General' => 'No help content available for this section.']];
    }

    private function saveHelpContent($content)
    {
        // For now, we'll store in a JSON file
        // In production, you might want to use a database
        $path = storage_path('app/help_content.json');
        file_put_contents($path, json_encode($content, JSON_PRETTY_PRINT));
    }
}
