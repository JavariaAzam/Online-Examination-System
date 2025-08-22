<?php
require __DIR__.'/../../app_config.php';
require __DIR__.'/../../app_helpers.php';
require __DIR__.'/../../app_auth.php';
require __DIR__.'/../../app_rbac.php';
require __DIR__.'/../../appCSRF.php';
require __DIR__.'/../../app_pagination.php';
require_role('admin');

$flash = flash_get('msg') ?? '';

/* Create */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='create') {
  if (!csrf_verify($_POST['csrf'] ?? '')) { $flash='Bad CSRF.'; }
  else {
    $title = trim($_POST['title'] ?? '');
    if ($title==='') $flash='Title required';
    else { $stmt=$pdo->prepare("INSERT INTO subjects(title) VALUES(?)"); $stmt->execute([$title]); $flash='Subject created.'; }
    flash_set('msg',$flash); redirect('/admin/subjects.php');
  }
}

/* Delete */
if (($_GET['del'] ?? '')) {
  $id=(int)$_GET['del'];
  $pdo->prepare("DELETE FROM subjects WHERE id=?")->execute([$id]);
  flash_set('msg','Subject deleted.'); redirect('/admin/subjects.php');
}

/* List + pagination */
$perPage=10;
$total = (int)$pdo->query("SELECT COUNT(*) c FROM subjects")->fetch()['c'];
[$offset,$limit,$page,$pages] = paginate((int)($_GET['page'] ?? 1), $perPage, $total);
$stmt=$pdo->prepare("SELECT * FROM subjects ORDER BY id DESC LIMIT :off,:lim");
$stmt->bindValue(':off',$offset, PDO::PARAM_INT);
$stmt->bindValue(':lim',$limit, PDO::PARAM_INT);
$stmt->execute();
$subjects=$stmt->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container py-4">
  <h3>Subjects</h3>
  <?php if($flash): ?><div class="alert alert-info"><?=sanitize($flash)?></div><?php endif; ?>

  <form method="post" class="row gy-2 gx-2 align-items-center mb-3">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="col"><input class="form-control" name="title" placeholder="New subject title" required></div>
    <div class="col-auto"><button class="btn btn-primary">Add</button></div>
  </form>

  <table class="table table-striped">
    <thead><tr><th>#</th><th>Title</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($subjects as $s): ?>
        <tr>
          <td><?= (int)$s['id'] ?></td>
          <td><?= sanitize($s['title']) ?></td>
          <td>
            <a class="btn btn-sm btn-secondary" href="/admin/questions.php?subject_id=<?=$s['id']?>">Questions</a>
            <a class="btn btn-sm btn-danger" href="?del=<?=$s['id']?>" onclick="return confirm('Delete?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <nav>
    <ul class="pagination">
      <?php for($i=1;$i<=$pages;$i++): ?>
        <li class="page-item <?=$i===$page?'active':''?>"><a class="page-link" href="?page=<?=$i?>"><?=$i?></a></li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>
