@extends('admin.layout')

@section('title', 'Worker Details')

@section('content')
<div class="mb-6 flex flex-wrap justify-between items-center gap-3">
    <h1 class="text-3xl font-bold text-gray-900">{{ $worker->name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.workers.edit', $worker) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">Edit</a>
        <a href="{{ route('admin.workers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
    </div>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 max-w-3xl">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><dt class="text-sm text-gray-500">Phone</dt><dd class="font-medium">{{ $worker->phone ?? '—' }}</dd></div>
        <div><dt class="text-sm text-gray-500">Email</dt><dd class="font-medium">{{ $worker->email ?? '—' }}</dd></div>
        <div><dt class="text-sm text-gray-500">Project</dt><dd class="font-medium">{{ $worker->project?->name ?? '—' }}</dd></div>
        <div><dt class="text-sm text-gray-500">Join Date</dt><dd class="font-medium">{{ $worker->join_date?->format('M d, Y') ?? '—' }}</dd></div>
        <div><dt class="text-sm text-gray-500">Wage Type</dt><dd class="font-medium">{{ ucfirst($worker->wage_type) }}</dd></div>
        <div><dt class="text-sm text-gray-500">Rate</dt><dd class="font-medium">{{ $worker->wage_label }}</dd></div>
        <div><dt class="text-sm text-gray-500">Status</dt><dd class="font-medium">{{ $worker->is_active ? 'Active' : 'Inactive' }}</dd></div>
        <div class="md:col-span-2"><dt class="text-sm text-gray-500">Address</dt><dd class="font-medium">{{ $worker->address ?? '—' }}</dd></div>
        <div class="md:col-span-2"><dt class="text-sm text-gray-500">Notes</dt><dd class="font-medium">{{ $worker->notes ?? '—' }}</dd></div>
    </dl>
</div>
@endsection
