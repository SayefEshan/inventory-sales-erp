@extends('layouts.app')

@section('title', 'Monthly Sales Report')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Monthly Sales by Distributor</h1>
    <form method="GET" action="{{ route('reports.monthly-sales') }}" class="flex gap-2">
        <select name="month" class="form-select">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ request('month', now()->month) == $i ? 'selected' : '' }}>
                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                </option>
            @endfor
        </select>
        <select name="year" class="form-select">
            @for($y = now()->year; $y >= now()->year - 2; $y--)
                <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>
        <button type="submit" class="btn btn-primary">Generate</button>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Total Revenue</div>
        <div class="text-2xl font-bold text-gray-900">৳{{ number_format($report['totals']['total_revenue'], 2) }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Total Quantity</div>
        <div class="text-2xl font-bold text-gray-900">{{ number_format($report['totals']['total_quantity']) }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Transactions</div>
        <div class="text-2xl font-bold text-gray-900">{{ number_format($report['totals']['total_transactions']) }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Active Distributors</div>
        <div class="text-2xl font-bold text-gray-900">{{ $report['totals']['active_distributors'] }}</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distributor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Outlets</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if(is_object($report['distributors']) && method_exists($report['distributors'], 'items'))
                    @foreach($report['distributors']->items() as $distributor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $distributor->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $distributor->region }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $distributor->active_outlets }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($distributor->transaction_count) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($distributor->total_quantity) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">৳{{ number_format($distributor->total_revenue, 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    @foreach($report['distributors'] as $distributor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $distributor->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $distributor->region }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $distributor->active_outlets }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($distributor->transaction_count) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($distributor->total_quantity) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">৳{{ number_format($distributor->total_revenue, 2) }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if(is_object($report['distributors']) && method_exists($report['distributors'], 'links'))
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $report['distributors']->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection