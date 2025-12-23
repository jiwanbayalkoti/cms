@extends('admin.layout')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Super Admin Dashboard</h1>
        <p class="mt-2 text-gray-600">Overview of all companies and their projects</p>
    </div>
</div>

@if($companies->isEmpty())
    <div class="bg-white shadow-lg rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No companies found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new company.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($companies as $company)
            <form action="{{ route('admin.companies.switch') }}" method="POST" class="h-full">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">
                <input type="hidden" name="redirect_to" value="projects">
                <button type="submit" 
                        class="w-full bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-all duration-200 cursor-pointer group border-2 border-transparent hover:border-indigo-500 text-left">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex-shrink-0">
                            @if($company->logo)
                                <img src="{{ $company->getLogoUrl() }}" alt="{{ $company->name }}" class="h-12 w-12 rounded-lg object-cover">
                            @else
                                <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-bold text-lg">{{ strtoupper(substr($company->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
                        {{ $company->name }}
                    </h3>
                    
                    <div class="flex items-center mt-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $company->projects_count }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($company->email || $company->phone)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                @if($company->email)
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="truncate">{{ $company->email }}</span>
                                    </div>
                                @endif
                                @if($company->phone)
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span>{{ $company->phone }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                </button>
            </form>
        @endforeach
    </div>
@endif

@endsection

