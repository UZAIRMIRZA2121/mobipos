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
    $storeSetting = Auth::check() ? \App\Models\StoreSetting::where('user_id', Auth::id())->first() : null;
    $isAdmin = Auth::check() && Auth::user()->type === 'admin';
@endphp
<script>
    window.ACTIVE_MODULE = {!! json_encode($storeSetting->business_type ?? null) !!};
    window.IS_ADMIN = {!! json_encode($isAdmin) !!};
    window.printSettings = {
        name: {!! json_encode($printSetting->store_name ?? 'MobiPOS') !!},
        desc: {!! json_encode($printSetting->header_text ?? 'Store address here') !!},
        address: {!! json_encode($printSetting->address ?? 'Address details') !!},
        heading: "INVOICE",
        footer: {!! json_encode($printSetting->footer_text ?? '*** Thank You! ***') !!},
        logo: {!! json_encode($printSetting->logo ?? null) !!},
        logoSize: {!! json_encode($printSetting->logo_size ?? 120) !!},
        barcode_print: {!! json_encode($printSetting->barcode_print ?? true) !!}
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
<script>
    if (window.ACTIVE_MODULE === null && !window.IS_ADMIN) {
        document.getElementById('selectModuleModal').classList.remove('hidden');
    }

    async function saveOnboardingModule() {
        const business_type = document.getElementById('onboardingBusinessType').value;
        try {
            const res = await fetch('/shop/api/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ business_type: business_type })
            });
            if (res.ok) {
                window.location.reload();
            } else {
                alert('Error saving business type');
            }
        } catch (e) {
            console.error(e);
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    // Global Barcode Scanner Listener for Invoices
    let barcodeBuffer = '';
    let barcodeTimeout;
    document.addEventListener('keydown', function(e) {
        if (e.key.length === 1) {
            barcodeBuffer += e.key;
            clearTimeout(barcodeTimeout);
            // Scanners type very fast (usually < 30ms per char). Human typing is slower.
            barcodeTimeout = setTimeout(() => {
                barcodeBuffer = '';
            }, 50); 
        } else if (e.key === 'Enter') {
            if (barcodeBuffer.length >= 3) {
                let code = barcodeBuffer.trim();
                barcodeBuffer = '';
                
                if (code.toUpperCase().startsWith('I')) {
                    // Installment invoice barcode
                    let cleanCode = code.substring(1);
                    if (window.location.pathname.includes('/installments')) {
                        let searchInput = document.getElementById('instSearch');
                        if (searchInput) {
                            searchInput.value = cleanCode;
                            if (typeof filterInstallments === 'function') {
                                filterInstallments();
                                setTimeout(() => {
                                    let visibleRows = Array.from(document.querySelectorAll('.installment-row')).filter(r => r.style.display !== 'none');
                                    if (visibleRows.length === 1) {
                                        let viewBtn = visibleRows[0].querySelector('.action-btn.view');
                                        if (viewBtn && viewBtn.href) {
                                            window.location.href = viewBtn.href;
                                        }
                                    }
                                }, 100);
                            }
                        }
                    } else {
                        window.location.href = '/shop/installments?search=' + encodeURIComponent(cleanCode);
                    }
                } else {
                    // Regular sale invoice barcode
                    if (window.location.pathname.includes('/sales')) {
                        let searchInput = document.getElementById('salesSearch');
                        if (searchInput) {
                            searchInput.value = code;
                            if (typeof renderSales === 'function') {
                                renderSales(1);
                                setTimeout(() => {
                                    let tbody = document.getElementById('salesTbody');
                                    if (tbody && tbody.children.length === 1 && tbody.children[0].querySelector('.btn-invoice')) {
                                        tbody.children[0].querySelector('.btn-invoice').click();
                                    }
                                }, 500); // Wait for API fetch and render
                            }
                        }
                    } else {
                        window.location.href = '/shop/sales?search=' + encodeURIComponent(code);
                    }
                }
            }
            barcodeBuffer = '';
        }
    });
</script>
</body>
</html>
