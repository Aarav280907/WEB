<?php
/**
 * StudentHub Portal - Contact Inquiry Processor
 * Server-Side Form Processing with Validation and CSV/JSON File Storage
 */

$errors = [];
$isSuccess = false;
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

$name = '';
$email = '';
$subject = '';
$message = '';
$recordData = null;

$allowedSubjects = [
    'attendance' => 'Attendance Discrepancy Query',
    'exam' => 'Examination & Seating Inquiries',
    'fees' => 'Fee Payment & Receipt Status',
    'technical' => 'Portal Technical Assistance',
    'other' => 'General Academic Question'
];

if ($isPost) {
    // 1. Sanitize
    $name = trim(strip_tags($_POST['name'] ?? ''));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $subjectKey = trim($_POST['subject'] ?? '');
    $message = trim(strip_tags($_POST['message'] ?? ''));

    // 2. Validate
    if (empty($name)) {
        $errors[] = 'Full Name is required.';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Full Name must be at least 3 characters.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($subjectKey) || !array_key_exists($subjectKey, $allowedSubjects)) {
        $errors[] = 'Please select a valid inquiry topic.';
    }

    if (empty($message)) {
        $errors[] = 'Message / Query description is required.';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters long.';
    }

    // 3. Store if valid
    if (empty($errors)) {
        $subjectTitle = $allowedSubjects[$subjectKey];
        $submitted_at = date('Y-m-d H:i:s');
        $jsonFile = __DIR__ . '/contacts.json';
        $csvFile = __DIR__ . '/contacts.csv';

        $existing = [];
        if (file_exists($jsonFile)) {
            $raw = file_get_contents($jsonFile);
            if (!empty($raw)) {
                $dec = json_decode($raw, true);
                if (is_array($dec)) $existing = $dec;
            }
        }

        $newId = count($existing) + 1;
        $recordData = [
            'id' => $newId,
            'name' => $name,
            'email' => $email,
            'subject' => $subjectTitle,
            'message' => $message,
            'submitted_at' => $submitted_at
        ];

        $existing[] = $recordData;
        $jsonSaved = @file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

        $isNew = !file_exists($csvFile) || (filesize($csvFile) === 0);
        $fp = @fopen($csvFile, 'a');
        $csvSaved = false;
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                if ($isNew) {
                    fputcsv($fp, ['id', 'name', 'email', 'subject', 'message', 'submitted_at']);
                }
                fputcsv($fp, [$newId, $name, $email, $subjectTitle, $message, $submitted_at]);
                flock($fp, LOCK_UN);
                $csvSaved = true;
            }
            fclose($fp);
        }

        if ($jsonSaved !== false && $csvSaved) {
            $isSuccess = true;
        } else {
            $errors[] = 'A file storage error occurred while logging your message.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $isSuccess ? 'Inquiry Submitted' : 'Contact Support'; ?> - StudentHub Portal</title>
  <link rel="icon" type="image/svg+xml" href="../for_css/extra/favicon.svg">
  <link rel="stylesheet" href="../for_css/common.css">
  <link rel="stylesheet" href="../for_css/contact.css">
  <style>
    .alert-box {
      padding: 16px 20px;
      border-radius: var(--radius-md);
      margin-bottom: 20px;
      font-size: 0.92rem;
      line-height: 1.5;
    }
    .alert-danger {
      background: var(--danger-bg);
      border: 1px solid var(--danger-border);
      color: var(--danger-text);
    }
    .alert-danger ul {
      margin: 6px 0 0 18px;
      padding: 0;
    }
    .alert-success {
      background: var(--success-bg);
      border: 1px solid var(--success-border);
      color: var(--success-text);
    }
  </style>
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

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
      </div>
    </div>
  </header>

  <main id="main-content" class="contact-main" style="max-width: 800px; margin: 30px auto; padding: 20px;">
    <div class="portal-page-header">
      <div class="header-info">
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <a href="../Html folder/home.html">Home</a>
          <span class="breadcrumb-separator">/</span>
          <a href="../Html folder/contact.html">Contact Us</a>
          <span class="breadcrumb-separator">/</span>
          <span>Inquiry Status</span>
        </nav>
        <h2>📞 Support Inquiry Response</h2>
        <p>University Helpdesk Server-Side Processing</p>
      </div>
    </div>

    <?php if ($isSuccess && $recordData): ?>
      <div class="card" style="padding: 28px;">
        <div class="alert-box alert-success">
          <strong>✅ Inquiry Submitted Successfully!</strong>
          <p style="margin: 4px 0 0 0;">Your ticket (#<?php echo htmlspecialchars((string)$recordData['id']); ?>) has been logged into the StudentHub support repository (CSV & JSON storage).</p>
        </div>

        <div style="background: var(--bg-subsurface); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 16px; margin: 16px 0;">
          <p><strong>Name:</strong> <?php echo htmlspecialchars($recordData['name']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($recordData['email']); ?></p>
          <p><strong>Topic:</strong> <?php echo htmlspecialchars($recordData['subject']); ?></p>
          <p><strong>Message:</strong> <?php echo htmlspecialchars($recordData['message']); ?></p>
          <p style="font-size: 0.82rem; color: var(--text-faint); margin-top: 8px;">Logged on: <?php echo htmlspecialchars($recordData['submitted_at']); ?></p>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 20px;">
          <a href="../Html folder/contact.html" class="btn btn-primary">Send Another Query</a>
          <a href="../Html folder/home.html" class="btn btn-secondary">Return to Home</a>
        </div>
      </div>
    <?php else: ?>
      <div class="card" style="padding: 28px;">
        <?php if (!empty($errors)): ?>
          <div class="alert-box alert-danger">
            <strong>⚠️ Inquiry Validation Errors:</strong>
            <ul>
              <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <a href="../Html folder/contact.html" class="btn btn-primary">Back to Contact Form</a>
      </div>
    <?php endif; ?>
  </main>

  <footer class="portal-footer">
    <div class="footer-bottom" style="border-top: none; padding: 0; justify-content: center; text-align: center;">
      <p>&copy; 2026 StudentHub Portal — Charotar University of Science and Technology (CHARUSAT)</p>
    </div>
  </footer>

  <script src="../javascript/theme.js"></script>
</body>
</html>
