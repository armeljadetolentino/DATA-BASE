<?php
require 'config.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';
 try{
  if($action==='create'){
   $student=(int)($_POST['student_id']??0);$term=(int)($_POST['term_id']??0);$sections=array_values(array_unique(array_map('intval',$_POST['section_ids']??[])));
   if(!$student||!$term||!$sections)throw new RuntimeException('Choose a student, academic term, and at least one section.');
   $pdo->beginTransaction();
   $st=$pdo->prepare('INSERT INTO Enrollments(student_id,term_id,enrollment_date,status) VALUES(?,?,CURDATE(),"Pending")');
   $st->execute([$student,$term]);$enrollmentId=(int)$pdo->lastInsertId();
   $check=$pdo->prepare('SELECT section_id,term_id,capacity FROM Sections WHERE section_id=? FOR UPDATE');
   $count=$pdo->prepare("SELECT COUNT(*) FROM EnrollmentDetails ed JOIN Enrollments e ON e.enrollment_id=ed.enrollment_id WHERE ed.section_id=? AND e.status <> 'Cancelled'");
   $ins=$pdo->prepare('INSERT INTO EnrollmentDetails(enrollment_id,section_id) VALUES(?,?)');
   foreach($sections as $sectionId){
    $check->execute([$sectionId]);$sec=$check->fetch();
    if(!$sec || (int)$sec['term_id']!==$term)throw new RuntimeException('All chosen sections must belong to the selected academic term.');
    $count->execute([$sectionId]);if((int)$count->fetchColumn()>=(int)$sec['capacity'])throw new RuntimeException('A selected section has reached capacity.');
    $ins->execute([$enrollmentId,$sectionId]);
   }
   $pdo->commit();flash('Enrollment created successfully.');
  }elseif($action==='status'){
   $id=(int)($_POST['enrollment_id']??0);$status=$_POST['status']??'Pending';
   if(!in_array($status,['Pending','Enrolled','Cancelled'],true))throw new RuntimeException('Invalid enrollment status.');
   $st=$pdo->prepare('UPDATE Enrollments SET status=? WHERE enrollment_id=?');$st->execute([$status,$id]);flash('Enrollment status updated.');
  }elseif($action==='delete'){
   $st=$pdo->prepare('DELETE FROM Enrollments WHERE enrollment_id=?');$st->execute([(int)$_POST['enrollment_id']]);flash('Enrollment and its details deleted.');
  }
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash($e instanceof PDOException?'Could not complete the operation. The student may already be enrolled for this term, or a duplicate section was selected.':$e->getMessage(),'error');}
 redirect('enrollments.php');
}
$students=$pdo->query('SELECT student_id,student_number,first_name,last_name FROM Students ORDER BY last_name,first_name')->fetchAll();
$terms=$pdo->query('SELECT * FROM Terms ORDER BY academic_year DESC,term_id DESC')->fetchAll();
$sections=$pdo->query('SELECT sec.section_id,sec.section_name,sec.term_id,sec.capacity,sub.subject_code,sub.subject_name,sub.units,t.academic_year,t.semester,(SELECT COUNT(*) FROM EnrollmentDetails ed JOIN Enrollments e ON e.enrollment_id=ed.enrollment_id WHERE ed.section_id=sec.section_id AND e.status <> "Cancelled") enrolled FROM Sections sec JOIN Subjects sub ON sub.subject_id=sec.subject_id JOIN Terms t ON t.term_id=sec.term_id ORDER BY sec.section_name,sub.subject_code')->fetchAll();
$rows=$pdo->query("SELECT e.*,s.student_number,s.first_name,s.last_name,t.academic_year,t.semester,
 (SELECT GROUP_CONCAT(CONCAT(sub.subject_code,' (',sec.section_name,')') ORDER BY sub.subject_code SEPARATOR ', ') FROM EnrollmentDetails ed JOIN Sections sec ON sec.section_id=ed.section_id JOIN Subjects sub ON sub.subject_id=sec.subject_id WHERE ed.enrollment_id=e.enrollment_id) subjects
 FROM Enrollments e JOIN Students s ON s.student_id=e.student_id JOIN Terms t ON t.term_id=e.term_id ORDER BY e.enrollment_id DESC")->fetchAll();
$pageTitle='Enrollments';require 'header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ENROLLMENT PROCESSING</p><h1>Enrollments</h1><p>Create student enrollment records and manage their status.</p></div></div>
<div class="content-grid">
<section class="panel"><div class="panel-heading"><div><h2>New enrollment</h2><p>Choose the student, term, and one or more sections.</p></div></div>
<?php if(!$students||!$terms||!$sections): ?><div class="alert error">Before enrolling, add a student, an academic term, and sections.</div><?php endif; ?>
<form method="post" class="form-grid one-col" id="enrollment-form"><?= csrf_field() ?><input type="hidden" name="action" value="create">
<label>Student *<select required name="student_id"><option value="">Choose student</option><?php foreach($students as $s): ?><option value="<?= (int)$s['student_id'] ?>"><?= h($s['student_number'].' — '.$s['last_name'].', '.$s['first_name']) ?></option><?php endforeach; ?></select></label>
<label>Academic term *<select required name="term_id" id="term-select"><option value="">Choose term</option><?php foreach($terms as $t): ?><option value="<?= (int)$t['term_id'] ?>"><?= h($t['academic_year'].' — '.$t['semester']) ?></option><?php endforeach; ?></select></label>
<fieldset class="section-picker"><legend>Available subject sections *</legend><?php foreach($sections as $s): ?><label class="check-row" data-term="<?= (int)$s['term_id'] ?>"><input type="checkbox" name="section_ids[]" value="<?= (int)$s['section_id'] ?>" <?= (int)$s['enrolled'] >= (int)$s['capacity']?'disabled':'' ?>><span><strong><?= h($s['subject_code'].' — '.$s['subject_name']) ?></strong><small><?= h($s['section_name'].' · '.$s['academic_year'].' '.$s['semester'].' · '.$s['units'].' units') ?></small><small><?= (int)$s['enrolled'] ?> / <?= (int)$s['capacity'] ?> seats used</small></span></label><?php endforeach; ?></fieldset>
<div class="form-actions"><button class="btn" <?= (!$students||!$terms||!$sections)?'disabled':'' ?>>Create enrollment</button></div></form>
</section>
<section class="panel"><div class="panel-heading"><div><h2>Enrollment records</h2><p><?= count($rows) ?> enrollment(s)</p></div></div>
<div class="table-wrap"><table><thead><tr><th>Student / term</th><th>Sections</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?= h($r['last_name'].', '.$r['first_name']) ?></strong><small class="subtext"><?= h($r['student_number']) ?></small><small class="subtext"><?= h($r['academic_year'].' · '.$r['semester']) ?></small><small class="subtext">Created <?= h($r['enrollment_date']) ?></small></td><td><?= h($r['subjects']?:'No sections') ?></td><td><form method="post" class="status-form"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="enrollment_id" value="<?= (int)$r['enrollment_id'] ?>"><select name="status" aria-label="Enrollment status"><?php foreach(['Pending','Enrolled','Cancelled'] as $st): ?><option <?= $r['status']===$st?'selected':'' ?>><?= h($st) ?></option><?php endforeach; ?></select><button class="btn btn-small">Save</button></form></td><td><form method="post" onsubmit="return confirm('Delete this enrollment and its details?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="enrollment_id" value="<?= (int)$r['enrollment_id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4" class="muted">No enrollment records yet.</td></tr><?php endif; ?>
</tbody></table></div></section>
</div>
<script>
const termSelect = document.getElementById('term-select');
function filterSections() {
  const term = termSelect.value;
  document.querySelectorAll('.check-row').forEach(row => {
    const visible = term !== '' && row.dataset.term === term;
    row.hidden = !visible;
    if (!visible) row.querySelector('input').checked = false;
  });
}
termSelect.addEventListener('change', filterSections);
filterSections();
</script>
<?php require 'footer.php'; ?>
