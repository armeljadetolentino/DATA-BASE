<?php
require 'config.php';
$pageTitle = 'Dashboard';
$counts = [];
foreach (['Students','Programs','Subjects','Terms','Sections','Enrollments'] as $table) {
    $counts[$table] = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
$recent = $pdo->query("
  SELECT e.enrollment_id, e.enrollment_date, e.status,
         s.student_number, s.first_name, s.last_name,
         t.academic_year, t.semester
  FROM Enrollments e
  JOIN Students s ON s.student_id=e.student_id
  JOIN Terms t ON t.term_id=e.term_id
  ORDER BY e.enrollment_id DESC LIMIT 8
")->fetchAll();
require 'header.php';
?>
<section class="hero">
  <div><p class="eyebrow">ADMINISTRATION</p><h1>Student Enrollment Dashboard</h1>
  <p>Manage student records, academic offerings, and enrollment transactions in one place.</p></div>
  <a class="btn btn-light" href="enrollments.php?action=new">+ New Enrollment</a>
</section>
<div class="stats-grid">
  <?php foreach ([
    'Students'=>['users','students.php'],
    'Programs'=>['graduation-cap','programs.php'],
    'Subjects'=>['book-open','subjects.php'],
    'Terms'=>['calendar','terms.php'],
    'Sections'=>['layers','sections.php'],
    'Enrollments'=>['clipboard','enrollments.php']
  ] as $label=>$meta): ?>
    <a class="stat-card" href="<?= h($meta[1]) ?>"><span class="stat-icon"><?= h(strtoupper(substr($label,0,1))) ?></span><span class="stat-label"><?= h($label) ?></span><strong><?= $counts[$label] ?></strong><span class="stat-link">Manage <?= h(strtolower($label)) ?> →</span></a>
  <?php endforeach; ?>
</div>
<section class="panel">
  <div class="panel-heading"><div><h2>Recent enrollments</h2><p>Latest saved enrollment records</p></div><a class="text-link" href="enrollments.php">View all →</a></div>
  <?php if (!$recent): ?><div class="empty-state"><h3>No enrollments yet</h3><p>Add a student and academic setup, then create an enrollment.</p><a class="btn" href="enrollments.php?action=new">Create enrollment</a></div>
  <?php else: ?>
  <div class="table-wrap"><table><thead><tr><th>ID</th><th>Student</th><th>Academic term</th><th>Date</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($recent as $r): ?><tr>
    <td>#<?= (int)$r['enrollment_id'] ?></td>
    <td><strong><?= h($r['first_name'].' '.$r['last_name']) ?></strong><small class="subtext"><?= h($r['student_number']) ?></small></td>
    <td><?= h($r['academic_year'].' · '.$r['semester']) ?></td><td><?= h($r['enrollment_date']) ?></td>
    <td><span class="status status-<?= strtolower(h($r['status'])) ?>"><?= h($r['status']) ?></span></td>
  </tr><?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</section>
<?php require 'footer.php'; ?>
