<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
class StockImport implements ToCollection
{
    public $data;
    /**
     * @access public
     * @param Collection $collection
     * return ExcelDataArray
     */

    public function collection(Collection $collection)
    {
        $data = ($collection->toArray());
        $this->data = $data;
    }
    // headingRow function is use for specific row heading in your xls file
    public function headingRow(): int
    {
        return 3;
    }
}
