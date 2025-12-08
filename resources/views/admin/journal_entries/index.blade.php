@extends('admin.layout')

@section('title', 'Journal Entries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Journal Entries</h1>
        <p class="text-muted mb-0">Double-entry bookkeeping system</p>
    </div>
    <a href="{{ route('admin.journal-entries.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Journal Entry
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Entry #</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th class="text-end">Total Debit</th>
                        <th class="text-end">Total Credit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journalEntries as $entry)
                        <tr>
                            <td><strong>{{ $entry->entry_number }}</strong></td>
                            <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                            <td>{{ Str::limit($entry->description, 50) }}</td>
                            <td>{{ $entry->reference ?? '—' }}</td>
                            <td class="text-end">{{ number_format($entry->total_debit, 2) }}</td>
                            <td class="text-end">{{ number_format($entry->total_credit, 2) }}</td>
                            <td>
                                @if($entry->is_posted)
                                    <span class="badge bg-success">Posted</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.journal-entries.show', $entry) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                    @if(!$entry->is_posted)
                                        <a href="{{ route('admin.journal-entries.edit', $entry) }}" class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.journal-entries.destroy', $entry) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted mb-0">No journal entries found.</p>
                                <a href="{{ route('admin.journal-entries.create') }}" class="btn btn-primary btn-sm mt-2">Create First Entry</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <x-pagination :paginator="$journalEntries" />
    </div>
</div>
@endsection

