<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Filament\Imports\QualityStandardImporter;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet;
$sheet = $spreadsheet->getActiveSheet();
$headers = [
    'standard_code',
    'standard_name',
    'standard_category_code',
    'standard_category_name',
    'standard_subcategory_code',
    'standard_subcategory_name',
    'scope_type',
    'spmi_period_name',
    'standard_statement_code',
    'standard_statement',
    'standard_description',
    'standard_status',
    'standard_version',
    'indicator_code',
    'indicator_statement',
    'indicator_type',
    'target_operator',
    'target_value',
    'target_unit',
    'weight',
    'evidence_required',
    'evidence_description',
];
foreach ($headers as $index => $header) {
    $col = Coordinate::stringFromColumnIndex($index + 1);
    $sheet->setCellValue($col.'1', $header);
}

// Row 2
$rowData = [
    'STD-01', 'Standar 1', 'CAT-1', 'Category 1', '', '', 'university', '', 'STMT-01', 'Statement 1', 'Desc', 'draft', 1, 'IND-01', 'Indicator 1', 'percentage', '>=', 100, '%', 1, 1, '',
];
foreach ($rowData as $index => $val) {
    $col = Coordinate::stringFromColumnIndex($index + 1);
    $sheet->setCellValue($col.'2', $val);
}

$writer = new Xlsx($spreadsheet);
$writer->save(storage_path('app/test_import.xlsx'));

try {
    $importer = new QualityStandardImporter(User::first());
    Excel::import($importer, storage_path('app/test_import.xlsx'));
    echo "Import success.\n";
} catch (ValidationException $e) {
    echo "Validation failed: \n";
    print_r($e->errors());
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage()."\n";
}
