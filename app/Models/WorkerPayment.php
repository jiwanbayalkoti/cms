<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;
use Carbon\Carbon;

class WorkerPayment extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'worker_id',
        'project_id',
        'wage_type',
        'period_start',
        'period_end',
        'present_days',
        'half_days',
        'absent_days',
        'leave_days',
        'total_days',
        'overtime_hours',
        'daily_wage',
        'monthly_salary',
        'calculated_amount',
        'overtime_amount',
        'bonus',
        'deduction',
        'net_amount',
        'paid_amount',
        'payment_date',
        'payment_method',
        'status',
        'expense_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_date' => 'date',
        'overtime_hours' => 'decimal:2',
        'daily_wage' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Summarize attendance and calculate payable for a worker in a period.
     *
     * @return array{
     *   present_days:int, half_days:int, absent_days:int, leave_days:int,
     *   total_days:int, overtime_hours:float, day_credits:float,
     *   calculated_amount:float, overtime_amount:float, net_amount:float,
     *   daily_wage:?float, monthly_salary:?float, wage_type:string
     * }
     */
    public static function calculateForWorker(Worker $worker, string $periodStart, string $periodEnd, float $bonus = 0, float $deduction = 0, ?float $overtimeRate = null): array
    {
        $start = Carbon::parse($periodStart)->startOfDay();
        $end = Carbon::parse($periodEnd)->startOfDay();
        $totalDays = $start->diffInDays($end) + 1;

        $rows = WorkerAttendance::where('worker_id', $worker->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $present = $rows->where('status', 'present')->count();
        $half = $rows->where('status', 'half_day')->count();
        $absent = $rows->where('status', 'absent')->count();
        $leave = $rows->where('status', 'leave')->count();
        $overtimeHours = (float) $rows->sum('overtime_hours');
        $dayCredits = $present + ($half * 0.5);

        $dailyWage = (float) ($worker->daily_wage ?? 0);
        $monthlySalary = (float) ($worker->monthly_salary ?? 0);
        $wageType = $worker->wage_type;

        if ($wageType === 'monthly') {
            // Prorate monthly salary by attendance day credits vs calendar days in period
            $calculated = $totalDays > 0
                ? ($monthlySalary / $totalDays) * $dayCredits
                : 0;
        } else {
            $calculated = $dayCredits * $dailyWage;
        }

        // Overtime: half of daily wage per hour if daily; else monthly/30/8 approx if monthly
        if ($overtimeRate === null) {
            if ($wageType === 'daily' && $dailyWage > 0) {
                $overtimeRate = $dailyWage / 8;
            } elseif ($wageType === 'monthly' && $monthlySalary > 0) {
                $overtimeRate = ($monthlySalary / 30) / 8;
            } else {
                $overtimeRate = 0;
            }
        }
        $overtimeAmount = $overtimeHours * $overtimeRate;
        $net = $calculated + $overtimeAmount + $bonus - $deduction;

        return [
            'present_days' => $present,
            'half_days' => $half,
            'absent_days' => $absent,
            'leave_days' => $leave,
            'total_days' => $totalDays,
            'overtime_hours' => round($overtimeHours, 2),
            'day_credits' => $dayCredits,
            'calculated_amount' => round($calculated, 2),
            'overtime_amount' => round($overtimeAmount, 2),
            'net_amount' => round(max(0, $net), 2),
            'daily_wage' => $dailyWage ?: null,
            'monthly_salary' => $monthlySalary ?: null,
            'wage_type' => $wageType,
        ];
    }
}
