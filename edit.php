<?php
require_once __DIR__ . '/inc/db.php';
session_start();

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

include __DIR__ . '/inc/header.php';

if (empty($_POST)) {
    header('Location: /error/404');
    exit();
}


if (isset($_POST['HomeworkId'])) {
    $homeworkQuery = $db->prepare('SELECT Homework.* FROM Homework WHERE Homework.HomeworkId=:HomeworkId AND Homework.AddedBy=:UserId AND Homework.General=1 LIMIT 1;');

    $homeworkQuery->execute([
        ':HomeworkId' => $_POST['HomeworkId'],
        ':UserId' => $_SESSION['UserId']
    ]);

    if ($homeworkQuery->rowCount()!=1) {
        header('Location: /error/404');
        exit();
    }
}

if (isset($_POST['editHomework']) && $_POST['editHomework'] = 'true') {
    $homeworkUpdateQuery = $db->prepare('UPDATE Homework SET Name=:Name, Description=:Description, Marking=:Marking, InputFile=:InputFile');

    $homeworkUpdateQuery->execute([

    ]);

}

echo '<form method="post">
                <div class="field">
                    <label for="Name">Name: </label>
                    <input type="text" name="Name" id="Name" placeholder="ex. Hello World!" pattern="^\S+(\s)?\S*$" onkeypress="this.style.width = ((this.value.length + 1) * 8) + \'px\';" required>
                </div>
                <div class="field">
                    <label for="Description" >Description:</label>
                    <textarea name="Description" id="Description" cols="40" rows="6" placeholder="ex. Print \'Hello world!\' on standard output." required></textarea>
                </div>
                <div class="field">
                    <label for="Marking">Marking:</label>
                    <textarea name="Marking" id="Marking" cols="40" rows="12" placeholder=\'ex. {
  "maximum": 1,
  "marking": [
      {"text": "Hello World!",
        "weight": "0.5"
      },
      {"text": "How are you?",
        "weight": "0.5"
      }
  ]
}\'  required></textarea>
                    <!--Marking regular expression: ^{\s*\"maximum\":\s*[1-9]+,\s*"marking":\s*\[\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*},\s*)+(?!,)\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*}\s*)\s*]\s*}$-->
                </div>
                <div class="field">
                    <label for="Stdin">Stdin: </label>
                    <input type="file" name="Stdin" id="Stdin">
                </div>
                <div class="field">
                    <label for="Visibility">Visibility: </label>
                    <input type="checkbox" name="Visibility" id="Visibility" value="true" >
                </div>
                <button type="submit" name="editHomework" value="true" >Edit</button>
            </form>';