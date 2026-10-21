<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';
 try{
  if($action==='save'){$id=(int)($_POST['term_id']??0);$year=trim($_POST['academic_year']??'');$semester=trim($_POST['semester']??'');$start=trim($_POST['start_date']??'');$end=trim($_POST['end_date']??'');if(!preg_match('/^\d{4}-\d{4}$/',$year)||!$semester)throw new RuntimeException('Use academic year format YYYY-YYYY and choose a semester.');if($start&&$end&&$start>$end)throw new RuntimeException('End date must not be earlier than start date.');
   if($id){$st=$pdo->prepare('UPDATE Terms SET academic_year=?,semester=?,start_date=?,end_date=? WHERE term_id=?');$st->execute([$year,$semester,$start?:null,$end?:null,$id]);flash('Academic term updated.');}
   else{$st=$pdo->prepare('INSERT INTO Terms(academic_year,semester,start_date,end_date) VALUES(?,?,?,?)');$st->execute([$year,$semester,$start?:null,$end?:null]);flash('Academic term added.');}
  }elseif($action==='delete'){$st=$pdo->prepare('DELETE FROM Terms WHERE term_id=?');$st->execute([(int)$_POST['term_id']]);flash('Term deleted.');}
 }catch(Throwable $e){flash($e instanceof PDOException?'Could not save/delete. This term may already exist or be used by an enrollment or section.':$e->getMessage(),'error');}
 redirect('terms.php');
}
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM Terms WHERE term_id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$rows=$pdo->query('SELECT * FROM Terms ORDER BY academic_year DESC,term_id DESC')->fetchAll();$pageTitle='Academic Terms';require 'header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ACADEMIC SETUP</p><h1>Academic Terms</h1><p>Set academic years, semesters, and date ranges.</p></div></div>
<div class="content-grid">
<section class="panel"><div class="panel-heading"><h2><?= $edit?'Edit term':'Add term' ?></h2></div><form method="post" class="form-grid one-col"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="term_id" value="<?= (int)($edit['term_id']??0) ?>">
<label>Academic year *<input required pattern="\d{4}-\d{4}" name="academic_year" value="<?= h($edit['academic_year']??'') ?>" placeholder="2026-2027"></label>
<label>Semester *<select name="semester" required><?php foreach(['1st Semester','2nd Semester','Summer'] as $sem): ?><option <?= ($edit['semester']??'')===$sem?'selected':'' ?>><?= h($sem) ?></option><?php endforeach; ?></select></label>
<label>Start date<input type="date" name="start_date" value="<?= h($edit['start_date']??'') ?>"></label>
<label>End date<input type="date" name="end_date" value="<?= h($edit['end_date']??'') ?>"></label>
<div class="form-actions"><button class="btn"><?= $edit?'Save changes':'Add term' ?></button><?php if($edit): ?><a class="btn btn-outline" href="terms.php">Cancel</a><?php endif; ?></div></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Academic term list</h2><p><?= count($rows) ?> term(s)</p></div></div><div class="table-wrap"><table><thead><tr><th>Academic year</th><th>Semester</th><th>Dates</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?= h($r['academic_year']) ?></strong></td><td><?= h($r['semester']) ?></td><td><?= h(($r['start_date']?:'—').' to '.($r['end_date']?:'—')) ?></td><td class="actions"><a class="text-link" href="?edit=<?= (int)$r['term_id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this term?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="term_id" value="<?= (int)$r['term_id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4" class="muted">No terms yet.</td></tr><?php endif; ?></tbody></table></div></section>
</div><?php require 'footer.php'; ?>
