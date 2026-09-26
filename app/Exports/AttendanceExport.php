<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceExport implements FromArray, WithEvents
{
    /**
     * @param  list<array{label:string,weekday:string,weekend:bool}>  $days
     * @param  list<array{name:string,project:string,cells:list<string>,present:int,half:int,leave:int,absent:int,total:float}>  $rows
     */
    public function __construct(
        protected string $title,
        protected string $subtitle,
        protected string $summaryLine,
        protected array $days,
        protected array $rows,
    ) {
    }

    public function array(): array
    {
        $head = ['SN', 'Worker', 'Project'];
        foreach ($this->days as $day) {
            $head[] = $day['label'] . "\n" . $day['weekday'];
        }
        $head = array_merge($head, ['Present', 'Half', 'Leave', 'Absent', 'Total Attendance']);

        $body = [];
        $sn = 0;
        foreach ($this->rows as $row) {
            $sn++;
            $line = [$sn, $row['name'], $row['project']];
            foreach ($row['cells'] as $cell) {
                $line[] = $cell;
            }
            $line[] = $row['present'];
            $line[] = $row['half'];
            $line[] = $row['leave'];
            $line[] = $row['absent'];
            $line[] = $row['total'];
            $body[] = $line;
        }

        return array_merge(
            [[$this->title], [$this->subtitle], [$this->summaryLine], []],
            [$head],
            $body
        );
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dayCount = count($this->days);
                $lastCol = 3 + $dayCount + 5;
                $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);
                $headerRow = 5;
                $firstDataRow = 6;
                $lastDataRow = $headerRow + max(1, count($this->rows));

                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->mergeCells("A3:{$lastColLetter}3");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A2:A3')->getFont()->setSize(11);
                $sheet->getStyle("A1:{$lastColLetter}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(28);
                $sheet->freezePane('D6');

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(18);
                for ($i = 0; $i < $dayCount; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(4 + $i))->setWidth(5);
                }
                for ($i = 0; $i < 5; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(4 + $dayCount + $i))->setWidth(12);
                }

                $fills = [
                    'P' => '10B981',
                    'H' => 'FBBF24',
                    'L' => '0EA5E9',
                    'A' => 'F43F5E',
                ];

                foreach ($this->rows as $rIndex => $row) {
                    $excelRow = $firstDataRow + $rIndex;
                    foreach ($row['cells'] as $cIndex => $code) {
                        if (!isset($fills[$code])) {
                            continue;
                        }
                        $col = Coordinate::stringFromColumnIndex(4 + $cIndex);
                        $sheet->getStyle($col . $excelRow)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $code === 'H' ? '78350F' : 'FFFFFF'], 'size' => 9],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fills[$code]]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }

                if (count($this->rows) > 0) {
                    $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$lastDataRow}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("D{$firstDataRow}:{$lastColLetter}{$lastDataRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $legendRow = $lastDataRow + 2;
                $sheet->setCellValue("A{$legendRow}", 'P = Present    H = Half day    L = Leave    A = Absent    Total Attendance = Present + (Half × 0.5)');
                $sheet->mergeCells("A{$legendRow}:{$lastColLetter}{$legendRow}");
                $sheet->getStyle("A{$legendRow}")->getFont()->setItalic(true)->setSize(10);
            },
        ];
    }
}
