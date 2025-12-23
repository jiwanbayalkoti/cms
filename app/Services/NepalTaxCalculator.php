<?php

namespace App\Services;

/**
 * Nepal Tax Calculator for FY 2080/81
 * Based on Nepal Government Tax Rates for Resident Natural Persons
 */
class NepalTaxCalculator
{
    // Tax brackets for Single Assessment (FY 2080/81)
    const SINGLE_BRACKETS = [
        ['min' => 0, 'max' => 500000, 'rate' => 0.01],      // 1% for first Rs. 500,000
        ['min' => 500000, 'max' => 700000, 'rate' => 0.10], // 10% for next Rs. 200,000
        ['min' => 700000, 'max' => 1000000, 'rate' => 0.20], // 20% for next Rs. 300,000
        ['min' => 1000000, 'max' => 2000000, 'rate' => 0.30], // 30% for next Rs. 1,000,000
        ['min' => 2000000, 'max' => 5000000, 'rate' => 0.36], // 36% for next Rs. 3,000,000
        ['min' => 5000000, 'max' => PHP_INT_MAX, 'rate' => 0.39], // 39% above Rs. 5,000,000
    ];

    // Tax brackets for Couple Assessment (FY 2080/81)
    const COUPLE_BRACKETS = [
        ['min' => 0, 'max' => 600000, 'rate' => 0.01],      // 1% for first Rs. 600,000
        ['min' => 600000, 'max' => 800000, 'rate' => 0.10], // 10% for next Rs. 200,000
        ['min' => 800000, 'max' => 1100000, 'rate' => 0.20], // 20% for next Rs. 300,000
        ['min' => 1100000, 'max' => 2000000, 'rate' => 0.30], // 30% for next Rs. 900,000
        ['min' => 2000000, 'max' => 5000000, 'rate' => 0.36], // 36% for next Rs. 3,000,000
        ['min' => 5000000, 'max' => PHP_INT_MAX, 'rate' => 0.39], // 39% above Rs. 5,000,000
    ];

    /**
     * Calculate tax based on taxable income and assessment type
     *
     * @param float $taxableIncome Annual taxable income
     * @param string $assessmentType 'single' or 'couple'
     * @return array ['tax_amount' => float, 'breakdown' => array]
     */
    public static function calculateTax(float $taxableIncome, string $assessmentType = 'single'): array
    {
        $brackets = $assessmentType === 'couple' ? self::COUPLE_BRACKETS : self::SINGLE_BRACKETS;
        
        $totalTax = 0;
        $breakdown = [];
        $remainingIncome = $taxableIncome;

        foreach ($brackets as $index => $bracket) {
            if ($remainingIncome <= 0) {
                break;
            }

            // Calculate taxable amount in this bracket
            $bracketRange = $bracket['max'] - $bracket['min'];
            $incomeInBracket = min($remainingIncome, $bracketRange);
            
            if ($incomeInBracket > 0) {
                $taxInBracket = $incomeInBracket * $bracket['rate'];
                $totalTax += $taxInBracket;
                
                $breakdown[] = [
                    'bracket' => $index + 1,
                    'range' => 'Rs. ' . number_format($bracket['min']) . ' - Rs. ' . number_format($bracket['max']),
                    'income_in_bracket' => $incomeInBracket,
                    'rate' => ($bracket['rate'] * 100) . '%',
                    'tax' => $taxInBracket,
                ];
                
                $remainingIncome -= $incomeInBracket;
            }
        }

        return [
            'tax_amount' => round($totalTax, 2),
            'taxable_income' => $taxableIncome,
            'assessment_type' => $assessmentType,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate monthly tax from annual taxable income
     *
     * @param float $annualTaxableIncome Annual taxable income
     * @param string $assessmentType 'single' or 'couple'
     * @return float Monthly tax amount
     */
    public static function calculateMonthlyTax(float $annualTaxableIncome, string $assessmentType = 'single'): float
    {
        $taxResult = self::calculateTax($annualTaxableIncome, $assessmentType);
        return round($taxResult['tax_amount'] / 12, 2);
    }

    /**
     * Calculate annual taxable income from monthly gross salary
     * This assumes the monthly amount is consistent throughout the year
     *
     * @param float $monthlyGrossSalary Monthly gross salary
     * @return float Annual taxable income
     */
    public static function calculateAnnualTaxableIncome(float $monthlyGrossSalary): float
    {
        return $monthlyGrossSalary * 12;
    }

    /**
     * Get tax exemption amount (if any)
     * For FY 2080/81, first bracket is exempt or at 1% for employment income
     *
     * @param string $assessmentType 'single' or 'couple'
     * @return float Exempt amount (0 for employment income as per brackets)
     */
    public static function getTaxExemptAmount(string $assessmentType = 'single'): float
    {
        // According to the tax brackets, the first bracket has 1% tax rate (not exempt)
        // But for practical purposes, some allowances might be exempt
        // This can be customized based on company policy
        return 0;
    }

    /**
     * Get tax breakdown in a readable format
     *
     * @param float $taxableIncome Annual taxable income
     * @param string $assessmentType 'single' or 'couple'
     * @return string Formatted breakdown
     */
    public static function getTaxBreakdownText(float $taxableIncome, string $assessmentType = 'single'): string
    {
        $result = self::calculateTax($taxableIncome, $assessmentType);
        $breakdown = [];
        
        foreach ($result['breakdown'] as $item) {
            if ($item['tax'] > 0) {
                $breakdown[] = sprintf(
                    "Bracket %d (%s @ %s): Rs. %s",
                    $item['bracket'],
                    $item['range'],
                    $item['rate'],
                    number_format($item['tax'], 2)
                );
            }
        }
        
        return implode("\n", $breakdown);
    }
}

