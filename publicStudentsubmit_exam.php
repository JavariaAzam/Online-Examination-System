<?php
require __DIR__.'/../../app_config.php';
require __DIR__.'/../../app_helpers.php';
require __DIR__.'/../../app_auth.php';
require_role('student');

$attempt_id = (int)($_POST['attempt_id'] ?? $_GET['attempt'] ?? 0);

$u = $_SESSION['user'];
$stmt=$pdo->prepare("SELECT * FROM exam_attempts WHERE id=? AND user_id=? LIMIT 1");
$stmt->execute([$attempt_id,$u['id']]);
$a = $stmt->fetch();
if (!$a) redirect('/student/dashboard.php');

/* Ensure time not exceeded */
$started = new DateTime($a['started_at']);
$end     = (clone $started)->modify("+{$a['duration_minutes']} minutes");
if (new DateTime() > $end) { $_POST['q'] = $_POST['q'] ?? []; } // late—accept whatever came (or none)

/* Fetch locked question set */
$q=$pdo->prepare("SELECT question_id FROM exam_attempt_questions WHERE attempt_id=?");
$q->execute([$attempt_id]);
$qids = array_column($q->fetchAll(),'question_id');

/* Pull correct answers */
$in = implode(',', array_fill(0,count($qids),'?'));
$qq=$pdo->prepare("SELECT id, correct_option FROM questions WHERE id IN ($in)");
$qq->execute($qids);
$correctMap=[];
foreach($qq->fetchAll() as $r){ $correctMap[$r['id']]=$r['correct_option']; }

/* Save answers (upsert) + compute score */
$answers = $_POST['q'] ?? [];
$score=0;
$ins=$pdo->prepare("INSERT INTO exam_answers(attempt_id,question_id,chosen_option,is_correct)
                    VALUES(?,?,?,?)
                    ON DUPLICATE KEY UPDATE chosen_option=VALUES(chosen_option), is_correct=VALUES(is_correct)");
foreach($qids as $qid){
  $chosen = $answers[$qid] ?? null;
  $is_correct = ($chosen && $chosen === $correctMap[$qid]) ? 1 : 0;
  if ($is_correct) $score++;
  $ins->execute([$attempt_id,$qid,$chosen,$is_correct]);
}

/* finalize */
$pdo->prepare("UPDATE exam_attempts SET score=?, finished=1, ended_at=NOW() WHERE id=?")->execute([$score,$attempt_id]);
redirect("/student/my_results.php?attempt=$attempt_id");
