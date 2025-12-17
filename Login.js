const rememberLogin = localStorage.getItem('rememberLogin');
if (rememberLogin === 'true') {
    window.location.replace('Product.html');
}

window.addEventListener('DOMContentLoaded', function () {
    const savedUsername = localStorage.getItem('savedUsername');
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

function handleLogin(e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('rememberMe').checked;

    if (username === 'admin' && password === 'admin') {
        sessionStorage.setItem('isLoggedIn', 'true');
        sessionStorage.setItem('username', username);

        if (rememberMe) {
            localStorage.setItem('rememberLogin', 'true');
            localStorage.setItem('savedUsername', username);
        } else {
            localStorage.removeItem('rememberLogin');
            localStorage.removeItem('savedUsername');
        }

        window.location.href = 'Product.html';
    } else {
        const userInput = document.querySelector('.user-input');
        const passInput = document.querySelector('.pass-input');

        userInput.querySelector('input').style.border = '2px solid #ff6b6b';
        passInput.querySelector('input').style.border = '2px solid #ff6b6b';

        let errorMsg = document.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('p');
            errorMsg.className = 'error-message';
            errorMsg.style.color = '#ff6b6b';
            errorMsg.style.fontSize = '13px';
            errorMsg.style.textAlign = 'center';
            errorMsg.style.position = 'absolute';
            errorMsg.style.left = '0';
            errorMsg.style.right = '0';
            errorMsg.style.marginTop = '5px';
            passInput.style.position = 'relative';
            passInput.appendChild(errorMsg);
        }

        errorMsg.textContent = 'Invalid username or password';
        errorMsg.style.display = 'block';
        document.getElementById('password').value = '';

        setTimeout(() => {
            userInput.querySelector('input').style.border = '1px solid #e2d2c4';
            passInput.querySelector('input').style.border = '1px solid #e2d2c4';
            errorMsg.style.display = 'none';
        }, 3000);
    }
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
            Enter your email address to reset your password
        </p>
        <input type="email" id="resetEmail"
            placeholder="your.email@example.com"
            style="width:93%;padding:12px;">
        <button id="sendResetBtn"
            style="width:100%;padding:12px;margin-top:10px;
            background:#6F4E37;color:#fff;border:none;">
            Send Reset Link
        </button>
        <p id="resetMessage"
            style="text-align:center;font-size:13px;display:none;"></p>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    modal.querySelector('.close-modal').onclick =
        () => document.body.removeChild(overlay);

    overlay.onclick = e => {
        if (e.target === overlay) document.body.removeChild(overlay);
    };

    modal.querySelector('#sendResetBtn').onclick = function () {
        const email = modal.querySelector('#resetEmail').value;
        const msg = modal.querySelector('#resetMessage');
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        msg.style.display = 'block';

        if (!email || !regex.test(email)) {
            msg.style.color = '#ff6b6b';
            msg.textContent = 'Invalid email';
            return;
        }

        msg.style.color = '#4caf50';
        msg.textContent = 'Reset link sent';

        setTimeout(() => document.body.removeChild(overlay), 2000);
    };
}
