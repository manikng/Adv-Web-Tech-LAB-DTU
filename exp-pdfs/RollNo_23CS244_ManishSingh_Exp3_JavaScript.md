# Experiment 3 — JavaScript Form Validation and DOM Manipulation

**Student Name:** Manish Singh  
**Roll No:** 23/CS/244  
**Course:** Advanced Web Technology  
**Semester:** 7th Semester  
**Date:** September 28, 2026

---

## Aim

To implement client-side form validation using plain JavaScript for a Student Registration Form, with live validation, error messaging, DOM manipulation, and success feedback — without any external libraries or page reloads.

---

## Objective

1. Link an external JavaScript file (`script.js`) with the HTML page from Experiments 1 & 2
2. Perform client-side validation for all form fields using plain JavaScript
3. Validate: required fields, name format, email format, mobile number format, password requirements, and confirm password matching
4. Implement live validation using `input`, `blur`, `change`, and `submit` events
5. Display clear error messages near respective input fields
6. Automatically remove error messages when user corrects input
7. Prevent form submission if any validation error exists
8. Display success message on successful validation without page reload
9. Use JavaScript DOM methods to access elements, display errors, change content, add/remove CSS classes, and show success message
10. Maintain responsive design from Experiment 2

---

## Procedure / Algorithm

### Step 1: Prepare Project Structure
- Copy `index.html` and `style.css` from Experiment 2 to `experiments/exp3/`
- Create new `script.js` in `experiments/exp3/`
- Update `index.html` to link `script.js` instead of inline script

### Step 2: Define Validation Rules
Create validation functions for each field type:
- **First Name / Last Name**: Required, 2+ characters, letters/spaces/hyphens/apostrophes only
- **Email**: Required, valid email format (user@domain.tld)
- **Phone**: Required, Indian mobile format (+91 XXXXX XXXXX or 10 digits starting with 6-9)
- **Password**: Required, minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 digit, 1 special character (!@#$%^&*)
- **Confirm Password**: Required, must match password field exactly
- **Checkbox**: Required, must be checked

### Step 3: Implement DOM Error Display
- Create `setError(input, message)` function to:
  - Find or create `.error-msg` span element near input
  - Display error message and add `.invalid` class to input
  - Remove error message and `.invalid` class when valid

### Step 4: Implement Validation Logic
- Create `validate(input)` function to:
  - Handle checkbox separately (check `checked` property)
  - Look up validation rule by input ID
  - Call rule with input value and form reference
  - Display error or clear via `setError()`
  - Return boolean validity

- Create `validateAll(form)` function to:
  - Select all required inputs and textareas
  - Validate each and return true only if all pass

### Step 5: Attach Event Listeners
On `DOMContentLoaded`:
- **`input` event**: Live validation as user types
- **`blur` event**: Validate when field loses focus (capture phase)
- **`change` event**: Validate checkbox on change
- **`submit` event**: 
  - Prevent default submission
  - Run `validateAll()`
  - If valid: show success message, reset form, restore button after 3 seconds
  - If invalid: let inline errors block submission

### Step 6: Add Validation Styles to CSS
Add to `style.css`:
- `.invalid` class: red border, red box-shadow
- `.error-msg` class: red text, small font, fade-in animation

### Step 7: Test All Scenarios
- Empty form submission → all errors shown
- Invalid data in each field → live errors appear
- Correct each field → errors disappear instantly
- All valid → success message, form reset, button restores after 3s
---

## HTML Code Used (Key Parts)

```html
<!-- index.html - Form section (from Experiment 2) -->
<form class="register-form" novalidate>
  <div class="form-grid">
    <div class="field">
      <label for="first-name">First Name <span class="required">*</span></label>
      <input type="text" id="first-name" name="first-name" placeholder="John" required>
    </div>
    <div class="field">
      <label for="last-name">Last Name <span class="required">*</span></label>
      <input type="text" id="last-name" name="last-name" placeholder="Doe" required>
    </div>
    <div class="field">
      <label for="email">Email Address <span class="required">*</span></label>
      <input type="email" id="email" name="email" placeholder="john.doe@example.com" required>
    </div>
    <div class="field">
      <label for="phone">Phone Number <span class="required">*</span></label>
      <input type="tel" id="phone" name="phone" placeholder="+91 98765 43210" required>
    </div>
    <div class="field">
      <label for="password">Password <span class="required">*</span></label>
      <input type="password" id="password" name="password" placeholder="Min 8 chars, 1 upper, 1 lower, 1 num, 1 special" required>
    </div>
    <div class="field">
      <label for="confirm-password">Confirm Password <span class="required">*</span></label>
      <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-enter password" required>
    </div>
    <div class="field full">
      <label for="message">Why CSE at DTU? (optional)</label>
      <textarea id="message" name="message" placeholder="Tell us about your interests..."></textarea>
    </div>
    <div class="field full">
      <label class="checkbox">
        <input type="checkbox" required>
        <span>I confirm the information provided is accurate...</span>
      </label>
    </div>
    <button type="submit" class="btn">Submit Registration <i class="fas fa-arrow-right"></i></button>
  </div>
</form>

<!-- Link external JS -->
<script src="script.js"></script>
```

---

## CSS Code Used (Validation Styles Added to style.css)

```css
/* Validation States (Experiment 3) */
.input.invalid,
.field input.invalid,
.field textarea.invalid,
.field select.invalid {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, .15);
}

.error-msg {
  color: #dc3545;
  font-size: 0.8rem;
  margin-top: 4px;
  display: block;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
```
---

## JavaScript Code Used (script.js)

```javascript
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
```

---

## Screenshots

### 1. Validation Errors for Different Invalid Inputs
![Validation Errors](screenshots/exp3-validation-errors.png)
*Shows error messages for empty fields, invalid email, invalid phone, weak password, and password mismatch*

### 2. Successful Validation
![Successful Validation](screenshots/exp3-success.png)
*Shows green success message "Registration Successful ✓" with form reset*

### 3. DOM-Based Changes/Messages
![DOM Changes](screenshots/exp3-dom-changes.png)
*Shows error messages dynamically inserted/removed via DOM, invalid class toggling, button state changes*

---

## Result

The experiment was successfully completed. The Student Registration Form now features:

✅ **External JavaScript file** (`script.js`) linked and functioning  
✅ **Client-side validation** for all required fields using plain JavaScript  
✅ **Live validation** on `input`, `blur`, `change`, and `submit` events  
✅ **Clear error messages** displayed near respective fields via DOM manipulation  
✅ **Auto-removal** of errors when user corrects input  
✅ **Form submission prevented** when validation errors exist  
✅ **Success message displayed** without page reload on valid submission  
✅ **DOM methods used**: `querySelector`, `createElement`, `appendChild`, `remove`, `classList.add/remove`, `textContent`, `innerHTML`, `reset()`  
✅ **Responsive design** maintained from Experiment 2  
✅ **No external libraries** used — pure HTML, CSS, and JavaScript  

All validation rules work as specified:
- Name format: Letters only, 2+ characters
- Email format: Standard user@domain.tld validation
- Mobile format: Indian numbers (+91 XXXXX XXXXX)
- Password: 8+ chars, upper, lower, digit, special character
- Confirm password: Exact match required
- Checkbox: Must be checked

---

**File Location:** `D:\7-sem\advWebTechRepo\experiments\exp3\`  
**Files:** `index.html`, `style.css`, `script.js`