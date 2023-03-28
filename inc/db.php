<?php
session_start();

if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    require_once __DIR__ . '/config_student.php';
} elseif (isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    require_once __DIR__ . '/config_teacher.php';
} else {
    require_once __DIR__ . '/config.php';
}

/** @var \PDO $db - připojení k databázi */
$db = new PDO(DB_CONNECTION_STRING, DB_USERNAME, DB_PASSWORD);

//při chybě v SQL chceme vyhodit Exception
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);