// ============================================================
// Experiment 3 · JavaScript Form Validation & DOM Manipulation
// Plain JS - no libraries
// ============================================================

// Validation rules: return error message string or null (valid)
const rules = {
  'first-name':  v => v.trim().length >= 2 && /^[A-Za-z\s'-]+$/.test(v.trim()) ? null : 'Enter valid first name (2+ letters)',
  'last-name':   v => v.trim().length >= 2 && /^[A-Za-z\s'-]+$/.test(v.trim()) ? null : 'Enter valid last name (2+ letters)',
  'email':       v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? null : 'Enter valid email address',
  'phone':       v => /^(\+91[\s-]?)?[6-9]\d{9}$/.test(v.replace(/\s/g, '')) ? null : 'Enter valid Indian mobile (+91 XXXXX XXXXX)',
  'password':    v => v.length >= 8 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /\d/.test(v) && /[!@#$%^&*]/.test(v) 
                    ? null : 'Min 8 chars, 1 upper, 1 lower, 1 number, 1 special (!@#$%^&*)',
  'confirm-password': (v, form) => v === form.password.value ? null : 'Passwords do not match',
};

// Show/hide error message near input
function setError(input, msg) {
  const field = input.closest('.field') || input.parentElement;
  let err = field.querySelector('.error-msg');
  
  if (msg) {
    if (!err) {
      err = document.createElement('span');
      err.className = 'error-msg';
      field.appendChild(err);
    }
    err.textContent = msg;
    input.classList.add('invalid');
  } else {
    if (err) err.remove();
    input.classList.remove('invalid');
  }
}

// Validate single field
function validate(input) {
  // Checkbox validation (no ID, check by type)
  if (input.type === 'checkbox') {
    const msg = input.checked ? null : 'You must agree to continue';
    setError(input, msg);
    return input.checked;
  }
  
  const rule = rules[input.id];
  if (!rule) return true;
  
  const msg = rule(input.value, input.form);
  setError(input, msg);
  return !msg;
}

// Validate all required fields in form
function validateAll(form) {
  const inputs = form.querySelectorAll('input[required], textarea[required]');
  return [...inputs].every(validate);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.register-form');
  if (!form) return;

  // Live validation on input & blur
  form.addEventListener('input', e => {
    if (e.target.matches('input, textarea')) validate(e.target);
  });
  
  form.addEventListener('blur', e => {
    if (e.target.matches('input, textarea')) validate(e.target);
  }, true); // capture phase for blur

  // Checkbox change validation
  form.addEventListener('change', e => {
    if (e.target.type === 'checkbox') validate(e.target);
  });

  // Form submit
  form.addEventListener('submit', e => {
    e.preventDefault();
    
    if (validateAll(form)) {
      // Success - show message without reload
      const btn = form.querySelector('.btn');
      const originalHTML = btn.innerHTML;
      btn.innerHTML = 'Registration Successful ✓';
      btn.style.background = '#28a745';
      btn.disabled = true;
      
      form.reset();
      
      setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.style.background = '';
        btn.disabled = false;
      }, 3000);
    }
  });
});