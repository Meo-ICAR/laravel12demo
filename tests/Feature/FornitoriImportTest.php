<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Imports\FornitoriImport;
use App\Models\Fornitori;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FornitoriImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_import_skips_existing_records()
    {
        // Create some existing fornitori records
        $existingFornitori = Fornitori::factory()->count(3)->create();
        $existingCodici = $existingFornitori->pluck('codice')->toArray();

        $initialCount = Fornitori::count();

        // Create test data with duplicate codici
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

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_') . '.tsv';
        $handle = fopen($tempFile, 'w');
        foreach ($testData as $row) {
            fputcsv($handle, $row, "\t");
        }
        fclose($handle);

        // Create UploadedFile instance
        $file = new UploadedFile($tempFile, 'test_import.tsv', 'text/tab-separated-values', null, true);

        // Import the file
        Excel::import(new FornitoriImport("\t"), $file);

        $finalCount = Fornitori::count();
        $imported = $finalCount - $initialCount;

        // Clean up
        unlink($tempFile);

        // Assertions
        $this->assertEquals(1, $imported, 'Only the unique record should be imported, duplicates should be skipped');
        $this->assertTrue($finalCount > $initialCount, 'At least one record should be imported');
    }

    public function test_import_handles_empty_file()
    {
        $initialCount = Fornitori::count();

        // Create empty file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_import_') . '.tsv';
        $file = new UploadedFile($tempFile, 'test_import.tsv', 'text/tab-separated-values', null, true);

        // Import the file
        Excel::import(new FornitoriImport("\t"), $file);

        $finalCount = Fornitori::count();

        // Clean up
        unlink($tempFile);

        // Assertions
        $this->assertEquals($initialCount, $finalCount, 'No records should be imported from empty file');
    }
}
