<?php
require __DIR__.'/../app_auth.php';
logout_user();
header('Location: /login.php');
