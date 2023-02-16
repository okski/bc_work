<?php

session_start();

require_once 'db.php';

//require_once __DIR__ . '/../vendor/autoload.php';

if (!empty($_SESSION['UserId'])){
    $userQuery = $db->prepare('SELECT UserId FROM BcWork.User WHERE UserId=:id LIMIT 1;');
    $userQuery->execute([
        ':id'=>$_SESSION['UserId']
    ]);
    if ($userQuery->rowCount()!=1){
        unset($_SESSION['UserId']);
        unset($_SESSION['Username']);
        header('Location: index.php');
        exit();
    }
}