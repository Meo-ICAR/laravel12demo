<?php

require_once 'vendor/autoload.php';

use App\Imports\FornitoriImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Fornitori;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing duplicate import functionality...\n";

// Get current count
$initialCount = Fornitori::count();
echo "Initial fornitori count: $initialCount\n";

// Get some existing codici
$existingCodici = Fornitori::take(3)->pluck('codice')->toArray();
echo "Existing codici: " . implode(', ', $existingCodici) . "\n";

// Create a test file with duplicate codici
$testData = [];
foreach ($existingCodici as $codice) {
    $testData[] = [
        $codice, // codice
        'TEST NAME', // denominazione
        'TEST NOME', // nome
        '', // natoil
        'TEST ADDRESS', // indirizzo
        'TEST CITY', // comune
        '12345', // cap
        'TE', // prov
        '123456789', // tel
        '', // empty
        '', // empty
        'test@test.com', // email
        'TEST REGION', // regione
        'TEST CITY', // citta
        'TEST COORDINATOR' // coordinatore
    ];
}

// Add a new unique codice
$testData[] = [
    'TEST_UNIQUE_' . time(), // codice
    'UNIQUE TEST NAME', // denominazione
    'UNIQUE TEST NOME', // nome
    '', // natoil
    'UNIQUE TEST ADDRESS', // indirizzo
    'UNIQUE TEST CITY', // comune
    '54321', // cap
    'UT', // prov
    '987654321', // tel
    '', // empty
    '', // empty
    'unique@test.com', // email
    'UNIQUE TEST REGION', // regione
    'UNIQUE TEST CITY', // citta
    'UNIQUE TEST COORDINATOR' // coordinatore
];

// Write test data to temporary file
$tempFile = tempnam(sys_get_temp_dir(), 'test_import_') . '.tsv';
$handle = fopen($tempFile, 'w');
foreach ($testData as $row) {
    fputcsv($handle, $row, "\t");
}
fclose($handle);

echo "Created test file: $tempFile\n";

// Import the test file
try {
    $file = new \Illuminate\Http\UploadedFile($tempFile, 'test_import.tsv', 'text/tab-separated-values', null, true);
    Excel::import(new FornitoriImport("\t"), $file);

    $finalCount = Fornitori::count();
    $imported = $finalCount - $initialCount;

    echo "Final fornitori count: $finalCount\n";
    echo "Imported records: $imported\n";

    if ($imported === 1) {
        echo "✅ SUCCESS: Only the unique record was imported, duplicates were skipped!\n";
    } else {
        echo "❌ FAILURE: Expected 1 import, got $imported\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// Clean up
unlink($tempFile);
echo "Test completed.\n";
