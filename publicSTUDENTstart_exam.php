<?php
require __DIR__.'/../../app_config.php';
require __DIR__.'/../../app_helpers.php';
require __DIR__.'/../../app_auth.php';
require __DIR__.'/../../appCSRF.php';
require_role('student');

$subs = $pdo->query("SELECT id,title FROM subjects ORDER BY title")->fetchAll();
$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_verify($_POST['csrf'] ?? '')) $err='Bad CSRF';
  else {
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $num_q      = max(1, min(50, (int)($_POST['num_q'] ?? 10)));
    $duration   = max(1, min(180, (int)($_POST['duration'] ?? 15)));

    // Create attempt
    $pdo->beginTransaction();
    $stmt=$pdo->prepare("INSERT INTO exam_attempts(user_id,subject_id,total_questions,duration_minutes,started_at) VALUES(?,?,?,?,NOW())");
    $stmt->execute([$_SESSION['user']['id'],$subject_id,$num_q,$duration]);
    $attempt_id=(int)$pdo->lastInsertId();

    // Lock randomized set: pick IDs then shuffle (safer than ORDER BY RAND() on large sets)
    $qids = $pdo->prepare("SELECT id FROM questions WHERE subject_id=?");
    $qids->execute([$subject_id]);
    $ids = array_column($qids->fetchAll(), 'id');
    shuffle($ids);
    $ids = array_slice($ids, 0, $num_q);

    $order=1;
    $ins=$pdo->prepare("INSERT INTO exam_attempt_questions(attempt_id,question_id,q_order) VALUES(?,?,?)");
    foreach($ids as $qid){ $ins->execute([$attempt_id,$qid,$order++]); }

    $pdo->commit();
    redirect("/student/exam.php?attempt=$attempt_id");
  }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container py-4" style="max-width:600px;">
  <h3>Start Exam</h3>
  <?php if($err): ?><div class="alert alert-danger"><?=sanitize($err)?></div><?php endif; ?>
  <form method="post" class="vstack gap-3">
    <?= csrf_field() ?>
    <select class="form-select" name="subject_id" required>
      <option value="">Select subject</option>
      <?php foreach($subs as $s): ?>
        <option value="<?=$s['id']?>"><?=sanitize($s['title'])?></option>
      <?php endforeach; ?>
    </select>
    <div class="row g-2">
      <div class="col">
        <label class="form-label">Questions</label>
        <input class="form-control" type="number" name="num_q" min="1" max="50" value="10">
      </div>
      <div class="col">
        <label class="form-label">Duration (minutes)</label>
        <input class="form-control" type="number" name="duration" min="1" max="180" value="15">
      </div>
    </div>
    <button class="btn btn-primary">Start</button>
  </form>
</div>
