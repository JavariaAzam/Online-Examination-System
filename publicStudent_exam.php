<?php
require __DIR__.'/../../app_config.php';
require __DIR__.'/../../app_helpers.php';
require __DIR__.'/../../app_auth.php';
require_role('student');

$attempt_id = (int)($_GET['attempt'] ?? 0);
$u = $_SESSION['user'];

$attempt = $pdo->prepare("SELECT * FROM exam_attempts WHERE id=? AND user_id=?");
$attempt->execute([$attempt_id,$u['id']]);
$a = $attempt->fetch();
if (!$a || (int)$a['finished']===1) redirect('/student/dashboard.php');

/* Server-side time left (trust server, not only JS) */
$started = new DateTime($a['started_at']);
$end     = (clone $started)->modify("+{$a['duration_minutes']} minutes");
$now     = new DateTime();
$seconds_left = max(0, $end->getTimestamp() - $now->getTimestamp());
if ($seconds_left===0) redirect("/student/submit_exam.php?attempt=$attempt_id&timeout=1");

/* Load questions + existing answers */
$q = $pdo->prepare("SELECT q.id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, eq.q_order
  FROM exam_attempt_questions eq
  JOIN questions q ON q.id=eq.question_id
  WHERE eq.attempt_id=?
  ORDER BY eq.q_order ASC");
$q->execute([$attempt_id]);
$questions = $q->fetchAll();

/* existing answers map */
$aStmt=$pdo->prepare("SELECT question_id, chosen_option FROM exam_answers WHERE attempt_id=?");
$aStmt->execute([$attempt_id]);
$answers = [];
foreach($aStmt->fetchAll() as $r){ $answers[$r['question_id']]=$r['chosen_option']; }
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container py-3">
  <div class="d-flex justify-content-between align-items-center">
    <h4>Exam (Subject #<?= (int)$a['subject_id'] ?>)</h4>
    <div><strong>Time Left: </strong><span id="timer" data-left="<?=$seconds_left?>"></span></div>
  </div>
  <hr>
  <form id="examForm" method="post" action="/student/submit_exam.php">
    <input type="hidden" name="attempt_id" value="<?=$attempt_id?>">
    <?php foreach($questions as $idx=>$qq): ?>
      <div class="card mb-3">
        <div class="card-header">Q<?= (int)$qq['q_order'] ?>.</div>
        <div class="card-body">
          <p><?= sanitize($qq['question_text']) ?></p>
          <?php
            $opts=['A'=>$qq['option_a'],'B'=>$qq['option_b'],'C'=>$qq['option_c'],'D'=>$qq['option_d']];
            foreach($opts as $k=>$label):
              $checked = (isset($answers[$qq['id']]) && $answers[$qq['id']]===$k) ? 'checked' : '';
          ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="q[<?=$qq['id']?>]" value="<?=$k?>" id="q<?=$qq['id'].'_'.$k?>" <?=$checked?>>
              <label class="form-check-label" for="q<?=$qq['id'].'_'.$k?>"><?=sanitize($label)?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="d-flex justify-content-between">
      <a class="btn btn-outline-secondary" href="/student/dashboard.php" onclick="return confirm('Leave exam? Unsaved answers won’t submit automatically.')">Exit</a>
      <button class="btn btn-success">Submit</button>
    </div>
  </form>
</div>
<script>
(function(){
  const el=document.getElementById('timer');
  let left=+el.dataset.left||0;
  const tick=()=> {
    if(left<=0){ document.getElementById('examForm').submit(); return; }
    const m=Math.floor(left/60), s=left%60;
    el.textContent = `${m}:${s.toString().padStart(2,'0')}`;
    left--; setTimeout(tick, 1000);
  };
  tick();
})();
</script>
