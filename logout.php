<?php
require_once __DIR__ . '/inc/user.php';

if (!empty($_SESSION['UserId'])){
    //smažeme ze session identifikaci uživatele
    unset($_SESSION['UserId']);
    unset($_SESSION['FirstName']);
    unset($_SESSION['LastName']);
    unset($_SESSION['Student']);
    unset($_SESSION['Teacher']);
    unset($_SESSION['Admin']);
}

//přesměrujeme uživatele na homepage
header('Location: /');