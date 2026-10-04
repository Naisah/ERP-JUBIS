<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Order Tracking | {{ $tracking }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col font-sans h-full w-full">
    
    @php
        $status = $shipment ? strtolower($shipment->status) : 'processing';
        
        $badgeClass = 'bg-yellow-100 text-yellow-800';
        $badgeText = 'Processing...';
        $eta = 'Pending pickup';
        $progress = 10;
        
        if ($status === 'dispatched' || $status === 'in_transit') {
            $badgeClass = 'bg-blue-100 text-blue-800';
            $badgeText = 'On the way';
            $eta = '12 Mins Away';
            $progress = 60;
        } elseif ($status === 'delivered') {
            $badgeClass = 'bg-green-100 text-green-800';
            $badgeText = 'Delivered';
            $eta = 'Arrived';
            $progress = 100;
        }
    @endphp

    <!-- Header -->
    <header class="bg-white shadow-md px-6 py-4 flex items-center justify-between z-20 shrink-0 relative">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold text-xl">L</div>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-tight">Live Delivery Tracking</h1>
                <p class="text-sm text-gray-500">Order ID: <span class="font-mono text-gray-700">{{ $tracking }}</span></p>
            </div>
        </div>
        <div class="text-right hidden sm:block">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $badgeClass }}">
                @if($status !== 'delivered')
                    <span class="w-2 h-2 rounded-full {{ str_replace('100', '500', explode(' ', $badgeClass)[0]) }} animate-pulse mr-2"></span> 
                @endif
                {{ $badgeText }}
            </span>
        </div>
    </header>

    <!-- Map Container -->
    <main class="relative flex-grow w-full h-full" style="flex: 1 1 auto;">
        <!-- Embedded OpenStreetMap for simulation -->
        <iframe 
            src="https://www.openstreetmap.org/export/embed.html?bbox=120.97232818603517%2C14.542279261274534%2C121.05472564697267%2C14.596075908581692&amp;layer=mapnik&amp;marker=14.569181674404764%2C121.0135269165039" 
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; filter: contrast(1.1) saturate(1.2);"
            frameborder="0" 
            scrolling="no" 
            marginheight="0" 
            marginwidth="0">
        </iframe>

        <!-- Floating Info Card -->
        <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 w-full max-w-md px-4 z-20 pointer-events-none">
            <div class="bg-white rounded-2xl shadow-2xl p-5 border border-gray-100 pointer-events-auto">
                <div class="flex items-center space-x-4 mb-4">
                    <img src="https://ui-avatars.com/api/?name=Rider+Juan&background=random" alt="Driver" class="w-12 h-12 rounded-full border-2 border-orange-500">
                    <div>
                        <h3 class="font-bold text-gray-900">Rider Juan</h3>
                        <p class="text-sm text-gray-500">Honda Click 125i • Plate: ABC 1234</p>
                    </div>
                    <div class="ml-auto flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.036 11.036 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                    </div>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Est. Arrival</span>
                    <span class="font-bold text-gray-900">{{ $eta }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="bg-orange-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
