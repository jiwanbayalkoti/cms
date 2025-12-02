<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialCalculatorExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @param  array<int,array>  $items
     * @param  array<string,mixed>  $summary
     */
    public function __construct(private array $items, private array $summary = [])
    {
    }

    public function collection(): Collection
    {
        return collect($this->items);
    }

    public function headings(): array
    {
        return [
            'S.N.',
            'Work Type',
            'Description',
            'Material Breakdown',
            'Cost Breakdown',
        ];
    }

    /**
     * @param  array  $row
     */
    public function map($row): array
    {
        $materials = collect($row['materials'] ?? [])
            ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', $key)) . ': ' . $value)
            ->implode(PHP_EOL);

        $costs = collect($row['cost'] ?? [])
            ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', $key)) . ': ' . number_format((float) $value, 2))
            ->implode(PHP_EOL);

        return [
            $row['sn'] ?? '',
            $row['work_type'] ?? '',
            $row['description'] ?? '',
            $materials,
            $costs,
        ];
    }
}

