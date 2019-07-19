<?php
<<<<<<< HEAD
header('Content-Type: application/json');
=======
>>>>>>> 0ef5c58972ca5f24dd008b858b970caeac857826

llog(date());

llog($_POST,true);


function llog($stuff){
    $stuff = print_r($stuff,true);
    $now = Date("d/m/Y h:i:s");
    file_put_contents('log.log',$now,FILE_APPEND);
    file_put_contents('log.log',$stuff,FILE_APPEND);
<<<<<<< HEAD
}

$both = ['GET' => $_GET,'POST' => $_POST];

echo json_encode($both);
=======
}
>>>>>>> 0ef5c58972ca5f24dd008b858b970caeac857826
