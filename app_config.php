<?php
declare(strict_types=1);

/* Strict session + security headers */
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
  'lifetime'=>0,'path'=>'/','domain'=>'',
  'secure'=>isset($_SERVER['HTTPS']),
  'httponly'=>true,'samesite'=>'Lax'
]);
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

const DB_HOST='127.0.0.1';
const DB_NAME='online_exam';
const DB_USER='root';
const DB_PASS='your_password';

$pdo = new PDO(
  'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
  DB_USER, DB_PASS,
  [ PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES=>false ]
);
