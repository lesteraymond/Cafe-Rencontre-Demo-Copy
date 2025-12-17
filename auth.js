function checkAuth() {
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    const rememberLogin = localStorage.getItem('rememberLogin');

    if (!isLoggedIn && rememberLogin !== 'true') {
        window.location.href = 'Login.html';
        return false;
    }

    if (!isLoggedIn && rememberLogin === 'true') {
        sessionStorage.setItem('isLoggedIn', 'true');
        const savedUsername = localStorage.getItem('savedUsername');
        if (savedUsername) {
            sessionStorage.setItem('username', savedUsername);
        }
    }

    return true;
}

function logout() {
    sessionStorage.removeItem('isLoggedIn');
    sessionStorage.removeItem('username');

    localStorage.removeItem('rememberLogin');
    localStorage.removeItem('savedUsername');

    window.location.href = 'Login.html';
}

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
