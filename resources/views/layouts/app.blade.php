<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inventory Sales ERP')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <!-- Global Loading Indicator -->
    <div id="globalLoader" class="fixed inset-0 bg-white bg-opacity-90 flex items-center justify-center z-[9999] hidden">
        <div class="ripple-loader">
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-blue-600">
                        Inventory ERP
                    </a>
                </div>
                
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" 
                       class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : '' }}">
                        Dashboard
                    </a>
                    
                    <a href="{{ route('sales.index') }}" 
                       class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('sales.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                        Sales
                    </a>
                    
                    <div class="dropdown-group">
                        <button class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                            Reports ▼
                        </button>
                        <div class="dropdown-menu">
                            <a href="{{ route('reports.top-products') }}">
                                Top Products
                            </a>
                            <a href="{{ route('reports.monthly-sales') }}">
                                Monthly Sales
                            </a>
                            <a href="{{ route('reports.low-stock') }}">
                                Low Stock
                            </a>
                            <a href="{{ route('reports.sales-trend') }}">
                                Sales Trend
                            </a>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-sm text-gray-500">
                &copy; 2024 Inventory Sales ERP. All rights reserved.
            </p>
        </div>
    </footer>

    @stack('scripts')
    
    <!-- Global Loader JavaScript -->
    <script>
        // Global loader functions
        function showGlobalLoader() {
            document.getElementById('globalLoader').classList.remove('hidden');
        }
        
        function hideGlobalLoader() {
            document.getElementById('globalLoader').classList.add('hidden');
        }
        
        // Show loader on page load
        document.addEventListener('DOMContentLoaded', function() {
            hideGlobalLoader(); // Ensure it's hidden when page is fully loaded
        });
        
        // Show loader when user clicks navigation links
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && !link.hasAttribute('download') && link.hostname === window.location.hostname) {
                // Don't show loader for same page links, downloads, or external links
                if (link.href !== window.location.href) {
                    showGlobalLoader();
                }
            }
        });
        
        // Show loader on form submissions (except AJAX)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            // Only show for forms that don't have data-ajax attribute
            if (!form.hasAttribute('data-ajax') && !form.id.includes('Modal')) {
                showGlobalLoader();
            }
        });
        
        // Hide loader when page is about to be hidden (back button, etc.)
        window.addEventListener('pagehide', function() {
            hideGlobalLoader();
        });
        
        // Hide loader if page load fails or user navigates back
        window.addEventListener('pageshow', function() {
            hideGlobalLoader();
        });
        
        // Auto-hide loader after 10 seconds (fallback for stuck states)
        let loaderTimeout;
        
        // Update showGlobalLoader to include timeout
        showGlobalLoader = function() {
            document.getElementById('globalLoader').classList.remove('hidden');
            clearTimeout(loaderTimeout);
            loaderTimeout = setTimeout(hideGlobalLoader, 10000); // Auto-hide after 10 seconds
        };
        
        // Make functions globally available for testing
        window.showGlobalLoader = showGlobalLoader;
        window.hideGlobalLoader = hideGlobalLoader;
    </script>
</body>
</html>