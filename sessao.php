<?php
$trinta_dias = 60 * 60 * 24 * 30;

ini_set('session.gc_maxlifetime', $trinta_dias);

session_set_cookie_params([
    'lifetime' => $trinta_dias,
    'path' => '/glicolife/',
    'secure' => false, 
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
