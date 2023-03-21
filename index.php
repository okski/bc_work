<?php
//spl_autoload('my_autoloader');

session_start();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

require_once __DIR__ . '/inc/user.php';

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}


include __DIR__ . '/inc/header.php';

include __DIR__ . '/inc/main.php';

include __DIR__ . '/inc/footer.php';

function my_autoloader($class) {
    include 'classes/'. $class . '.class.php';
}