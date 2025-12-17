const API_BASE_URL = 'http://localhost:8001/backend/api';

window.addEventListener('DOMContentLoaded', function () {
    // checkExistingSession();
    
    const savedUsername = localStorage.getItem('username');
    if (savedUsername) {
        const usernameInput = document.getElementById('username');
        const rememberCheckbox = document.getElementById('rememberMe');
        if (usernameInput) usernameInput.value = savedUsername;
        if (rememberCheckbox) rememberCheckbox.checked = true;
    }

    const forgotPassword = document.getElementById('ForgotPassword');
    if (forgotPassword) {
        forgotPassword.addEventListener('click', showForgotPasswordModal);
    }

    const loginForm = document.getElementById('LoginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const toggleBtn = document.querySelector('.toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.type = 
                    passwordInput.type === 'password' ? 'text' : 'password';
            }
        });
    }
});

async function handleLogin(e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('rememberMe').checked;

    clearErrors();

    if (!username || !password) {
        showError('Please fill all fields');
        return;
    }

    try {
        console.log('Attempting login to:', `${API_BASE_URL}/login.php`);
        
        const response = await fetch(`${API_BASE_URL}/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
            // credentials: 'include'
        });

        console.log('Response status:', response.status);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse JSON:', parseError);
            showError('Server returned invalid response');
            return;
        }
        
        console.log('Parsed data:', data);

        if (data.success) {
            if (rememberMe) {
                localStorage.setItem('username', username);
            } else {
                localStorage.removeItem('username');
            }
            
            window.location.href = 'Product.html';
        } else {
            showError(data.message || 'Login failed');
        }
    } catch (error) {
        console.error('Login error:', error);
        showError(`Cannot connect to server: ${error.message}`);
    }
}

function showError(message) {
    const userInput = document.querySelector('.user-input');
    const passInput = document.querySelector('.pass-input');

    if (userInput) userInput.querySelector('input').style.border = '2px solid #ff6b6b';
    if (passInput) passInput.querySelector('input').style.border = '2px solid #ff6b6b';

    let errorMsg = document.querySelector('.error-message');
    if (!errorMsg) {
        errorMsg = document.createElement('p');
        errorMsg.className = 'error-message';
        errorMsg.style.color = '#ff6b6b';
        errorMsg.style.fontSize = '13px';
        errorMsg.style.textAlign = 'center';
        errorMsg.style.marginTop = '10px';
        errorMsg.style.marginBottom = '10px';
        
        const form = document.querySelector('form');
        if (form) {
            form.appendChild(errorMsg);
        }
    }

    errorMsg.textContent = message;
    errorMsg.style.display = 'block';
}

function clearErrors() {
    const userInput = document.querySelector('.user-input');
    const passInput = document.querySelector('.pass-input');
    
    if (userInput) userInput.querySelector('input').style.border = '1px solid #e2d2c4';
    if (passInput) passInput.querySelector('input').style.border = '1px solid #e2d2c4';
    
    const errorMsg = document.querySelector('.error-message');
    if (errorMsg) errorMsg.style.display = 'none';
}

function showForgotPasswordModal() {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position:fixed;top:0;left:0;width:100%;height:100%;
        background:rgba(0,0,0,0.6);
        display:flex;justify-content:center;align-items:center;
        z-index:9999;
    `;

    const modal = document.createElement('div');
    modal.style.cssText = `
        background:#fff7f0;padding:35px;border-radius:20px;
        width:90%;max-width:420px;
        box-shadow:20px 20px 0 #CB6D33;
        border:2px solid #d8aa8a;
        position:relative;
    `;

    modal.innerHTML = `
        <span class="close-modal"
            style="position:absolute;top:15px;right:20px;
            font-size:28px;cursor:pointer;color:#A18072;">&times;</span>
        <h2 style="text-align:center;color:#6b3a1f;">Forgot Password?</h2>
        <p style="text-align:center;font-size:13px;color:#A18072;">
            Contact system administrator to reset your password.
        </p>
        <button onclick="closeForgotModal()"
            style="width:100%;padding:12px;margin-top:10px;
            background:#6F4E37;color:#fff;border:none;">
            OK
        </button>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    window.closeForgotModal = function() {
        document.body.removeChild(overlay);
    };

    overlay.onclick = e => {
        if (e.target === overlay) document.body.removeChild(overlay);
    };
}