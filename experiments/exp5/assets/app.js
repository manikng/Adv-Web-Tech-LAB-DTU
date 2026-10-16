/**
 * DTU Lumina Academy - Client Application Logic
 * Don Norman Affordances, Signifiers & Instant Apple Feedback
 */

document.addEventListener('DOMContentLoaded', () => {
    const studentModal = document.getElementById('studentModal');
    const transcriptModal = document.getElementById('transcriptModal');
    const studentForm = document.getElementById('studentForm');
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.segment-btn');
    const toastContainer = document.getElementById('toastContainer');

    let currentFilter = 'all';
    let activeStudents = [];

    // Keyboard Shortcuts (Norman: Discoverability & Efficiency)
    window.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
        } else if (e.key === '/' && document.activeElement !== searchInput && !isModalOpen()) {
            e.preventDefault();
            searchInput.focus();
        } else if (e.key === 'Escape') {
            closeAllModals();
        }
    });

    function isModalOpen() {
        return studentModal.classList.contains('active') || transcriptModal.classList.contains('active');
    }

    // Modal Opening & Closing
    window.openNewStudentModal = function() {
        studentForm.reset();
        document.getElementById('modalStudentId').value = '';
        document.getElementById('modalTitle').textContent = 'Enroll Student Result';
        document.getElementById('submitBtnText').textContent = 'Save Record';
        updateLiveCalculator();
        showModal(studentModal);
    };

    window.closeStudentModal = function() {
        hideModal(studentModal);
    };

    window.closeTranscriptModal = function() {
        hideModal(transcriptModal);
    };

    function showModal(modalEl) {
        modalEl.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function hideModal(modalEl) {
        modalEl.classList.remove('active');
        document.body.style.overflow = '';
    }

    function closeAllModals() {
        hideModal(studentModal);
        hideModal(transcriptModal);
    }

    // Close when clicking outside modal sheet
    [studentModal, transcriptModal].forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                hideModal(modal);
            }
        });
    });

    // Real-Time Score Feedback (Norman Principle: Immediate Feedback)
    const scoreInputs = ['inputMaths', 'inputPhysics', 'inputCs', 'inputWebTech', 'inputEnglish'];
    scoreInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                let val = parseInt(el.value, 10);
                if (val > 100) el.value = 100;
                if (val < 0) el.value = 0;
                updateLiveCalculator();
            });
        }
    });

    function updateLiveCalculator() {
        let total = 0;
        let validScores = true;

        scoreInputs.forEach(id => {
            const input = document.getElementById(id);
            const val = parseInt(input.value, 10);
            if (!isNaN(val)) {
                total += val;
            } else {
                validScores = false;
            }
        });

        const perc = (total / 500) * 100;
        const totalEl = document.getElementById('previewTotal');
        const percEl = document.getElementById('previewPerc');
        const gradeEl = document.getElementById('previewGrade');

        if (totalEl) totalEl.textContent = `${total}/500`;
        if (percEl) percEl.textContent = `${perc.toFixed(1)}%`;

        if (gradeEl) {
            let grade = 'F';
            let color = 'rose';
            if (perc >= 90) { grade = 'A+'; color = 'emerald'; }
            else if (perc >= 80) { grade = 'A'; color = 'emerald'; }
            else if (perc >= 70) { grade = 'B'; color = 'blue'; }
            else if (perc >= 60) { grade = 'C'; color = 'blue'; }
            else if (perc >= 50) { grade = 'D'; color = 'amber'; }

            gradeEl.textContent = grade;
            gradeEl.className = `badge badge-${color}`;
        }
    }

    // Form Submission (Add / Edit)
    studentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const studentId = document.getElementById('modalStudentId').value;
        const isEdit = Boolean(studentId);

        const payload = {
            roll_no: document.getElementById('inputRoll').value.trim(),
            name: document.getElementById('inputName').value.trim(),
            email: document.getElementById('inputEmail').value.trim(),
            semester: document.getElementById('inputSemester').value,
            maths: parseInt(document.getElementById('inputMaths').value, 10),
            physics: parseInt(document.getElementById('inputPhysics').value, 10),
            cs: parseInt(document.getElementById('inputCs').value, 10),
            web_tech: parseInt(document.getElementById('inputWebTech').value, 10),
            english: parseInt(document.getElementById('inputEnglish').value, 10)
        };

        const submitBtn = studentForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';

        try {
            const url = isEdit ? `api.php?action=update&id=${studentId}` : 'api.php?action=create';
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            if (!result.success) {
                throw new Error(result.error || 'Operation failed');
            }

            showToast(result.message, 'success');
            closeStudentModal();
            loadStudentData(); // Refresh table & metrics
        } catch (err) {
            showToast(err.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        }
    });

    // Edit Student Action
    window.editStudent = async function(id) {
        try {
            const res = await fetch(`api.php?action=get&id=${id}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            const s = data.data;
            document.getElementById('modalStudentId').value = s.id;
            document.getElementById('inputRoll').value = s.roll_no;
            document.getElementById('inputName').value = s.name;
            document.getElementById('inputEmail').value = s.email;
            document.getElementById('inputSemester').value = s.semester;
            document.getElementById('inputMaths').value = s.maths;
            document.getElementById('inputPhysics').value = s.physics;
            document.getElementById('inputCs').value = s.cs;
            document.getElementById('inputWebTech').value = s.web_tech;
            document.getElementById('inputEnglish').value = s.english;

            document.getElementById('modalTitle').textContent = `Edit Record — ${s.name}`;
            document.getElementById('submitBtnText').textContent = 'Update Record';
            updateLiveCalculator();
            showModal(studentModal);
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    // Delete Student Action (Norman: Forgiving System with Confirmation)
    window.deleteStudent = async function(id, name, roll) {
        const confirmed = window.confirm(`Are you sure you want to delete the academic record for:\n\n${name} (${roll})?\n\nThis action cannot be undone.`);
        if (!confirmed) return;

        try {
            const res = await fetch(`api.php?action=delete&id=${id}`, {
                method: 'POST'
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            showToast(data.message, 'success');
            loadStudentData();
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    // View Transcript Action
    window.viewTranscript = async function(id) {
        try {
            const res = await fetch(`api.php?action=get&id=${id}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            const s = data.data;
            renderTranscript(s);
            showModal(transcriptModal);
        } catch (err) {
            showToast(err.message, 'error');
        }
    };

    function renderTranscript(s) {
        document.getElementById('tName').textContent = s.name;
        document.getElementById('tRoll').textContent = s.roll_no;
        document.getElementById('tEmail').textContent = s.email;
        document.getElementById('tSemester').textContent = s.semester;
        document.getElementById('tDate').textContent = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

        const tbody = document.getElementById('transcriptCourseRows');
        tbody.innerHTML = '';

        const courses = [
            { code: 'CS-601', title: 'Advanced Applied Mathematics', credits: 4, mark: s.maths },
            { code: 'CS-602', title: 'Applied Quantum Physics', credits: 3, mark: s.physics },
            { code: 'CS-603', title: 'Data Structures & Algorithms', credits: 4, mark: s.cs },
            { code: 'CS-604', title: 'Modern Dynamic Web Architectures', credits: 4, mark: s.web_tech },
            { code: 'CS-605', title: 'Technical & Professional Communication', credits: 2, mark: s.english }
        ];

        courses.forEach(c => {
            const tr = document.createElement('tr');
            const passed = c.mark >= 40;
            const letter = c.mark >= 90 ? 'A+' : (c.mark >= 80 ? 'A' : (c.mark >= 70 ? 'B' : (c.mark >= 60 ? 'C' : (c.mark >= 50 ? 'D' : 'F'))));
            tr.innerHTML = `
                <td><code>${c.code}</code></td>
                <td><strong>${c.title}</strong></td>
                <td style="text-align: center;">${c.credits}</td>
                <td style="text-align: right; font-weight: 600;">${c.mark}/100</td>
                <td style="text-align: center;"><span class="badge badge-${passed ? 'emerald' : 'rose'}">${letter}</span></td>
                <td style="text-align: center;">${passed ? '<span style="color: var(--apple-green)">PASS</span>' : '<span style="color: var(--apple-red)">FAIL</span>'}</td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('tTotalMarks').textContent = `${s.total_marks} / ${s.max_marks}`;
        document.getElementById('tPercentage').textContent = `${s.percentage}%`;
        document.getElementById('tGPA').textContent = `${s.gpa} / 4.00`;
        document.getElementById('tStatus').textContent = s.status;
        document.getElementById('tStatus').className = `badge badge-${s.status_color}`;
    }

    window.printTranscript = function() {
        window.print();
    };

    // Filter Segmentation & Instant Search
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            applyFilters();
        });
    });

    searchInput.addEventListener('input', () => {
        applyFilters();
    });

    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.student-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const roll = row.dataset.roll.toLowerCase();
            const email = row.dataset.email.toLowerCase();
            const status = row.dataset.status;
            const grade = row.dataset.grade;
            const perc = parseFloat(row.dataset.perc);

            const matchesQuery = !query || name.includes(query) || roll.includes(query) || email.includes(query);
            let matchesFilter = true;

            if (currentFilter === 'distinction') {
                matchesFilter = perc >= 80;
            } else if (currentFilter === 'first') {
                matchesFilter = perc >= 70 && perc < 80;
            } else if (currentFilter === 'pass') {
                matchesFilter = perc >= 50 && perc < 70 && status !== 'Backlog / Arrear';
            } else if (currentFilter === 'backlog') {
                matchesFilter = status === 'Backlog / Arrear' || perc < 50;
            }

            if (matchesQuery && matchesFilter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyEl = document.getElementById('noResultsRow');
        if (emptyEl) {
            emptyEl.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    // Refresh Data from Server
    async function loadStudentData() {
        try {
            const res = await fetch('api.php?action=list');
            const data = await res.json();
            if (!data.success) return;

            activeStudents = data.data;
            updateStatsUI(data.stats);
            renderStudentTable(activeStudents);
            applyFilters();
        } catch (e) {
            console.error('Fetch error:', e);
        }
    }

    function updateStatsUI(stats) {
        document.getElementById('statTotal').textContent = stats.total_students;
        document.getElementById('statAverage').textContent = `${stats.class_average}%`;
        document.getElementById('statPassRate').textContent = `${stats.pass_percentage}%`;
        
        if (stats.top_student) {
            document.getElementById('statTopStudent').textContent = stats.top_student.name;
            document.getElementById('statTopMeta').textContent = `${stats.top_student.percentage}% (${stats.top_student.roll_no})`;
        } else {
            document.getElementById('statTopStudent').textContent = '—';
            document.getElementById('statTopMeta').textContent = 'No records yet';
        }
    }

    function renderStudentTable(students) {
        const tbody = document.getElementById('studentsTbody');
        tbody.innerHTML = '';

        if (students.length === 0) {
            tbody.innerHTML = `
                <tr id="noResultsRow">
                    <td colspan="7" class="empty-state">
                        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <p>No student results enrolled yet.</p>
                        <button class="btn btn-primary" style="margin-top: 14px;" onclick="openNewStudentModal()">Enroll First Student</button>
                    </td>
                </tr>
            `;
            return;
        }

        students.forEach(s => {
            const initials = s.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
            const tr = document.createElement('tr');
            tr.className = 'student-row';
            tr.dataset.name = s.name;
            tr.dataset.roll = s.roll_no;
            tr.dataset.email = s.email;
            tr.dataset.status = s.status;
            tr.dataset.grade = s.grade;
            tr.dataset.perc = s.percentage;

            tr.innerHTML = `
                <td>
                    <div class="student-cell">
                        <div class="student-avatar">${initials}</div>
                        <div class="student-info">
                            <span class="name">${escapeHtml(s.name)}</span>
                            <span class="meta">${escapeHtml(s.roll_no)} • ${escapeHtml(s.semester)}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-weight: 600; font-size: 0.95rem;">${s.total_marks}</span>
                    <span style="color: var(--apple-text-muted); font-size: 0.78rem;">/ ${s.max_marks}</span>
                </td>
                <td>
                    <div>
                        <span style="font-weight: 600;">${s.percentage}%</span>
                        <div class="mini-score-bar">
                            <div class="mini-score-fill" style="width: ${s.percentage}%; background-color: var(--apple-${s.status_color === 'rose' ? 'red' : (s.status_color === 'emerald' ? 'green' : 'blue')});"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-${s.status_color}">${s.grade} (${s.gpa})</span>
                </td>
                <td>
                    <span class="badge badge-${s.status_color}">${s.status}</span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="icon-btn" title="View Official Transcript" onclick="viewTranscript(${s.id})">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                        <button class="icon-btn" title="Edit Student Record" onclick="editStudent(${s.id})">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button class="icon-btn delete-btn" title="Delete Record" onclick="deleteStudent(${s.id}, '${escapeHtml(s.name)}', '${escapeHtml(s.roll_no)}')">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[m]);
    }

    // Dynamic Island Toast (Feedback)
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'toast';
        const iconSvg = type === 'success' 
            ? `<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#34C759"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`
            : `<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#FF3B30"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
        
        toast.innerHTML = `${iconSvg} <span>${escapeHtml(message)}</span>`;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px) scale(0.95)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3400);
    }
});
