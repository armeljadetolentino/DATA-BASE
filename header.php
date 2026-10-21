<?php
$pageTitle = $pageTitle ?? 'Student Enrollment System';
$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($pageTitle) ?> | Student Enrollment System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-icon">SE</span><span>Student Enrollment<small>Management System</small></span></a>
  <button class="menu-toggle" type="button" onclick="document.querySelector('.nav').classList.toggle('open')">☰ Menu</button>
  <nav class="nav">
    <a href="index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="programs.php">Programs</a>
    <a href="subjects.php">Subjects</a>
    <a href="terms.php">Terms</a>
    <a href="sections.php">Sections</a>
    <a href="enrollments.php">Enrollments</a>
  </nav>
</header>
<main class="container">
<?php if ($flashMessage): ?>
  <div class="alert <?= h($flashMessage['type']) ?>"><?= h($flashMessage['message']) ?></div>
<?php endif; ?>
