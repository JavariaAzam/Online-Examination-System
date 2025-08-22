<?php
function sanitize(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): never { header("Location: $url"); exit; }
function flash_set(string $k,string $v): void { $_SESSION['flash'][$k]=$v; }
function flash_get(string $k): ?string {
  $v = $_SESSION['flash'][$k] ?? null; unset($_SESSION['flash'][$k]); return $v;
}
