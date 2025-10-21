<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'storage/app/FATTURE ATTIIVE DAL 1-7-2025 AL 17-10-2025.xlsx';

try {
    // Load the Excel file
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Get all data as array
    $data = $sheet->toArray(null, true, true, true);
    
    // Get headers (first row)
    $headers = array_keys(reset($data));
    
    echo "=== Excel File Structure ===\n";
    echo "Headers (first row):\n";
    print_r($headers);
    
    echo "\nFirst data row (second row):\n";
    print_r(array_slice($data, 1, 1, true));
    
    echo "\nExpected headers from import script:\n";
    $expectedHeaders = [
        'Nr.',
        'Nr. cliente',
        'Ragione Sociale',
        'Partita IVA',
        'Data di registrazione',
        'Importo',
        'Importo IVA inclusa',
        'Importo residuo',
        'Cdc Codice',
        'Cod. colleg. dimen. 2',
        'Tipo di documento Fattura'
    ];
    
    print_r($expectedHeaders);
    
    // Check if all expected headers exist
    $missingHeaders = array_diff($expectedHeaders, $headers);
    
    if (!empty($missingHeaders)) {
        echo "\n\n=== WARNING: Missing Headers ===\n";
        echo "The following expected headers were not found in the Excel file:\n";
        foreach ($missingHeaders as $header) {
            echo "- $header\n";
        }
        
        echo "\nAvailable headers in the Excel file:\n";
        foreach ($headers as $header) {
            echo "- $header\n";
        }
    } else {
        echo "\n\nAll expected headers are present in the Excel file.\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
