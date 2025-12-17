<?php
require_once 'backend/config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Rencontre | Premium Coffee Experience</title>

    <!-- Fonts: Playfair Display (Headers) & Poppins (Body) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="assets/Logo.png" type="image/x-icon">
    <style>
        /* --- 1. VARIABLES & RESET --- */
        :root {
            --primary: #4A301E;
            --secondary: #6F4E37;
            --gold: #D4A373;
            --cream: #FAF3E0;
            --white: #FFFFFF;
            --text-dark: #2C2C2C;
            --text-light: #666;
            --shadow: 0 10px 40px rgba(74, 48, 30, 0.1);
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cream);
            color: var(--text-dark);
            overflow-x: hidden;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234a301e' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* --- 2. GLOBAL STYLES --- */
        h1,
        h2,
        h3,
        h4 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 5%;
        }

        .section-padding {
            padding: 100px 0;
        }

        .gold-text {
            color: var(--gold);
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .text-center {
            text-align: center;
        }

        button {
            cursor: pointer;
            border: none;
            outline: none;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }

        /* Animation Classes */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: var(--transition);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- 3. NAVIGATION --- */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%;
            background: rgba(250, 243, 224, 0.95);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            gap: 40px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: var(--gold);
            transition: 0.3s;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .btn-order {
            background: var(--primary);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 5px 15px rgba(74, 48, 30, 0.3);
        }

        .btn-order:hover {
            background: var(--secondary);
            transform: translateY(-3px);
        }

        /* --- 4. VIEWS SYSTEM --- */
        .view-section {
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .view-section.active-view {
            display: block;
            opacity: 1;
        }

        /* --- 5. HOME PAGE SECTIONS --- */

        .hero {
            height: 90vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.4)), url('https://images.unsplash.com/photo-1509042239860-f550ce710b93?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
        }

        .hero h1 {
            font-size: 4.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin-bottom: 30px;
            font-style: italic;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .story-img {
            width: 100%;
            border-radius: 20px;
            box-shadow: var(--shadow);
            transform: rotate(2deg);
            transition: var(--transition);
        }

        .story-img:hover {
            transform: rotate(0deg) scale(1.02);
        }

        .signature-section {
            background: var(--primary);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .sig-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .sig-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
        }

        .sig-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-15px);
        }

        .sig-img {
            height: 200px;
            object-fit: contain;
            margin-bottom: 20px;
            filter: drop-shadow(0 10px 10px rgba(0, 0, 0, 0.5));
        }

        .review-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stars {
            color: #FFC107;
            margin: 10px 0;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .info-item i {
            width: 40px;
            height: 40px;
            background: var(--gold);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- 6. MENU PAGE --- */
        .menu-hero {
            text-align: center;
            margin-bottom: 40px;
            padding-top: 40px;
        }

        .cat-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .cat-btn {
            background: white;
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: var(--text-dark);
        }

        .cat-btn:hover,
        .cat-btn.active {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .menu-layout {
            display: grid;
            grid-template-columns: 3fr 1.2fr;
            gap: 40px;
            padding-bottom: 80px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .prod-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .prod-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(74, 48, 30, 0.15);
        }

        .prod-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .prod-info {
            padding: 20px;
        }

        .add-btn-circle {
            width: 35px;
            height: 35px;
            background: var(--gold);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            float: right;
            transition: 0.3s;
        }

        .prod-card:hover .add-btn-circle {
            background: var(--primary);
            transform: rotate(90deg);
        }

        /* Cart Sidebar */
        .cart-panel {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .cart-item {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        /* --- 7. MODALS --- */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .overlay.active {
            display: flex;
            animation: fadeIn 0.3s forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Product Detail Modal */
        .modal {
            background: white;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            position: relative;
            animation: slideUp 0.4s ease;
        }

        /* Checkout Large Modal */
        .checkout-modal {
            background: #F9F3EB;
            width: 95%;
            max-width: 1000px;
            height: 90vh;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.4s ease;
        }

        .checkout-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100%;
            overflow: hidden;
        }

        .checkout-left {
            padding: 30px;
            overflow-y: auto;
            background: white;
        }

        .checkout-right {
            padding: 30px;
            overflow-y: auto;
            background: #F9F3EB;
        }

        /* Confirmation Modal */
        .confirm-modal {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            animation: slideUp 0.4s ease;
        }

        /* Form Elements */
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
        }

        .selection-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            background: white;
            transition: 0.2s;
        }

        .selection-card.selected {
            border-color: var(--primary);
            background: rgba(74, 48, 30, 0.05);
        }

        .upload-zone {
            border: 2px dashed #ccc;
            padding: 30px;
            border-radius: 10px;
            color: #888;
            cursor: pointer;
            margin: 15px 0;
            transition: 0.2s;
        }

        .upload-zone:hover {
            border-color: var(--primary);
            background: #FAF3E0;
        }

        /* Mobile */
        @media (max-width: 900px) {

            .grid-2,
            .checkout-body,
            .menu-layout {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 3rem;
            }

            .cart-panel {
                display: none;
            }

            /* Simplified for mobile */
            .checkout-modal {
                height: 100vh;
                border-radius: 0;
            }

            .nav-links {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <nav id="navbar">
        <div class="logo" onclick="window.location.href='Login.html'">
            <img src="assets/Logo.png" width="40" alt="logo">
            Café Rencontre
        </div>
        <div class="nav-links">
            <a href="#home" class="active" onclick="switchView('home')">Home</a>
            <a href="#about" onclick="switchToHomeAndScroll('about')">About us</a>
            <a href="#reviews" onclick="switchToHomeAndScroll('reviews')">Reviews</a>
            <a href="#location" onclick="switchToHomeAndScroll('location')">Location</a>
        </div>
        <button class="btn-order" onclick="switchView('menu')">Order Now</button>
    </nav>

    <!-- === HOME VIEW === -->
    <div id="home-view" class="view-section active-view">
        <header class="hero" id="home">
            <div class="reveal active">
                <h1>Café Rencontre</h1>
                <p>"For everything God created is good, and nothing is to be rejected if it is received with
                    thanksgiving." <br>— 1 Timothy 4:4-5 NIV</p>
                <button class="btn-order" style="border: 2px solid white; background: transparent;"
                    onclick="switchView('menu')">Explore Menu</button>
            </div>
        </header>

        <!-- Story Section -->
        <section class="section-padding" id="about">
            <div class="container grid-2">
                <div class="reveal">
                    <span class="gold-text">Our Story</span>
                    <h2 style="font-size: 2.5rem; margin-bottom: 20px;">A Cup of Inspiration</h2>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 20px;">
                        It all started with a small wooden table, a rainy morning, and a single cup of coffee that
                        changed everything. Years ago, our founder sat in a quiet corner of a neighborhood café,
                        watching people come and go.
                    </p>
                    <p style="color: #666; line-height: 1.8;">
                        Inspired by that simple idea, we began our journey. We started sourcing beans from local farmers
                        who pour their heart into every harvest.
                    </p>
                </div>
                <div class="reveal">
                    <img src="assets/OurStory.jpg" class="story-img">
                </div>
            </div>
        </section>

        <!-- Signature Coffee Section -->
        <section class="section-padding signature-section">
            <div class="container text-center">
                <span class="gold-text">Special Featured</span>
                <h2 style="color: white; margin-bottom: 40px; font-style: italic;">Our Signature Coffee</h2>
                <div class="sig-grid">
                    <div class="sig-card reveal">
                        <img src="assets/Hot-Spanish-Latte.jpg" class="sig-img">
                        <h3 style="color: white;">Hot Spanish Latte</h3>
                    </div>
                    <div class="sig-card reveal" style="transition-delay: 0.1s;">
                        <img src="assets/Iced-Spanish-Latte.jpg" class="sig-img">
                        <h3 style="color: white;">Iced Spanish Latte</h3>
                    </div>
                    <div class="sig-card reveal" style="transition-delay: 0.2s;">
                        <img src="assets/Iced-Americano.jpg" class="sig-img">
                        <h3 style="color: white;">Iced Americano</h3>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reviews Section -->
        <section class="section-padding" id="reviews"
            style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
            <div class="container">
                <h2 class="text-center" style="margin-bottom: 50px;">Product Reviews</h2>
                <div class="grid-3"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                    <div class="review-card reveal">
                        <p style="font-style: italic; color: #555;">"Lorem ipsum dolor sit amet, consectetur adipiscing
                            elit. Sed do eiusmod tempor incididunt ut labore."</p>
                        <div class="stars">★★★★★</div>
                        <h4>- Sarah M.</h4>
                    </div>
                    <div class="review-card reveal">
                        <p style="font-style: italic; color: #555;">"Ut enim ad minim veniam, quis nostrud exercitation
                            ullamco laboris nisi ut aliquip ex ea commodo."</p>
                        <div class="stars">★★★★★</div>
                        <h4>- John D.</h4>
                    </div>
                    <div class="review-card reveal">
                        <p style="font-style: italic; color: #555;">"Duis aute irure dolor in reprehenderit in voluptate
                            velit esse cillum dolore eu fugiat nulla pariatur."</p>
                        <div class="stars">★★★★☆</div>
                        <h4>- Emily R.</h4>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location Section -->
        <section class="section-padding" id="location" style="background: #4A301E; color: white;">
            <div class="container grid-2">
                <div class="reveal">
                    <h2 style="color: var(--gold); margin-bottom: 30px;">Directions</h2>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>BarCIE International Center, La Consolacion University Philippines, Malolos,
                            Bulacan</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone-alt"></i>
                        <span>0998 548 7050</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>cafe.rencontre@gmail.com</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>10:00 AM - 5:00 PM / Weekdays</span>
                    </div>
                </div>
                <div class="reveal">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3856.234674987!2d120.8!3d14.8!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTTCsDQ4JzAwLjAiTiAxMjDCsDQ4JzAwLjAiRQ!5e0!3m2!1sen!2sph"
                        width="100%" height="350" style="border:0; border-radius: 20px;" allowfullscreen=""
                        loading="lazy"></iframe>
                </div>
            </div>
        </section>

        <!-- Comments -->
        <section class="section-padding">
            <div class="container text-center">
                <h2>Comments & Suggestions</h2>
                <p style="margin-bottom: 30px;">We value your feedback and would love to hear from you</p>
                <div
                    style="max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow);">
                    <input type="text" class="form-input" placeholder="Full Name">
                    <input type="email" class="form-input" placeholder="Email Address">
                    <textarea class="form-input" rows="4" placeholder="Your Message"></textarea>
                    <button class="btn-order" style="width: 100%;">Submit Feedback</button>
                </div>
            </div>
        </section>

        <footer style="background: #3E2723; color: white; padding: 30px; text-align: center;">
            <p>&copy; 2025 Café Rencontre. All rights reserved.</p>
        </footer>
    </div>

    <!-- === MENU VIEW === -->
    <div id="menu-view" class="view-section"
        style="background: url('assets/bg.jpg') center/cover no-repeat fixed; position: relative;">
        <div
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('https://www.transparenttextures.com/patterns/coffee-beans.png') repeat, rgba(74, 48, 30, 0.05); z-index: 1;">
        </div>
        <div style="position: relative; z-index: 2;">
            <div class="container">
                <div class="menu-hero">
                    <h2 style="font-size: 3rem;">Our Menu</h2>
                    <p style="color: #666; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Discover our carefully
                        crafted selection of premium beverages, made with the finest ingredients and love for coffee
                        culture.</p>
                </div>

                <div class="cat-container">
                    <button class="cat-btn active" onclick="renderMenu('brewed', this)"><i class="fas fa-mug-hot"></i>
                        Brewed Base</button>
                    <button class="cat-btn" onclick="renderMenu('milk', this)"><i class="fas fa-glass-whiskey"></i> Milk
                        Series</button>
                    <button class="cat-btn" onclick="renderMenu('soda', this)"><i class="fas fa-lemon"></i> Fruit
                        Soda</button>
                </div>

                <div class="menu-layout">
                    <!-- Product Grid -->
                    <div id="product-list" class="product-grid"></div>

                    <!-- Cart Sidebar -->
                    <div class="cart-panel">
                        <div
                            style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">
                            <h3>Your Cart</h3>
                            <span id="cart-count"
                                style="background:var(--gold); color:white; padding:2px 8px; border-radius:10px; font-size:0.8rem;">0</span>
                        </div>
                        <div id="cart-items" style="max-height: 300px; overflow-y: auto;">
                            <p style="text-align:center; color:#999; margin-top:20px;">Cart is empty</p>
                        </div>
                        <div style="margin-top:20px; padding-top:10px; border-top:1px solid #eee;">
                            <div
                                style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:15px;">
                                <span>Total</span>
                                <span id="sidebar-total">₱0.00</span>
                            </div>
                            <button class="btn-order" style="width:100%;" onclick="openCheckout()">Checkout</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- === MODALS === -->

        <!-- 1. Product Modal -->
        <div class="overlay" id="product-modal">
            <div class="modal">
                <span style="position:absolute; top:15px; right:20px; font-size:1.5rem; cursor:pointer;"
                    onclick="closeModal('product-modal')">&times;</span>
                <div style="display:flex; gap:20px; margin-bottom:20px;">
                    <img id="modal-img" src="" style="width:80px; height:80px; border-radius:10px; object-fit:cover;">
                    <div>
                        <h3 id="modal-name">Product</h3>
                        <p id="modal-price" style="color:var(--primary); font-weight:600;">₱0.00</p>
                    </div>
                </div>

                <div class="size-section" style="margin-bottom:15px;">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Size</label>
                    <div style="display:flex; gap:10px;">
                        <button class="cat-btn active" style="padding:5px 15px; font-size:0.9rem;"
                            onclick="selectSize(this, 'truth')">Truth (₱49)</button>
                        <button class="cat-btn" style="padding:5px 15px; font-size:0.9rem;"
                            onclick="selectSize(this, 'unity')">Unity (₱69)</button>
                    </div>
                </div>

                <div class="temp-section" style="margin-bottom:15px;">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Temperature</label>
                    <div style="display:flex; gap:10px;">
                        <button class="cat-btn active" style="padding:5px 15px; font-size:0.9rem;"
                            onclick="selectTemp(this, 'iced')">Iced</button>
                        <button class="cat-btn" style="padding:5px 15px; font-size:0.9rem;"
                            onclick="selectTemp(this, 'hot')">Hot</button>
                    </div>
                </div>

                <div class="addons-section" style="margin-bottom:15px;">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Add-ons</label>
                    <div style="display:flex; flex-direction:column; gap:5px;">
                        <label style="display:flex; align-items:center; gap:10px;"><input type="checkbox"
                                onchange="updateTotal()" data-price="20"> Extra Shot (+₱20)</label>
                        <label style="display:flex; align-items:center; gap:10px;"><input type="checkbox"
                                onchange="updateTotal()" data-price="15"> Caramel Drizzle (+₱15)</label>
                        <label style="display:flex; align-items:center; gap:10px;"><input type="checkbox"
                                onchange="updateTotal()" data-price="10"> Vanilla (+₱10)</label>
                        <label style="display:flex; align-items:center; gap:10px;"><input type="checkbox"
                                onchange="updateTotal()" data-price="15"> Whipped Cream (+₱15)</label>
                    </div>
                </div>

                <div class="custom-section" style="margin-bottom:15px;">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Customizations</label>
                    <textarea id="custom-text" class="form-input" rows="3"
                        placeholder="Describe your customizations (e.g., extra sugar, less ice, special ingredients, etc.)"
                        oninput="updateTotal()"></textarea>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Quantity</label>
                    <div
                        style="display:flex; align-items:center; gap:15px; border:1px solid #ddd; width:fit-content; padding:5px 15px; border-radius:20px;">
                        <button onclick="updateQty(-1)" style="background:none;">-</button>
                        <span id="modal-qty">1</span>
                        <button onclick="updateQty(1)" style="background:none;">+</button>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <button class="btn-order" style="flex:1;" onclick="addToCart()">Add to Cart (₱<span
                            id="modal-total">0.00</span>)</button>
                    <button onclick="closeModal('product-modal')"
                        style="background:#ccc; color:#333; padding:12px 20px; border-radius:30px; font-weight:600;">Cancel</button>
                </div>
            </div>
        </div>

        <!-- 2. Checkout Order Summary -->
        <div class="overlay" id="checkout-overlay">
            <div class="checkout-modal">
                <div
                    style="padding: 20px 30px; border-bottom: 1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <img src="assets/Logo.png" width="40" alt="logo">
                        <h2>Café Rencontre</h2>
                    </div>
                    <button onclick="closeModal('checkout-overlay')"
                        style="background:none; text-decoration:underline;">Back to Menu</button>
                </div>

                <div class="checkout-body">
                    <!-- Billing Left -->
                    <div class="checkout-left">
                        <h3 style="margin-bottom:20px;">Customer Information</h3>
                        <input type="text" class="form-input" placeholder="Full Name">
                        <input type="tel" class="form-input" placeholder="Phone Number">

                        <h4 style="font-size:1rem; margin-bottom:10px; margin-top:20px;">Customer Type</h4>
                        <div class="selection-card selected" onclick="selectType(this, 'student')">
                            <i class="fas fa-graduation-cap" style="color:var(--primary); font-size:1.2rem;"></i>
                            <div>
                                <strong>Student</strong>
                                <div style="font-size:0.8rem; color:green;">10% Discount Applied</div>
                            </div>
                        </div>
                        <div class="selection-card" onclick="selectType(this, 'visitor')">
                            <i class="fas fa-user" style="color:var(--primary); font-size:1.2rem;"></i>
                            <div>
                                <strong>Visitor</strong>
                                <div style="font-size:0.8rem; color:#666;">Regular Pricing</div>
                            </div>
                        </div>

                        <h4 style="font-size:1rem; margin-bottom:10px; margin-top:20px;">Payment Method</h4>
                        <div class="selection-card selected" onclick="selectPayment(this, 'cash')">
                            <i class="fas fa-money-bill-wave" style="color:var(--primary); font-size:1.2rem;"></i>
                            <div>
                                <strong>Cash</strong>
                                <div style="font-size:0.8rem; color:#666;">Pay at counter</div>
                            </div>
                        </div>
                        <div class="selection-card" onclick="openCardPaymentModal()">
                            <i class="fas fa-credit-card" style="color:var(--primary); font-size:1.2rem;"></i>
                            <div>
                                <strong>Card</strong>
                                <div style="font-size:0.8rem; color:#666;">Credit/Debit Card</div>
                            </div>
                        </div>
                        <div class="selection-card" onclick="openOnlinePaymentModal()">
                            <i class="fas fa-mobile-alt" style="color:var(--primary); font-size:1.2rem;"></i>
                            <div>
                                <strong>Online</strong>
                                <div style="font-size:0.8rem; color:#666;">Digital Wallet</div>
                            </div>
                        </div>

                        <button class="btn-order" style="width:100%; margin-top:30px;"
                            onclick="proceedToConfirm()">Complete Checkout</button>
                    </div>

                    <!-- Order Summary Right -->
                    <div class="checkout-right">
                        <h3 style="margin-bottom:20px;">Order Summary</h3>
                        <div id="checkout-list" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                            <!-- JS Injected -->
                        </div>

                        <div style="border-top:1px dotted var(--primary); padding-top:15px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                <span>Subtotal</span> <span id="co-subtotal">₱0.00</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:green;">
                                <span>Discount</span> <span id="co-discount">-₱0.00</span>
                            </div>
                            <div
                                style="display:flex; justify-content:space-between; font-weight:700; font-size:1.2rem; margin-top:10px;">
                                <span>Total</span> <span id="co-total">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Confirmation Modal -->
        <div class="overlay" id="confirm-overlay">
            <div class="confirm-modal">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="assets/Logo.png" width="60" alt="logo" style="margin-bottom: 10px;">
                    <h2 style="margin-bottom:10px;">Delivery/Pickup Confirmation</h2>
                    <p style="color:#666; font-size:0.9rem; margin-bottom:20px;">Please provide your details to confirm
                        your order.</p>
                </div>

                <div style="text-align:left;">
                    <label style="font-weight:600;">Upload ID <span style="color:red">*</span></label>
                    <div class="upload-zone" id="drop-zone">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem; margin-bottom:5px;"></i><br>
                        Click to upload
                    </div>

                    <label style="font-weight:600;">Room Number <span style="color:red">*</span></label>
                    <input type="text" class="form-input" placeholder="e.g. Room 101">

                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                        <input type="checkbox" id="terms">
                        <label for="terms" style="font-size:0.8rem;">I confirm details are correct.</label>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <button onclick="closeModal('confirm-overlay')"
                        style="background:#ccc; color:#333; padding:12px 20px; border-radius:30px; font-weight:600; flex:1;">Cancel</button>
                    <button class="btn-order" style="flex:1;" onclick="finalizeOrder()">Confirm Order</button>
                </div>
            </div>
        </div>

        <!-- 4. Card Payment Modal -->
        <div class="overlay" id="card-payment-overlay">
            <div class="modal">
                <span style="position:absolute; top:15px; right:20px; font-size:1.5rem; cursor:pointer;"
                    onclick="closeModal('card-payment-overlay')">&times;</span>
                <h3 style="margin-bottom:20px; text-align:center;">Card Payment Details</h3>

                <div style="display:flex; flex-direction:column; gap:15px;">
                    <div>
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Card Number</label>
                        <input type="text" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>

                    <div style="display:flex; gap:15px;">
                        <div style="flex:1;">
                            <label style="font-weight:600; display:block; margin-bottom:5px;">Expiry Date</label>
                            <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div style="flex:1;">
                            <label style="font-weight:600; display:block; margin-bottom:5px;">CVV</label>
                            <input type="text" class="form-input" placeholder="123" maxlength="4">
                        </div>
                    </div>

                    <div>
                        <label style="font-weight:600; display:block; margin-bottom:5px;">Cardholder Name</label>
                        <input type="text" class="form-input" placeholder="John Doe">
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; margin-top:10px;">
                        <input type="checkbox" id="save-card">
                        <label for="save-card" style="font-size:0.9rem;">Save card for future payments</label>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:30px;">
                    <button onclick="closeModal('card-payment-overlay')"
                        style="background:#ccc; color:#333; padding:12px 20px; border-radius:30px; font-weight:600; flex:1;">Cancel</button>
                    <button class="btn-order" style="flex:1;" onclick="processCardPayment()">Pay Now</button>
                </div>
            </div>
        </div>

        <!-- 5. Online Payment Modal -->
        <div class="overlay" id="online-payment-overlay">
            <div class="modal">
                <span style="position:absolute; top:15px; right:20px; font-size:1.5rem; cursor:pointer;"
                    onclick="closeModal('online-payment-overlay')">&times;</span>
                <h3 style="margin-bottom:20px; text-align:center;">Choose Digital Wallet</h3>

                <div
                    style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:15px; margin-bottom:20px;">
                    <div class="selection-card" onclick="selectOnlinePayment('gcash')"
                        style="text-align:center; padding:20px;">
                        <i class="fas fa-mobile-alt"
                            style="color:var(--primary); font-size:2rem; margin-bottom:10px;"></i>
                        <div>
                            <strong>GCash</strong>
                            <div style="font-size:0.8rem; color:#666;">Popular in Philippines</div>
                        </div>
                    </div>

                    <div class="selection-card" onclick="selectOnlinePayment('paymaya')"
                        style="text-align:center; padding:20px;">
                        <i class="fas fa-credit-card"
                            style="color:var(--primary); font-size:2rem; margin-bottom:10px;"></i>
                        <div>
                            <strong>PayMaya</strong>
                            <div style="font-size:0.8rem; color:#666;">Digital wallet</div>
                        </div>
                    </div>

                    <div class="selection-card" onclick="selectOnlinePayment('bank')"
                        style="text-align:center; padding:20px;">
                        <i class="fas fa-university"
                            style="color:var(--primary); font-size:2rem; margin-bottom:10px;"></i>
                        <div>
                            <strong>Bank Transfer</strong>
                            <div style="font-size:0.8rem; color:#666;">Direct bank transfer</div>
                        </div>
                    </div>

                    <div class="selection-card" onclick="selectOnlinePayment('paypal')"
                        style="text-align:center; padding:20px;">
                        <i class="fab fa-paypal" style="color:var(--primary); font-size:2rem; margin-bottom:10px;"></i>
                        <div>
                            <strong>PayPal</strong>
                            <div style="font-size:0.8rem; color:#666;">International payments</div>
                        </div>
                    </div>
                </div>

                <div id="online-payment-details" style="display:none;">
                    <div style="border-top:1px solid #eee; padding-top:20px;">
                        <h4 style="margin-bottom:15px;">Payment Details</h4>
                        <div id="payment-instructions"></div>
                        <input type="text" class="form-input" id="payment-reference"
                            placeholder="Enter reference number" style="margin-top:15px;">
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:30px;">
                    <button onclick="closeModal('online-payment-overlay')"
                        style="background:#ccc; color:#333; padding:12px 20px; border-radius:30px; font-weight:600; flex:1;">Cancel</button>
                    <button class="btn-order" style="flex:1;" id="confirm-online-payment"
                        onclick="processOnlinePayment()" disabled>Confirm Payment</button>
                </div>
            </div>
        </div>

        <!-- JAVASCRIPT -->
        <script>
            // Data
            const products = [{
                    id: 1,
                    name: "Spanish Latte",
                    price: 160,
                    cat: "brewed",
                    img: "https://data.thefeedfeed.com/static/2022/12/19/167148578963a0d95dd1a02.jpg"
                },
                {
                    id: 2,
                    name: "Machiato",
                    price: 150,
                    cat: "brewed",
                    img: "https://www.thespruceeats.com/thmb/HXaU0FwlEoZ6d5MoPVzGCXKx41k=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/85153452-56a176765f9b58b7d0bf84dd.jpg"
                },
                {
                    id: 3,
                    name: "Hazelnut Latte",
                    price: 170,
                    cat: "brewed",
                    img: "https://lifestyleofafoodie.com/wp-content/uploads/2024/04/Spanish-Latte-Recipe-5.jpg"
                },
                {
                    id: 4,
                    name: "Americano",
                    price: 120,
                    cat: "brewed",
                    img: "https://loveincrediblerecipes.com/wp-content/uploads/2023/12/nespresso-americano-1200x1200-1.jpg"
                },
                {
                    id: 5,
                    name: "Caramel Macchiato",
                    price: 170,
                    cat: "brewed",
                    img: "https://athome.starbucks.com/sites/default/files/styles/recipe_banner_xlarge/public/2024-05/CaramelMacchiato_RecipeHeader_848x539_%402x.jpg.webp?itok=jO6d0gba"
                },
                {
                    id: 6,
                    name: "Vanilla Latte",
                    price: 165,
                    cat: "brewed",
                    img: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcReXQigakVzOdxqfoOQsuvQ6PCm3icW--IOYg&s"
                },
                {
                    id: 7,
                    name: "Strawberry Milk",
                    price: 140,
                    cat: "milk",
                    img: "https://www.cookerru.com/wp-content/uploads/2021/01/korean-strawberry-milk-feature-main.jpg"
                },
                {
                    id: 8,
                    name: "Strawberry Hazelnut",
                    price: 155,
                    cat: "milk",
                    img: "https://theveggieyaya.com/wp-content/uploads/2024/06/iced-strawberry-latte-square-image.jpg"
                },
                {
                    id: 9,
                    name: "Choco Milk",
                    price: 145,
                    cat: "milk",
                    img: "https://www.thespruceeats.com/thmb/93UHJ043ztF5RyiPsyFJt_OVCs8=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/chocolate-milk-recipe-2355494-hero-02-80cffdb175904e03a8fd6bb7d6ffc0dd.jpg"
                },
                {
                    id: 10,
                    name: "Strawberry",
                    price: 130,
                    cat: "soda",
                    img: "https://mocktail.net/wp-content/uploads/2022/06/Homemade-Strawberry-Soda_11ig.jpg"
                },
                {
                    id: 11,
                    name: "Blueberry",
                    price: 135,
                    cat: "soda",
                    img: "https://img.freepik.com/premium-photo/blueberry-soda-plastic-cup_504796-499.jpg"
                },
                {
                    id: 12,
                    name: "Green Apple",
                    price: 135,
                    cat: "soda",
                    img: "https://www.shutterstock.com/image-photo/plastic-glass-refreshing-iced-green-260nw-1850934520.jpg"
                },
                {
                    id: 13,
                    name: "Blue Lemonade",
                    price: 140,
                    cat: "soda",
                    img: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRrabIPKt5940sVoStVoFKt7iV9QBlSWVaZLA&s"
                },
                {
                    id: 14,
                    name: "Lychee",
                    price: 140,
                    cat: "soda",
                    img: "https://img.freepik.com/free-photo/lychee-juice-lychee-fruit_1150-13685.jpg?semt=ais_hybrid&w=740&q=80"
                },
                {
                    id: 15,
                    name: "Four Season",
                    price: 145,
                    cat: "soda",
                    img: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVEX8dwT83HVWXLS5WuX7YY0fZZwuwZVZwOA&s"
                }
            ];

            let cart = [];
            let currentProduct = null;
            let qty = 1;
            let isStudent = true;
            let selectedSize = 'medium';
            let selectedTemp = 'iced';
            let addOns = [];
            let selectedPayment = 'cash';

            // Navigation
            function switchView(view) {
                document.querySelectorAll('.view-section').forEach(el => {
                    el.style.opacity = '0';
                    setTimeout(() => el.classList.remove('active-view'), 400);
                });
                setTimeout(() => {
                    const target = document.getElementById(view + '-view');
                    target.classList.add('active-view');
                    target.style.opacity = '1';
                    if (view === 'home') window.scrollTo(0, 0);
                }, 400);

                // Nav active state
                document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
                if (view === 'home') document.querySelector('.nav-links a[href="#home"]').classList.add('active');
            }

            function switchToHomeAndScroll(section) {
                switchView('home');
                setTimeout(() => {
                    const element = document.getElementById(section);
                    if (element) {
                        element.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                    // Set active nav
                    document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
                    document.querySelector(`.nav-links a[href="#${section}"]`).classList.add('active');
                }, 500);
            }

            // Scroll Animation
            window.addEventListener('scroll', () => {
                document.querySelectorAll('.reveal').forEach(el => {
                    if (el.getBoundingClientRect().top < window.innerHeight - 100) el.classList.add('active');
                });
            });

            // Menu Logic
            function renderMenu(cat, btn) {
                if (btn) {
                    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
                const grid = document.getElementById('product-list');
                grid.innerHTML = products.filter(p => p.cat === cat).map(p => {
                    let displayPrice = p.price;
                    if (p.cat === 'brewed') {
                        displayPrice = '49-69';
                    } else if (p.cat === 'milk') {
                        displayPrice = '69-79';
                    } else if (p.cat === 'soda') {
                        displayPrice = '49';
                    }

                    return `
                <div class="prod-card">
                    <img src="${p.img}" class="prod-img">
                    <div class="prod-info">
                        <h3>${p.name}</h3>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-weight:600;">₱${displayPrice}</span>
                            <div class="add-btn-circle" onclick="openProductModal(${p.id})">+</div>
                        </div>
                        <button class="btn-order" style="width:100%; padding:8px; font-size:0.8rem;" onclick="openProductModal(${p.id})">
                            <i class="fas fa-edit"></i> Customize Order
                        </button>
                    </div>
                </div>
            `
                }).join('');
            }

            function openProductModal(id) {
                currentProduct = products.find(p => p.id === id);
                qty = 1;
                selectedSize = 'medium';
                selectedTemp = 'iced';
                addOns = [];
                document.getElementById('modal-img').src = currentProduct.img;
                document.getElementById('modal-name').innerText = currentProduct.name;
                document.getElementById('modal-price').innerText = `₱${currentProduct.price}`;
                document.getElementById('modal-qty').innerText = qty;
                document.querySelectorAll('#product-modal .cat-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('#product-modal input[type="checkbox"]').forEach(cb => cb.checked = false);
                document.getElementById('custom-text').value = '';
                configureModalForCategory(currentProduct.cat);
                updateTotal();
                document.getElementById('product-modal').classList.add('active');
            }

            function configureModalForCategory(category) {
                const sizeSection = document.querySelector('#product-modal .size-section');
                const tempSection = document.querySelector('#product-modal .temp-section');
                const addonsSection = document.querySelector('#product-modal .addons-section');

                if (category === 'brewed') {
                    sizeSection.style.display = 'block';
                    tempSection.style.display = 'block';
                    addonsSection.style.display = 'block';
                    updateSizeButtonsForBrewed();
                } else if (category === 'milk') {
                    sizeSection.style.display = 'block';
                    tempSection.style.display = 'none';
                    addonsSection.style.display = 'block';
                    updateSizeButtonsForMilk();
                } else if (category === 'soda') {
                    sizeSection.style.display = 'none';
                    tempSection.style.display = 'none';
                    addonsSection.style.display = 'block';
                    document.getElementById('modal-price').innerText = '₱49';
                }
            }

            function openCustomizationModalForProduct(productId) {
                currentProduct = products.find(p => p.id === productId);
                openCustomizationModal();
            }

            function openCustomizationModal() {
                const customModal = document.createElement('div');
                customModal.innerHTML = `
                <div class="overlay active" id="custom-modal">
                    <div class="modal">
                        <span style="position:absolute; top:15px; right:20px; font-size:1.5rem; cursor:pointer;" onclick="closeModal('custom-modal')">&times;</span>
                        <h3 style="margin-bottom:20px;">Customize Your Order</h3>
                        <p style="margin-bottom:15px; color:#666; font-size:0.9rem;">Product: <strong>${currentProduct ? currentProduct.name : 'Unknown'}</strong></p>
                        <textarea id="custom-text" class="form-input" rows="4" placeholder="Describe your customizations (e.g., extra sugar, less ice, special ingredients, etc.)"></textarea>
                        <div style="display:flex; gap:10px; margin-top:20px;">
                            <button class="btn-order" style="flex:1;" onclick="applyCustomization()">Apply Customization</button>
                            <button onclick="closeModal('custom-modal')" style="background:#ccc; color:#333; padding:12px 20px; border-radius:30px; font-weight:600;">Cancel</button>
                        </div>
                    </div>
                </div>
            `;
                document.body.appendChild(customModal);
            }

            function applyCustomization() {
                const customText = document.getElementById('custom-text').value;
                if (customText.trim()) {
                    addOns.push(`Custom: ${customText}`);
                    updateTotal();
                    closeModal('custom-modal');
                    document.getElementById('custom-modal').remove();
                } else {
                    alert('Please enter your customization details.');
                }
            }

            function updateSizeButtonsForBrewed() {
                const sizeButtons = document.querySelectorAll('#product-modal .size-section .cat-btn');
                if (selectedTemp === 'hot') {
                    sizeButtons[0].innerHTML = 'Truth (₱49)';
                    sizeButtons[0].setAttribute('onclick', "selectSize(this, 'truth')");
                    sizeButtons[1].innerHTML = 'Unity (₱69)';
                    sizeButtons[1].setAttribute('onclick', "selectSize(this, 'unity')");
                } else {
                    sizeButtons[0].innerHTML = 'Unity (₱49)';
                    sizeButtons[0].setAttribute('onclick', "selectSize(this, 'unity')");
                    sizeButtons[1].innerHTML = 'Love (₱59)';
                    sizeButtons[1].setAttribute('onclick', "selectSize(this, 'love')");
                }
            }

            function updateSizeButtonsForMilk() {
                const sizeButtons = document.querySelectorAll('#product-modal .size-section .cat-btn');
                sizeButtons[0].innerHTML = 'Unity (₱69)';
                sizeButtons[0].setAttribute('onclick', "selectSize(this, 'unity')");
                sizeButtons[1].innerHTML = 'Love (₱79)';
                sizeButtons[1].setAttribute('onclick', "selectSize(this, 'love')");
            }

            function selectSize(btn, size) {
                document.querySelectorAll('#product-modal .cat-btn[onclick*="selectSize"]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedSize = size;
                updateTotal();
            }

            function selectTemp(btn, temp) {
                document.querySelectorAll('#product-modal .cat-btn[onclick*="selectTemp"]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedTemp = temp;
                if (currentProduct.cat === 'brewed') {
                    updateSizeButtonsForBrewed();
                }
                updateTotal();
            }

            function updateTotal() {
                addOns = [];
                let basePrice = currentProduct.price;
                if (currentProduct.cat === 'brewed') {
                    if (selectedTemp === 'hot') {
                        basePrice = selectedSize === 'truth' ? 49 : 69;
                    } else {
                        basePrice = selectedSize === 'unity' ? 49 : 59;
                    }
                } else if (currentProduct.cat === 'milk') {
                    basePrice = selectedSize === 'unity' ? 69 : 79;
                } else if (currentProduct.cat === 'soda') {
                    basePrice = 49;
                }

                let total = basePrice;
                document.querySelectorAll('#product-modal input[type="checkbox"]:checked').forEach(cb => {
                    let price = parseInt(cb.getAttribute('data-price'));
                    total += price;
                    addOns.push(cb.parentElement.textContent.split(' (+')[0]);
                });
                const customText = document.getElementById('custom-text').value.trim();
                if (customText) {
                    total += 20;
                    addOns.push(`Custom: ${customText}`);
                }

                total *= qty;
                document.getElementById('modal-total').innerText = total.toFixed(2);
            }

            function closeModal(id) {
                document.getElementById(id).classList.remove('active');
            }

            function updateQty(n) {
                if (qty + n > 0) {
                    qty += n;
                    document.getElementById('modal-qty').innerText = qty;
                    updateTotal();
                }
            }

            function addToCart() {
                let basePrice = currentProduct.price;
                if (currentProduct.cat === 'brewed') {
                    if (selectedTemp === 'hot') {
                        basePrice = selectedSize === 'truth' ? 49 : 69;
                    } else {
                        basePrice = selectedSize === 'unity' ? 49 : 59;
                    }
                } else if (currentProduct.cat === 'milk') {
                    basePrice = selectedSize === 'unity' ? 69 : 79;
                } else if (currentProduct.cat === 'soda') {
                    basePrice = 49;
                }

                let total = basePrice;
                document.querySelectorAll('#product-modal input[type="checkbox"]:checked').forEach(cb => {
                    total += parseInt(cb.getAttribute('data-price'));
                });
                const customText = document.getElementById('custom-text').value.trim();
                if (customText) {
                    total += 20;
                }

                total *= qty;
                cart.push({
                    ...currentProduct,
                    qty: qty,
                    size: selectedSize,
                    temp: selectedTemp,
                    addOns: [...addOns],
                    total: total
                });
                updateCart();
                closeModal('product-modal');
            }

            function updateCart() {
                const list = document.getElementById('cart-items');
                document.getElementById('cart-count').innerText = cart.length;

                if (cart.length === 0) {
                    list.innerHTML = '<p style="text-align:center; color:#999; margin-top:20px;">Cart is empty</p>';
                    document.getElementById('sidebar-total').innerText = '₱0.00';
                    return;
                }

                let grandTotal = 0;
                list.innerHTML = cart.map((item, index) => {
                    grandTotal += item.total;
                    let details = `${item.size.charAt(0).toUpperCase() + item.size.slice(1)} • ${item.temp.charAt(0).toUpperCase() + item.temp.slice(1)}`;
                    if (item.addOns.length > 0) details += ` • ${item.addOns.join(', ')}`;
                    return `
                <div class="cart-item">
                    <img src="${item.img}" style="width:50px; height:50px; border-radius:5px; object-fit:cover;">
                    <div style="flex:1;">
                        <h4 style="font-size:0.9rem;">${item.name}</h4>
                        <small>${details}</small>
                        <small>x${item.qty}</small>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:5px;">
                        <div style="font-weight:600;">₱${item.total.toFixed(2)}</div>
                        <button onclick="removeFromCart(${index})" style="background:#ff4757; color:white; border:none; border-radius:3px; padding:2px 6px; font-size:0.7rem; cursor:pointer;">Remove</button>
                    </div>
                </div>`;
                }).join('');
                document.getElementById('sidebar-total').innerText = `₱${grandTotal.toFixed(2)}`;
            }

            function removeFromCart(index) {
                cart.splice(index, 1);
                updateCart();
            }

            function openCheckout() {
                if (cart.length === 0) return alert("Your cart is empty!");
                renderCheckoutItems();
                document.getElementById('checkout-overlay').classList.add('active');
            }

            function renderCheckoutItems() {
                const list = document.getElementById('checkout-list');
                let sub = 0;
                list.innerHTML = cart.map(item => {
                    sub += item.total;
                    let sizeDisplay = item.size.charAt(0).toUpperCase() + item.size.slice(1);
                    let tempDisplay = item.temp.charAt(0).toUpperCase() + item.temp.slice(1);
                    let details = `${sizeDisplay} • ${tempDisplay}`;
                    if (item.addOns && item.addOns.length > 0) {
                        details += ` • ${item.addOns.join(', ')}`;
                    }
                    return `
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding:15px 0;">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <img src="${item.img}" style="width:60px; height:60px; border-radius:8px; object-fit:cover;">
                        <div>
                            <h4>${item.name}</h4>
                            <small>${details}</small>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div>x${item.qty}</div>
                        <div style="font-weight:600;">₱${item.total.toFixed(2)}</div>
                    </div>
                </div>`;
                }).join('');

                calculateTotals(sub);
            }

            function selectType(el, type) {
                document.querySelectorAll('.selection-card').forEach(c => c.classList.remove('selected'));
                el.classList.add('selected');
                isStudent = (type === 'student');
                let sub = cart.reduce((a, b) => a + b.total, 0);
                calculateTotals(sub);
            }

            function calculateTotals(sub) {
                document.getElementById('co-subtotal').innerText = `₱${sub}`;
                let discount = isStudent ? sub * 0.10 : 0;
                document.getElementById('co-discount').innerText = `-₱${discount.toFixed(2)}`;
                document.getElementById('co-total').innerText = `₱${(sub - discount).toFixed(2)}`;
            }

            function proceedToConfirm() {
                closeModal('checkout-overlay');
                setTimeout(() => document.getElementById('confirm-overlay').classList.add('active'), 300);
            }

            function finalizeOrder() {
                if (!document.getElementById('terms').checked) return alert("Please confirm details.");
                alert("Order Placed Successfully!");
                cart = [];
                updateCart();
                closeModal('confirm-overlay');
                switchView('home');
            }

            function openCardPaymentModal() {
                document.getElementById('card-payment-overlay').classList.add('active');
            }

            function openOnlinePaymentModal() {
                document.getElementById('online-payment-overlay').classList.add('active');
            }

            function selectOnlinePayment(method) {
                document.querySelectorAll('#online-payment-overlay .selection-card').forEach(card => {
                    card.classList.remove('selected');
                });
                event.currentTarget.classList.add('selected');

                document.getElementById('online-payment-details').style.display = 'block';

                const instructionsDiv = document.getElementById('payment-instructions');
                let instructions = '';

                switch (method) {
                    case 'gcash':
                        instructions = `
                        <p><strong>GCash Payment Instructions:</strong></p>
                        <ol style="text-align:left; margin:10px 0;">
                            <li>Open your GCash app</li>
                            <li>Go to Pay QR</li>
                            <li>Scan the QR code or enter merchant ID: <strong>1234567890</strong></li>
                            <li>Enter amount: <strong>₱${document.getElementById('co-total').innerText.replace('₱', '')}</strong></li>
                            <li>Confirm payment</li>
                        </ol>
                        <p style="color:#666; font-size:0.9rem;">After payment, enter the reference number in the field below.</p>
                    `;
                        break;
                    case 'paymaya':
                        instructions = `
                        <p><strong>PayMaya Payment Instructions:</strong></p>
                        <ol style="text-align:left; margin:10px 0;">
                            <li>Open your PayMaya app</li>
                            <li>Tap "Pay" or "Scan QR"</li>
                            <li>Scan the QR code or enter account: <strong>paymaya@cafe-rencontre.ph</strong></li>
                            <li>Enter amount: <strong>₱${document.getElementById('co-total').innerText.replace('₱', '')}</strong></li>
                            <li>Complete the transaction</li>
                        </ol>
                        <p style="color:#666; font-size:0.9rem;">Enter the transaction reference number below.</p>
                    `;
                        break;
                    case 'bank':
                        instructions = `
                        <p><strong>Bank Transfer Instructions:</strong></p>
                        <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin:10px 0;">
                            <p><strong>Bank Details:</strong></p>
                            <p>Bank: BDO Unibank</p>
                            <p>Account Name: Café Rencontre</p>
                            <p>Account Number: 1234-5678-9012</p>
                            <p>Amount: ₱${document.getElementById('co-total').innerText.replace('₱', '')}</p>
                        </div>
                        <p style="color:#666; font-size:0.9rem;">Enter the bank reference number after transfer.</p>
                    `;
                        break;
                    case 'paypal':
                        instructions = `
                        <p><strong>PayPal Payment Instructions:</strong></p>
                        <ol style="text-align:left; margin:10px 0;">
                            <li>Click the PayPal button below</li>
                            <li>Log in to your PayPal account</li>
                            <li>Review and confirm the payment</li>
                            <li>Complete the transaction</li>
                        </ol>
                        <p style="color:#666; font-size:0.9rem;">Enter the PayPal transaction ID below.</p>
                    `;
                        break;
                }

                instructionsDiv.innerHTML = instructions;
                document.getElementById('confirm-online-payment').disabled = false;
            }

            function processCardPayment() {
                const cardNumber = document.querySelector('#card-payment-overlay input[placeholder*="1234"]').value;
                const expiry = document.querySelector('#card-payment-overlay input[placeholder*="MM/YY"]').value;
                const cvv = document.querySelector('#card-payment-overlay input[placeholder*="123"]').value;
                const name = document.querySelector('#card-payment-overlay input[placeholder*="John Doe"]').value;

                if (!cardNumber || !expiry || !cvv || !name) {
                    alert('Please fill in all card details.');
                    return;
                }
                alert('Card payment processed successfully!');
                closeModal('card-payment-overlay');
                proceedToConfirm();
            }

            function processOnlinePayment() {
                const reference = document.getElementById('payment-reference').value;
                if (!reference.trim()) {
                    alert('Please enter the payment reference number.');
                    return;
                }

                alert('Payment verified successfully!');
                closeModal('online-payment-overlay');
                proceedToConfirm();
            }

            const dz = document.getElementById('drop-zone');
            dz.ondragover = (e) => {
                e.preventDefault();
                dz.style.background = '#eee';
            };
            dz.ondrop = (e) => {
                e.preventDefault();
                dz.innerHTML = '<i class="fas fa-check" style="color:green; font-size:1.5rem;"></i> Uploaded';
                dz.style.borderColor = 'green';
            };

            window.onload = () => {
                renderMenu('brewed');
                document.querySelector('.reveal').classList.add('active');
            };
        </script>
</body>

</html>