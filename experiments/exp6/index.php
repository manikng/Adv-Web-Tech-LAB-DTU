<?php
/**
 * Experiment 6: Database Connectivity and CRUD Operations
 * Main Application View & Server-Rendered Shell
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$db = Database::getInstance();
$dbStatus = $db->getConnectionStatus();
$stats = $db->getAnalytics();
$initialStudents = $db->readAll('', '', '', 'id', 'DESC');
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumina Academic SIS • PHP & MySQL CRUD Engine | Experiment 06</title>
  <meta name="description" content="Experiment 06: Database Connectivity and CRUD Operations using PHP and MySQL. Designed by Manish Singh (23/CS/244) with full resilient storage, realtime grading analytics, and modern glassmorphic interface.">
  <link rel="stylesheet" href="assets/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220%22%200%22%20100%22%20100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
</head>
<body>
  <div class="app-wrapper">
    <!-- Navbar -->
    <header class="navbar" id="main-navbar">
      <a href="index.php" class="brand" id="brand-link" aria-label="Lumina SIS Home">
        <div class="brand-icon">⚡</div>
        <div class="brand-text">
          <h1><?= htmlspecialchars(APP_NAME) ?></h1>
          <span class="tagline">Exp 06 • PHP & MySQL Database Connectivity & CRUD</span>
        </div>
      </a>

      <div class="nav-actions">
        <!-- DB Status Pill -->
        <button id="db-status-pill" class="db-status-pill <?= $dbStatus['active_driver'] === 'mysql' ? 'mysql-active' : 'sqlite-fallback' ?>" title="Click to view Database Diagnostics">
          <span class="status-dot"></span>
          <?= $dbStatus['active_driver'] === 'mysql' ? 'MySQL (Active)' : 'SQLite Fallback (MySQL Offline)' ?>
        </button>

        <!-- Export Dropdown / Buttons -->
        <a href="api.php?action=export_csv" class="btn btn-secondary" id="export-csv-btn" title="Export Ledger as CSV">
          <span>📊</span> CSV
        </a>
        <a href="api.php?action=export_json" class="btn btn-secondary" id="export-json-btn" title="Export Ledger as JSON">
          <span>📦</span> JSON
        </a>

        <!-- Reset Button -->
        <button class="btn btn-secondary" id="reset-db-btn" title="Reset to Benchmark Records">
          <span>🔄</span> Reset
        </button>

        <!-- Dark/Light Theme Toggle -->
        <button class="btn btn-secondary btn-icon" id="theme-toggle" aria-label="Toggle Dark/Light Mode">
          ☀️
        </button>

        <!-- New Student Button -->
        <button class="btn btn-primary" id="open-add-modal-btn">
          <span>+</span> New Student
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <main>
      <!-- Hero & Student Academic Credits Header -->
      <section class="hero-header" id="hero-section">
        <div class="hero-info">
          <h2>Student Information & Academic CRUD Suite</h2>
          <p class="hero-subtitle">High-performance relational persistence layer utilizing PHP Data Objects (PDO) with prepared statements, strict parameterization, and analytics engine.</p>
          <div class="meta-badges">
            <span class="badge-tag">👤 Student: <?= htmlspecialchars(STUDENT_NAME) ?></span>
            <span class="badge-tag">🆔 Roll No: <?= htmlspecialchars(ROLL_NUMBER) ?></span>
            <span class="badge-tag">📚 <?= htmlspecialchars(SUBJECT_NAME) ?></span>
            <span class="badge-tag">🧪 Experiment <?= htmlspecialchars(EXPERIMENT_NO) ?></span>
          </div>
        </div>
      </section>

      <!-- Key Performance Metrics Grid -->
      <section class="metrics-grid" id="metrics-grid" aria-label="Academic Analytics">
        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-title">Enrolled Scholars</span>
            <div class="metric-icon icon-blue">👥</div>
          </div>
          <div class="metric-value" id="stat-total-students"><?= $stats['total_students'] ?></div>
          <div class="metric-subtitle">Active registered scholars in ledger</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-title">Class Aggregate Avg</span>
            <div class="metric-icon icon-green">📈</div>
          </div>
          <div class="metric-value" id="stat-average-pct"><?= $stats['average_pct'] ?>%</div>
          <div class="metric-subtitle">Mean cumulative score across all courses</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-title">Top Distinction Scorer</span>
            <div class="metric-icon icon-purple">🏆</div>
          </div>
          <div class="metric-value" id="stat-top-scorer" style="font-size: 1.35rem;">
            <?= $stats['top_scorer'] ? htmlspecialchars(explode(' ', $stats['top_scorer']['name'])[0] . ' (' . $stats['top_scorer']['percentage'] . '%)') : 'N/A' ?>
          </div>
          <div class="metric-subtitle">Valedictorian benchmark scholar</div>
        </div>

        <div class="metric-card">
          <div class="metric-header">
            <span class="metric-title">Passing Rate</span>
            <div class="metric-icon icon-pink">🎯</div>
          </div>
          <div class="metric-value" id="stat-pass-rate"><?= $stats['pass_rate'] ?>%</div>
          <div class="metric-subtitle"><?= $stats['passed_count'] ?> of <?= $stats['total_students'] ?> scholars in clear standing</div>
        </div>
      </section>

      <!-- Search, Filtering, and Controls -->
      <section class="control-bar" id="controls-section">
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input type="text" id="search-input" placeholder="Search by student name, roll number, department..." autocomplete="off">
          <span class="search-shortcut">/</span>
        </div>

        <div class="filter-group">
          <select id="semester-filter" class="select-control" aria-label="Filter by Semester">
            <option value="All">All Semesters</option>
            <?php foreach (SEMESTERS as $sem): ?>
              <option value="<?= htmlspecialchars($sem) ?>"><?= htmlspecialchars($sem) ?></option>
            <?php endforeach; ?>
          </select>

          <select id="sort-select" class="select-control" aria-label="Sort Order">
            <option value="id-desc">Newest First</option>
            <option value="id-asc">Oldest First</option>
            <option value="percentage-desc">Highest Percentage</option>
            <option value="percentage-asc">Lowest Percentage</option>
            <option value="name-asc">Name (A-Z)</option>
            <option value="roll_no-asc">Roll No (Asc)</option>
          </select>
        </div>
      </section>

      <!-- Grade Filter Pills -->
      <div class="filter-pills" id="grade-pills">
        <button class="pill-btn active" data-grade="All">All Grades</button>
        <button class="pill-btn" data-grade="A+">Distinction (A+)</button>
        <button class="pill-btn" data-grade="A">First Class (A)</button>
        <button class="pill-btn" data-grade="B+">First Class (B+)</button>
        <button class="pill-btn" data-grade="B">Second Class (B)</button>
        <button class="pill-btn" data-grade="C">Pass Class (C)</button>
        <button class="pill-btn" data-grade="F">Remedial (F)</button>
      </div>

      <!-- Student Records Data Table -->
      <section class="table-container" id="table-section">
        <div class="table-responsive">
          <table id="students-table">
            <thead>
              <tr>
                <th>Scholar Identity</th>
                <th>Roll Number</th>
                <th>Department</th>
                <th>Semester</th>
                <th>Aggregate Score</th>
                <th>GPA</th>
                <th>Standing</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="student-table-body">
              <!-- Rendered via JavaScript and SSR fallback -->
              <?php foreach ($initialStudents as $s): ?>
                <?php 
                  $initials = strtoupper(substr($s['name'], 0, 2));
                  $gradeClass = 'grade-' . str_replace('+', '-plus', $s['grade']);
                ?>
                <tr>
                  <td>
                    <div class="student-info-cell">
                      <div class="avatar-badge"><?= htmlspecialchars($initials) ?></div>
                      <div>
                        <span class="student-name"><?= htmlspecialchars($s['name']) ?></span>
                        <span class="student-email"><?= htmlspecialchars($s['email']) ?></span>
                      </div>
                    </div>
                  </td>
                  <td><span class="roll-tag"><?= htmlspecialchars($s['roll_no']) ?></span></td>
                  <td><span style="font-size: 0.8rem; color: var(--text-secondary);"><?= htmlspecialchars($s['department']) ?></span></td>
                  <td><span style="font-size: 0.8rem;"><?= htmlspecialchars($s['semester']) ?></span></td>
                  <td>
                    <div style="font-weight: 700;"><?= $s['percentage'] ?>%</div>
                    <div class="score-progress">
                      <div class="score-progress-bar" style="width: <?= min(100, $s['percentage']) ?>%;"></div>
                    </div>
                  </td>
                  <td><strong><?= number_format((float)$s['gpa'], 1) ?></strong></td>
                  <td><span class="grade-badge <?= $gradeClass ?>"><?= htmlspecialchars($s['grade']) ?></span></td>
                  <td>
                    <div class="action-buttons">
                      <button class="action-btn" title="View Transcript" onclick="window.LuminaApp.openTranscript(<?= $s['id'] ?>)">📄</button>
                      <button class="action-btn" title="Edit Student" onclick="window.LuminaApp.openEditModal(<?= $s['id'] ?>)">✏️</button>
                      <button class="action-btn btn-delete" title="Delete Student" onclick="window.LuminaApp.openDeleteModal(<?= $s['id'] ?>)">🗑️</button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div id="empty-state" style="display: none; padding: 3rem; text-align: center; color: var(--text-muted);">
          <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔍</div>
          <h3>No Student Records Found</h3>
          <p>Try adjusting your search criteria or register a new scholar.</p>
        </div>
      </section>
    </main>
  </div>

  <!-- Create / Edit Student Modal -->
  <div class="modal-overlay" id="student-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 class="modal-title" id="modal-title">Enroll New Student</h3>
        <button class="close-modal-btn" aria-label="Close Modal">&times;</button>
      </div>
      <form id="student-form">
        <div class="modal-body">
          <input type="hidden" id="student-id" name="id">
          
          <div class="form-row">
            <div class="form-group">
              <label for="roll_no">Roll / Reg Number *</label>
              <input type="text" id="roll_no" name="roll_no" class="form-control" placeholder="e.g. 23/CS/250" required>
            </div>
            <div class="form-group">
              <label for="name">Candidate Full Name *</label>
              <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Priyanshu Varma" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="email">Institutional Email *</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="e.g. p.varma@lumina.edu" required>
            </div>
            <div class="form-group">
              <label for="department">Academic Department</label>
              <select id="department" name="department" class="form-control">
                <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                <option value="Information Technology">Information Technology</option>
                <option value="Artificial Intelligence">Artificial Intelligence</option>
                <option value="Electronics & Communication">Electronics & Communication</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="semester">Academic Semester</label>
            <select id="semester" name="semester" class="form-control">
              <?php foreach (SEMESTERS as $sem): ?>
                <option value="<?= htmlspecialchars($sem) ?>"><?= htmlspecialchars($sem) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="marks-container">
            <label style="margin-bottom: 0.75rem; display: block; font-weight: 700; color: var(--text-primary);">
              Course Examination Scores (Out of 100)
            </label>
            <div class="marks-grid">
              <div class="form-group">
                <label for="maths">Discrete Maths</label>
                <input type="number" id="maths" name="maths" class="form-control" min="0" max="100" step="0.5" value="0">
              </div>
              <div class="form-group">
                <label for="physics">Applied Physics</label>
                <input type="number" id="physics" name="physics" class="form-control" min="0" max="100" step="0.5" value="0">
              </div>
              <div class="form-group">
                <label for="cs">Data Structures</label>
                <input type="number" id="cs" name="cs" class="form-control" min="0" max="100" step="0.5" value="0">
              </div>
              <div class="form-group">
                <label for="web_tech">Adv Web Tech</label>
                <input type="number" id="web_tech" name="web_tech" class="form-control" min="0" max="100" step="0.5" value="0">
              </div>
              <div class="form-group">
                <label for="english">Technical Comm</label>
                <input type="number" id="english" name="english" class="form-control" min="0" max="100" step="0.5" value="0">
              </div>
            </div>

            <!-- Live Calculation Summary -->
            <div class="calc-preview-card">
              <div class="calc-preview-item">
                <div class="label">Total Score</div>
                <div class="val" id="preview-total">0.0</div>
              </div>
              <div class="calc-preview-item">
                <div class="label">Aggregate %</div>
                <div class="val" id="preview-pct">0.0%</div>
              </div>
              <div class="calc-preview-item">
                <div class="label">Computed GPA</div>
                <div class="val" id="preview-gpa">0.0</div>
              </div>
              <div class="calc-preview-item">
                <div class="label">Standing</div>
                <div class="val" id="preview-grade">F</div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-modal-cancel">Cancel</button>
          <button type="submit" class="btn btn-primary" id="save-student-btn">Save Student Record</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Academic Transcript Modal -->
  <div class="modal-overlay" id="transcript-modal">
    <div class="modal-card" style="max-width: 760px;">
      <div class="modal-header">
        <h3 class="modal-title">Official Scholar Academic Transcript</h3>
        <button class="close-modal-btn" aria-label="Close Modal">&times;</button>
      </div>
      <div class="modal-body" id="transcript-content">
        <!-- Injected via JS -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-modal-cancel">Close</button>
        <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Transcript</button>
      </div>
    </div>
  </div>

  <!-- Database Connection Diagnostics Modal -->
  <div class="modal-overlay" id="db-modal">
    <div class="modal-card">
      <div class="modal-header">
        <h3 class="modal-title">Database Engine Diagnostics</h3>
        <button class="close-modal-btn" aria-label="Close Modal">&times;</button>
      </div>
      <div class="modal-body" id="db-details-content">
        <!-- Injected via JS -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-modal-cancel">Close</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal-overlay" id="delete-modal">
    <div class="modal-card" style="max-width: 440px;">
      <div class="modal-header">
        <h3 class="modal-title">Confirm Record Deletion</h3>
        <button class="close-modal-btn" aria-label="Close Modal">&times;</button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the student record for <strong id="delete-student-name"></strong>?</p>
        <p style="font-size: 0.8rem; color: var(--danger); margin-top: 0.5rem;">⚠️ This action executes a SQL DELETE statement and cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-modal-cancel">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirm-delete-btn">Permanently Delete</button>
      </div>
    </div>
  </div>

  <!-- Toast Notification Stack -->
  <div class="toast-container" id="toast-container"></div>

  <script src="assets/app.js"></script>
</body>
</html>