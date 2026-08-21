<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>MobiPOS — Mobile Shop Management</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#4f46e5">
<link rel="apple-touch-icon" href="{{ asset('assets/logo/main-logo.png') }}">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
<script>
    if (localStorage.getItem('sidebarState') === 'collapsed') {
        document.body.classList.add('sidebar-collapsed');
    }
</script>
    @include('partials.sidebar')
    
    <!-- OVERLAY -->
    <div class="overlay" id="overlay"></div>
    
    <!-- MAIN -->
    <div class="main-wrapper">
        @include('partials.topbar')
        
        @yield('content')
        {{ $slot ?? '' }}
    </div>
    
    @include('partials.modals')
    
<div class="toast-container" id="toastContainer"></div>

@php
    $printSetting = Auth::check() ? \App\Models\InvoiceSetting::where('user_id', Auth::id())->first() : null;
@endphp
<script>
    window.printSettings = {
        name: {!! json_encode($printSetting->store_name ?? 'MobiPOS') !!},
        desc: {!! json_encode($printSetting->header_text ?? 'Store address here') !!},
        address: {!! json_encode($printSetting->address ?? 'Address details') !!},
        heading: "INVOICE",
        footer: {!! json_encode($printSetting->footer_text ?? '*** Thank You! ***') !!},
        logo: {!! json_encode($printSetting->logo ?? null) !!},
        logoSize: {!! json_encode($printSetting->logo_size ?? 120) !!}
    };
</script>

<script src="{{ asset('assets/js/core.js') }}?v={{ time() }}"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').then(registration => {
                console.log('SW registered:', registration.scope);
            }).catch(error => {
                console.log('SW registration failed:', error);
            });
        });
    }
</script>
@yield('scripts')
</body>
</html>
