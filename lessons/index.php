<?php

$dir = scandir('.');

// remove . and ..
array_shift($dir);
array_shift($dir);

foreach($dir as $entry){
    echo"$entry<br>";
}