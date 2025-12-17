// const API_BASE_URL = 'http://localhost:8001/backend/api';

let editingProduct = null;
let deletingProduct = null;

document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    loadProducts();
});

function initializePage() {
    document.querySelector('.products a').classList.add('active');
    
    document.getElementById("popup").style.display = "none";
    document.getElementById("deletePopup").style.display = "none";
    
    document.getElementById("pprice").addEventListener("input", function(e) {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });
    
    document.getElementById("add-button").onclick = () => {
        editingProduct = null;
        clearFields();
        document.querySelector(".popup-content h2").textContent = "Add Product";
        document.getElementById("confirmPopup").textContent = "Confirm";
        document.getElementById("popup").style.display = "flex";
    };
    
    document.getElementById("closePopup").onclick = closePopup;
    document.getElementById("cancelPopup").onclick = closePopup;
    
    document.getElementById("closeDeletePopup").onclick = closeDeletePopup;
    document.getElementById("cancelDelete").onclick = closeDeletePopup;
    
    const searchBar = document.querySelector(".search-bar");
    if (searchBar) {
        searchBar.addEventListener("input", debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterProducts(searchTerm);
        }, 300));
    }
}

async function loadProducts() {
    try {
        showLoading(true);
        
        const response = await fetch(`${API_BASE_URL}/products.php`, {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            renderProducts(data.data || data);
        } else {
            showError('Failed to load products: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading products:', error);
        showError('Cannot connect to server. Please check your connection.');
    } finally {
        showLoading(false);
    }
}

function renderProducts(products) {
    const container = document.getElementById("productList");
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!products || products.length === 0) {
        container.innerHTML = `
            <div class="no-products">
                <p>No products found. Click "Add Product" to create one.</p>
            </div>
        `;
        return;
    }
    
    products.forEach(product => {
        addProductToDOM(product);
    });
}

function addProductToDOM(product) {
    const container = document.getElementById("productList");
    const productElement = document.createElement("div");
    productElement.className = "product-item";
    productElement.dataset.id = product.id;
    
    productElement.innerHTML = `
        <div class="product-info">
            <div class="name-category">
                <div class="name">
                    <h3>${escapeHtml(product.name)}</h3>
                </div>
                <div class="category">
                    <p>${escapeHtml(product.category)}</p>
                </div>
            </div>
            <div class="description">
                <p>
                    <span class="desc-text">${escapeHtml(product.description)}</span>
                    <img class="edit-icon" src="/Background-Image/edit icon.png" alt="Edit">
                </p>
            </div>
        </div>
        <div class="price-stock-button-delete-button">
            <div class="price-stock">
                <p>₱${parseFloat(product.base_price).toFixed(2)}</p>
            </div>
            <div class="button-group">
                <button class="delete-btn">
                    <img src="/Background-Image/delete icon.png" alt="Delete">
                </button>
                <button class="stock-toggle">
                    <img class="in-stock" src="/Background-Image/${product.is_available == 1 ? 'toggle left.jpg' : 'toggle right.jpg'}" alt="Toggle Stock">
                </button>
            </div>
        </div>
    `;
    
    const editBtn = productElement.querySelector(".edit-icon");
    const deleteBtn = productElement.querySelector(".delete-btn");
    const stockToggle = productElement.querySelector(".stock-toggle");
    
    if (editBtn) {
        editBtn.onclick = () => openEdit(productElement);
    }
    
    if (deleteBtn) {
        deleteBtn.onclick = () => openDeleteConfirmation(productElement);
    }
    
    if (stockToggle) {
        stockToggle.onclick = () => toggleProductStock(productElement);
    }
    
    container.appendChild(productElement);
}

async function saveProduct(productData, isEditing = false) {
    try {
        const url = `${API_BASE_URL}/products.php`;
        const method = isEditing ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(productData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Error saving product:', error);
        return {
            success: false,
            message: 'Network error: ' + error.message
        };
    }
}

document.getElementById("confirmPopup").onclick = async () => {
    const name = document.getElementById("pname").value.trim();
    const category = document.getElementById("pcategory").value.trim();
    const description = document.getElementById("pdesc").value.trim();
    const price = document.getElementById("pprice").value.trim();
    
    if (!name || !category || !description || !price) {
        alert("Please fill all fields");
        return;
    }
    
    if (isNaN(price) || parseFloat(price) <= 0) {
        alert("Please enter a valid price");
        return;
    }
    
    const productData = {
        name: name,
        category: category,
        description: description,
        base_price: parseFloat(price)
    };
    
    if (editingProduct) {
        const productId = editingProduct.dataset.id;
        if (!productId) {
            alert("Error: Product ID not found");
            return;
        }
        productData.id = parseInt(productId);
    }
    
    const confirmBtn = document.getElementById("confirmPopup");
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = "Saving...";
    confirmBtn.disabled = true;
    
    try {
        const result = await saveProduct(productData, editingProduct !== null);
        
        if (result.success) {
            // Refresh product list
            await loadProducts();
            closePopup();
        } else {
            alert("Error: " + result.message);
        }
    } catch (error) {
        alert("Error saving product: " + error.message);
    } finally {
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    }
};

function openEdit(productElement) {
    editingProduct = productElement;
    
    document.querySelector(".popup-content h2").textContent = "Edit Product";
    document.getElementById("confirmPopup").textContent = "Save";
    
    const name = productElement.querySelector(".name h3").textContent;
    const category = productElement.querySelector(".category p").textContent;
    const description = productElement.querySelector(".description .desc-text").textContent;
    const priceText = productElement.querySelector(".price-stock p").textContent;
    const price = priceText.replace('₱', '').trim();
    
    document.getElementById("pname").value = name;
    document.getElementById("pcategory").value = category;
    document.getElementById("pdesc").value = description;
    document.getElementById("pprice").value = price;
    
    document.getElementById("popup").style.display = "flex";
}

async function deleteProduct(productId) {
    try {
        const response = await fetch(`${API_BASE_URL}/products.php?id=${productId}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Error deleting product:', error);
        return {
            success: false,
            message: 'Network error: ' + error.message
        };
    }
}

function openDeleteConfirmation(productElement) {
    deletingProduct = productElement;
    const productName = productElement.querySelector(".name h3").textContent;
    document.getElementById("deleteProductName").textContent = productName;
    document.getElementById("deletePopup").style.display = "flex";
}

// Confirm delete
document.getElementById("confirmDelete").onclick = async () => {
    if (!deletingProduct) return;
    
    const productId = deletingProduct.dataset.id;
    if (!productId) {
        alert("Error: Product ID not found");
        closeDeletePopup();
        return;
    }
    
    const deleteBtn = document.getElementById("confirmDelete");
    const originalText = deleteBtn.textContent;
    deleteBtn.textContent = "Deleting...";
    deleteBtn.disabled = true;
    
    try {
        const result = await deleteProduct(productId);
        
        if (result.success) {
            deletingProduct.remove();
            showMessage('Product deleted successfully');
        } else {
            alert("Error: " + result.message);
        }
    } catch (error) {
        alert("Error deleting product: " + error.message);
    } finally {
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
        closeDeletePopup();
    }
};

async function toggleProductStock(productElement) {
    const productId = productElement.dataset.id;
    const stockImage = productElement.querySelector(".in-stock");
    const currentSrc = stockImage.src.toLowerCase();
    const isAvailable = currentSrc.includes("toggle left");
    
    const newAvailability = isAvailable ? 0 : 1;
    
    try {
        const response = await fetch(`${API_BASE_URL}/products.php`, {
            method: 'PUT',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: parseInt(productId),
                is_available: newAvailability
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            stockImage.src = newAvailability == 1 ? 
                "/Background-Image/toggle left.jpg" : 
                "/Background-Image/toggle right.jpg";
        } else {
            alert("Failed to update stock: " + data.message);
        }
    } catch (error) {
        console.error('Error toggling stock:', error);
        alert('Error updating stock status');
    }
}

function closePopup() {
    document.getElementById("popup").style.display = "none";
    editingProduct = null;
    clearFields();
}

function closeDeletePopup() {
    document.getElementById("deletePopup").style.display = "none";
    deletingProduct = null;
}

function clearFields() {
    document.getElementById("pname").value = "";
    document.getElementById("pcategory").value = "";
    document.getElementById("pdesc").value = "";
    document.getElementById("pprice").value = "";
}

function filterProducts(searchTerm) {
    const productItems = document.querySelectorAll(".product-item");
    
    productItems.forEach(item => {
        const productName = item.querySelector(".name h3").textContent.toLowerCase();
        const productCategory = item.querySelector(".category p").textContent.toLowerCase();
        const productDesc = item.querySelector(".desc-text").textContent.toLowerCase();
        
        const matches = productName.includes(searchTerm) || 
                       productCategory.includes(searchTerm) || 
                       productDesc.includes(searchTerm);
        
        item.style.display = matches ? "block" : "none";
    });
}

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
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showLoading(show) {
    const container = document.getElementById("productList");
    if (!container) return;
    
    if (show) {
        container.innerHTML = `
            <div class="loading">
                <p>Loading products...</p>
            </div>
        `;
    }
}

function showError(message) {
    const container = document.getElementById("productList");
    if (!container) return;
    
    container.innerHTML = `
        <div class="error">
            <p style="color: red;">${message}</p>
            <button onclick="loadProducts()">Retry</button>
        </div>
    `;
}

function showMessage(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 10000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        document.body.removeChild(notification);
    }, 3000);
}