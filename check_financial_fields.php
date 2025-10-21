<?php

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'storage/app/FATTURE ATTIIVE DAL 1-7-2025 AL 17-10-2025.xlsx';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $headers = $sheet->rangeToArray('A1:Z1', null, true, false)[0];
    
    echo "Available headers in the Excel file:\n";
    foreach ($headers as $index => $header) {
        echo sprintf("%s: %s\n", chr(65 + $index), $header);
    }
    
    // Show first data row for reference
    $firstRow = $sheet->rangeToArray('A2:Z2', null, true, false)[0];
    echo "\nFirst data row values:\n";
    foreach ($firstRow as $index => $value) {
        echo sprintf("%s: %s\n", chr(65 + $index), $value);
    }
    
} catch (\Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
