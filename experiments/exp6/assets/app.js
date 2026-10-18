/**
 * Experiment 6: Database Connectivity and CRUD Operations
 * Client-side Controller & Dynamic Interactivity
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 */

document.addEventListener('DOMContentLoaded', () => {
  // State
  let students = [];
  let currentFilterGrade = 'All';
  let activeStudent = null;

  // DOM Elements
  const searchInput = document.getElementById('search-input');
  const semesterFilter = document.getElementById('semester-filter');
  const sortSelect = document.getElementById('sort-select');
  const studentTableBody = document.getElementById('student-table-body');
  const emptyState = document.getElementById('empty-state');
  const themeToggle = document.getElementById('theme-toggle');
  const dbStatusPill = document.getElementById('db-status-pill');

  // Modals
  const studentModal = document.getElementById('student-modal');
  const studentForm = document.getElementById('student-form');
  const studentNameInput = document.getElementById('name');
  const rollNoInput = document.getElementById('roll_no');
  const emailInput = document.getElementById('email');
  const modalTitle = document.getElementById('modal-title');
  const transcriptModal = document.getElementById('transcript-modal');
  const dbModal = document.getElementById('db-modal');
  const deleteModal = document.getElementById('delete-modal');

  // Inputs for live score calculation
  const mathInput = document.getElementById('maths');
  const physicsInput = document.getElementById('physics');
  const csInput = document.getElementById('cs');
  const webTechInput = document.getElementById('web_tech');
  const englishInput = document.getElementById('english');
  const previewTotal = document.getElementById('preview-total');
  const previewPct = document.getElementById('preview-pct');
  const previewGpa = document.getElementById('preview-gpa');
  const previewGrade = document.getElementById('preview-grade');

  // Toast Container
  const toastContainer = document.getElementById('toast-container');

  // Theme Management
  const savedTheme = localStorage.getItem('dtu_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeIcon(savedTheme);

  themeToggle?.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('dtu_theme', next);
    updateThemeIcon(next);
  });

  function updateThemeIcon(theme) {
    if (themeToggle) {
      themeToggle.innerHTML = theme === 'dark' ? '☀️' : '🌙';
    }
  }

  function updateInstitutionalEmail() {
    if (!emailInput) return;
    const name = studentNameInput?.value.trim().toLowerCase()
      .replace(/[^a-z0-9]+/g, '.')
      .replace(/^\.+|\.+$/g, '') || '';
    const rollNo = rollNoInput?.value.replace(/[^a-z0-9]/gi, '').toLowerCase() || '';
    emailInput.value = name && rollNo ? `${name}.${rollNo}@dtu.ac.in` : '';
  }

  [studentNameInput, rollNoInput].forEach(input => {
    input?.addEventListener('input', updateInstitutionalEmail);
  });

  // Toast Notification
  function showToast(message, type = 'success') {
    if (!toastContainer) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    toastContainer.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }

  // Score Calculator
  function calculateLiveMetrics() {
    const m = parseFloat(mathInput?.value) || 0;
    const p = parseFloat(physicsInput?.value) || 0;
    const c = parseFloat(csInput?.value) || 0;
    const w = parseFloat(webTechInput?.value) || 0;
    const e = parseFloat(englishInput?.value) || 0;

    const total = Math.min(500, Math.max(0, m + p + c + w + e));
    const pct = +(total / 5).toFixed(2);
    
    let gpa = 0.0;
    let grade = 'F';

    if (pct >= 90) { grade = 'A+'; gpa = 10.0; }
    else if (pct >= 80) { grade = 'A'; gpa = 9.0; }
    else if (pct >= 70) { grade = 'B+'; gpa = 8.0; }
    else if (pct >= 60) { grade = 'B'; gpa = 7.0; }
    else if (pct >= 50) { grade = 'C'; gpa = 6.0; }
    else if (pct >= 40) { grade = 'P'; gpa = 5.0; }
    else { grade = 'F'; gpa = 0.0; }

    if (previewTotal) previewTotal.textContent = total.toFixed(1);
    if (previewPct) previewPct.textContent = `${pct}%`;
    if (previewGpa) previewGpa.textContent = gpa.toFixed(1);
    if (previewGrade) previewGrade.textContent = grade;
  }

  [mathInput, physicsInput, csInput, webTechInput, englishInput].forEach(inp => {
    inp?.addEventListener('input', calculateLiveMetrics);
  });

  // Fetch Students from API
  async function loadStudents() {
    const q = searchInput?.value.trim() || '';
    const sem = semesterFilter?.value || 'All';
    const sortVal = sortSelect?.value || 'id-desc';
    const [sort, dir] = sortVal.split('-');

    try {
      const url = `api.php?action=list&q=${encodeURIComponent(q)}&semester=${encodeURIComponent(sem)}&grade=${encodeURIComponent(currentFilterGrade)}&sort=${sort}&dir=${dir}`;
      const res = await fetch(url);
      const data = await res.json();

      if (data.success) {
        students = data.students;
        renderTable(students);
        updateMetrics(data.stats);
        updateDbPill(data.db_status);
      } else {
        showToast(data.error || 'Failed to fetch student data', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Network error while connecting to PHP backend.', 'error');
    }
  }

  // Update Database Status Pill
  function updateDbPill(status) {
    if (!dbStatusPill || !status) return;
    if (status.active_driver === 'mysql') {
      dbStatusPill.className = 'db-status-pill mysql-active';
      dbStatusPill.innerHTML = `<span class="status-dot"></span> MySQL (Active)`;
    } else {
      dbStatusPill.className = 'db-status-pill sqlite-fallback';
      dbStatusPill.innerHTML = `<span class="status-dot"></span> SQLite Fallback (MySQL Offline)`;
    }
  }

  // Update Metric Cards
  function updateMetrics(stats) {
    if (!stats) return;
    const totalEl = document.getElementById('stat-total-students');
    const avgEl = document.getElementById('stat-average-pct');
    const topEl = document.getElementById('stat-top-scorer');
    const passEl = document.getElementById('stat-pass-rate');

    if (totalEl) totalEl.textContent = stats.total_students;
    if (avgEl) avgEl.textContent = `${stats.average_pct}%`;
    if (topEl) {
      topEl.textContent = stats.top_scorer ? `${stats.top_scorer.name.split(' ')[0]} (${stats.top_scorer.percentage}%)` : 'N/A';
    }
    if (passEl) passEl.textContent = `${stats.pass_rate}%`;
  }

  // Render Student Table
  function renderTable(list) {
    if (!studentTableBody) return;
    studentTableBody.innerHTML = '';

    if (list.length === 0) {
      if (emptyState) emptyState.style.display = 'block';
      return;
    }
    if (emptyState) emptyState.style.display = 'none';

    list.forEach(s => {
      const tr = document.createElement('tr');
      const initials = s.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
      const gradeClass = `grade-${s.grade.replace('+', '-plus')}`;

      tr.innerHTML = `
        <td>
          <div class="student-info-cell">
            <div class="avatar-badge">${initials}</div>
            <div>
              <span class="student-name">${escapeHtml(s.name)}</span>
              <span class="student-email">${escapeHtml(s.email)}</span>
            </div>
          </div>
        </td>
        <td><span class="roll-tag">${escapeHtml(s.roll_no)}</span></td>
        <td><span style="font-size: 0.8rem; color: var(--text-secondary);">${escapeHtml(s.department)}</span></td>
        <td><span style="font-size: 0.8rem;">${escapeHtml(s.semester)}</span></td>
        <td>
          <div style="font-weight: 700;">${s.percentage}%</div>
          <div class="score-progress">
            <div class="score-progress-bar" style="width: ${Math.min(100, s.percentage)}%;"></div>
          </div>
        </td>
        <td><strong>${parseFloat(s.gpa).toFixed(1)}</strong></td>
        <td><span class="grade-badge ${gradeClass}">${s.grade}</span></td>
        <td>
          <div class="action-buttons">
            <button class="action-btn" title="View Transcript" onclick="window.DtuApp.openTranscript(${s.id})">📄</button>
            <button class="action-btn" title="Edit Student" onclick="window.DtuApp.openEditModal(${s.id})">✏️</button>
            <button class="action-btn btn-delete" title="Delete Student" onclick="window.DtuApp.openDeleteModal(${s.id})">🗑️</button>
          </div>
        </td>
      `;
      studentTableBody.appendChild(tr);
    });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    })[m]);
  }

  // Filter Pill clicks
  document.querySelectorAll('.pill-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilterGrade = btn.getAttribute('data-grade') || 'All';
      loadStudents();
    });
  });

  // Filter & Search events
  let debounceTimer;
  searchInput?.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(loadStudents, 250);
  });
  semesterFilter?.addEventListener('change', loadStudents);
  sortSelect?.addEventListener('change', loadStudents);

  // Keyboard shortcut '/' to search
  window.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement !== searchInput && !['input', 'textarea'].includes(document.activeElement.tagName.toLowerCase())) {
      e.preventDefault();
      searchInput?.focus();
    }
    if (e.key === 'Escape') {
      closeAllModals();
    }
  });

  // Modal Open/Close Handlers
  function openModal(modal) {
    if (modal) modal.classList.add('active');
  }

  function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
  }

  document.querySelectorAll('.close-modal-btn, .btn-modal-cancel').forEach(btn => {
    btn.addEventListener('click', closeAllModals);
  });

  // Open Add Modal
  const openAddBtn = document.getElementById('open-add-modal-btn');
  openAddBtn?.addEventListener('click', () => {
    studentForm.reset();
    document.getElementById('student-id').value = '';
    modalTitle.textContent = 'Enroll New Student';
    calculateLiveMetrics();
    openModal(studentModal);
  });

  // Form Submit (Create / Update)
  studentForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('student-id').value;
    const isUpdate = Boolean(id);

    const payload = {
      id: id ? parseInt(id, 10) : undefined,
      roll_no: document.getElementById('roll_no').value.trim(),
      name: document.getElementById('name').value.trim(),
      email: document.getElementById('email').value.trim(),
      department: document.getElementById('department').value.trim(),
      semester: document.getElementById('semester').value,
      maths: parseFloat(mathInput.value) || 0,
      physics: parseFloat(physicsInput.value) || 0,
      cs: parseFloat(csInput.value) || 0,
      web_tech: parseFloat(webTechInput.value) || 0,
      english: parseFloat(englishInput.value) || 0,
    };

    try {
      const endpoint = isUpdate ? 'api.php?action=update' : 'api.php?action=create';
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message, 'success');
        closeAllModals();
        loadStudents();
      } else {
        showToast(data.error || 'Operation failed', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Error sending data to server.', 'error');
    }
  });

  // Delete Action
  const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
  confirmDeleteBtn?.addEventListener('click', async () => {
    if (!activeStudent) return;
    try {
      const res = await fetch('api.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: activeStudent.id })
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        closeAllModals();
        loadStudents();
      } else {
        showToast(data.error || 'Failed to delete record', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Error deleting student.', 'error');
    }
  });

  // DB Diagnostics Modal
  dbStatusPill?.addEventListener('click', async () => {
    try {
      const res = await fetch('api.php?action=db_status');
      const data = await res.json();
      if (data.success) {
        renderDbDetails(data.db_status);
        openModal(dbModal);
      }
    } catch (err) {
      showToast('Failed to check database details', 'error');
    }
  });

  function renderDbDetails(st) {
    const el = document.getElementById('db-details-content');
    if (!el) return;
    el.innerHTML = `
      <div style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px; font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; line-height: 1.8;">
        <div><strong>Active Driver:</strong> <span style="color: ${st.active_driver === 'mysql' ? '#10b981' : '#f59e0b'}; text-transform: uppercase; font-weight: bold;">${st.active_driver}</span></div>
        <div><strong>Fallback Mode:</strong> ${st.is_fallback ? 'Yes (SQLite resilient sandbox active)' : 'No (Direct MySQL Engine)'}</div>
        <div><strong>Target MySQL Host:</strong> ${st.mysql_host}</div>
        <div><strong>Target MySQL Database:</strong> ${st.mysql_database}</div>
        <div><strong>Target MySQL User:</strong> ${st.mysql_user}</div>
        <div><strong>Server Version:</strong> ${st.server_version}</div>
        <div><strong>Available PDO Drivers:</strong> ${st.available_drivers.join(', ')}</div>
        ${st.mysql_error ? `<div style="margin-top: 0.5rem; color: #ef4444; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 0.5rem;"><strong>MySQL Connection Status:</strong> ${escapeHtml(st.mysql_error)}</div>` : ''}
      </div>
      <div style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-secondary);">
        <p><strong>💡 How to switch to live MySQL:</strong></p>
        <p>1. Start your local MySQL / MariaDB server (e.g. XAMPP, WAMP, Laragon, or Windows MySQL service on port 3306).</p>
        <p>2. Import <code>schema.sql</code> into MySQL or let the application auto-create <code>exp6_db</code>.</p>
        <p>3. Refresh the page to auto-connect!</p>
      </div>
    `;
  }

  // Quick Reset DB Action
  const resetDbBtn = document.getElementById('reset-db-btn');
  resetDbBtn?.addEventListener('click', async () => {
    if (!confirm("Are you sure you want to reset records to default benchmark data?")) return;
    try {
      const res = await fetch('api.php?action=reset', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        loadStudents();
      }
    } catch (err) {
      showToast('Error resetting database.', 'error');
    }
  });

  // Global DtuApp object for row actions
  window.DtuApp = {
    openEditModal: async (id) => {
      try {
        const res = await fetch(`api.php?action=get&id=${id}`);
        const data = await res.json();
        if (data.success) {
          const s = data.student;
          document.getElementById('student-id').value = s.id;
          document.getElementById('roll_no').value = s.roll_no;
          document.getElementById('name').value = s.name;
          document.getElementById('email').value = s.email;
          document.getElementById('department').value = s.department;
          document.getElementById('semester').value = s.semester;
          mathInput.value = s.maths;
          physicsInput.value = s.physics;
          csInput.value = s.cs;
          webTechInput.value = s.web_tech;
          englishInput.value = s.english;

          modalTitle.textContent = `Edit Student #${s.id} (${s.roll_no})`;
          calculateLiveMetrics();
          openModal(studentModal);
        }
      } catch (err) {
        showToast('Error fetching student details', 'error');
      }
    },

    openDeleteModal: (id) => {
      activeStudent = students.find(s => s.id === id);
      if (!activeStudent) return;
      const text = document.getElementById('delete-student-name');
      if (text) text.textContent = `${activeStudent.name} (${activeStudent.roll_no})`;
      openModal(deleteModal);
    },

    openTranscript: (id) => {
      const s = students.find(item => item.id === id);
      if (!s) return;
      renderTranscript(s);
      openModal(transcriptModal);
    }
  };

  function renderTranscript(s) {
    const el = document.getElementById('transcript-content');
    if (!el) return;
    el.innerHTML = `
      <div class="transcript-card">
        <div class="transcript-header">
          <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; font-weight: 700;">Department of Computer Science & Engineering</div>
          <h2 class="transcript-title">Official Academic Transcript</h2>
          <div style="font-size: 0.75rem; color: #94a3b8;">Delhi Technological University</div>
        </div>
        <div class="transcript-meta">
          <div><strong>Candidate Name:</strong> ${escapeHtml(s.name)}</div>
          <div><strong>Roll / Registration No:</strong> ${escapeHtml(s.roll_no)}</div>
          <div><strong>Academic Department:</strong> ${escapeHtml(s.department)}</div>
          <div><strong>Academic Semester:</strong> ${escapeHtml(s.semester)}</div>
          <div><strong>Email Address:</strong> ${escapeHtml(s.email)}</div>
          <div><strong>Issued Timestamp:</strong> ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
        </div>

        <table class="transcript-table">
          <thead>
            <tr>
              <th>Course Code</th>
              <th>Subject Title</th>
              <th>Maximum</th>
              <th>Score Secured</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>CS-301</td>
              <td>Discrete Mathematics</td>
              <td>100</td>
              <td><strong>${s.maths}</strong></td>
              <td>${s.maths >= 40 ? 'PASS' : 'FAIL'}</td>
            </tr>
            <tr>
              <td>CS-302</td>
              <td>Applied Physics</td>
              <td>100</td>
              <td><strong>${s.physics}</strong></td>
              <td>${s.physics >= 40 ? 'PASS' : 'FAIL'}</td>
            </tr>
            <tr>
              <td>CS-303</td>
              <td>Data Structures & Algorithms</td>
              <td>100</td>
              <td><strong>${s.cs}</strong></td>
              <td>${s.cs >= 40 ? 'PASS' : 'FAIL'}</td>
            </tr>
            <tr>
              <td>CS-304</td>
              <td>Advanced Web Technologies (PHP/MySQL)</td>
              <td>100</td>
              <td><strong>${s.web_tech}</strong></td>
              <td>${s.web_tech >= 40 ? 'PASS' : 'FAIL'}</td>
            </tr>
            <tr>
              <td>HS-301</td>
              <td>Technical Communication</td>
              <td>100</td>
              <td><strong>${s.english}</strong></td>
              <td>${s.english >= 40 ? 'PASS' : 'FAIL'}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr style="background: #f8fafc; font-weight: 700;">
              <td colspan="2">Consolidated Academic Aggregate</td>
              <td>500</td>
              <td>${s.total_marks} (${s.percentage}%)</td>
              <td>GPA: ${parseFloat(s.gpa).toFixed(1)} / 10</td>
            </tr>
          </tfoot>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed #cbd5e1;">
          <div>
            <div style="font-size: 0.8rem; color: #64748b;">Overall Academic Classification:</div>
            <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">${s.status} (Grade: ${s.grade})</div>
          </div>
          <div style="text-align: right;">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: #94a3b8;">DIGITALLY VERIFIED VIA DTU SIS</div>
            <div style="font-weight: 700; font-size: 0.85rem; margin-top: 0.25rem;">Registrar of Examinations</div>
          </div>
        </div>
      </div>
    `;
  }

  // Initial Data Load
  loadStudents();
});
