<?php
function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function csrf_field(): string {
  return '<input type="hidden" name="csrf" value="'.htmlspecialchars(csrf_token()).'">';
}
function csrf_verify(string $t): bool {
  return hash_equals($_SESSION['csrf']??'', $t);
}
