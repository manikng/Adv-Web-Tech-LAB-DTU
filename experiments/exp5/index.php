<?php
/**
 * Lumina Academy - Student Result Management System
 * Dynamic PHP Web Application with Apple Aesthetics & Don Norman Design Principles
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$db = LuminaDB::getInstance();
$students = $db->getAll();

// Compute stats server-side for initial instantaneous rendering
$totalStudents = count($students);
$totalPerc = 0;
$distinctions = 0;
$passed = 0;
$topStudent = !empty($students) ? $students[0] : null;

foreach ($students as $s) {
    $totalPerc += $s['percentage'];
    if ($s['percentage'] >= 80) $distinctions++;
    if ($s['percentage'] >= 50 && empty($s['failed_subjects'])) $passed++;
    if ($topStudent && $s['percentage'] > $topStudent['percentage']) {
        $topStudent = $s;
    }
}

$classAvg = $totalStudents > 0 ? round($totalPerc / $totalStudents, 1) : 0;
$passRate = $totalStudents > 0 ? round(($passed / $totalStudents) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Student Result Management</title>
    <meta name="description" content="Dynamic Academic Result Management System designed with Apple clarity and Don Norman human-centered affordances.">
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="assets/style.css">
    
    <!-- Preconnect Apple Font Glyphs -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Frosted Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <h1><?= APP_NAME ?></h1>
                    <span><?= INSTITUTION_NAME ?></span>
                </div>
            </a>

            <div class="nav-actions">
                <a href="api.php?action=export_csv" class="btn btn-secondary" title="Export Ledger as CSV">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Export CSV</span>
                </a>
                <button type="button" class="btn btn-primary" onclick="openNewStudentModal()" id="btnNewStudent">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Enroll Student</span>
                </button>
            </div>
        </div>
    </header>

    <main class="container">
        
        <!-- Hero Header -->
        <section class="hero-header">
            <div class="hero-subtitle">Academic Intelligence • <?= ACADEMIC_YEAR ?></div>
            <h2 class="hero-title">Student Performance Ledger</h2>
            <p class="hero-desc">
                Seamlessly compute marks, assess grade distributions, and generate official academic transcripts in real-time.
            </p>
        </section>

        <!-- Stats Overview Cards -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Enrolled</span>
                    <div class="stat-icon" style="background: rgba(0, 113, 227, 0.1); color: var(--apple-blue);">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statTotal"><?= $totalStudents ?></div>
                <div class="stat-sub">Active semester candidates</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Class Average</span>
                    <div class="stat-icon" style="background: rgba(52, 199, 89, 0.1); color: var(--apple-green);">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statAverage"><?= $classAvg ?>%</div>
                <div class="stat-sub"><?= $distinctions ?> distinctions awarded</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Pass Percentage</span>
                    <div class="stat-icon" style="background: rgba(175, 82, 222, 0.1); color: var(--apple-purple);">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statPassRate"><?= $passRate ?>%</div>
                <div class="stat-sub"><?= $passed ?> out of <?= $totalStudents ?> cleared</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Class Topper</span>
                    <div class="stat-icon" style="background: rgba(255, 149, 0, 0.1); color: var(--apple-amber);">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statTopStudent" style="font-size: 1.45rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= $topStudent ? h($topStudent['name']) : '—' ?>
                </div>
                <div class="stat-sub" id="statTopMeta">
                    <?= $topStudent ? $topStudent['percentage'] . '% (' . h($topStudent['roll_no']) . ')' : 'No records yet' ?>
                </div>
            </div>
        </section>

        <!-- Don Norman Control Bar (Affordance: Search + Apple Segmented Filter) -->
        <section class="control-bar">
            <div class="search-box">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by name, roll number, or email... (Press ⌘K or /)" autocomplete="off">
            </div>

            <div class="segmented-control" role="tablist" aria-label="Student grade filters">
                <button type="button" class="segment-btn active" data-filter="all">All</button>
                <button type="button" class="segment-btn" data-filter="distinction">Distinction (≥80%)</button>
                <button type="button" class="segment-btn" data-filter="first">First Class (70-79%)</button>
                <button type="button" class="segment-btn" data-filter="pass">Pass (50-69%)</button>
                <button type="button" class="segment-btn" data-filter="backlog">At Risk / Backlog</button>
            </div>
        </section>

        <!-- Results Roster Table -->
        <section class="table-container">
            <table class="results-table" id="studentsTable">
                <thead>
                    <tr>
                        <th>Student Identity</th>
                        <th>Aggregate Marks</th>
                        <th>Percentage</th>
                        <th>Grade / GPA</th>
                        <th>Academic Standing</th>
                        <th style="text-align: right; padding-right: 28px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="studentsTbody">
                    <?php if (empty($students)): ?>
                        <tr id="noResultsRow">
                            <td colspan="6" class="empty-state">
                                <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p>No student results enrolled yet.</p>
                                <button class="btn btn-primary" style="margin-top: 14px;" onclick="openNewStudentModal()">Enroll First Student</button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): 
                            $nameParts = explode(' ', $s['name']);
                            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                        ?>
                            <tr class="student-row" 
                                data-name="<?= h($s['name']) ?>" 
                                data-roll="<?= h($s['roll_no']) ?>" 
                                data-email="<?= h($s['email']) ?>" 
                                data-status="<?= h($s['status']) ?>" 
                                data-grade="<?= h($s['grade']) ?>" 
                                data-perc="<?= $s['percentage'] ?>">
                                <td>
                                    <div class="student-cell">
                                        <div class="student-avatar"><?= $initials ?></div>
                                        <div class="student-info">
                                            <span class="name"><?= h($s['name']) ?></span>
                                            <span class="meta"><?= h($s['roll_no']) ?> • <?= h($s['semester']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600; font-size: 0.95rem;"><?= $s['total_marks'] ?></span>
                                    <span style="color: var(--apple-text-muted); font-size: 0.78rem;">/ <?= $s['max_marks'] ?></span>
                                </td>
                                <td>
                                    <div>
                                        <span style="font-weight: 600;"><?= $s['percentage'] ?>%</span>
                                        <div class="mini-score-bar">
                                            <div class="mini-score-fill" style="width: <?= $s['percentage'] ?>%; background-color: var(--apple-<?= $s['status_color'] === 'rose' ? 'red' : ($s['status_color'] === 'emerald' ? 'green' : 'blue') ?>);"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $s['status_color'] ?>"><?= $s['grade'] ?> (<?= $s['gpa'] ?>)</span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $s['status_color'] ?>"><?= h($s['status']) ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <button type="button" class="icon-btn" title="View Official Transcript" onclick="viewTranscript(<?= $s['id'] ?>)">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="icon-btn" title="Edit Student Record" onclick="editStudent(<?= $s['id'] ?>)">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="icon-btn delete-btn" title="Delete Record" onclick="deleteStudent(<?= $s['id'] ?>, '<?= h(addslashes($s['name'])) ?>', '<?= h(addslashes($s['roll_no'])) ?>')">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

    <!-- Modal 1: Enroll / Edit Student Sheet -->
    <div class="modal-overlay" id="studentModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-sheet">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Enroll Student Result</h3>
                <button type="button" class="modal-close" onclick="closeStudentModal()" aria-label="Close modal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form id="studentForm" autocomplete="off">
                <input type="hidden" id="modalStudentId" value="">
                
                <div class="modal-body">
                    
                    <!-- Student Demographic Grid -->
                    <div class="form-grid">
                        <div>
                            <label class="form-label" for="inputRoll">Student ID / Roll No</label>
                            <input type="text" id="inputRoll" class="form-input" placeholder="e.g. LA-2026-009" required>
                        </div>
                        <div>
                            <label class="form-label" for="inputSemester">Semester</label>
                            <select id="inputSemester" class="form-input" required>
                                <option value="Semester 6">Semester 6</option>
                                <option value="Semester 5">Semester 5</option>
                                <option value="Semester 4">Semester 4</option>
                                <option value="Semester 3">Semester 3</option>
                                <option value="Semester 2">Semester 2</option>
                                <option value="Semester 1">Semester 1</option>
                            </select>
                        </div>
                        <div class="form-group-full">
                            <label class="form-label" for="inputName">Full Name</label>
                            <input type="text" id="inputName" class="form-input" placeholder="e.g. Eleanor Vance" required>
                        </div>
                        <div class="form-group-full">
                            <label class="form-label" for="inputEmail">Academic Email</label>
                            <input type="email" id="inputEmail" class="form-input" placeholder="e.g. [EMAIL_ADDRESS]" required>
                        </div>
                    </div>

                    <!-- Subject Mark Inputs (0 - 100) -->
                    <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 12px; margin-top: 20px;">Curriculum Performance (Out of 100)</h4>
                    <div class="form-grid">
                        <div>
                            <label class="form-label" for="inputMaths">CS-601: Mathematics</label>
                            <input type="number" id="inputMaths" class="form-input" min="0" max="100" placeholder="0 - 100" required>
                        </div>
                        <div>
                            <label class="form-label" for="inputPhysics">CS-602: Applied Physics</label>
                            <input type="number" id="inputPhysics" class="form-input" min="0" max="100" placeholder="0 - 100" required>
                        </div>
                        <div>
                            <label class="form-label" for="inputCs">CS-603: Computer Science</label>
                            <input type="number" id="inputCs" class="form-input" min="0" max="100" placeholder="0 - 100" required>
                        </div>
                        <div>
                            <label class="form-label" for="inputWebTech">CS-604: Web Architectures</label>
                            <input type="number" id="inputWebTech" class="form-input" min="0" max="100" placeholder="0 - 100" required>
                        </div>
                        <div class="form-group-full">
                            <label class="form-label" for="inputEnglish">CS-605: Professional English</label>
                            <input type="number" id="inputEnglish" class="form-input" min="0" max="100" placeholder="0 - 100" required>
                        </div>
                    </div>

                    <!-- Don Norman Immediate Feedback: Live Score Preview Box -->
                    <div class="score-preview-box">
                        <div class="preview-stat">
                            <div class="preview-val" id="previewTotal">0/500</div>
                            <div class="preview-label">Calculated Aggregate</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-val" id="previewPerc">0.0%</div>
                            <div class="preview-label">Percentage</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-val"><span id="previewGrade" class="badge badge-rose">F</span></div>
                            <div class="preview-label">Estimated Grade</div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeStudentModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitStudent">
                        <span id="submitBtnText">Save Record</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Official Academic Transcript Sheet (Apple Print Grade Sheet) -->
    <div class="modal-overlay" id="transcriptModal" role="dialog" aria-modal="true">
        <div class="modal-sheet" style="max-width: 720px;">
            <div class="modal-header">
                <h3 class="modal-title">Official Academic Transcript</h3>
                <button type="button" class="modal-close" onclick="closeTranscriptModal()" aria-label="Close modal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="transcript-card" id="printableTranscript">
                    <div class="transcript-header">
                        <h2><?= INSTITUTION_NAME ?></h2>
                        <p>Office of the Academic Registrar • Department of Computer Engineering</p>
                        <p style="font-size: 0.78rem; margin-top: 4px; color: var(--apple-text-light);">Accredited Grade A++ • Session <?= ACADEMIC_YEAR ?></p>
                    </div>

                    <div class="transcript-meta-grid">
                        <div><strong>Student Name:</strong> <span id="tName">—</span></div>
                        <div><strong>Roll / ID:</strong> <span id="tRoll">—</span></div>
                        <div><strong>Registered Email:</strong> <span id="tEmail">—</span></div>
                        <div><strong>Semester:</strong> <span id="tSemester">—</span></div>
                        <div><strong>Issue Date:</strong> <span id="tDate">—</span></div>
                        <div><strong>Verification:</strong> <code>VERIFIED-ACADEMIC-SEAL</code></div>
                    </div>

                    <table class="transcript-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Course Title</th>
                                <th style="text-align: center;">Credits</th>
                                <th style="text-align: right;">Marks Obtained</th>
                                <th style="text-align: center;">Grade</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="transcriptCourseRows">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>

                    <div style="display: flex; justify-content: space-between; align-items: center; background: #F8F9FA; padding: 14px 20px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                        <div>
                            <span style="font-size: 0.8rem; color: var(--apple-text-muted);">Aggregate Score:</span>
                            <strong id="tTotalMarks" style="margin-left: 6px; font-size: 1.05rem;">—</strong>
                        </div>
                        <div>
                            <span style="font-size: 0.8rem; color: var(--apple-text-muted);">Percentage:</span>
                            <strong id="tPercentage" style="margin-left: 6px; font-size: 1.05rem;">—</strong>
                        </div>
                        <div>
                            <span style="font-size: 0.8rem; color: var(--apple-text-muted);">Cumulative GPA:</span>
                            <strong id="tGPA" style="margin-left: 6px; font-size: 1.05rem; color: var(--apple-blue);">—</strong>
                        </div>
                        <div>
                            <span id="tStatus" class="badge badge-emerald">Distinction</span>
                        </div>
                    </div>

                    <div class="transcript-footer">
                        <div class="official-seal">
                            ✦ OFFICIAL SEAL ✦<br>Delhi Technical University Academic Affairs
                        </div>
                        <div style="text-align: right; font-size: 0.8rem; color: var(--apple-text-muted);">
                            <div style="height: 35px; border-bottom: 1px solid #1D1D1F; width: 160px; margin-bottom: 4px;"></div>
                            Controller of Examinations
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTranscriptModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="printTranscript()">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Print Official Transcript</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Dynamic Island Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Client Script -->
    <script src="assets/app.js"></script>
</body>
</html>
