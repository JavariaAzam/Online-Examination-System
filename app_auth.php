<?php
require_once __DIR__.'/config.php';
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void { if (!current_user()) redirect('/login.php'); }
function login_user(array $u): void {
  session_regenerate_id(true);
  $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role'],'roll_no'=>$u['roll_no']];
}
function logout_user(): void { $_SESSION=[]; session_destroy(); }
