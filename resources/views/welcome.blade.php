<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MobiPOS - The Ultimate POS & Inventory for Mobile Shops</title>
    <meta name="description" content="Buy, Sell, Repair, Exchange and Manage Mobile Phones, Tablets, Laptops & Accessories with One Powerful POS System.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('assets/logo/main-logo.png') }}" alt="MobiPOS Logo" style="height: 40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#modules">Modules</a></li>
                    {{-- <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li> --}}
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                </ul>
                <div class="d-flex ms-3">
                    <a href="{{ route('login') }}" class="btn btn-outline-premium me-2">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-premium">Start Free Trial</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="hero">
        <div id="hero-canvas"></div>
        <div class="glow-circle glow-1"></div>
        <div class="glow-circle glow-2"></div>
        
        <div class="container hero-content">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-7" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">
                        Manage Your Mobile Business <br>
                        <span class="text-gradient">Smarter Than Ever.</span>
                    </h1>
                    <p class="lead mb-5 text-secondary w-100 w-lg-75">
                        Buy, Sell, Repair, Exchange and Manage Mobile Phones, Tablets, Laptops & Accessories with One Powerful POS System.
                    </p>
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <a href="{{ route('register') }}" class="btn btn-premium btn-lg">Start Free Trial</a>
                        <button class="btn btn-outline-premium btn-lg"><i class="fas fa-play-circle me-2"></i>Watch Demo</button>
                    </div>
                    <div class="d-flex align-items-center text-secondary">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <span>Trusted by 5000+ Mobile Shops Worldwide</span>
                    </div>
                </div>
                <!-- Right side is empty to allow 3D canvas to be visible -->
                <div class="col-lg-5"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-gradient fw-bold">POWERFUL FEATURES</h6>
                <h2 class="display-5 fw-bold">Everything you need to run your store</h2>
            </div>
            <div class="row g-4">
                <!-- Feature Cards -->
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="glass-card p-4 h-100">
                        <div class="feature-icon"><i class="fas fa-cash-register"></i></div>
                        <h4>POS Billing</h4>
                        <p class="text-secondary">Fast and intuitive point of sale interface with barcode scanner integration.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="glass-card p-4 h-100">
                        <div class="feature-icon"><i class="fas fa-boxes"></i></div>
                        <h4>Inventory Management</h4>
                        <p class="text-secondary">Track every phone, tablet, and accessory across multiple warehouses.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="glass-card p-4 h-100">
                        <div class="feature-icon"><i class="fas fa-barcode"></i></div>
                        <h4>IMEI Tracking</h4>
                        <p class="text-secondary">Track individual devices by IMEI/Serial number from purchase to sale.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="glass-card p-4 h-100">
                        <div class="feature-icon"><i class="fas fa-tools"></i></div>
                        <h4>Repair Management</h4>
                        <p class="text-secondary">Manage repair jobs, spare parts, statuses, and send SMS updates to customers.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="glass-card p-4 h-100">
                        <div class="feature-icon"><i class="fas fa-exchange-alt"></i></div>
                        <h4>Trade-In & Exchange</h4>
                        <p class="text-secondary">Seamlessly buy old phones, offer trade-in discounts, and manage refurbished stock.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="glass-card p-4 h-100">
                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                        <h4>Analytics & Reports</h4>
                        <p class="text-secondary">Live profit analytics, sales reports, and low stock notifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3D Showcase Section -->
    <section id="showcase">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                    <h6 class="text-gradient fw-bold">IMMERSIVE EXPERIENCE</h6>
                    <h2 class="display-4 fw-bold mb-4">Step into the Future of Retail</h2>
                    <p class="lead text-secondary mb-4">Our cloud-based architecture ensures your business is always online. Manage your physical store and digital inventory simultaneously from anywhere in the world.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle text-accent me-2"></i> Cloud Backup & Security</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-accent me-2"></i> Multi-Store Management</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-accent me-2"></i> Employee Role Permissions</li>
                    </ul>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div id="showcase-canvas" class="glass-card"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modules Section -->
    <section id="modules" class="bg-opacity-50" style="background: rgba(255,255,255,0.01);">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-gradient fw-bold">COMPREHENSIVE</h6>
                <h2 class="display-5 fw-bold">Modules Built for You</h2>
            </div>
            <div class="row g-3">
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-mobile-alt fa-2x mb-2 text-primary"></i>
                        <h5>New Phones</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-recycle fa-2x mb-2 text-primary"></i>
                        <h5>Used Phones</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-tablet-alt fa-2x mb-2 text-primary"></i>
                        <h5>Tablets</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-laptop fa-2x mb-2 text-primary"></i>
                        <h5>Laptops</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="500">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-headphones fa-2x mb-2 text-secondary"></i>
                        <h5>Accessories</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="600">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-wrench fa-2x mb-2 text-secondary"></i>
                        <h5>Repairs</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="700">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-money-bill-wave fa-2x mb-2 text-secondary"></i>
                        <h5>Finance</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="zoom-in" data-aos-delay="800">
                    <div class="glass-card p-3 text-center">
                        <i class="fas fa-users fa-2x mb-2 text-secondary"></i>
                        <h5>Customers</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Timeline -->
    <section id="why-choose">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-gradient fw-bold">WORKFLOW</h6>
                <h2 class="display-5 fw-bold">Why Choose MobiPOS?</h2>
            </div>
            <div class="timeline">
                <div class="timeline-item" data-aos="fade-up">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content glass-card p-4">
                        <h4 class="text-primary">Fast Billing</h4>
                        <p class="text-secondary mb-0">Complete sales in seconds with our optimized POS screen and barcode scanner integration.</p>
                    </div>
                </div>
                <div class="timeline-item" data-aos="fade-up">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content glass-card p-4">
                        <h4 class="text-primary">Stock Alerts</h4>
                        <p class="text-secondary mb-0">Get automated notifications when inventory is running low for high-demand products.</p>
                    </div>
                </div>
                <div class="timeline-item" data-aos="fade-up">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content glass-card p-4">
                        <h4 class="text-primary">Easy Returns & Exchanges</h4>
                        <p class="text-secondary mb-0">Process returns effortlessly and calculate exchange differences automatically.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="border-top border-bottom border-secondary" style="background: rgba(79, 70, 229, 0.05);">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number text-gradient counter-val" data-target="10000+">0</div>
                        <div class="text-secondary fw-bold text-uppercase">Products Managed</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number text-gradient counter-val" data-target="2500+">0</div>
                        <div class="text-secondary fw-bold text-uppercase">Daily Sales</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number text-gradient counter-val" data-target="99.99%">0</div>
                        <div class="text-secondary fw-bold text-uppercase">Uptime</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box">
                        <div class="stat-number text-gradient">24/7</div>
                        <div class="text-secondary fw-bold text-uppercase">Support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Screenshots Mockup Section -->
    <section id="screenshots" class="overflow-hidden">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-gradient fw-bold">INTERFACE</h6>
                <h2 class="display-5 fw-bold">Beautiful Dark Mode UI</h2>
            </div>
            <div class="mockup-container">
                <!-- Placeholders using unspash random tech images to simulate UI screens -->
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80" alt="Dashboard Left" class="mockup-img mockup-left">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80" alt="Dashboard Main" class="mockup-img mockup-main">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80" alt="Dashboard Right" class="mockup-img mockup-right">
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-gradient fw-bold">REVIEWS</h6>
                <h2 class="display-5 fw-bold">Trusted by Store Owners</h2>
            </div>
            <!-- Swiper -->
            <div class="swiper testimonials-swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="glass-card testimonial-card">
                            <img src="https://i.pravatar.cc/150?img=11" alt="Avatar" class="testimonial-avatar">
                            <div class="stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <p class="text-secondary">"MobiPOS changed the way we handle our laptop and mobile inventory. The IMEI tracking is flawless!"</p>
                            <h5 class="mb-0">David Miller</h5>
                            <small class="text-primary">Tech Hub Electronics</small>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="glass-card testimonial-card">
                            <img src="https://i.pravatar.cc/150?img=47" alt="Avatar" class="testimonial-avatar">
                            <div class="stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <p class="text-secondary">"The repair management module is a game changer. Our customers love the automated SMS updates."</p>
                            <h5 class="mb-0">Sarah Jenkins</h5>
                            <small class="text-primary">QuickFix Mobiles</small>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="glass-card testimonial-card">
                            <img src="https://i.pravatar.cc/150?img=33" alt="Avatar" class="testimonial-avatar">
                            <div class="stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                            <p class="text-secondary">"Managing 3 branches used to be a headache. Now with the multi-store cloud sync, it's a breeze."</p>
                            <h5 class="mb-0">Michael Chen</h5>
                            <small class="text-primary">Gadget Galaxy</small>
                        </div>
                    </div>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination mt-4"></div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
   {{--  <section id="pricing">

        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-gradient fw-bold">PRICING</h6>
                <h2 class="display-5 fw-bold">Simple, Transparent Plans</h2>
            </div>
            <div class="row g-4 align-items-center">
                <!-- Starter -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="glass-card pricing-card text-center">
                        <h3>Starter</h3>
                        <div class="price">$29<span>/mo</span></div>
                        <p class="text-secondary mb-4">Perfect for single store startups.</p>
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> POS Billing</li>
                            <li><i class="fas fa-check"></i> 1 Store Location</li>
                            <li><i class="fas fa-check"></i> Unlimited Products</li>
                            <li><i class="fas fa-check"></i> Basic Reporting</li>
                        </ul>
                        <button class="btn btn-outline-premium w-100 mt-4">Get Started</button>
                    </div>
                </div>
                <!-- Business -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="glass-card pricing-card popular text-center">
                        <h3 class="text-primary">Business</h3>
                        <div class="price">$79<span>/mo</span></div>
                        <p class="text-secondary mb-4">Best for growing mobile shops.</p>
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> Everything in Starter</li>
                            <li><i class="fas fa-check"></i> Up to 3 Stores</li>
                            <li><i class="fas fa-check"></i> Repair Management</li>
                            <li><i class="fas fa-check"></i> IMEI Tracking</li>
                            <li><i class="fas fa-check"></i> Advanced Analytics</li>
                        </ul>
                        <button class="btn btn-premium w-100 mt-4">Start Free Trial</button>
                    </div>
                </div>
                <!-- Enterprise -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="glass-card pricing-card text-center">
                        <h3>Enterprise</h3>
                        <div class="price">$199<span>/mo</span></div>
                        <p class="text-secondary mb-4">For large retail chains.</p>
                        <ul class="pricing-features">
                            <li><i class="fas fa-check"></i> Unlimited Stores</li>
                            <li><i class="fas fa-check"></i> Custom Roles</li>
                            <li><i class="fas fa-check"></i> API Access</li>
                            <li><i class="fas fa-check"></i> Dedicated Support</li>
                        </ul>
                        <button class="btn btn-outline-premium w-100 mt-4">Contact Sales</button>
                    </div>
                </div>
            </div>
        </div>
    </section>--}}

    <!-- FAQ -->
    <section id="faq" style="background: rgba(255,255,255,0.01);">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-gradient fw-bold">SUPPORT</h6>
                <h2 class="display-5 fw-bold">Frequently Asked Questions</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item bg-transparent border-secondary mb-3 glass-card">
                            <h2 class="accordion-header">
                                <button class="accordion-button bg-transparent text-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Can I track products by IMEI?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Yes! MobiPOS is specifically designed for mobile shops. You can track every single device by its unique IMEI or serial number from purchase to sale.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item bg-transparent border-secondary mb-3 glass-card">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent text-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Does it support barcode scanners and receipt printers?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Absolutely. MobiPOS is compatible with standard USB/Bluetooth barcode scanners and thermal receipt printers right out of the box.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item bg-transparent border-secondary mb-3 glass-card">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent text-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Can I manage multiple store locations?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    Yes, our Business and Enterprise plans allow you to manage multiple branches, transfer stock between warehouses, and view consolidated reports.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section text-center">
        <div class="container" data-aos="zoom-in">
            <div class="glass-card p-5" style="background: linear-gradient(135deg, rgba(0, 229, 255, 0.1), rgba(79, 70, 229, 0.1)); border-color: var(--primary-color);">
                <h2 class="display-5 fw-bold mb-4">Ready to Grow Your Mobile Business?</h2>
                <p class="lead text-secondary mb-5">Start Using MobiPOS Today and experience the difference.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button class="btn btn-premium btn-lg px-5">Get Started</button>
                    <button class="btn btn-outline-premium btn-lg px-5">Request Demo</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <h4 class="text-primary mb-4 d-flex align-items-center">
                        <img src="{{ asset('assets/logo/main-logo.png') }}" alt="MobiPOS Logo" style="height: 40px;">
                    </h4>
                    <p class="text-secondary">The ultimate Point of Sale and Inventory Management system for modern mobile, laptop, and electronics stores.</p>
                    <div class="social-icons mt-4">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="mb-4">Quick Links</h5>
                    <a href="#features" class="footer-link">Features</a>
                    {{-- <a href="#pricing" class="footer-link">Pricing</a> --}}
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Contact</a>
                </div>
                <div class="col-lg-2 col-6">
                    <h5 class="mb-4">Products</h5>
                    <a href="#" class="footer-link">POS Billing</a>
                    <a href="#" class="footer-link">Repair Module</a>
                    <a href="#" class="footer-link">Inventory Sync</a>
                    <a href="#" class="footer-link">Mobile App</a>
                </div>
                <div class="col-lg-4">
                    <h5 class="mb-4">Subscribe to Newsletter</h5>
                    <div class="input-group mb-3 glass-card" style="padding: 5px; border-radius: 50px;">
                        <input type="email" class="form-control bg-transparent border-0 text-white shadow-none" placeholder="Enter your email">
                        <button class="btn btn-premium" style="border-radius: 50px;" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <div class="row pt-4 border-top border-secondary text-center">
                <div class="col-12 text-secondary">
                    <p>&copy; 2026 MobiPOS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Three.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- AOS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="{{ asset('js/three-scene.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
