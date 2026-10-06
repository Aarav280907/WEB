<?php
/**
 * StudentHub Portal - Student Registration Processor
 * Server-Side Form Processing with Validation and CSV/JSON File Storage
 */

// Define allowed courses, years, and genders for validation
$allowedCourses = [
    'Computer Engineering',
    'Information Technology',
    'AI & Data Science',
    'Electronics & Communication',
    'Mechanical Engineering',
    'Civil Engineering'
];

$allowedYears = [
    '1st Year (Semester 1/2)',
    '2nd Year (Semester 3/4)',
    '3rd Year (Semester 5/6)',
    '4th Year (Semester 7/8)',
    '1st Year',
    '2nd Year',
    '3rd Year',
    '4th Year'
];

$allowedGenders = ['Male', 'Female', 'Other'];

// State variables
$errors = [];
$isSuccess = false;
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

// Initial/default sanitized values
$fullname = '';
$student_id = '';
$email = '';
$mobile = '';
$course = '';
$year = '';
$gender = '';
$termsAccepted = false;
$recordData = null;

if ($isPost) {
    // 1. Sanitize Inputs
    $fullname = trim(strip_tags($_POST['fullname'] ?? $_POST['name'] ?? ''));
    $student_id = trim(strtoupper(strip_tags($_POST['student_id'] ?? $_POST['username'] ?? '')));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $mobile = trim(preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? ''));
    $course = trim(strip_tags($_POST['course'] ?? ''));
    $year = trim(strip_tags($_POST['year'] ?? ''));
    $gender = trim(strip_tags($_POST['gender'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? $_POST['confirm-password'] ?? '';
    $termsAccepted = isset($_POST['terms']) && in_array(strtolower($_POST['terms']), ['1', 'on', 'yes', 'true'], true);

    // 2. Server-Side Validation
    // Full Name
    if (empty($fullname)) {
        $errors[] = 'Full Name is required.';
    } elseif (strlen($fullname) < 3 || strlen($fullname) > 60) {
        $errors[] = 'Full Name must be between 3 and 60 characters.';
    } elseif (!preg_match("/^[a-zA-Z\s\.\-']+$/", $fullname)) {
        $errors[] = 'Full Name can only contain letters, spaces, dots, and hyphens.';
    }

    // Student ID / Roll Number
    if (empty($student_id)) {
        $errors[] = 'Student ID / Roll Number is required.';
    } elseif (!preg_match('/^[A-Z0-9]{5,15}$/', $student_id)) {
        $errors[] = 'Student ID must be 5 to 15 alphanumeric characters.';
    }

    // University Email
    if (empty($email)) {
        $errors[] = 'University Email Address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Mobile Number
    if (empty($mobile)) {
        $errors[] = 'Mobile Number is required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = 'Mobile Number must be exactly 10 digits.';
    }

    // Course
    if (empty($course) || !in_array($course, $allowedCourses, true)) {
        $errors[] = 'Please select a valid engineering department / course.';
    }

    // Year
    if (empty($year) || !in_array($year, $allowedYears, true)) {
        $errors[] = 'Please select a valid academic year.';
    }

    // Gender
    if (empty($gender) || !in_array($gender, $allowedGenders, true)) {
        $errors[] = 'Please select your gender.';
    }

    // Password
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    // Confirm Password
    if (empty($confirm_password)) {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match. Please re-enter identical passwords.';
    }

    // Terms Acceptance
    if (!$termsAccepted) {
        $errors[] = 'You must agree to the Student Portal Academic Terms and University Guidelines.';
    }

    // 3. Process & Store Data if Valid
    if (empty($errors)) {
        $registration_date = date('Y-m-d H:i:s');
        $jsonFile = __DIR__ . '/registrations.json';
        $csvFile = __DIR__ . '/registrations.csv';

        // Load existing records to determine incremental ID
        $existingRecords = [];
        if (file_exists($jsonFile)) {
            $jsonRaw = file_get_contents($jsonFile);
            if (!empty($jsonRaw)) {
                $decoded = json_decode($jsonRaw, true);
                if (is_array($decoded)) {
                    $existingRecords = $decoded;
                }
            }
        }

        $newId = count($existingRecords) + 1;

        $recordData = [
            'id' => $newId,
            'fullname' => $fullname,
            'student_id' => $student_id,
            'email' => $email,
            'mobile' => $mobile,
            'course' => $course,
            'year' => $year,
            'gender' => $gender,
            'registration_date' => $registration_date
        ];

        // Safe JSON File Storage
        $existingRecords[] = $recordData;
        $jsonSaved = @file_put_contents(
            $jsonFile,
            json_encode($existingRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );

        // Safe CSV File Storage
        $csvSaved = false;
        $isNewCsv = !file_exists($csvFile) || (filesize($csvFile) === 0);
        $fp = @fopen($csvFile, 'a');
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                if ($isNewCsv) {
                    fputcsv($fp, ['id', 'fullname', 'student_id', 'email', 'mobile', 'course', 'year', 'gender', 'registration_date']);
                }
                fputcsv($fp, [
                    $newId,
                    $fullname,
                    $student_id,
                    $email,
                    $mobile,
                    $course,
                    $year,
                    $gender,
                    $registration_date
                ]);
                flock($fp, LOCK_UN);
                $csvSaved = true;
            }
            fclose($fp);
        }

        if ($jsonSaved !== false && $csvSaved) {
            $isSuccess = true;
        } else {
            $errors[] = 'A storage file-write error occurred. Please check folder permissions and try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $isSuccess ? 'Registration Successful' : 'Student Registration'; ?> - StudentHub Portal</title>
  <link rel="icon" type="image/svg+xml" href="../for_css/extra/favicon.svg">
  <link rel="stylesheet" href="../for_css/common.css">
  <link rel="stylesheet" href="../for_css/signup.css">
  <style>
    .alert-box {
      padding: 16px 20px;
      border-radius: var(--radius-md);
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      font-size: 0.92rem;
      line-height: 1.5;
    }
    .alert-danger {
      background: var(--danger-bg);
      border: 1px solid var(--danger-border);
      color: var(--danger-text);
    }
    .alert-danger ul {
      margin: 4px 0 0 18px;
      padding: 0;
    }
    .alert-danger li {
      margin-bottom: 4px;
    }
    .alert-success {
      background: var(--success-bg);
      border: 1px solid var(--success-border);
      color: var(--success-text);
    }
    .success-summary-card {
      background: var(--bg-subsurface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 20px;
      margin: 20px 0;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid var(--border-subtle);
      font-size: 0.92rem;
    }
    .summary-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    .summary-lbl {
      color: var(--text-faint);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.78rem;
      letter-spacing: 0.03em;
    }
    .summary-val {
      font-weight: 700;
      color: var(--text-main);
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    @media (max-width: 600px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }
    .radio-group {
      display: flex;
      align-items: center;
      gap: 18px;
      margin-top: 6px;
    }
    .radio-label {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.9rem;
      cursor: pointer;
      color: var(--text-main);
      font-weight: 500;
    }
    .checkbox-label {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 0.88rem;
      cursor: pointer;
      color: var(--text-muted);
      margin-top: 12px;
      line-height: 1.4;
    }
    .checkbox-label input[type="checkbox"] {
      margin-top: 3px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <!-- Header -->
  <header>
    <div class="header-inner">
      <a href="../Html folder/home.html" class="header-brand-link" aria-label="StudentHub Home">
        <img src="../for_css/extra/logo.svg" alt="StudentHub Logo" class="portal-logo">
      </a>
      
      <div class="header-actions">
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark/light theme" title="Toggle Theme">
          <span class="theme-icon" id="theme-icon">🌙</span>
          <span class="theme-text" id="theme-text">Dark</span>
        </button>

        <button id="hamburger-btn" class="hamburger-btn" aria-label="Toggle navigation menu" aria-expanded="false">
          <span class="bar"></span>
          <span class="bar"></span>
          <span class="bar"></span>
        </button>
      </div>
    </div>

    <div class="header-inner" style="padding-top: 0; padding-bottom: 8px;">
      <nav aria-label="Primary Navigation">
        <a href="../Html folder/home.html">🏠 Home</a>
        <a href="../Html folder/dashboard.html">📊 Dashboard</a>
        <a href="../Html folder/attendance.html">📈 Attendance</a>
        <a href="../Html folder/timetable.html">🗓️ Timetable</a>
        <a href="../Html folder/fees.html">💳 Fees</a>
        <a href="../Html folder/announcement.html">📢 Announcements</a>
        <a href="../Html folder/profile.html">👤 Profile</a>
        <a href="../Html folder/aboutus.html">ℹ️ About Us</a>
        <a href="../Html folder/contact.html">📞 Contact</a>
        <a href="../Html folder/login.html">🔑 Login</a>
        <a href="../Html folder/signup.html" class="active">📝 Register</a>
      </nav>
    </div>
  </header>

  <main id="main-content" class="auth-main">
    <div class="auth-card" style="max-width: 580px;">

      <?php if ($isSuccess && $recordData): ?>
        <!-- ================= SUCCESS VIEW ================= -->
        <div class="auth-header">
          <img src="../for_css/extra/logo.svg" alt="StudentHub Logo" class="auth-logo">
          <h2 style="color: var(--success);">🎉 Registration Completed!</h2>
          <p>Your student account record has been successfully validated and stored.</p>
        </div>

        <div class="alert-box alert-success" role="status">
          <strong>✅ Registration Record Stored</strong>
          <span>Your profile information has been saved into university student registration records (CSV & JSON storage).</span>
        </div>

        <div class="success-summary-card">
          <div class="summary-row">
            <span class="summary-lbl">Record ID</span>
            <span class="summary-val">#<?php echo htmlspecialchars((string)$recordData['id']); ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">Full Name</span>
            <span class="summary-val"><?php echo htmlspecialchars($recordData['fullname']); ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">Student ID / Roll No</span>
            <span class="summary-val"><span class="badge badge-info"><?php echo htmlspecialchars($recordData['student_id']); ?></span></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">University Email</span>
            <span class="summary-val"><?php echo htmlspecialchars($recordData['email']); ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">Mobile Number</span>
            <span class="summary-val"><?php echo htmlspecialchars($recordData['mobile']); ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">Department / Course</span>
            <span class="summary-val"><?php echo htmlspecialchars($recordData['course']); ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">Academic Year</span>
            <span class="summary-val"><?php echo htmlspecialchars($recordData['year']); ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">Gender</span>
            <span class="summary-val"><?php echo htmlspecialchars($recordData['gender']); ?></span>
          </div>
          <div class="summary-row">
            <span class="summary-lbl">Timestamp</span>
            <span class="summary-val" style="font-size: 0.85rem;"><?php echo htmlspecialchars($recordData['registration_date']); ?></span>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
          <a href="../Html folder/login.html" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">
            🔑 Proceed to Student Login
          </a>
          <a href="../Html folder/signup.html" class="btn btn-outline" style="width: 100%; padding: 10px;">
            📝 Register Another Student
          </a>
          <a href="../Html folder/home.html" class="btn btn-secondary" style="width: 100%; padding: 10px;">
            🏠 Return to StudentHub Portal
          </a>
        </div>

      <?php else: ?>
        <!-- ================= REGISTRATION FORM VIEW ================= -->
        <div class="auth-header">
          <img src="../for_css/extra/logo.svg" alt="StudentHub Logo" class="auth-logo">
          <h2>Create Student Account</h2>
          <p>Register with your official university credentials to access StudentHub</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert-box alert-danger" role="alert">
            <strong>⚠️ Please correct the following errors:</strong>
            <ul>
              <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="process_signup.php" method="POST" class="auth-form" novalidate>
          <fieldset style="border: none; padding: 0; margin: 0;">
            <legend style="display: none;">Student Registration Form</legend>

            <div class="form-group">
              <label for="fullname">Full Name:</label>
              <input 
                type="text" 
                id="fullname" 
                name="fullname" 
                required 
                placeholder="Enter full name" 
                value="<?php echo htmlspecialchars($fullname); ?>"
                autocomplete="name"
              >
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="student_id">Student ID / Roll Number:</label>
                <input 
                  type="text" 
                  id="student_id" 
                  name="student_id" 
                  required 
                  placeholder="Enter student ID" 
                  value="<?php echo htmlspecialchars($student_id); ?>"
                  autocomplete="username"
                >
              </div>

              <div class="form-group">
                <label for="mobile">Mobile Number (10 Digits):</label>
                <input 
                  type="tel" 
                  id="mobile" 
                  name="mobile" 
                  required 
                  placeholder="Enter mobile number" 
                  value="<?php echo htmlspecialchars($mobile); ?>"
                  autocomplete="tel"
                  maxlength="10"
                >
              </div>
            </div>

            <div class="form-group">
              <label for="email">University Email Address:</label>
              <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                placeholder="Enter email address" 
                value="<?php echo htmlspecialchars($email); ?>"
                autocomplete="email"
              >
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="course">Department / Course:</label>
                <select id="course" name="course" class="filter-select" required>
                  <option value="">-- Select Course --</option>
                  <?php foreach ($allowedCourses as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($course === $c) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($c); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="year">Academic Year:</label>
                <select id="year" name="year" class="filter-select" required>
                  <option value="">-- Select Year --</option>
                  <?php 
                  $yearOptions = [
                      '1st Year (Semester 1/2)',
                      '2nd Year (Semester 3/4)',
                      '3rd Year (Semester 5/6)',
                      '4th Year (Semester 7/8)'
                  ];
                  foreach ($yearOptions as $y): ?>
                    <option value="<?php echo htmlspecialchars($y); ?>" <?php echo ($year === $y || $year === explode(' ', $y)[0] . ' ' . explode(' ', $y)[1]) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($y); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Gender:</label>
              <div class="radio-group">
                <label class="radio-label">
                  <input type="radio" name="gender" value="Male" <?php echo ($gender === 'Male') ? 'checked' : ''; ?> required>
                  <span>Male</span>
                </label>
                <label class="radio-label">
                  <input type="radio" name="gender" value="Female" <?php echo ($gender === 'Female') ? 'checked' : ''; ?>>
                  <span>Female</span>
                </label>
                <label class="radio-label">
                  <input type="radio" name="gender" value="Other" <?php echo ($gender === 'Other') ? 'checked' : ''; ?>>
                  <span>Other</span>
                </label>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="password">Create Password:</label>
                <input 
                  type="password" 
                  id="password" 
                  name="password" 
                  required 
                  autocomplete="new-password" 
                  placeholder="Enter password"
                >
              </div>

              <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input 
                  type="password" 
                  id="confirm_password" 
                  name="confirm_password" 
                  required 
                  placeholder="Confirm password"
                >
              </div>
            </div>

            <label class="checkbox-label">
              <input type="checkbox" name="terms" value="1" <?php echo $termsAccepted ? 'checked' : ''; ?> required>
              <span>I agree to the <strong>StudentHub Academic Terms</strong> and university guidelines.</span>
            </label>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; margin-top: 16px;">
              📝 Complete Registration
            </button>
          </fieldset>
        </form>

        <div class="auth-footer-links">
          <p>Already registered? <a href="../Html folder/login.html">Sign in to your account here &rarr;</a></p>
          <p style="margin-top: 6px;"><a href="../Html folder/home.html">Or return to Home &rarr;</a></p>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <footer class="portal-footer">
    <div class="footer-bottom" style="border-top: none; padding: 0; justify-content: center; text-align: center;">
      <p>&copy; 2026 StudentHub Portal — Charotar University of Science and Technology (CHARUSAT)</p>
    </div>
  </footer>

  <script src="../javascript/theme.js"></script>
  <script src="../javascript/navigation.js"></script>
  <script src="../javascript/notification.js"></script>
  <script src="../javascript/faq.js"></script>
  <script src="../javascript/modal.js"></script>
  <script src="../javascript/slider.js"></script>
  <script src="../javascript/main.js"></script>
</body>
</html>
