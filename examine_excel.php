<?php

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'storage/FATTURE PASSIVE DAL 1-7-2025 AL 17-10-2025.xlsx';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Get headers
    $headers = [];
    foreach ($sheet->getRowIterator(1, 1) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $headers[] = $cell->getValue();
        }
    }
    
    // Get first data row
    $firstRow = [];
    foreach ($sheet->getRowIterator(2, 2) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $firstRow[] = $cell->getValue();
        }
    }
    
    echo "Headers:\n";
    foreach ($headers as $index => $header) {
        echo sprintf("%2d: %s\n", $index + 1, $header);
    }
    
    echo "\nFirst row data:\n";
    foreach ($firstRow as $index => $value) {
        echo sprintf("%2d: %s\n", $index + 1, $value);
    }
    
} catch (\Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
