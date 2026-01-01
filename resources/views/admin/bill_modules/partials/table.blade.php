@forelse($bills as $bill)
    <tr>
        <td>{{ $bill->id }}</td>
        <td>
            <a href="{{ route('admin.bill-modules.show', $bill) }}" class="text-decoration-none">
                {{ $bill->title }}
            </a>
        </td>
        <td>{{ $bill->project->name ?? '—' }}</td>
        <td>{{ $bill->version }}</td>
        <td>{{ $bill->mb_number ?? '—' }}</td>
        <td>
            @php
                $badgeClass = [
                    'draft' => 'bg-secondary',
                    'submitted' => 'bg-info',
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'archived' => 'bg-dark',
                ][$bill->status] ?? 'bg-secondary';
            @endphp
            <span class="badge {{ $badgeClass }}">{{ ucfirst($bill->status) }}</span>
        </td>
        <td>{{ number_format($bill->aggregate->grand_total ?? 0, 2) }}</td>
        <td>{{ $bill->creator->name ?? '—' }}</td>
        <td>{{ $bill->created_at->format('Y-m-d') }}</td>
        <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
                <a href="{{ route('admin.bill-modules.show', $bill) }}" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="bi bi-eye"></i>
                </a>
                    @if($bill->canEdit())
                        <button onclick="openEditBillModal({{ $bill->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                    @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center text-muted py-3">No bills found.</td>
    </tr>
@endforelse

