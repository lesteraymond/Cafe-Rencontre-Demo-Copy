
const orders = [
    {
        id: "ORD-01-01-2025",
        status: "pending",
        customer: "John Doe",
        email: "JohnDoe@email.com", 
        number: "0912345678",
        date: "01/01/2025",
        mop: "Online",
        total: "₱59.00",
        itemsCount: 1,
        details: [
            { name: "Caramel Macchiato", quantity: 1, price: "₱59.00", subtotal: "₱59.00" }
        ]
    },
    {
        id: "ORD-01-02-2025",
        status: "rejected",
        customer: "John Doe",
        email: "JohnDoe@email.com",
        number: "0912345678",
        date: "01/02/2025",
        mop: "Cash",
        total: "₱79.00",
        itemsCount: 1,
        details: [
            { name: "Cafe Latte", quantity: 1, price: "₱79.00", subtotal: "₱79.00" }
        ]
    },
    {
        id: "ORD-01-03-2025",
        status: "completed",
        customer: "Jane Smith",
        email: "jane@email.com",
        number: "0912345678",
        date: "01/03/2025",
        mop: "Online",
        total: "₱109.00",
        itemsCount: 2,
        details: [
            { name: "Americano", quantity: 1, price: "₱50.00", subtotal: "₱50.00" },
            { name: "Croissant", quantity: 1, price: "₱59.00", subtotal: "₱59.00" }
        ]
    },
    {
        id: "ORD-01-04-2025", 
        status: "approved",
        customer: "Bob Wilson",
        email: "bob@email.com",
        number: "0912345678",
        date: "01/01/2025",
        mop: "Cash",
        total: "₱59.00",
        itemsCount: 1,
        details: [
             { name: "Espresso", quantity: 1, price: "₱59.00", subtotal: "₱59.00" }
        ]
    }
];

let currentFilter = "all";
let currentSearchTerm = "";

document.querySelector('.cart a').classList.add('active');

function getStatusClass(status) {
    return `status-${status}`;
}

function getStatusText(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function renderOrders() {
    const container = document.getElementById("ordersList");
    container.innerHTML = "";

    const filteredOrders = currentFilter === "all" 
        ? orders 
        : orders.filter(order => order.status === currentFilter);

    filteredOrders.forEach(order => {
        const orderCard = document.createElement("div");
        orderCard.className = "order-card";
        orderCard.innerHTML = `
            <div class="order-header">
                <span class="order-id">${order.id}</span>
                <span class="status-badge ${getStatusClass(order.status)}">
                    ${getStatusText(order.status)}
                </span>
            </div>
            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Customer:</span>
                    <span class="detail-value">${order.customer}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Number:</span>
                    <span class="detail-value">${order.number}</span>
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
                <button class="view-details-btn" onclick="viewDetails('${order.id}')">
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

    document.getElementById('modalCustomer').textContent = order.customer;
    document.getElementById('modalEmail').textContent = order.email;
    document.getElementById('modalDate').textContent = order.date;
    
    const statusEl = document.getElementById('modalStatus');
    statusEl.textContent = order.status.toUpperCase();

    statusEl.className = 'value status-text';


    const tbody = document.getElementById('modalItemsList');
    tbody.innerHTML = ''; 

    order.details.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.name}</td>
            <td class="text-center">${item.quantity}</td>
            <td class="text-right">${item.price}</td>
            <td class="text-right">${item.subtotal}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('modalTotal').textContent = order.total;

    const modal = document.getElementById('orderModal');
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('orderModal');
    modal.style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".filter-tab").forEach(tab => {
        tab.addEventListener("click", function() {
            document.querySelectorAll(".filter-tab").forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            currentFilter = this.dataset.status;
            renderOrders();
        });
    });

    document.querySelector(".search-bar").addEventListener("input", function(e) {
        currentSearchTerm = e.target.value.toLowerCase();
        renderOrders();
    });

    renderOrders();
});