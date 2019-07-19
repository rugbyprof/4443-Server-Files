<?php

$hash = file_get_contents('log.log');

echo md5($hash);
