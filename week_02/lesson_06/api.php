<?php

llog(date());

llog($_POST,true);


function llog($stuff){
    $stuff = print_r($stuff,true);
    $now = Date("d/m/Y h:i:s");
    file_put_contents('log.log',$now,FILE_APPEND);
    file_put_contents('log.log',$stuff,FILE_APPEND);
}