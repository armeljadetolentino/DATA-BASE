<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf(); $action=$_POST['action']??'';
 try {
  if($action==='save'){
   $id=(int)($_POST['program_id']??0); $code=strtoupper(trim($_POST['program_code']??'')); $name=trim($_POST['program_name']??'');
   if(!$code||!$name) throw new RuntimeException('Program code and name are required.');
   if($id){$st=$pdo->prepare('UPDATE Programs SET program_code=?,program_name=? WHERE program_id=?');$st->execute([$code,$name,$id]);flash('Program updated.');}
   else{$st=$pdo->prepare('INSERT INTO Programs(program_code,program_name) VALUES(?,?)');$st->execute([$code,$name]);flash('Program added.');}
  } elseif($action==='delete'){$st=$pdo->prepare('DELETE FROM Programs WHERE program_id=?');$st->execute([(int)$_POST['program_id']]);flash('Program deleted.');}
 }catch(Throwable $e){flash($e instanceof PDOException?'Could not save/delete. Code may already exist or program may be in use.':$e->getMessage(),'error');}
 redirect('programs.php');
}
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM Programs WHERE program_id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$rows=$pdo->query('SELECT p.*, (SELECT COUNT(*) FROM Students s WHERE s.program_id=p.program_id) student_count FROM Programs p ORDER BY program_code')->fetchAll();
$pageTitle='Programs';require 'header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ACADEMIC SETUP</p><h1>Programs</h1><p>Manage the academic programs offered.</p></div></div>
<div class="content-grid">
<section class="panel"><div class="panel-heading"><div><h2><?= $edit?'Edit program':'Add program' ?></h2></div></div>
<form method="post" class="form-grid one-col"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="program_id" value="<?= (int)($edit['program_id']??0) ?>">
<label>Program code *<input required maxlength="20" name="program_code" value="<?= h($edit['program_code']??'') ?>" placeholder="e.g. BSIT"></label>
<label>Program name *<input required maxlength="100" name="program_name" value="<?= h($edit['program_name']??'') ?>" placeholder="Full program name"></label>
<div class="form-actions"><button class="btn"><?= $edit?'Save changes':'Add program' ?></button><?php if($edit): ?><a class="btn btn-outline" href="programs.php">Cancel</a><?php endif; ?></div></form></section>
<section class="panel"><div class="panel-heading"><div><h2>Program list</h2><p><?= count($rows) ?> program(s)</p></div></div><div class="table-wrap"><table><thead><tr><th>Code</th><th>Program name</th><th>Students</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?= h($r['program_code']) ?></strong></td><td><?= h($r['program_name']) ?></td><td><?= (int)$r['student_count'] ?></td><td class="actions"><a class="text-link" href="?edit=<?= (int)$r['program_id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this program?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="program_id" value="<?= (int)$r['program_id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4" class="muted">No programs yet.</td></tr><?php endif; ?></tbody></table></div></section>
</div><?php require 'footer.php'; ?>
