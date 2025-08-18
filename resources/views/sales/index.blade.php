@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Sales Data</h1>
    <div class="flex gap-3">
        <button onclick="showImportModal()" class="btn btn-primary">Import CSV</button>
        <button onclick="showExportModal()" class="btn btn-success">Export CSV</button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('sales.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Outlet</label>
            <select name="outlet_id" class="form-select">
                <option value="">All Outlets</option>
                @foreach(\App\Models\Outlet::limit(100)->get() as $outlet)
                    <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                        {{ $outlet->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline">Clear</a>
        </div>
    </form>
</div>

<!-- Sales Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outlet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distributor</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($sales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sale->date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sale->outlet->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sale->product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->product->sku }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($sale->quantity_sold) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">৳{{ number_format($sale->unit_price, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">৳{{ number_format($sale->total_price, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->outlet->distributor->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $sales->links() }}
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

document.getElementById('importForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const statusDiv = document.getElementById('importStatus');
    
    statusDiv.innerHTML = '<div class="flex items-center"><div class="loading"></div><span class="ml-2">Uploading...</span></div>';
    
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
    
    statusDiv.innerHTML = '<div class="flex items-center"><div class="loading"></div><span class="ml-2">Processing...</span></div>';
    
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
    const interval = setInterval(async () => {
        try {
            const response = await fetch(`/sales/export/status/${jobId}`);
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