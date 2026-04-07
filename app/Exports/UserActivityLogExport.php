<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserActivityLogExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'Time',
            'User Name',
            'User Email',
            'Company',
            'Action',
            'Method',
            'Route Name',
            'Description',
            'URL',
            'IP Address',
            'Status Code',
        ];
    }

    public function map($log): array
    {
        return [
            optional($log->created_at)->format('Y-m-d H:i:s'),
            optional($log->user)->name ?? 'Unknown user',
            optional($log->user)->email ?? '—',
            optional($log->company)->name ?? '—',
            $log->action,
            $log->method,
            $log->route_name,
            $log->description,
            $log->url,
            $log->ip_address,
            $log->status_code,
        ];
    }
}

