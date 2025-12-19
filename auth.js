const API_BASE_URL = 'http://localhost:8001/backend/api';

async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE_URL}/check-auth.php`, {
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!data.authenticated) {
            window.location.href = 'Login.html';
            return false;
        }
        
        return true;
        
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'Login.html';
        return false;
    }
}

function logout() {
    fetch(`${API_BASE_URL}/logout.php`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.clear();
            sessionStorage.clear();
            window.location.href = 'Login.html';
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
        window.location.href = 'Login.html';
    });
}

window.logout = logout;

// Update order notification badge
async function updateOrderBadge() {
    try {
        const response = await fetch(`${API_BASE_URL}/orders.php?count_pending=true`, {
            credentials: 'include'
        });
        
        const data = await response.json();
        
        const badge = document.getElementById('orderBadge');
        if (badge && data.success) {
            const count = data.pending_count || 0;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error fetching order count:', error);
    }
}

// Update ingredient low stock notification badge
async function updateIngredientBadge() {
    try {
        const response = await fetch(`${API_BASE_URL}/ingredients.php?count_low_stock=true`, {
            credentials: 'include'
        });
        
        const data = await response.json();
        
        const badge = document.getElementById('ingredientBadge');
        if (badge && data.success) {
            const count = data.low_stock_count || 0;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error fetching low stock count:', error);
    }
}

window.addEventListener('DOMContentLoaded', function() {
    checkAuth();
    
    // Update badges on page load
    updateOrderBadge();
    updateIngredientBadge();
    
    // Refresh badges every 30 seconds
    setInterval(updateOrderBadge, 30000);
    setInterval(updateIngredientBadge, 30000);
    
    const backButton = document.getElementById('back-button');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }
});