<?php
require_once __DIR__ . '/inc/db.php';
session_start();

$homework = null;
$seminarInfo = '';

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

$General = 1;

if (isset($_POST['General']) && $_POST['General'] == 0) {
    $General = 0;
}

if (isset($_POST['Seminar'])) {
    $seminarInfo = $_POST['Seminar'];
}

if (isset($_POST['HomeworkId'])) {
    $queryString = '';

    if (!$General) {
        $queryString = 'SELECT Homework.*, SeminarHomework.Visible FROM Homework INNER JOIN SeminarHomework ON Homework.HomeworkId = SeminarHomework.HomeworkId
                  WHERE Homework.HomeworkId=:HomeworkId AND Homework.AddedBy=:UserId AND Homework.General=0  LIMIT 1;';
    } else {
        $queryString = 'SELECT Homework.*, SeminarHomework.Visible FROM Homework INNER JOIN SeminarHomework ON Homework.HomeworkId = SeminarHomework.HomeworkId
                  WHERE Homework.HomeworkId=:HomeworkId AND Homework.AddedBy=:UserId AND Homework.General=1  LIMIT 1;';
    }

    $homeworkQuery = $db->prepare($queryString);

    $homeworkQuery->execute([
        ':HomeworkId' => $_POST['HomeworkId'],
        ':UserId' => $_SESSION['UserId']
    ]);

    if ($homeworkQuery->rowCount()!=1) {
        header('Location: /error/404');
        exit();
    }

    $homeworkQuery->bindColumn('InputFile', $test, PDO::PARAM_LOB);

    $homework = $homeworkQuery->fetch();

    if (isset($_POST['editHomework']) && $_POST['editHomework'] = 'true') {
        json_decode($_POST['Marking']);

        if (empty(trim($_POST['Name']))) {
            $errors['Name'] = 'You have to set some name of homework.';
        }
        if (empty(trim($_POST['Description']))) {
            $errors['Description'] = 'You have to set some description of homework.';
        }
        if (empty(trim($_POST['Marking']))) {
            $errors['Marking'] = 'You have to set some marking for homework.';
        } elseif (json_last_error() !== JSON_ERROR_NONE) {
            $errors['Marking'] = 'Invalid JSON format.';
        } elseif (!preg_match('/^{\s*"maximum":\s*[1-9]+,\s*"marking":\s*\[\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*},\s*)*(?!,)\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*}\s*)\s*]\s*}$/' , $_POST['Marking'])) {
            $errors['Marking'] = 'Does not match wanted JSON structure.';
        }

        if (empty($errors)) {
            if (isset($_POST['Visible']) && $_POST['Visible'] == 'true') {
                $visible = 1;
            } else {
                $visible = 0;
            }

            $inputFile = $homework['InputFile'];
            if (isset($_FILES['InputFile']) && !empty($_FILES['InputFile']['name'])) {
                $inputFile = substr(file_get_contents($_FILES['InputFile']['tmp_name']), 0, $_FILES['InputFile']['size']);
            }

            $homeworkUpdateQuery = $db->prepare('UPDATE Homework SET Name=:Name, Description=:Description, Marking=:Marking, InputFile=:InputFile WHERE HomeworkId=:HomeworkId;');

            $homeworkUpdateQuery->execute([
                ':Name' => $_POST['Name'],
                ':Description' => $_POST['Description'],
                ':Marking' => $_POST['Marking'],
                ':InputFile' => $inputFile,
                ':HomeworkId' => $_POST['HomeworkId']
            ]);

            $seminarHomeworkUpdateQuery = $db->prepare('UPDATE SeminarHomework SET Visible=:Visible WHERE HomeworkId=:HomeworkId;');

            $seminarHomeworkUpdateQuery->execute([
                ':Visible' => $visible,
                ':HomeworkId' => $_POST['HomeworkId']
            ]);

            header('Location: '.$_SESSION['rdrurl']);
            exit();
        }



    }
}

?>

<div class="breadcrumb_div">
    <div class="breadcrumbPath">
        <a href="/">Home</a>
        <p class="arrow">→</p>
    </div>
    <div class="breadcrumbPath">
        <a href="<?php
        if (!$General) {
            echo substr($_SESSION['rdrurl'], 0, strpos($_SESSION['rdrurl'], '/homework'));
        } else {
            echo $_SESSION['rdrurl'];
        }
        ?>">
        <?php
        if (!$General) {
            echo 'Seminar (' . htmlspecialchars($_POST['Seminar']);
        } else {
            echo 'Course (';
            echo htmlspecialchars($_GET['Ident']). ' in ' . htmlspecialchars($_GET['Semester']) . ' in ' . htmlspecialchars($_GET['Year'] . '/' . (substr($_GET['Year'], 2, 2) +1));
        } ?>)</a>
        <p class="arrow">→</p>
        <p></p>
    </div>
    <div class="breadcrumbPath">
    <?php
    if (!$General) {
        echo '<a href="' . $_SESSION['rdrurl'] . '">';
        echo htmlspecialchars($homework['Name']) . '</a><p class="arrow">→</p>
        <p></p>';
    }
    ?>
    </div>
    <div class="breadcrumbPath">
        <p><?php
                if (!$General) {
                    echo 'edit';
                } else {
                    echo  'edit:' . htmlspecialchars($homework['Name']);
                }
            ?></p>
    </div>
</div>

<form method="post" enctype="multipart/form-data" name="homeworkForm">
                <div class="field">
                    <label for="Name">Name: </label>
                    <input type="text" name="Name" id="Name" placeholder="ex. Hello World!" pattern="^\S+(\s)?\S*$" value="<?php if (!empty($errors)) echo htmlspecialchars($_POST['Name']); else echo htmlspecialchars($homework['Name']) ?>" required>
                    <?php if (!empty($errors['Name'])) echo '<div class="text-danger">' . $errors['Name'] . '</div>'?>
                </div>
                <div class="field">
                    <label for="Description" >Description:</label>
                    <textarea name="Description" id="Description" cols="40" rows="6" placeholder="ex. Print \'Hello world!\' on standard output." required><?php if (!empty($errors)) echo htmlspecialchars($_POST['Description']); else echo htmlspecialchars($homework['Description']) ?></textarea>
                    <?php if (!empty($errors['Description'])) echo '<div class="text-danger">' . $errors['Description'] . '</div>'?>
                </div>
                <div class="field">
                    <label for="Marking">Marking:</label>
                    <textarea name="Marking" id="Marking" cols="40" rows="12" placeholder='ex. {
  "maximum": 1,
  "marking": [
      {"text": "Hello World!",
        "weight": "0.5"
      },
      {"text": "How are you?",
        "weight": "0.5"
      }
  ]
}'  required><?php if (!empty($errors)) echo htmlspecialchars($_POST['Marking']); else echo $homework['Marking'] ?></textarea>
                    <div class="text-danger" <?php if (!empty($errors['Marking'])) echo '>' . $errors['Marking']; else echo 'style="display: none">'?></div>
                </div>
                <div class="field">
                    <label for="InputFile">Input: </label>
                    <input type="file" name="InputFile" id="InputFile">
                </div>
                <div class="field">
                    <label for="Visible">Visible: </label>
                    <input type="checkbox" name="Visible" id="Visible" value="true"
 <?php if (!empty($errors) && $_POST['Visible'] == 'true') echo 'checked';
 else {
     if($homework['Visible']) {
         echo 'checked';
     }
 }
 ?>
            >
                </div>
                <input type="hidden" name="HomeworkId" id="HomeworkId" value="<?php echo htmlspecialchars($_POST['HomeworkId']) ?>">
                <input type="hidden" name="Seminar" id="Seminar" value="<?php echo $seminarInfo?>">
    <?php
        if (!$General) {
            echo '<input type="hidden" name="General" id="General" value="' . $General . '">';
        }
    ?>
                <button type="submit" name="editHomework" value="true" >Edit</button>
            </form>

<?php
include __DIR__ . '/inc/footer.php';