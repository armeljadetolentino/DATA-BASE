<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';
 try{
  if($action==='save'){$id=(int)($_POST['subject_id']??0);$code=strtoupper(trim($_POST['subject_code']??''));$name=trim($_POST['subject_name']??'');$units=(int)($_POST['units']??3);if(!$code||!$name||$units<1||$units>30)throw new RuntimeException('Enter a subject code, name, and units from 1 to 30.');
   if($id){$st=$pdo->prepare('UPDATE Subjects SET subject_code=?,subject_name=?,units=? WHERE subject_id=?');$st->execute([$code,$name,$units,$id]);flash('Subject updated.');}
   else{$st=$pdo->prepare('INSERT INTO Subjects(subject_code,subject_name,units) VALUES(?,?,?)');$st->execute([$code,$name,$units]);flash('Subject added.');}
  }elseif($action==='delete'){$st=$pdo->prepare('DELETE FROM Subjects WHERE subject_id=?');$st->execute([(int)$_POST['subject_id']]);flash('Subject deleted.');}
 }catch(Throwable $e){flash($e instanceof PDOException?'Could not save/delete. Subject code may already exist or subject is used by a section.':$e->getMessage(),'error');}
 redirect('subjects.php');
}
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM Subjects WHERE subject_id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$rows=$pdo->query('SELECT * FROM Subjects ORDER BY subject_code')->fetchAll();$pageTitle='Subjects';require 'header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ACADEMIC SETUP</p><h1>Subjects</h1><p>Maintain the subject catalog and unit values.</p></div></div>
<div class="content-grid">
<section class="panel"><div class="panel-heading"><h2><?= $edit?'Edit subject':'Add subject' ?></h2></div><form method="post" class="form-grid one-col"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="subject_id" value="<?= (int)($edit['subject_id']??0) ?>">
<label>Subject code *<input required maxlength="20" name="subject_code" value="<?= h($edit['subject_code']??'') ?>" placeholder="e.g. DBMS101"></label>
<label>Subject name *<input required maxlength="100" name="subject_name" value="<?= h($edit['subject_name']??'') ?>"></label>
<label>Units *<input required type="number" min="1" max="30" name="units" value="<?= h($edit['units']??3) ?>"></label>
<div class="form-actions"><button class="btn"><?= $edit?'Save changes':'Add subject' ?></button><?php if($edit): ?><a class="btn btn-outline" href="subjects.php">Cancel</a><?php endif; ?></div></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Subject list</h2><p><?= count($rows) ?> subject(s)</p></div></div><div class="table-wrap"><table><thead><tr><th>Code</th><th>Subject</th><th>Units</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?= h($r['subject_code']) ?></strong></td><td><?= h($r['subject_name']) ?></td><td><?= (int)$r['units'] ?></td><td class="actions"><a class="text-link" href="?edit=<?= (int)$r['subject_id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this subject?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="subject_id" value="<?= (int)$r['subject_id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4" class="muted">No subjects yet.</td></tr><?php endif; ?></tbody></table></div></section>
</div><?php require 'footer.php'; ?>
