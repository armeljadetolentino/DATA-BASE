<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';
 try{
  if($action==='save'){$id=(int)($_POST['section_id']??0);$name=trim($_POST['section_name']??'');$subject=(int)($_POST['subject_id']??0);$term=(int)($_POST['term_id']??0);$capacity=(int)($_POST['capacity']??40);if(!$name||!$subject||!$term||$capacity<1)throw new RuntimeException('Section name, subject, term, and positive capacity are required.');
   if($id){$st=$pdo->prepare('UPDATE Sections SET section_name=?,subject_id=?,term_id=?,capacity=? WHERE section_id=?');$st->execute([$name,$subject,$term,$capacity,$id]);flash('Section updated.');}
   else{$st=$pdo->prepare('INSERT INTO Sections(section_name,subject_id,term_id,capacity) VALUES(?,?,?,?)');$st->execute([$name,$subject,$term,$capacity]);flash('Section added.');}
  }elseif($action==='delete'){$st=$pdo->prepare('DELETE FROM Sections WHERE section_id=?');$st->execute([(int)$_POST['section_id']]);flash('Section deleted.');}
 }catch(Throwable $e){flash($e instanceof PDOException?'Could not save/delete. Section may already exist or be used in an enrollment.':$e->getMessage(),'error');}
 redirect('sections.php');
}
$subjects=$pdo->query('SELECT * FROM Subjects ORDER BY subject_code')->fetchAll();$terms=$pdo->query('SELECT * FROM Terms ORDER BY academic_year DESC,term_id DESC')->fetchAll();
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM Sections WHERE section_id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$rows=$pdo->query('SELECT sec.*,sub.subject_code,sub.subject_name,t.academic_year,t.semester,(SELECT COUNT(*) FROM EnrollmentDetails ed JOIN Enrollments e ON e.enrollment_id=ed.enrollment_id WHERE ed.section_id=sec.section_id AND e.status <> "Cancelled") enrolled FROM Sections sec JOIN Subjects sub ON sub.subject_id=sec.subject_id JOIN Terms t ON t.term_id=sec.term_id ORDER BY sec.section_id DESC')->fetchAll();
$pageTitle='Sections';require 'header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ACADEMIC SETUP</p><h1>Sections</h1><p>Offer subjects in sections for specific academic terms.</p></div></div>
<div class="content-grid">
<section class="panel"><div class="panel-heading"><h2><?= $edit?'Edit section':'Add section' ?></h2></div>
<?php if(!$subjects||!$terms): ?><div class="alert error">Create at least one subject and one academic term first.</div><?php endif; ?>
<form method="post" class="form-grid one-col"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="section_id" value="<?= (int)($edit['section_id']??0) ?>">
<label>Section name *<input required maxlength="30" name="section_name" value="<?= h($edit['section_name']??'') ?>" placeholder="e.g. BSIT-2C"></label>
<label>Subject *<select required name="subject_id"><option value="">Choose subject</option><?php foreach($subjects as $s): ?><option value="<?= (int)$s['subject_id'] ?>" <?= (int)($edit['subject_id']??0)===(int)$s['subject_id']?'selected':'' ?>><?= h($s['subject_code'].' — '.$s['subject_name']) ?></option><?php endforeach; ?></select></label>
<label>Academic term *<select required name="term_id"><option value="">Choose term</option><?php foreach($terms as $t): ?><option value="<?= (int)$t['term_id'] ?>" <?= (int)($edit['term_id']??0)===(int)$t['term_id']?'selected':'' ?>><?= h($t['academic_year'].' — '.$t['semester']) ?></option><?php endforeach; ?></select></label>
<label>Capacity *<input required type="number" min="1" max="500" name="capacity" value="<?= h($edit['capacity']??40) ?>"></label>
<div class="form-actions"><button class="btn" <?= (!$subjects||!$terms)?'disabled':'' ?>><?= $edit?'Save changes':'Add section' ?></button><?php if($edit): ?><a class="btn btn-outline" href="sections.php">Cancel</a><?php endif; ?></div></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Section list</h2><p><?= count($rows) ?> section(s)</p></div></div><div class="table-wrap"><table><thead><tr><th>Section / subject</th><th>Term</th><th>Capacity</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?= h($r['section_name']) ?></strong><small class="subtext"><?= h($r['subject_code'].' — '.$r['subject_name']) ?></small></td><td><?= h($r['academic_year'].' · '.$r['semester']) ?></td><td><?= (int)$r['enrolled'] ?> / <?= (int)$r['capacity'] ?></td><td class="actions"><a class="text-link" href="?edit=<?= (int)$r['section_id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this section?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="section_id" value="<?= (int)$r['section_id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4" class="muted">No sections yet.</td></tr><?php endif; ?></tbody></table></div></section>
</div><?php require 'footer.php'; ?>
