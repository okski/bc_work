<?php

spl_autoload('my_autoloader');
//require_once __WEBROOT__ . '/includes/safestring.class.php';

session_start();
//session_destroy();

require_once __DIR__ . '/inc/user.php';

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: login.php');
    exit();
}


include __DIR__ . '/inc/header.php';

include __DIR__ . '/inc/course.php';

include __DIR__ . '/inc/footer.php';

function my_autoloader($class) {
    include 'classes/'. $class . '.class.php';
}