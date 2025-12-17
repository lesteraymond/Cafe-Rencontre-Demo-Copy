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

window.addEventListener('DOMContentLoaded', function() {
    checkAuth();
    
    const backButton = document.getElementById('back-button');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }
});