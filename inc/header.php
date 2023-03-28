<?php
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">';
if ($_SESSION['refresh']) {
    echo '<meta http-equiv="refresh" content="30" />';
}
echo '
    <title id="title">Bachelor\'s work of hosj03</title>
    <link rel="stylesheet" href="/resources/main.css">
</head>
<body>';

if (isset($_SESSION['UserId']) && !empty($_SESSION['UserId'])) {
    echo '<div class="user">logged in as <span class="username">'.$_SESSION['FirstName'] . ' ' . $_SESSION['LastName'] .'</span></div>';
    echo '<a href="/logout.php" class="btn btn-primary">logout</a>';
}