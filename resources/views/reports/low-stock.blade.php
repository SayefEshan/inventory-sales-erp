@extends('layouts.app')

@section('title', 'Low Stock Alerts')

@section('content')
<h1 class="text-3xl font-bold text-gray-900 mb-8">Low Stock Alerts</h1>

<div class="custom-grid grid-cols-2 mb-30">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Total Product Alerts</div>
        <div class="text-3xl font-bold text-red-600">{{ $report['total_alerts'] }}</div>
        <div class="text-sm text-red-600 mt-2">Products below minimum stock</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Affected Outlets</div>
        <div class="text-3xl font-bold text-red-600">{{ $report['affected_outlets'] }}</div>
        <div class="text-sm text-red-600 mt-2">Outlets with low inventory</div>
    </div>
</div>

<!-- Low Stock Products -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-900">Products Below Minimum Stock Level</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outlet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shortage</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if(is_object($report['products']) && method_exists($report['products'], 'items'))
                    @foreach($report['products']->items() as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->outlet_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->city }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $product->quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->min_stock_level }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                -{{ $product->shortage }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                @else
                    @foreach($report['products'] as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->outlet_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->city }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $product->quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->min_stock_level }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                -{{ $product->shortage }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Pagination for Products -->
    @if(is_object($report['products']) && method_exists($report['products'], 'links'))
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $report['products']->fragment('products')->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Outlets with Multiple Low Stock Items -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-900">Outlets with Multiple Low Stock Items</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outlet Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Low Stock Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if(is_object($report['outlets']) && method_exists($report['outlets'], 'items'))
                    @foreach($report['outlets']->items() as $outlet)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $outlet->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $outlet->city }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $outlet->state }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $outlet->low_stock_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $outlet->total_products }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ round(($outlet->low_stock_count / $outlet->total_products) * 100, 1) }}%
                        </td>
                    </tr>
                    @endforeach
                @else
                    @foreach($report['outlets'] as $outlet)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $outlet->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $outlet->city }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $outlet->state }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $outlet->low_stock_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $outlet->total_products }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ round(($outlet->low_stock_count / $outlet->total_products) * 100, 1) }}%
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Pagination for Outlets -->
    @if(is_object($report['outlets']) && method_exists($report['outlets'], 'links'))
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $report['outlets']->fragment('outlets')->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection