<?php
require __DIR__.'/../../app/config.php';
require __DIR__.'/../../app/helpers.php';
require __DIR__.'/../../app/auth.php';
require __DIR__.'/../../app/pagination.php';
require_role('student');

$u=$_SESSION['user'];
$perPage=10;
$total=(int)$pdo->query("SELECT COUNT(*) c FROM exam_attempts WHERE user_id=".$u['id'])->fetch()['c'];
[$offset,$limit,$page,$pages]=paginate((int)($_GET['page']??1),$perPage,$total);

$stmt=$pdo->prepare("SELECT ea.*, s.title
                     FROM exam_attempts ea
                     JOIN subjects s ON s.id=ea.subject_id
                     WHERE user_id=?
                     ORDER BY ea.id DESC
                     LIMIT :off,:lim");
$stmt->bindValue(1,$u['id'],PDO::PARAM_INT);
$stmt->bindValue(':off',$offset,PDO::PARAM_INT);
$stmt->bindValue(':lim',$limit,PDO::PARAM_INT);
$stmt->execute();
$rows=$stmt->fetchAll();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container py-4">
  <h3>My Results</h3>
  <table class="table table-striped">
    <thead><tr><th>#</th><th>Subject</th><th>Score</th><th>Out of</th><th>Started</th><th>Ended</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= sanitize($r['title']) ?></td>
          <td><?= (int)$r['score'] ?></td>
          <td><?= (int)$r['total_questions'] ?></td>
          <td><?= sanitize($r['started_at']) ?></td>
          <td><?= sanitize($r['ended_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <ul class="pagination">
    <?php for($i=1;$i<=$pages;$i++): ?>
      <li class="page-item <?=$i===$page?'active':''?>"><a class="page-link" href="?page=<?=$i?>"><?=$i?></a></li>
    <?php endfor; ?>
  </ul>
</div>
