<?php

/**
 *
 */
//var_dump($_FILES["myfile"]);
if ($_FILES["myfile"] != null) {
    echo '<pre>';
    $fileName = $_FILES["myfile"]["tmp_name"];
//    var_dump($_FILES["myfile"]);
//    var_dump($fileName);

    $compose = shell_exec("docker compose up -d --quiet-pull --force-recreate 2>&1");
//    echo $compose;
//
//    docker container ls --filter name=localwebdev-app-1 | awk '/localwebdev-app-1/ {print $1}'
    $containerID = shell_exec("docker container ls --all --quiet --filter name=localwebdev-app-1");

    $containerID = substr($containerID, 0, 12);

    $containerPort = shell_exec("docker port localwebdev-app-1");

    $containerPort = substr($containerPort, strrpos($containerPort, ":") + 1);

    $containerPort = substr($containerPort, 0, strlen($containerPort) - 1);

//    var_dump($containerPort);

//    docker cp  ~/Desktop/filename.txt container-id:/path/filename.txt
    $dockerCopyCommand = "docker cp " . $fileName . " " . $containerID . ":/var/www/html/test.java 2>&1";

//    var_dump($dockerCopyCommand);

    $dockerCopy = shell_exec($dockerCopyCommand);
//    echo $dockerCopy;

    $systemUser = shell_exec('docker container ls 2>&1');
    echo $systemUser;

    if (strpos($systemUser, "localwebdev-app-1") !== false) {
      echo "it worked";

      $curlCommand = "curl localhost:" . $containerPort;
//      var_dump($curlCommand);

      shell_exec($curlCommand);
    }
    echo '</pre>';



}
echo '<html lang="en">
<head>
<title>
test 3
</title>
</head>
<body>
<form id="uploadbanner" enctype="multipart/form-data" method="post" action="#">
<input id="fileupload" name="myfile" type="file" />
   <input type="submit" value="submit" id="submit" />
</form>
</body>
</html>
';