// const API_BASE_URL = 'http://localhost:8001/backend/api';

let editingIngredient = null;
let deletingIngredient = null;

document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    loadIngredients();
    checkLowStock();
});

async function checkLowStock() {
    try {
        const response = await fetch(`${API_BASE_URL}/inventory.php?action=low_stock`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            showLowStockBanner(data.data);
        }
    } catch (error) {
        console.error('Error checking low stock:', error);
    }
}

function showLowStockBanner(items) {
    const existingBanner = document.getElementById('lowStockBanner');
    if (existingBanner) existingBanner.remove();
    
    const banner = document.createElement('div');
    banner.id = 'lowStockBanner';
    banner.style.cssText = `
        background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
        color: white;
        padding: 12px 20px;
        margin: 0 auto 20px;
        width: 93%;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(255, 107, 107, 0.3);
    `;
    
    const criticalCount = items.filter(i => i.status === 'CRITICAL').length;
    const lowCount = items.filter(i => i.status === 'LOW').length;
    
    banner.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 20px;">⚠️</span>
            <div>
                <strong>Low Stock Alert!</strong>
                <span style="margin-left: 10px; opacity: 0.9;">
                    ${criticalCount > 0 ? `${criticalCount} critical` : ''}
                    ${criticalCount > 0 && lowCount > 0 ? ', ' : ''}
                    ${lowCount > 0 ? `${lowCount} low` : ''}
                </span>
            </div>
        </div>
        <button onclick="this.parentElement.remove()" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
            Dismiss
        </button>
    `;
    
    const container = document.querySelector('.ingredients-management-card');
    const searchContainer = document.getElementById('search');
    if (container && searchContainer) {
        container.insertBefore(banner, searchContainer);
    }
}

function initializePage() {
    document.querySelector('.Ingredients a').classList.add('active');
    
    document.getElementById("popup").style.display = "none";
    document.getElementById("deletePopup").style.display = "none";
    
    document.getElementById("quantity").addEventListener("input", function(e) {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });
    
    document.getElementById("add-button").onclick = () => {
        editingIngredient = null;
        clearFields();
        document.querySelector(".popup-content h2").textContent = "Add Ingredients";
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
            filterIngredients(searchTerm);
        }, 300));
    }
}

async function loadIngredients() {
    try {
        showLoading(true);
        
        const response = await fetch(`${API_BASE_URL}/ingredients.php`, {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            renderIngredients(data.data || data);
        } else {
            showError('Failed to load ingredients: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading ingredients:', error);
        renderIngredients([]);
    } finally {
        showLoading(false);
    }
}

function renderIngredients(ingredients) {
    const container = document.getElementById("IngredientsList");
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!ingredients || ingredients.length === 0) {
        container.innerHTML = `
            <div class="no-ingredients">
                <p>No ingredients found. Click "Add Ingredients" to create one.</p>
            </div>
        `;
        return;
    }
    
    ingredients.forEach(ingredient => {
        addIngredientToDOM(ingredient);
    });
}

function calculatePercentage(bottles, available, capacity = 2000) {
    const totalCapacity = bottles * capacity;
    if (totalCapacity <= 0) return 0;
    const percentage = Math.min(100, (available / totalCapacity) * 100);
    return Math.round(percentage);
}

function addIngredientToDOM(ingredient) {
    const container = document.getElementById("IngredientsList");
    const ingredientElement = document.createElement("div");
    ingredientElement.className = "ingredient-item";
    ingredientElement.dataset.id = ingredient.id;
    
    const percentage = ingredient.percentage || calculatePercentage(
        ingredient.bottles_count, 
        ingredient.available_quantity, 
        ingredient.bottle_capacity || 2000
    );
    
    let progressColor = '#532C04'; // default
    if (percentage <= 20) progressColor = '#ff6b6b'; // red for low stock
    else if (percentage <= 50) progressColor = '#ffa726'; // orange for medium stock
    
    ingredientElement.innerHTML = `
        <div class="ingredient-header">
            <h3 class="ingredient-name">${escapeHtml(ingredient.name)}</h3>
            <div class="ingredient-actions">
                <button class="edit-btn-ing">
                    <img src="/Background-Image/edit icon.png" alt="Edit">
                </button>
                <button class="delete-btn-ing">
                    <img src="/Background-Image/trash icon.png" alt="Delete">
                </button>
            </div>
        </div>
        
        <div class="ingredient-info">
            <div class="bottles-info">
                <span class="label">Bottles:</span>
                <span class="bottles-count">${ingredient.bottles_count}</span>
            </div>
            <div class="available-info">
                <span class="label">Available:</span>
                <span class="available-quantity">${ingredient.available_quantity}</span>
                <span class="available-unit">${escapeHtml(ingredient.unit)}</span>
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${percentage}%; background-color: ${progressColor};"></div>
            </div>
            <span class="percentage-text">${percentage}%</span>
        </div>
    `;
    
    const editBtn = ingredientElement.querySelector(".edit-btn-ing");
    const deleteBtn = ingredientElement.querySelector(".delete-btn-ing");
    
    if (editBtn) {
        editBtn.onclick = () => openEdit(ingredientElement);
    }
    
    if (deleteBtn) {
        deleteBtn.onclick = () => openDeleteConfirmation(ingredientElement);
    }
    
    container.appendChild(ingredientElement);
}

async function saveIngredient(ingredientData, isEditing = false) {
    try {
        const url = `${API_BASE_URL}/ingredients.php`;
        const method = isEditing ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(ingredientData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Error saving ingredient:', error);
        return {
            success: false,
            message: 'Network error: ' + error.message
        };
    }
}

document.getElementById("confirmPopup").onclick = async () => {
    const name = document.getElementById("ingname").value.trim();
    const unit = document.getElementById("unit").value.trim();
    const pack = document.getElementById("pack").value.trim();
    const quantity = document.getElementById("quantity").value.trim();
    
    if (!name || !unit || !pack || !quantity) {
        alert("Please fill all fields");
        return;
    }
    
    if (isNaN(pack) || parseInt(pack) < 0) {
        alert("Please enter a valid bottle count");
        return;
    }
    
    if (isNaN(quantity) || parseFloat(quantity) < 0) {
        alert("Please enter a valid quantity");
        return;
    }
    
    const ingredientData = {
        name: name,
        unit: unit,
        bottles_count: parseInt(pack),
        available_quantity: parseFloat(quantity)
    };
    
    if (editingIngredient) {
        const ingredientId = editingIngredient.dataset.id;
        if (!ingredientId) {
            alert("Error: Ingredient ID not found");
            return;
        }
        ingredientData.id = parseInt(ingredientId);
    }
    
    const confirmBtn = document.getElementById("confirmPopup");
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = "Saving...";
    confirmBtn.disabled = true;
    
    try {
        const result = await saveIngredient(ingredientData, editingIngredient !== null);
        
        if (result.success) {
            await loadIngredients();
            closePopup();
        } else {
            alert("Error: " + result.message);
        }
    } catch (error) {
        alert("Error saving ingredient: " + error.message);
    } finally {
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    }
};

function openEdit(ingredientElement) {
    editingIngredient = ingredientElement;
    
    document.querySelector(".popup-content h2").textContent = "Edit Ingredients";
    document.getElementById("confirmPopup").textContent = "Save";
    
    const name = ingredientElement.querySelector(".ingredient-name").textContent;
    const bottles = ingredientElement.querySelector(".bottles-count").textContent;
    const quantity = ingredientElement.querySelector(".available-quantity").textContent;
    const unit = ingredientElement.querySelector(".available-unit").textContent;
    
    document.getElementById("ingname").value = name;
    document.getElementById("unit").value = unit;
    document.getElementById("pack").value = bottles;
    document.getElementById("quantity").value = quantity;
    
    document.getElementById("popup").style.display = "flex";
}

async function deleteIngredient(ingredientId) {
    try {
        const response = await fetch(`${API_BASE_URL}/ingredients.php?id=${ingredientId}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Error deleting ingredient:', error);
        return {
            success: false,
            message: 'Network error: ' + error.message
        };
    }
}

function openDeleteConfirmation(ingredientElement) {
    deletingIngredient = ingredientElement;
    document.getElementById("deletePopup").style.display = "flex";
}

document.getElementById("confirmDelete").onclick = async () => {
    if (!deletingIngredient) return;
    
    const ingredientId = deletingIngredient.dataset.id;
    if (!ingredientId) {
        alert("Error: Ingredient ID not found");
        closeDeletePopup();
        return;
    }
    
    const deleteBtn = document.getElementById("confirmDelete");
    const originalText = deleteBtn.textContent;
    deleteBtn.textContent = "Deleting...";
    deleteBtn.disabled = true;
    
    try {
        const result = await deleteIngredient(ingredientId);
        
        if (result.success) {
            deletingIngredient.remove();
            showMessage('Ingredient deleted successfully');
        } else {
            alert("Error: " + result.message);
        }
    } catch (error) {
        alert("Error deleting ingredient: " + error.message);
    } finally {
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
        closeDeletePopup();
    }
};

function closePopup() {
    document.getElementById("popup").style.display = "none";
    editingIngredient = null;
    clearFields();
}

function closeDeletePopup() {
    document.getElementById("deletePopup").style.display = "none";
    deletingIngredient = null;
}

function clearFields() {
    document.getElementById("ingname").value = "";
    document.getElementById("unit").value = "";
    document.getElementById("pack").value = "";
    document.getElementById("quantity").value = "";
}

function filterIngredients(searchTerm) {
    const ingredientItems = document.querySelectorAll(".ingredient-item");
    
    ingredientItems.forEach(item => {
        const ingredientName = item.querySelector(".ingredient-name").textContent.toLowerCase();
        const matches = ingredientName.includes(searchTerm);
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
    const container = document.getElementById("IngredientsList");
    if (!container) return;
    
    if (show) {
        container.innerHTML = `
            <div class="loading">
                <p>Loading ingredients...</p>
            </div>
        `;
    }
}

function showError(message) {
    const container = document.getElementById("IngredientsList");
    if (!container) return;
    
    container.innerHTML = `
        <div class="error">
            <p style="color: red;">${message}</p>
            <button onclick="loadIngredients()">Retry</button>
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
        padding: 10px 20px;
        border-radius: 5px;
        z-index: 10000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 2000);
}