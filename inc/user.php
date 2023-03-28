<?php
require_once 'db.php';

session_start();


if (!empty($_SESSION['UserId'])){
    $userQuery = $db->prepare('SELECT UserId FROM BcWork.User WHERE UserId=:id LIMIT 1;');
    $userQuery->execute([
        ':id'=>$_SESSION['UserId']
    ]);
    if ($userQuery->rowCount()!=1){
        unset($_SESSION['UserId']);
        unset($_SESSION['Username']);
        unset($_SESSION['UserId']);
        unset($_SESSION['FirstName']);
        unset($_SESSION['LastName']);
        unset($_SESSION['Student']);
        unset($_SESSION['Teacher']);
        unset($_SESSION['Admin']);
        header('Location: /');
        exit();
    }
}