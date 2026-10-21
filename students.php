<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save') {
            $id = (int)($_POST['student_id'] ?? 0);
            $number = trim($_POST['student_number'] ?? '');
            $first = trim($_POST['first_name'] ?? '');
            $last = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $birth = trim($_POST['birth_date'] ?? '');
            $program = (int)($_POST['program_id'] ?? 0);
            if (!$number || !$first || !$last || !$program) throw new RuntimeException('Student number, name, and program are required.');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email address.');
            if ($id) {
                $st = $pdo->prepare('UPDATE Students SET student_number=?, first_name=?, last_name=?, email=?, birth_date=?, program_id=? WHERE student_id=?');
                $st->execute([$number,$first,$last,$email ?: null,$birth ?: null,$program,$id]);
                flash('Student record updated.');
            } else {
                $st = $pdo->prepare('INSERT INTO Students (student_number,first_name,last_name,email,birth_date,program_id) VALUES (?,?,?,?,?,?)');
                $st->execute([$number,$first,$last,$email ?: null,$birth ?: null,$program]);
                flash('Student added successfully.');
            }
        } elseif ($action === 'delete') {
            $st = $pdo->prepare('DELETE FROM Students WHERE student_id=?');
            $st->execute([(int)$_POST['student_id']]);
            flash('Student deleted.');
        }
    } catch (Throwable $e) {
        flash($e instanceof PDOException ? 'Could not save/delete. Check duplicate values or whether this student has enrollment records.' : $e->getMessage(), 'error');
    }
    redirect('students.php');
}
$programs = $pdo->query('SELECT program_id,program_code,program_name FROM Programs ORDER BY program_code')->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $st=$pdo->prepare('SELECT * FROM Students WHERE student_id=?');
    $st->execute([(int)$_GET['edit']]); $edit=$st->fetch();
}
$q = trim($_GET['q'] ?? '');
$sql = "SELECT s.*,p.program_code FROM Students s JOIN Programs p ON p.program_id=s.program_id";
$params=[];
if ($q !== '') { $sql .= " WHERE s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?"; $like="%$q%"; $params=[$like,$like,$like]; }
$sql .= " ORDER BY s.student_id DESC";
$st=$pdo->prepare($sql); $st->execute($params); $students=$st->fetchAll();
$pageTitle='Students'; require 'header.php';
?>
<div class="page-heading"><div><p class="eyebrow">RECORDS</p><h1>Students</h1><p>Register and maintain student information.</p></div><a class="btn" href="#student-form">+ Add Student</a></div>
<div class="content-grid">
<section class="panel" id="student-form"><div class="panel-heading"><div><h2><?= $edit?'Edit student':'Add student' ?></h2><p>Fields marked * are required.</p></div></div>
<?php if (!$programs): ?><div class="alert error">Add a program before registering a student.</div><?php endif; ?>
<form method="post" class="form-grid">
<?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="student_id" value="<?= (int)($edit['student_id']??0) ?>">
<label>Student number *<input required name="student_number" maxlength="30" value="<?= h($edit['student_number']??'') ?>" placeholder="e.g. 2026-0001"></label>
<label>Program *<select required name="program_id"><option value="">Choose program</option><?php foreach($programs as $p): ?><option value="<?= (int)$p['program_id'] ?>" <?= (int)($edit['program_id']??0)===(int)$p['program_id']?'selected':'' ?>><?= h($p['program_code'].' — '.$p['program_name']) ?></option><?php endforeach; ?></select></label>
<label>First name *<input required name="first_name" maxlength="50" value="<?= h($edit['first_name']??'') ?>"></label>
<label>Last name *<input required name="last_name" maxlength="50" value="<?= h($edit['last_name']??'') ?>"></label>
<label>Email<input type="email" name="email" maxlength="100" value="<?= h($edit['email']??'') ?>" placeholder="student@example.com"></label>
<label>Birth date<input type="date" name="birth_date" value="<?= h($edit['birth_date']??'') ?>"></label>
<div class="form-actions"><button class="btn" type="submit" <?= !$programs?'disabled':'' ?>><?= $edit?'Save changes':'Add student' ?></button><?php if($edit): ?><a class="btn btn-outline" href="students.php">Cancel</a><?php endif; ?></div>
</form></section>
<section class="panel"><div class="panel-heading"><div><h2>Student directory</h2><p><?= count($students) ?> record(s) shown</p></div></div>
<form class="search-form" method="get"><input name="q" value="<?= h($q) ?>" placeholder="Search number or name"><button class="btn btn-small">Search</button><?php if($q!==''): ?><a class="btn btn-outline btn-small" href="students.php">Clear</a><?php endif; ?></form>
<div class="table-wrap"><table><thead><tr><th>Student</th><th>Program</th><th>Actions</th></tr></thead><tbody>
<?php foreach($students as $s): ?><tr><td><strong><?= h($s['first_name'].' '.$s['last_name']) ?></strong><small class="subtext"><?= h($s['student_number']) ?></small><small class="subtext"><?= h($s['email']) ?></small></td><td><?= h($s['program_code']) ?></td><td class="actions"><a class="text-link" href="students.php?edit=<?= (int)$s['student_id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this student record?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="student_id" value="<?= (int)$s['student_id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$students): ?><tr><td colspan="3" class="muted">No student records found.</td></tr><?php endif; ?>
</tbody></table></div></section>
</div>
<?php require 'footer.php'; ?>
