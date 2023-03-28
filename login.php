<?php
session_start();

//načteme připojení k databázi a inicializujeme session
require_once __DIR__ . '/inc/user.php';

if (!empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    if (isset($_SESSION['rdrurl'])) {
        $rdrurl = $_SESSION['rdrurl'];
        unset($_SESSION['rdrurl']);
        header('Location: ' . $rdrurl);
    } else {
        header('Location: /');
    }
    exit();
}

$errors=false;

if (!empty($_POST)) {
    #region zpracování formuláře
    $userQuery = $db->prepare('SELECT * FROM User WHERE Email=:Email;');
    $userQuery->execute([
        ':Email' => trim($_POST['Email'])
    ]);

    if ($user = $userQuery->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($_POST['password'], $user['Password'])) {
//            //heslo je platné => přihlásíme uživatele
            $_SESSION['UserId'] = $user['UserId'];
            $_SESSION['FirstName'] = $user['FirstName'];
            $_SESSION['LastName'] = $user['LastName'];
            $_SESSION['Student'] = $user['Student'];
            $_SESSION['Teacher'] = $user['Teacher'];
            $_SESSION['Admin'] = $user['Admin'];
//
//            //smažeme požadavky na obnovu hesla
////            $forgottenDeleteQuery=$db->prepare('DELETE FROM hosj03.forgotten_passwords WHERE UserId=:user;');
////            $forgottenDeleteQuery->execute([':user'=>$user['UserId']]);
//
            if (isset($_SESSION['rdrurl'])) {
                $rdrurl = $_SESSION['rdrurl'];
                unset($_SESSION['rdrurl']);
                header('Location: ' . $rdrurl);
            } else {
                header('Location: /');
            }
            exit();
        } else {
            $errors = true;
        }
//
    } else {
        $errors = true;
    }
    #endregion zpracování formuláře
}


//nastavení parametrů pro vyžádání oprávnění a odkaz na přesměrování po přihlášení
//    $permissions = ['email'];



//include __DIR__ . '/../inc/header.php';
    ?>

    <div class="login">
        <h2>User login</h2>
        <form method="post">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" name="Email" id="email" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['email'])?>"/>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" />
            </div>
            <?php
            echo ($errors?'<div class="invalid-feedback">Combination of this email and password is incorrect.</div>':'');
            ?>
            <button type="submit" class="btn btn-primary">login</button>
        </form>
    </div>



<?php
//include __DIR__ . '/../inc/footer.php';
