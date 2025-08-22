<?php
function require_role(string $role): void {
  $u = $_SESSION['user'] ?? null;
  if (!$u || $u['role'] !== $role) redirect('/login.php');
}
