<?php

namespace App\Services;

use App\Models\BillItem;
use App\Models\BillAggregate;
use App\Models\BillSetting;
use App\Support\CompanyContext;

class BillCalculatorService
{
    /**
     * Calculate item amounts (amount, effective_quantity, total_amount, net_amount)
     */
    public function calculateItem(BillItem $item): void
    {
        // Basic amount = quantity * unit_rate
        $item->amount = round($item->quantity * $item->unit_rate, 2);

        // Effective quantity with wastage
        $item->effective_quantity = round(
            $item->quantity * (1 + ($item->wastage_percent / 100)),
            3
        );

        // Total amount = effective_quantity * unit_rate
        $item->total_amount = round($item->effective_quantity * $item->unit_rate, 2);

        // Net amount = total_amount + tax
        $item->net_amount = round(
            $item->total_amount * (1 + ($item->tax_percent / 100)),
            2
        );
    }

    /**
     * Calculate and update aggregate totals for a bill module
     */
    public function calculateAggregate($billModuleId, ?float $overheadPercent = null, ?float $contingencyPercent = null): BillAggregate
    {
        $items = BillItem::where('bill_module_id', $billModuleId)->get();
        
        $subtotal = $items->sum('total_amount');
        $taxTotal = $items->sum(function ($item) {
            return $item->total_amount * ($item->tax_percent / 100);
        });

        // Get defaults from settings if not provided
        if ($overheadPercent === null || $contingencyPercent === null) {
            $companyId = CompanyContext::getActiveCompanyId();
            $settings = BillSetting::where('company_id', $companyId)->first();
            
            if ($settings) {
                $overheadPercent = $overheadPercent ?? $settings->overhead_default;
                $contingencyPercent = $contingencyPercent ?? $settings->contingency_default;
            } else {
                $overheadPercent = $overheadPercent ?? 10.0;
                $contingencyPercent = $contingencyPercent ?? 5.0;
            }
        }

        $overheadAmount = round($subtotal * ($overheadPercent / 100), 2);
        $contingencyAmount = round($subtotal * ($contingencyPercent / 100), 2);
        
        $grandTotal = round(
            $subtotal + $taxTotal + $overheadAmount + $contingencyAmount,
            2
        );

        $aggregate = BillAggregate::updateOrCreate(
            ['bill_module_id' => $billModuleId],
            [
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'overhead_percent' => round($overheadPercent, 2),
                'overhead_amount' => $overheadAmount,
                'contingency_percent' => round($contingencyPercent, 2),
                'contingency_amount' => $contingencyAmount,
                'grand_total' => $grandTotal,
            ]
        );

        return $aggregate;
    }

    /**
     * Recalculate all items and aggregate for a bill module
     */
    public function recalculateBill($billModuleId, ?float $overheadPercent = null, ?float $contingencyPercent = null): BillAggregate
    {
        $items = BillItem::where('bill_module_id', $billModuleId)->get();
        
        foreach ($items as $item) {
            $this->calculateItem($item);
            $item->save();
        }

        return $this->calculateAggregate($billModuleId, $overheadPercent, $contingencyPercent);
    }
}

