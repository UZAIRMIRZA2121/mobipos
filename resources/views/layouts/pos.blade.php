<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>MobiPOS — POS Terminal</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
<style>
    /* Full screen specific overrides */
    body {
        margin: 0;
        padding: 0;
        height: 100vh;
        width: 100vw;
        overflow: hidden;
    }
    .pos-layout-wrapper {
        height: 100vh;
        width: 100vw;
        display: flex;
        flex-direction: column;
    }
</style>
</head>
<body>
    
    <div class="pos-layout-wrapper">
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

<script src="{{ asset('assets/js/script.js') }}"></script>
</body>
</html>
