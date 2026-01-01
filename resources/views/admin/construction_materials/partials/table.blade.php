<tbody id="materialsTableBody">
    @forelse($materials as $material)
        <tr data-material-id="{{ $material->id }}">
            <td>{{ $material->id }}</td>
            <td>
                <div class="fw-semibold">{{ $material->material_name }}</div>
                <small class="text-muted">{{ $material->material_category }}</small>
            </td>
            <td>{{ $material->project_name }}</td>
            <td>{{ $material->supplier_name }}</td>
            <td>{{ number_format($material->quantity_received, 2) }} {{ $material->unit }}</td>
            <td>{{ number_format($material->quantity_used, 2) }} {{ $material->unit }}</td>
            <td>{{ number_format($material->quantity_remaining, 2) }} {{ $material->unit }}</td>
            <td>{{ number_format($material->total_cost, 2) }}</td>
            <td>
                <span class="badge bg-secondary">{{ $material->status }}</span>
            </td>
            <td class="text-end">
                <div class="d-flex gap-1 justify-content-end text-nowrap">
                    <button onclick="openViewMaterialModal({{ $material->id }})" class="btn btn-sm btn-outline-primary" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditMaterialModal({{ $material->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="{{ route('admin.construction-materials.clone', $material) }}" class="btn btn-sm btn-outline-info" onclick="return confirm('Are you sure you want to duplicate this material record?');" title="Duplicate">
                        <i class="bi bi-files"></i>
                    </a>
                    <button onclick="showDeleteMaterialConfirmation({{ $material->id }}, '{{ addslashes($material->material_name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="10" class="text-center text-muted py-3">
                No records found.
            </td>
        </tr>
    @endforelse
</tbody>
@if($materials->count() > 0)
<tfoot id="materialsTableFooter">
    <tr class="table-secondary">
        <td colspan="7" class="text-end"><strong>Total:</strong></td>
        <td class="text-end"><strong id="totalCostDisplay">{{ number_format($totalCost ?? $materials->sum('total_cost'), 2) }}</strong></td>
        <td colspan="2"></td>
    </tr>
    @if(isset($totalAdvancePayments) && $totalAdvancePayments > 0)
    <tr class="table-info">
        <td colspan="7" class="text-end"><strong>Less: Advance Payments</strong></td>
        <td class="text-end"><strong class="text-info" id="advancePaymentsDisplay">({{ number_format($totalAdvancePayments, 2) }})</strong></td>
        <td colspan="2"></td>
    </tr>
    <tr class="table-success">
        <td colspan="7" class="text-end"><strong>Net Balance (After Advance Payments):</strong></td>
        <td class="text-end"><strong class="{{ ($netBalance ?? 0) > 0 ? 'text-danger' : (($netBalance ?? 0) < 0 ? 'text-success' : 'text-secondary') }}" id="netBalanceDisplay">{{ number_format($netBalance ?? 0, 2) }}</strong></td>
        <td colspan="2"></td>
    </tr>
    @endif
</tfoot>
@endif

