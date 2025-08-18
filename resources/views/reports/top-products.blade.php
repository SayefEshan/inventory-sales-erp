@extends('layouts.app')

@section('title', 'Top Products Report')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Top 100 Selling Products</h1>
    <div class="flex gap-3 items-center">
        <form method="GET" action="{{ route('reports.top-products') }}">
            <select name="period" onchange="this.form.submit()" class="form-select">
                <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
            </select>
        </form>
        <a href="{{ route('reports.top-products.export', ['period' => request('period', 'last_month')]) }}" 
           class="btn btn-success">
            Export CSV
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <p class="text-sm text-gray-600">
            Period: {{ $report['start_date'] }} to {{ $report['end_date'] }}
            <span class="ml-4">
                @if(is_object($report['products']) && method_exists($report['products'], 'total'))
                    Showing {{ $report['products']->total() }} top selling products ({{ $report['products']->perPage() }} per page)
                @endif
            </span>
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outlets</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if(is_object($report['products']) && method_exists($report['products'], 'items'))
                    @foreach($report['products']->items() as $index => $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ($report['products']->currentPage() - 1) * $report['products']->perPage() + $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $product->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($product->total_quantity) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">৳{{ number_format($product->total_revenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->outlet_count }}</td>
                    </tr>
                    @endforeach
                @else
                    @foreach($report['products'] as $index => $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $product->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($product->total_quantity) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">৳{{ number_format($product->total_revenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->outlet_count }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if(is_object($report['products']) && method_exists($report['products'], 'links'))
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $report['products']->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection