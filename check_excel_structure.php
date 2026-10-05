<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'storage/app/FATTURE ATTIIVE DAL 1-7-2025 AL 17-10-2025.xlsx';

try {
    // Load the Excel file
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Get all data as array with column letters as keys
    $data = $sheet->toArray(null, true, true, true);
    
    // Display first 5 rows with column letters
    echo str_repeat("=", 100) . "\n";
    echo "EXCEL FILE STRUCTURE\n";
    echo str_repeat("=", 100) . "\n\n";
    
    $rowCount = 0;
    foreach ($data as $rowNum => $row) {
        $rowCount++;
        if ($rowCount > 5) break; // Show only first 5 rows
        
        echo "ROW $rowNum:\n";
        foreach ($row as $colLetter => $value) {
            // Skip empty cells
            if ($value === null || $value === '') continue;
            
            // Truncate long values for display
            $displayValue = $value;
            if (strlen($displayValue) > 50) {
                $displayValue = substr($displayValue, 0, 47) . '...';
            }
            
            printf("  %-5s: %s\n", $colLetter, $displayValue);
        }
        echo "\n" . str_repeat("-", 100) . "\n\n";
    }
    
    // Get all column letters that have data in the first row
    $firstRow = reset($data);
    $usedColumns = [];
    foreach ($firstRow as $colLetter => $value) {
        if ($value !== null && $value !== '') {
            $usedColumns[] = $colLetter;
        }
    }
    
    echo "\nUSED COLUMNS: " . implode(', ', $usedColumns) . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
