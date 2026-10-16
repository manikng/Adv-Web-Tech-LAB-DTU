/**
 * Experiment 7: User Authentication using Sessions and Cookies
 * Client-side Controller & Dynamic Auth Governance
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 */

document.addEventListener('DOMContentLoaded', () => {
  // Theme Manager
  const themeToggle = document.getElementById('theme-toggle');
  const savedTheme = localStorage.getItem('lumina_auth_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeIcon(savedTheme);

  themeToggle?.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('lumina_auth_theme', next);
    updateThemeIcon(next);
  });

  function updateThemeIcon(t) {
    if (themeToggle) themeToggle.textContent = t === 'dark' ? '☀️' : '🌙';
  }

  // Toast Notification System
  const toastContainer = document.getElementById('toast-container');
  function showToast(msg, type = 'success') {
    if (!toastContainer) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
    toast.innerHTML = `<span>${icon}</span> <span>${msg}</span>`;
    toastContainer.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }

  // Tab Switching (Login vs Register)
  const tabLogin = document.getElementById('tab-login');
  const tabRegister = document.getElementById('tab-register');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');

  tabLogin?.addEventListener('click', () => {
    tabLogin.classList.add('active');
    tabRegister?.classList.remove('active');
    loginForm.style.display = 'block';
    registerForm.style.display = 'none';
  });

  tabRegister?.addEventListener('click', () => {
    tabRegister.classList.add('active');
    tabLogin?.classList.remove('active');
    registerForm.style.display = 'block';
    loginForm.style.display = 'none';
  });

  // Password Visibility Toggles
  document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (input) {
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        btn.textContent = isPass ? '👁️' : '🔒';
      }
    });
  });

  // Password Strength Meter
  const regPassword = document.getElementById('reg-password');
  const bar1 = document.getElementById('bar-1');
  const bar2 = document.getElementById('bar-2');
  const bar3 = document.getElementById('bar-3');
  const strengthText = document.getElementById('strength-text');

  regPassword?.addEventListener('input', () => {
    const val = regPassword.value;
    let score = 0;

    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

    [bar1, bar2, bar3].forEach(b => { if (b) b.className = 'strength-bar'; });

    if (val.length === 0) {
      if (strengthText) strengthText.textContent = 'Password strength';
    } else if (score === 1) {
      bar1?.classList.add('weak');
      if (strengthText) strengthText.textContent = 'Weak (add numbers & symbols)';
    } else if (score === 2) {
      bar1?.classList.add('medium');
      bar2?.classList.add('medium');
      if (strengthText) strengthText.textContent = 'Medium (add special characters)';
    } else if (score >= 3) {
      bar1?.classList.add('strong');
      bar2?.classList.add('strong');
      bar3?.classList.add('strong');
      if (strengthText) strengthText.textContent = 'Strong & Secure!';
    }
  });

  // Handle Login Submit
  loginForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('login-submit-btn');
    if (btn) btn.disabled = true;

    const payload = {
      identifier: document.getElementById('login-identifier')?.value.trim(),
      password: document.getElementById('login-password')?.value,
      remember_me: document.getElementById('login-remember')?.checked || false,
      csrf_token: document.getElementById('login-csrf')?.value,
    };

    try {
      const res = await fetch('api.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.error || 'Authentication failed.', 'error');
        if (btn) btn.disabled = false;
      }
    } catch (err) {
      showToast('Network error during login.', 'error');
      if (btn) btn.disabled = false;
    }
  });

  // Handle Registration Submit
  registerForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('register-submit-btn');
    if (btn) btn.disabled = true;

    const payload = {
      username: document.getElementById('reg-username')?.value.trim(),
      email: document.getElementById('reg-email')?.value.trim(),
      full_name: document.getElementById('reg-fullname')?.value.trim(),
      department: document.getElementById('reg-department')?.value,
      role: document.getElementById('reg-role')?.value,
      password: document.getElementById('reg-password')?.value,
      csrf_token: document.getElementById('reg-csrf')?.value,
    };

    try {
      const res = await fetch('api.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message, 'success');
        setTimeout(() => window.location.reload(), 600);
      } else {
        showToast(data.error || 'Registration failed.', 'error');
        if (btn) btn.disabled = false;
      }
    } catch (err) {
      showToast('Network error during registration.', 'error');
      if (btn) btn.disabled = false;
    }
  });

  // Handle Quick Demo Account Clicks
  document.querySelectorAll('.demo-account-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const role = btn.getAttribute('data-role');
      try {
        const res = await fetch('api.php?action=quick_demo', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ role })
        });
        const data = await res.json();
        if (data.success) {
          showToast(data.message, 'success');
          setTimeout(() => window.location.reload(), 500);
        } else {
          showToast(data.error || 'Quick login failed', 'error');
        }
      } catch (err) {
        showToast('Network error.', 'error');
      }
    });
  });

  // Handle Logout
  const logoutBtn = document.getElementById('logout-btn');
  logoutBtn?.addEventListener('click', async () => {
    try {
      const res = await fetch('api.php?action=logout', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'info');
        setTimeout(() => window.location.reload(), 500);
      }
    } catch (err) {
      showToast('Error during logout.', 'error');
    }
  });

  // Live Session Countdown Timer (for authenticated state)
  const countdownEl = document.getElementById('session-countdown');
  if (countdownEl) {
    let remaining = parseInt(countdownEl.getAttribute('data-seconds') || '1800', 10);

    const timer = setInterval(() => {
      remaining--;
      if (remaining <= 0) {
        clearInterval(timer);
        countdownEl.textContent = '00:00 (Expired)';
        showToast('Your session has expired due to inactivity. Please log in again.', 'error');
        setTimeout(() => window.location.reload(), 1500);
        return;
      }

      const mins = Math.floor(remaining / 60);
      const secs = remaining % 60;
      countdownEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }, 1000);
  }
});
