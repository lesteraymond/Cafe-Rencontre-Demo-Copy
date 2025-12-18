// Orders Management - Connected to Database

let orders = [];
let currentFilter = "all";
let currentSearchTerm = "";
let currentOrderId = null;

document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('.cart a').classList.add('active');
    initializeEventListeners();
    loadOrders();
});

function initializeEventListeners() {
    // Filter tabs
    document.querySelectorAll(".filter-tab").forEach(tab => {
        tab.addEventListener("click", function() {
            document.querySelectorAll(".filter-tab").forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            currentFilter = this.dataset.status;
            renderOrders();
        });
    });

    // Search bar
    document.querySelector(".search-bar").addEventListener("input", debounce(function(e) {
        currentSearchTerm = e.target.value.toLowerCase();
        renderOrders();
    }, 300));
}

// Load orders from database
async function loadOrders() {
    try {
        showLoading(true);
        
        const response = await fetch(`${API_BASE_URL}/orders.php`, {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            orders = (data.data || []).map(order => ({
                id: order.id,
                orderNumber: order.order_number,
                status: order.status,
                customer: order.customer_name,
                email: order.customer_email || '',
                phone: order.customer_phone || '',
                roomNumber: order.room_number || '',
                date: formatDate(order.order_date),
                mop: order.payment_method ? capitalizeFirst(order.payment_method) : 'Cash',
                total: `₱${parseFloat(order.final_amount).toFixed(2)}`,
                totalAmount: parseFloat(order.total_amount),
                discountAmount: parseFloat(order.discount_amount || 0),
                finalAmount: parseFloat(order.final_amount),
                isStudent: order.is_student == 1,
                itemsCount: order.items_count || order.items?.length || 0,
                paymentProof: order.payment_proof,
                details: (order.items || []).map(item => ({
                    name: item.product_name,
                    size: item.size || '',
                    temperature: item.temperature || '',
                    quantity: item.quantity,
                    price: `₱${parseFloat(item.unit_price).toFixed(2)}`,
                    subtotal: `₱${parseFloat(item.subtotal).toFixed(2)}`
                }))
            }));
            renderOrders();
        } else {
            showError('Failed to load orders: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        showError('Cannot connect to server. Please check your connection.');
    } finally {
        showLoading(false);
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: '2-digit',
        day: '2-digit',
        year: 'numeric'
    });
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getStatusClass(status) {
    return `status-${status}`;
}

function getStatusText(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function renderOrders() {
    const container = document.getElementById("ordersList");
    container.innerHTML = "";

    let filteredOrders = currentFilter === "all" 
        ? orders 
        : orders.filter(order => order.status === currentFilter);

    // Apply search filter
    if (currentSearchTerm) {
        filteredOrders = filteredOrders.filter(order => 
            order.orderNumber?.toLowerCase().includes(currentSearchTerm) ||
            order.customer?.toLowerCase().includes(currentSearchTerm) ||
            order.email?.toLowerCase().includes(currentSearchTerm) ||
            order.phone?.toLowerCase().includes(currentSearchTerm)
        );
    }

    if (filteredOrders.length === 0) {
        container.innerHTML = `
            <div class="no-orders" style="text-align: center; padding: 40px; color: #666;">
                <p>No orders found${currentSearchTerm ? ` for "${currentSearchTerm}"` : ''}.</p>
            </div>
        `;
        return;
    }

    filteredOrders.forEach(order => {
        const orderCard = document.createElement("div");
        orderCard.className = "order-card";
        orderCard.innerHTML = `
            <div class="order-header">
                <span class="order-id">${order.orderNumber}</span>
                <span class="status-badge ${getStatusClass(order.status)}">
                    ${getStatusText(order.status)}
                </span>
            </div>
            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Customer:</span>
                    <span class="detail-value">${escapeHtml(order.customer)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">${escapeHtml(order.phone || 'N/A')}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value">${order.total}</span>
                </div>
            </div>
            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">${order.date}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">MOP:</span>
                    <span class="detail-value">${order.mop}</span>
                </div>
                <div class="detail-item"></div>
            </div>
            <div class="order-footer">
                <span class="item-count">${order.itemsCount} item(s)</span>
                <button class="view-details-btn" onclick="viewDetails(${order.id})">
                     <span><img src="/Background-Image/eye.png" style="width:16px;"></span>
                     <p style="margin:0;">View Details</p>
                </button>
            </div>
        `;
        container.appendChild(orderCard);
    });
}

function viewDetails(orderId) {
    const order = orders.find(o => o.id === orderId);
    if (!order) return;

    currentOrderId = orderId;

    document.getElementById('modalCustomer').textContent = order.customer;
    document.getElementById('modalEmail').textContent = order.email || 'N/A';
    document.getElementById('modalDate').textContent = order.date;
    
    const statusEl = document.getElementById('modalStatus');
    statusEl.textContent = order.status.toUpperCase();
    statusEl.className = 'value status-text';

    const tbody = document.getElementById('modalItemsList');
    tbody.innerHTML = ''; 

    order.details.forEach(item => {
        const tr = document.createElement('tr');
        let productDisplay = item.name;
        if (item.size) productDisplay += ` (${item.size})`;
        if (item.temperature) productDisplay += ` - ${item.temperature}`;
        
        tr.innerHTML = `
            <td>${escapeHtml(productDisplay)}</td>
            <td class="text-center">${item.quantity}</td>
            <td class="text-right">${item.price}</td>
            <td class="text-right">${item.subtotal}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('modalTotal').textContent = order.total;

    // Update payment proof display
    const paymentProofFile = document.getElementById('paymentProofFile');
    const viewProofBtn = document.getElementById('viewProofBtn');
    
    if (order.paymentProof) {
        paymentProofFile.textContent = 'File: ' + order.paymentProof;
        viewProofBtn.style.display = 'block';
        viewProofBtn.setAttribute('data-proof', order.paymentProof);
    } else {
        paymentProofFile.textContent = 'No payment proof uploaded';
        viewProofBtn.style.display = 'none';
    }

    // Update modal actions based on order status
    updateModalActions(order.status);

    const modal = document.getElementById('orderModal');
    modal.style.display = 'flex';
}

// View payment proof in new window
function viewPaymentProof() {
    const btn = document.getElementById('viewProofBtn');
    const proofFile = btn.getAttribute('data-proof');
    if (proofFile) {
        window.open('backend/uploads/' + proofFile, '_blank');
    }
}

function updateModalActions(status) {
    const actionsContainer = document.querySelector('.modal-actions');
    
    if (status === 'pending') {
        actionsContainer.innerHTML = `
            <button class="btn-reject" onclick="updateOrderStatus('rejected')">Reject</button>
            <button class="btn-approve" onclick="updateOrderStatus('approved')">Approve</button>
            <button class="btn-close" onclick="closeModal()">Close</button>
        `;
    } else if (status === 'approved') {
        actionsContainer.innerHTML = `
            <button class="btn-approve" onclick="updateOrderStatus('completed')">Mark Complete</button>
            <button class="btn-close" onclick="closeModal()">Close</button>
        `;
    } else {
        actionsContainer.innerHTML = `
            <button class="btn-close" onclick="closeModal()">Close</button>
        `;
    }
}

async function updateOrderStatus(newStatus) {
    if (!currentOrderId) return;

    try {
        const response = await fetch(`${API_BASE_URL}/orders.php`, {
            method: 'PUT',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: currentOrderId,
                status: newStatus
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(`Order ${newStatus} successfully!`, 'success');
            
            // Check for low stock warnings
            if (data.data && data.data.low_stock_warning && data.data.low_stock_warning.length > 0) {
                setTimeout(() => {
                    showLowStockWarning(data.data.low_stock_warning);
                }, 500);
            }
            
            closeModal();
            await loadOrders();
        } else {
            showNotification('Failed to update order: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error updating order:', error);
        showNotification('Error updating order status', 'error');
    }
}

function showLowStockWarning(items) {
    const warningDiv = document.createElement('div');
    warningDiv.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        z-index: 10001;
        max-width: 400px;
        text-align: center;
    `;
    warningDiv.innerHTML = `
        <div style="color: #f59e0b; font-size: 48px; margin-bottom: 15px;">⚠️</div>
        <h3 style="color: #532C04; margin-bottom: 15px;">Low Stock Warning</h3>
        <p style="color: #666; margin-bottom: 15px;">The following ingredients are running low:</p>
        <ul style="text-align: left; color: #333; margin-bottom: 20px; padding-left: 20px;">
            ${items.map(item => `<li style="margin-bottom: 5px;">${item}</li>`).join('')}
        </ul>
        <button onclick="this.parentElement.remove(); document.getElementById('lowStockOverlay').remove();" 
                style="background: #532C04; color: white; border: none; padding: 10px 30px; border-radius: 6px; cursor: pointer; font-weight: 600;">
            OK, Got it
        </button>
    `;
    
    const overlay = document.createElement('div');
    overlay.id = 'lowStockOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10000;
    `;
    
    document.body.appendChild(overlay);
    document.body.appendChild(warningDiv);
}

function closeModal() {
    const modal = document.getElementById('orderModal');
    modal.style.display = 'none';
    currentOrderId = null;
}

window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showLoading(show) {
    const container = document.getElementById("ordersList");
    if (!container) return;
    
    if (show) {
        container.innerHTML = `
            <div class="loading" style="text-align: center; padding: 40px;">
                <p>Loading orders...</p>
            </div>
        `;
    }
}

function showError(message) {
    const container = document.getElementById("ordersList");
    if (!container) return;
    
    container.innerHTML = `
        <div class="error" style="text-align: center; padding: 40px;">
            <p style="color: red;">${message}</p>
            <button onclick="loadOrders()" style="margin-top: 10px; padding: 10px 20px; cursor: pointer;">Retry</button>
        </div>
    `;
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 10000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
