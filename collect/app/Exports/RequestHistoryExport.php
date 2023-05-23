<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use App\Helpers\Helper;

class RequestHistoryExport implements WithEvents
{
    protected $list;
    protected $template;
    public function __construct($list, $template)
    {
        $this->list = $list;
        $this->template = $template;
    }
    /**
     * 「Excel出力」をクリックする時
     * @access public
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function (BeforeExport $event) {
                $event->writer->reopen(new \Maatwebsite\Excel\Files\LocalTemporaryFile(storage_path($this->template)), Excel::XLSX);
                $sheet = $event->writer->getSheetByIndex(0);
                $row = 2;
                foreach ($this->list as $v) {
                    $column = 'A';
                    $sheet->setCellValue($column . $row, $v->request_no); //1
                    $column++;
                    $sheet->setCellValue($column . $row, $v->status_name); //2
                    $column++;
                    $sheet->setCellValue($column . $row, $v->RTypeNM); //3
                    $column++;
                    $sheet->setCellValue($column . $row, $v->BRNDNM); //4
                    $column++;
                    $sheet->setCellValue($column . $row, $v->created_at); //5
                    $column++;
                    $sheet->setCellValue($column . $row, $v->applied_at); //6
                    $column++;
                    $sheet->setCellValue($column . $row, $v->hoped_on); //7
                    $column++;
                    $sheet->setCellValue($column . $row, $v->recovered_on); //8
                    $column++;
                    $sheet->setCellValue($column . $row, $v->information); //9
                    $row++;

                }
                    $styleArray = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '9ca3af'],
                            ],
                        ]
                    ];
                    $styleArrayFill = [
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'color' => ['rgb' => 'dee2e6']
                        ]
                    ];
                    $sheet->getDelegate()->getStyle('A1'  . ':'.$column . ($row))->applyFromArray($styleArray);
                    $sheet->getDelegate()->getStyle('A1'  . ':'.$column . '1')->applyFromArray($styleArrayFill);
                return $event->getWriter()->getSheetByIndex(0);
            },

            // 書き込み直前イベントハンドラ
            BeforeWriting::class => function (BeforeWriting $event) {
                // テンプレート読み込みでついてくる余計な空シートを削除
                $event->writer->removeSheetByIndex(1);
                return;
            },
        ];
    }
}
