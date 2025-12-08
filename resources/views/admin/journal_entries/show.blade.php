@extends('admin.layout')

@section('title', 'Journal Entry Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Journal Entry Details</h1>
        <p class="text-muted mb-0">{{ $journalEntry->entry_number }}</p>
    </div>
    <div>
        @if(!$journalEntry->is_posted)
            <a href="{{ route('admin.journal-entries.edit', $journalEntry) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <form action="{{ route('admin.journal-entries.post', $journalEntry) }}" method="POST" class="d-inline me-2">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to post this journal entry?');">
                    <i class="bi bi-check-circle me-1"></i> Post Entry
                </button>
            </form>
        @else
            <form action="{{ route('admin.journal-entries.unpost', $journalEntry) }}" method="POST" class="d-inline me-2">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to unpost this journal entry?');">
                    <i class="bi bi-x-circle me-1"></i> Unpost Entry
                </button>
            </form>
        @endif
        <a href="{{ route('admin.journal-entries.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Entry Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Entry Number:</th>
                                <td><strong>{{ $journalEntry->entry_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Entry Date:</th>
                                <td>{{ $journalEntry->entry_date->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <th>Entry Type:</th>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($journalEntry->entry_type) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Project:</th>
                                <td>{{ $journalEntry->project->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Reference:</th>
                                <td>{{ $journalEntry->reference ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Status:</th>
                                <td>
                                    @if($journalEntry->is_posted)
                                        <span class="badge bg-success">Posted</span>
                                    @else
                                        <span class="badge bg-warning">Draft</span>
                                    @endif
                                </td>
                            </tr>
                            @if($journalEntry->is_posted)
                                <tr>
                                    <th>Posted At:</th>
                                    <td>{{ $journalEntry->posted_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Posted By:</th>
                                    <td>{{ $journalEntry->postedBy->name ?? '—' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>Total Debit:</th>
                                <td><strong>{{ number_format($journalEntry->total_debit, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Total Credit:</th>
                                <td><strong>{{ number_format($journalEntry->total_credit, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Balance:</th>
                                <td>
                                    @if($journalEntry->isBalanced())
                                        <span class="badge bg-success">Balanced</span>
                                    @else
                                        <span class="badge bg-danger">Not Balanced</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created By:</th>
                                <td>{{ $journalEntry->creator->name ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($journalEntry->description)
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <p class="mb-0">{{ $journalEntry->description }}</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Journal Entry Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journalEntry->items as $item)
                                <tr>
                                    <td>{{ $item->line_number }}</td>
                                    <td>
                                        <a href="{{ route('admin.chart-of-accounts.show', $item->account) }}">
                                            {{ $item->account->account_code }} - {{ $item->account->account_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->entry_type === 'debit' ? 'success' : 'danger' }}">
                                            {{ ucfirst($item->entry_type) }}
                                        </span>
                                    </td>
                                    <td class="text-end"><strong>{{ number_format($item->amount, 2) }}</strong></td>
                                    <td>{{ $item->description ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                <td></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-between">
                                        <span>Debit: <strong>{{ number_format($journalEntry->total_debit, 2) }}</strong></span>
                                        <span>Credit: <strong>{{ number_format($journalEntry->total_credit, 2) }}</strong></span>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                @if(!$journalEntry->is_posted)
                    <a href="{{ route('admin.journal-entries.edit', $journalEntry) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-pencil me-1"></i> Edit Entry
                    </a>
                    <form action="{{ route('admin.journal-entries.post', $journalEntry) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to post this journal entry?');">
                            <i class="bi bi-check-circle me-1"></i> Post Entry
                        </button>
                    </form>
                    <form action="{{ route('admin.journal-entries.destroy', $journalEntry) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this journal entry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Entry
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.journal-entries.unpost', $journalEntry) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Are you sure you want to unpost this journal entry?');">
                            <i class="bi bi-x-circle me-1"></i> Unpost Entry
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.journal-entries.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

