@extends('admin.layout')

@section('title', 'Company Details')

@section('content')
<div class="mb-6 flex items-center justify-between">
  <h1 class="text-2xl font-bold">{{ $company->name }}</h1>
  <div class="space-x-2">
    <a href="{{ route('admin.companies.edit', $company) }}" class="bg-indigo-600 text-white px-4 py-2 rounded">Edit</a>
    <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 rounded border">Back</a>
  </div>
  </div>

<div class="bg-white rounded shadow p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
  <div class="md:col-span-1 flex items-start justify-center">
    @php
      $logoUrl = $company->getLogoUrl();
    @endphp
    @if($logoUrl)
      <img src="{{ $logoUrl }}" alt="Logo" class="h-32 rounded shadow bg-white p-2">
    @else
      <div class="h-32 w-32 flex items-center justify-center rounded bg-gray-100 text-gray-600 font-semibold">
        {{ $company->name }}
      </div>
    @endif
  </div>
  <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <div class="text-sm text-gray-500">Address</div>
      <div class="font-medium">{{ $company->address ?: '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">Email</div>
      <div class="font-medium">{{ $company->email ?: '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">Phone</div>
      <div class="font-medium">{{ $company->phone ?: '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">Website</div>
      <div class="font-medium">@if($company->website)<a href="{{ $company->website }}" target="_blank" class="text-indigo-600 hover:underline">{{ $company->website }}</a>@else — @endif</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">Tax Number</div>
      <div class="font-medium">{{ $company->tax_number ?: '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">City</div>
      <div class="font-medium">{{ $company->city ?: '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">State</div>
      <div class="font-medium">{{ $company->state ?: '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">Country</div>
      <div class="font-medium">{{ $company->country ?: '—' }}</div>
    </div>
    <div>
      <div class="text-sm text-gray-500">ZIP</div>
      <div class="font-medium">{{ $company->zip ?: '—' }}</div>
    </div>
  </div>
</div>
@endsection


