<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MaterialCalculatorExport;
use App\Http\Controllers\Controller;
use App\Models\MaterialCalculatorSet;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class MaterialCalculatorController extends Controller
{
    public function index()
    {
        $concreteGrades = [
            ['label' => 'M5 (1:5:10)', 'value' => 'M5', 'ratio' => [1, 5, 10]],
            ['label' => 'M10 (1:3:6)', 'value' => 'M10', 'ratio' => [1, 3, 6]],
            ['label' => 'M15 (1:2:4)', 'value' => 'M15', 'ratio' => [1, 2, 4]],
            ['label' => 'M20 (1:1.5:3)', 'value' => 'M20', 'ratio' => [1, 1.5, 3]],
            ['label' => 'M25 (1:1:2)', 'value' => 'M25', 'ratio' => [1, 1, 2]],
        ];

        $mortarMixes = [
            ['label' => '1:6', 'value' => '1:6', 'ratio' => [1, 6]],
            ['label' => '1:5', 'value' => '1:5', 'ratio' => [1, 5]],
            ['label' => '1:4', 'value' => '1:4', 'ratio' => [1, 4]],
            ['label' => '1:3', 'value' => '1:3', 'ratio' => [1, 3]],
        ];

        $solingMaterials = [
            ['label' => 'Gravel', 'value' => 'gravel'],
            ['label' => 'Stone', 'value' => 'stone'],
            ['label' => 'Sand', 'value' => 'sand'],
        ];

        $defaultCosts = [
            'cement_bag' => 0,
            'sand_m3' => 0,
            'aggregate_m3' => 0,
            'water_litre' => 0,
            'brick_unit' => 0,
            'soling_m3' => 0,
            'steel_kg' => 0,
        ];

        $savedSets = MaterialCalculatorSet::with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.material_calculator.index', compact(
            'concreteGrades',
            'mortarMixes',
            'solingMaterials',
            'defaultCosts',
            'savedSets'
        ));
    }

    public function save(Request $request)
    {
        [$items, $summary] = $this->validatePayload($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        MaterialCalculatorSet::create([
            'company_id' => CompanyContext::getActiveCompanyId(),
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'calculations' => $items,
            'summary' => $summary,
        ]);

        return redirect()
            ->route('admin.material-calculator.index')
            ->with('success', 'Calculation set saved for future use.');
    }

    public function exportExcel(Request $request)
    {
        [$items, $summary] = $this->validatePayload($request);

        return Excel::download(
            new MaterialCalculatorExport($items, $summary),
            'material-calculator-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        [$items, $summary] = $this->validatePayload($request);

        $pdf = Pdf::loadView('admin.material_calculator.pdf', [
            'items' => $items,
            'summary' => $summary,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('material-calculator-' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * @return array{array<int,array>,array<string,mixed>}
     *
     * @throws ValidationException
     */
    private function validatePayload(Request $request): array
    {
        $payload = $request->all();

        if (isset($payload['calculations']) && is_string($payload['calculations'])) {
            $payload['calculations'] = json_decode($payload['calculations'], true) ?? [];
        }

        if (isset($payload['summary']) && is_string($payload['summary'])) {
            $payload['summary'] = json_decode($payload['summary'], true) ?? [];
        }

        $validator = Validator::make($payload, [
            'calculations' => 'required|array|min:1',
            'calculations.*.work_type' => 'required|string',
            'calculations.*.description' => 'required|string',
            'calculations.*.materials' => 'required|array',
            'summary' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $items = collect($payload['calculations'])
            ->map(function (array $item, int $index) {
                return [
                    'sn' => $index + 1,
                    'work_type' => Arr::get($item, 'work_type'),
                    'description' => Arr::get($item, 'description'),
                    'materials' => Arr::get($item, 'materials', []),
                    'cost' => Arr::get($item, 'cost', []),
                ];
            })->values()->all();

        $summary = $payload['summary'] ?? [];

        return [$items, $summary];
    }
}

