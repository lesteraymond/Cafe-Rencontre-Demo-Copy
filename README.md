# TABLE OF CONTENTS

1. [System Overview](#1-system-overview)
2. [How the Website Works (User Flow)](#2-how-the-website-works)
3. [The Login System](#3-the-login-system)
4. [Product Management](#4-product-management)
5. [Order Management](#5-order-management)
6. [Ingredients/Inventory Management](#6-ingredients-inventory-management)
7. [Analytics Dashboard](#7-analytics-dashboard)
8. [Database Structure](#8-database-structure)
9. [Key Code Explanations](#9-key-code-explanations)
10. [Security Features](#10-security-features)

---

# 1. SYSTEM OVERVIEW

## What is this system?

Café Rencontre is a **coffee shop management website** with two main parts:

1. **Customer-Facing Website** (Index.php) - Where customers can:
   - View the café's story and information
   - Browse the menu
   - Place orders online
   - See location and contact info

2. **Admin Dashboard** - Where staff can:
   - Manage products (add, edit, delete drinks)
   - Process customer orders
   - Track ingredient inventory
   - View sales analytics and reports

## Technology Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: Runs on localhost:8001

---

# 2. HOW THE WEBSITE WORKS

## The Customer Journey

```
Customer visits Index.php
        ↓
Sees homepage with café info, story, reviews
        ↓
Clicks "Order Now" button
        ↓
Views menu with 3 categories:
   - Brewed Based (coffee drinks)
   - Milk Series (milk-based drinks)
   - Fruit Soda (refreshing sodas)
        ↓
Selects a drink → Popup appears
        ↓
Chooses options:
   - Size: Truth (₱49) or Unity (₱69)
   - Temperature: Iced or Hot
   - Add-ons: Extra shot, etc.
        ↓
Adds to cart → Proceeds to checkout
        ↓
Fills customer info → Submits order
        ↓
Order goes to admin dashboard as "Pending"
```

## The Admin Journey

```
Admin visits Login.html
        ↓
Enters username and password
        ↓
If correct → Goes to Product.html (dashboard)
        ↓
Can navigate to:
   - Products: Manage menu items
   - Ingredients: Track inventory
   - Orders: Process customer orders
   - Analytics: View reports
```

---

# 3. THE LOGIN SYSTEM

## How Login Works

The login system has 3 main files working together:

### File 1: Login.html (The Form)
This is what the admin sees - a simple form with username and password fields.

### File 2: Login.js (The Brain)
This JavaScript file handles what happens when you click "Sign In":

```javascript
// When the form is submitted, this function runs
async function handleLogin(e) {
    e.preventDefault();  // Stop the page from refreshing

    // Get what the user typed
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Check if fields are empty
    if (!username || !password) {
        showError('Please fill all fields');
        return;
    }

    // Send to server for verification
    const response = await fetch(`${API_BASE_URL}/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    const data = await response.json();

    if (data.success) {
        // Login worked! Go to dashboard
        window.location.href = 'Product.html';
    } else {
        // Login failed, show error
        showError(data.message);
    }
}
```

**In Simple Terms:**
1. User types username and password
2. JavaScript sends this info to the server
3. Server checks if it's correct
4. If yes → go to dashboard
5. If no → show error message

### File 3: backend/api/login.php (Server Check)
This PHP file checks if the username and password are correct:

```php
// Get the username and password sent from JavaScript
$username = sanitize($data['username']);
$password = $data['password'];

// Try to log in
$result = loginUser($username, $password);

if ($result['success']) {
    // Password is correct!
    jsonResponse(true, 'Login successful', $result['user']);
} else {
    // Wrong password or username
    jsonResponse(false, $result['message']);
}
```

### Staying Logged In (auth.js)

Once logged in, every page checks if you're still logged in:

```javascript
async function checkAuth() {
    // Ask server: "Am I still logged in?"
    const response = await fetch(`${API_BASE_URL}/check-auth.php`);
    const data = await response.json();
    
    if (!data.authenticated) {
        // Not logged in! Go back to login page
        window.location.href = 'Login.html';
        return false;
    }
    return true;
}
```

This runs automatically when any admin page loads. If you're not logged in, you get sent back to the login page.

---

# 4. PRODUCT MANAGEMENT

## What Products Look Like in the Database

Each product has:
- **id**: Unique number (like 1, 2, 3...)
- **name**: "Spanish Latte", "Americano", etc.
- **category**: "brewed", "milk", or "soda"
- **description**: What the drink is about
- **base_price**: How much it costs (₱49, ₱69)
- **image_url**: Picture of the drink
- **is_available**: Can customers order it? (yes/no)

## How Products are Displayed (Product.js)

When the page loads, it fetches all products from the server:

```javascript
async function loadProducts() {
    // Show "Loading..." message
    showLoading(true);
    
    // Ask server for all products
    const response = await fetch(`${API_BASE_URL}/products.php`);
    const data = await response.json();
    
    if (data.success) {
        // Got the products! Show them on screen
        renderProducts(data.data);
    } else {
        // Something went wrong
        showError('Failed to load products');
    }
}
```

## Adding a New Product

When admin clicks "Add Product" and fills the form:

```javascript
document.getElementById("confirmPopup").onclick = async () => {
    // Get all the info from the form
    const name = document.getElementById("pname").value.trim();
    const category = document.getElementById("pcategory").value.trim();
    const description = document.getElementById("pdesc").value.trim();
    const price = document.getElementById("pprice").value.trim();
    
    // Make sure nothing is empty
    if (!name || !category || !description || !price) {
        alert("Please fill all fields");
        return;
    }
    
    // Package the data
    const productData = {
        name: name,
        category: category,
        description: description,
        base_price: parseFloat(price)
    };
    
    // Send to server
    const result = await saveProduct(productData, false);
    
    if (result.success) {
        // Refresh the list to show new product
        await loadProducts();
        closePopup();
    }
};
```

## The Toggle Switch (In Stock / Out of Stock)

Each product has a toggle to mark it available or not:

```javascript
async function toggleProductStock(productElement) {
    const productId = productElement.dataset.id;
    const toggleBtn = productElement.querySelector(".stock-toggle");
    
    // Check current state
    const isAvailable = toggleBtn.classList.contains("toggle-on");
    
    // Flip it: if ON, make it OFF. If OFF, make it ON
    const newAvailability = isAvailable ? 0 : 1;
    
    // Tell server about the change
    const response = await fetch(`${API_BASE_URL}/products.php`, {
        method: 'PUT',
        body: JSON.stringify({
            id: parseInt(productId),
            is_available: newAvailability
        })
    });
    
    // Update the button appearance
    if (newAvailability == 1) {
        toggleBtn.classList.remove("toggle-off");
        toggleBtn.classList.add("toggle-on");
    } else {
        toggleBtn.classList.remove("toggle-on");
        toggleBtn.classList.add("toggle-off");
    }
}
```

---

# 5. ORDER MANAGEMENT

## Order Lifecycle

```
PENDING → APPROVED → COMPLETED
    ↓
 REJECTED (if order is cancelled)
```

## How Orders are Created (Customer Side)

When a customer places an order from Index.php:

1. They add items to cart
2. Fill in their details (name, phone, etc.)
3. Choose payment method
4. Submit order

The order data looks like this:
```javascript
{
    customer_name: "John Doe",
    customer_email: "john@email.com",
    customer_phone: "09123456789",
    total_amount: 147.00,
    payment_method: "cash",
    is_student: true,           // Gets 10% discount
    discount_amount: 14.70,
    final_amount: 132.30,
    items: [
        {
            product_id: 1,
            product_name: "Spanish Latte",
            size: "Unity",
            temperature: "Iced",
            quantity: 2,
            unit_price: 69.00,
            subtotal: 138.00
        }
    ]
}
```

## How Admin Processes Orders (Orders.js)

### Loading Orders
```javascript
async function loadOrders() {
    const response = await fetch(`${API_BASE_URL}/orders.php`);
    const data = await response.json();
    
    if (data.success) {
        // Transform server data into usable format
        orders = data.data.map(order => ({
            id: order.id,
            orderNumber: order.order_number,
            status: order.status,
            customer: order.customer_name,
            // ... more fields
        }));
        
        renderOrders();  // Show on screen
    }
}
```

### Filtering Orders by Status
```javascript
function renderOrders() {
    // Filter based on selected tab (All, Pending, Approved, etc.)
    let filteredOrders = currentFilter === "all" 
        ? orders 
        : orders.filter(order => order.status === currentFilter);
    
    // Also apply search filter if user typed something
    if (currentSearchTerm) {
        filteredOrders = filteredOrders.filter(order => 
            order.customer.toLowerCase().includes(currentSearchTerm)
        );
    }
    
    // Display each order as a card
    filteredOrders.forEach(order => {
        // Create HTML for order card...
    });
}
```

### Approving/Rejecting Orders
```javascript
async function updateOrderStatus(newStatus) {
    const response = await fetch(`${API_BASE_URL}/orders.php`, {
        method: 'PUT',
        body: JSON.stringify({
            id: currentOrderId,
            status: newStatus  // 'approved', 'rejected', or 'completed'
        })
    });
    
    if (response.success) {
        showNotification(`Order ${newStatus} successfully!`);
        loadOrders();  // Refresh the list
    }
}
```

## What Happens When Order is Completed (Server Side)

This is important! When an order is marked "completed", the server:

1. **Updates order status** to "completed"
2. **Deducts ingredients** from inventory
3. **Updates sales statistics** for analytics

```php
// When order is completed
if ($status === 'completed') {
    // Get all items in this order
    $itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    
    // For each item ordered...
    while ($item = $itemsResult->fetch_assoc()) {
        // Find what ingredients this product uses
        $ingredientsStmt = $conn->prepare("
            SELECT ingredient_id, quantity_needed 
            FROM product_ingredients 
            WHERE product_id = ?
        ");
        
        // Subtract from inventory
        while ($ingredient = $ingredientsResult->fetch_assoc()) {
            $deductAmount = $ingredient['quantity_needed'] * $quantity;
            $newQty = $previousQty - $deductAmount;
            
            // Update ingredient quantity
            $conn->query("UPDATE ingredients SET available_quantity = $newQty");
        }
    }
}
```

---

# 6. INGREDIENTS/INVENTORY MANAGEMENT

## What Ingredients Look Like

Each ingredient has:
- **name**: "Coffee Beans", "Milk", "Sugar", etc.
- **unit**: "ML", "G", "KG", "PCS"
- **bottles_count**: How many bottles/packs you have
- **available_quantity**: How much is left
- **percentage**: How much has been used (shown as progress bar)

## The Progress Bar Logic

The progress bar shows how much of an ingredient has been used:

```javascript
function calculatePercentage(bottles, available) {
    if (bottles <= 0) return 0;
    
    // Formula: 100 - (what's left / total) * 100
    // Example: If you had 1000ml and 300ml left:
    // 100 - (300/1000) * 100 = 70% used
    const percentage = 100 - (available / bottles) * 100;
    
    return Math.round(percentage);
}
```

## Color Coding
```javascript
let progressColor = '#532C04';  // Brown (normal)

if (percentage >= 80) {
    progressColor = '#ff6b6b';  // RED - Almost empty! Restock soon!
} else if (percentage >= 50) {
    progressColor = '#ffa726';  // ORANGE - Getting low
}
```

## Low Stock Alert

The system automatically warns when ingredients are running low:

```javascript
async function checkLowStock() {
    const response = await fetch(`${API_BASE_URL}/inventory.php?action=low_stock`);
    const data = await response.json();
    
    if (data.data.length > 0) {
        // Show warning banner at top of page
        showLowStockBanner(data.data);
    }
}
```

## Notification Badge

The navigation bar shows a badge when ingredients are low:

```javascript
async function updateIngredientBadge() {
    const response = await fetch(`${API_BASE_URL}/ingredients.php?count_low_stock=true`);
    const data = await response.json();
    
    const badge = document.getElementById('ingredientBadge');
    const count = data.low_stock_count;
    
    if (count > 0) {
        badge.textContent = count;  // Show number like "3"
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');  // Hide if no low stock
    }
}
```

---

# 7. ANALYTICS DASHBOARD

## What Analytics Shows

The Analytics page (Analytics.html) displays:

1. **Summary Cards**:
   - Total Orders
   - Total Revenue
   - Completed Orders
   - Average Order Value

2. **Charts**:
   - Sales Trend (line chart)
   - Top Products by Revenue (bar chart)
   - Payment Methods (pie chart)
   - Customer Types (pie chart)

3. **Tables**:
   - Daily Summary (last 7 days)
   - Peak Hours

## How Data is Loaded

```javascript
async function loadAnalytics(range) {
    // range can be: 'day', 'week', 'month', 'year'
    const response = await fetch(`${API_BASE_URL}/analytics.php?range=${range}`);
    const data = await response.json();
    
    if (data.success) {
        updateStats(data.data.stats);
        updateSalesChart(data.data.sales_trend);
        updateProductsChart(data.data.product_performance);
        updatePaymentChart(data.data.payment_distribution);
        updateCustomerChart(data.data.customer_distribution);
        updateDailySummary(data.data.daily_summary);
        updatePeakHours(data.data.peak_hours);
    }
}
```

## Creating Charts (Using Chart.js)

Example - Sales Trend Chart:

```javascript
function updateSalesChart(salesTrend) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Extract data from server response
    const labels = salesTrend.map(item => item.period);  // Dates
    const ordersData = salesTrend.map(item => item.orders);  // Order counts
    const revenueData = salesTrend.map(item => item.revenue);  // Money earned
    
    // Create the chart
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Orders',
                    data: ordersData,
                    borderColor: '#532C04',  // Brown line
                },
                {
                    label: 'Revenue (₱)',
                    data: revenueData,
                    borderColor: '#8B6F47',  // Lighter brown
                    borderDash: [5, 5],  // Dashed line
                }
            ]
        }
    });
}
```

## PDF Report Generation

Admin can download reports as PDF:

```javascript
async function generatePDFReport() {
    const reportType = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Fetch data for the report
    const response = await fetch(
        `${API_BASE_URL}/analytics.php?report=true&type=${reportType}&start=${startDate}&end=${endDate}`
    );
    
    // Use jsPDF library to create PDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Add title
    doc.text('Café Rencontre - Sales Report', 20, 20);
    
    // Add data tables
    doc.autoTable({
        head: [['Date', 'Orders', 'Revenue']],
        body: reportData.map(row => [row.date, row.orders, row.revenue])
    });
    
    // Download the PDF
    doc.save('cafe-report.pdf');
}
```

---

# 8. DATABASE STRUCTURE

## Tables Overview

The system uses 7 main tables:

### 1. users
Stores admin/staff accounts
```sql
- id: Unique identifier
- username: Login name
- password_hash: Encrypted password
- full_name: Real name
- role: 'admin' or 'staff'
```

### 2. products
Stores menu items
```sql
- id: Unique identifier
- name: Product name
- category: 'brewed', 'milk', or 'soda'
- description: Product description
- base_price: Price in pesos
- image_url: Picture URL
- is_available: true/false
```

### 3. ingredients
Stores inventory items
```sql
- id: Unique identifier
- name: Ingredient name
- unit: 'ML', 'G', 'KG', etc.
- bottles_count: Total bottles/packs
- available_quantity: Current amount left
- min_threshold: Alert when below this
```

### 4. orders
Stores customer orders
```sql
- id: Unique identifier
- order_number: Like "ORD-20251219-001"
- customer_name: Who ordered
- customer_email: Contact email
- customer_phone: Contact phone
- total_amount: Before discount
- discount_amount: Student discount
- final_amount: After discount
- status: 'pending', 'approved', 'completed', 'rejected'
- payment_method: 'cash', 'card', 'online'
```

### 5. order_items
Stores items within each order
```sql
- id: Unique identifier
- order_id: Links to orders table
- product_id: Links to products table
- product_name: Name of product
- size: 'Truth' or 'Unity'
- temperature: 'Iced' or 'Hot'
- quantity: How many
- unit_price: Price per item
- subtotal: quantity × unit_price
```

### 6. product_ingredients
Links products to their ingredients
```sql
- product_id: Which product
- ingredient_id: Which ingredient
- quantity_needed: How much per serving
```

### 7. inventory_logs
Tracks all inventory changes
```sql
- ingredient_id: Which ingredient changed
- order_id: Which order caused it (if any)
- change_type: 'deduction', 'restock', 'adjustment'
- quantity_change: How much changed
- previous_quantity: Before change
- new_quantity: After change
```

---

# 9. KEY CODE EXPLANATIONS

## The API Pattern

All pages follow this pattern to talk to the server:

```javascript
// 1. Define the server address
const API_BASE_URL = 'http://localhost:8001/backend/api';

// 2. Make a request
const response = await fetch(`${API_BASE_URL}/products.php`, {
    method: 'GET',           // or 'POST', 'PUT', 'DELETE'
    credentials: 'include',  // Send login cookies
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)  // For POST/PUT requests
});

// 3. Get the response
const result = await response.json();

// 4. Check if successful
if (result.success) {
    // Do something with result.data
} else {
    // Show error: result.message
}
```

## The Server Response Pattern

All PHP files respond in the same format:

```php
function jsonResponse($success, $message = '', $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Usage:
jsonResponse(true, 'Product created', $newProduct);
jsonResponse(false, 'Product not found');
```

## Debounce Function (For Search)

This prevents too many server requests while typing:

```javascript
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        // Clear previous timer
        clearTimeout(timeout);
        
        // Set new timer
        timeout = setTimeout(() => {
            func(...args);  // Run the function after waiting
        }, wait);
    };
}

// Usage: Only search after user stops typing for 300ms
searchBar.addEventListener("input", debounce(function(e) {
    filterProducts(e.target.value);
}, 300));
```

## Escape HTML (Security)

Prevents hackers from injecting malicious code:

```javascript
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;  // Treats as plain text, not HTML
    return div.innerHTML;
}

// Example:
// Input: "<script>alert('hacked!')</script>"
// Output: "&lt;script&gt;alert('hacked!')&lt;/script&gt;"
// This is displayed as text, not executed as code
```

---

# 10. SECURITY FEATURES

## 1. Password Hashing

Passwords are never stored as plain text:

```php
// When creating account
$hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
// Stores something like: $2y$10$abcdef123456...

// When logging in
if (password_verify($inputPassword, $storedHash)) {
    // Password is correct
}
```

## 2. Session Management

```php
// Start session on every page
session_start();

// After successful login
$_SESSION['user_id'] = $user['id'];
$_SESSION['logged_in'] = true;

// Check if logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    jsonResponse(false, 'Authentication required');
}
```

## 3. Input Sanitization

All user input is cleaned before use:

```php
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Usage
$username = sanitize($data['username']);
```

## 4. SQL Injection Prevention

Using prepared statements instead of direct queries:

```php
// BAD (vulnerable):
$sql = "SELECT * FROM users WHERE username = '$username'";

// GOOD (safe):
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

## 5. CORS Headers

Controls which websites can access the API:

```php
header("Access-Control-Allow-Origin: http://localhost:8001");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
```

---

# SUMMARY

This Café Rencontre system is a complete coffee shop management solution that:

1. **For Customers**: Provides an attractive website to browse menu and place orders
2. **For Staff**: Offers tools to manage products, process orders, and track inventory
3. **For Management**: Delivers analytics and reports for business decisions

The system uses modern web technologies with proper security measures, making it both user-friendly and secure.

**Key Technologies:**
- Frontend: HTML5, CSS3, JavaScript (ES6+)
- Backend: PHP 7+
- Database: MySQL
- Libraries: Chart.js (charts), jsPDF (PDF generation)

**Main Features:**
- User authentication with sessions
- CRUD operations for products and ingredients
- Order processing workflow
- Real-time inventory tracking
- Sales analytics and reporting
- Responsive design for different screen sizes
