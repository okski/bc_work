<?php
require_once __DIR__ . '/config.php';

/** @var \PDO $db - připojení k databázi */
$db = new PDO(DB_CONNECTION_STRING, DB_USERNAME, DB_PASSWORD);

//při chybě v SQL chceme vyhodit Exception
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);