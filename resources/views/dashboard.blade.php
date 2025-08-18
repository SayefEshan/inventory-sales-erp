@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard Overview</h1>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Today's Sales</div>
        <div class="text-3xl font-bold text-gray-900">৳{{ number_format($summary['today']->total_revenue ?? 0, 2) }}</div>
        <div class="text-sm text-green-600 mt-2">{{ $summary['today']->total_transactions ?? 0 }} transactions</div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Monthly Sales</div>
        <div class="text-3xl font-bold text-gray-900">৳{{ number_format($summary['this_month']->total_revenue ?? 0, 2) }}</div>
        <div class="text-sm text-green-600 mt-2">{{ $summary['this_month']->total_transactions ?? 0 }} transactions</div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Avg Transaction</div>
        <div class="text-3xl font-bold text-gray-900">৳{{ number_format($summary['this_month']->avg_transaction_value ?? 0, 2) }}</div>
        <div class="text-sm text-gray-600 mt-2">This month</div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-2">Low Stock Alerts</div>
        <div class="text-3xl font-bold text-red-600">{{ $summary['low_stock_count'] }}</div>
        <div class="text-sm text-red-600 mt-2">Products need restocking</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Quick Actions</div>
        <div class="flex flex-wrap gap-3">
            <button onclick="showImportModal()" class="btn btn-primary">Import Sales</button>
            <button onclick="showExportModal()" class="btn btn-success">Export Sales</button>
            <a href="{{ route('reports.low-stock') }}" class="btn btn-warning">View Low Stock</a>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Reports</div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('reports.top-products') }}" class="btn btn-outline">Top Products</a>
            <a href="{{ route('reports.monthly-sales') }}" class="btn btn-outline">Monthly Sales</a>
            <a href="{{ route('reports.sales-trend') }}" class="btn btn-outline">Sales Trends</a>
        </div>
    </div>
</div>

<!-- Top Products Today -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Top Selling Products Today</div>
    <div class="table-responsive">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($summary['top_products_today'] as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            {{ $product->category }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($product->total_quantity) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">৳{{ number_format($product->total_revenue, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No sales today</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Import Sales Data</h3>
        <form id="importForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV File</label>
                <input type="file" name="file" accept=".csv" required 
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Upload</button>
                <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Cancel</button>
            </div>
        </form>
        <div id="importStatus" class="mt-4"></div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Export Sales Data</h3>
        <form id="exportForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" name="date_from" class="form-input">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" name="date_to" class="form-input">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-success">Export</button>
                <button type="button" class="btn btn-secondary" onclick="closeExportModal()">Cancel</button>
            </div>
        </form>
        <div id="exportStatus" class="mt-4"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Modal functions
function showImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}

function showExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}

// Import/Export handlers (same as before)
document.getElementById('importForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const statusDiv = document.getElementById('importStatus');
    
    statusDiv.innerHTML = '<div class="loading"></div> <span class="ml-2">Uploading...</span>';
    
    try {
        const response = await fetch('{{ route("sales.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        statusDiv.innerHTML = `<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">${data.message}</div>`;
        
        setTimeout(() => {
            closeImportModal();
            statusDiv.innerHTML = '';
            e.target.reset();
        }, 3000);
    } catch (error) {
        statusDiv.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error: ${error.message}</div>`;
    }
});

document.getElementById('exportForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const statusDiv = document.getElementById('exportStatus');
    
    statusDiv.innerHTML = '<div class="loading"></div> <span class="ml-2">Processing...</span>';
    
    try {
        const response = await fetch('{{ route("sales.export") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        checkExportStatus(data.job_id, statusDiv);
        
    } catch (error) {
        statusDiv.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error: ${error.message}</div>`;
    }
});

async function checkExportStatus(jobId, statusDiv) {
    const checkUrl = `/sales/export/status/${jobId}`;
    
    const interval = setInterval(async () => {
        try {
            const response = await fetch(checkUrl);
            const data = await response.json();
            
            if (data.status === 'completed') {
                clearInterval(interval);
                statusDiv.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        Export ready! 
                        <a href="${data.download_url}" class="underline font-semibold">Download</a>
                    </div>
                `;
            } else if (data.status === 'failed') {
                clearInterval(interval);
                statusDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Export failed</div>';
            }
        } catch (error) {
            clearInterval(interval);
            statusDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error checking status</div>';
        }
    }, 2000);
}
</script>
@endpush