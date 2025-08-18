@extends('layouts.app')

@section('title', 'Sales Trend Report')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Product Sales Trend Analysis</h1>
    @if(isset($report))
        <a href="{{ route('reports.sales-trend.export', request()->query()) }}" class="btn btn-success">
            Export CSV
        </a>
    @endif
</div>

<!-- Product Selection Form -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-8">
    <form method="GET" action="{{ route('reports.sales-trend') }}" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[250px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Product</label>
            <select name="product_id" required class="form-select">
                <option value="">Choose Product...</option>
                @foreach(\App\Models\Product::limit(100)->orderBy('name')->get() as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="block text-sm font-medium text-gray-700 mb-1">Group By</label>
            <select name="group_by" class="form-select">
               <option value="day" {{ request('group_by') == 'day' ? 'selected' : '' }}>Day</option>
               <option value="week" {{ request('group_by') == 'week' ? 'selected' : '' }}>Week</option>
               <option value="month" {{ request('group_by') == 'month' ? 'selected' : '' }}>Month</option>
           </select>
       </div>
       <div class="w-32">
           <label class="block text-sm font-medium text-gray-700 mb-1">Days</label>
           <select name="days" class="form-select">
               <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>30 Days</option>
               <option value="60" {{ request('days') == 60 ? 'selected' : '' }}>60 Days</option>
               <option value="90" {{ request('days') == 90 ? 'selected' : '' }}>90 Days</option>
               <option value="180" {{ request('days') == 180 ? 'selected' : '' }}>180 Days</option>
           </select>
       </div>
       <button type="submit" class="btn btn-primary">Generate Report</button>
   </form>
</div>

@if(isset($report))
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
   <div class="bg-white rounded-lg shadow p-6">
       <div class="text-sm font-medium text-gray-500 mb-2">Total Transactions</div>
       <div class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']->transaction_count ?? 0) }}</div>
   </div>
   <div class="bg-white rounded-lg shadow p-6">
       <div class="text-sm font-medium text-gray-500 mb-2">Total Sold</div>
       <div class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']->total_sold ?? 0) }}</div>
   </div>
   <div class="bg-white rounded-lg shadow p-6">
       <div class="text-sm font-medium text-gray-500 mb-2">Total Revenue</div>
       <div class="text-2xl font-bold text-gray-900">৳{{ number_format($report['summary']->total_revenue ?? 0, 2) }}</div>
   </div>
   <div class="bg-white rounded-lg shadow p-6">
       <div class="text-sm font-medium text-gray-500 mb-2">Avg Price</div>
       <div class="text-2xl font-bold text-gray-900">৳{{ number_format($report['summary']->avg_selling_price ?? 0, 2) }}</div>
   </div>
</div>

<!-- Trend Chart -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-8">
   <h2 class="text-lg font-semibold text-gray-900 mb-4">Sales Trend Chart</h2>
   <canvas id="trendChart" width="400" height="100"></canvas>
</div>

<!-- Trend Data Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
   <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
       <h2 class="text-lg font-semibold text-gray-900">Detailed Trend Data</h2>
   </div>
   <div class="overflow-x-auto">
       <table class="min-w-full divide-y divide-gray-200">
           <thead class="bg-gray-50">
               <tr>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Price</th>
               </tr>
           </thead>
           <tbody class="bg-white divide-y divide-gray-200">
               @foreach($report['trend_data'] as $data)
               <tr class="hover:bg-gray-50">
                   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data->period }}</td>
                   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data->total_quantity) }}</td>
                   <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">৳{{ number_format($data->total_revenue, 2) }}</td>
                   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">৳{{ number_format($data->avg_price, 2) }}</td>
               </tr>
               @endforeach
           </tbody>
       </table>
   </div>
</div>
@endif
@endsection

@push('scripts')
@if(isset($report))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('trendChart').getContext('2d');
const trendData = @json($report['trend_data']);

new Chart(ctx, {
   type: 'line',
   data: {
       labels: trendData.map(d => d.period),
       datasets: [{
           label: 'Quantity Sold',
           data: trendData.map(d => d.total_quantity),
           borderColor: 'rgb(34, 197, 94)',
           backgroundColor: 'rgba(34, 197, 94, 0.1)',
           yAxisID: 'y',
       }, {
           label: 'Revenue (৳)',
           data: trendData.map(d => d.total_revenue),
           borderColor: 'rgb(59, 130, 246)',
           backgroundColor: 'rgba(59, 130, 246, 0.1)',
           yAxisID: 'y1',
       }]
   },
   options: {
       responsive: true,
       interaction: {
           mode: 'index',
           intersect: false,
       },
       scales: {
           y: {
               type: 'linear',
               display: true,
               position: 'left',
           },
           y1: {
               type: 'linear',
               display: true,
               position: 'right',
               grid: {
                   drawOnChartArea: false,
               },
           },
       }
   }
});
</script>
@endif
@endpush