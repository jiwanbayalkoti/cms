@forelse($books as $b)
<tr>
    <td>{{ $b->id }}</td>
    <td>{{ $b->measurement_date->format('Y-m-d') }}</td>
    <td>{{ $b->project->name ?? '—' }}</td>
    <td>{{ $b->contract_no ?? '—' }}</td>
    <td>{{ Str::limit($b->title, 40) ?: '—' }}</td>
    <td class="text-end">
        <a href="{{ route('admin.measurement-books.show', $b) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
        <a href="{{ route('admin.measurement-books.edit', $b) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form action="{{ route('admin.measurement-books.destroy', $b) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Measurement Book?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-4">No records. <a href="{{ route('admin.measurement-books.create') }}">Create one</a></td></tr>
@endforelse
