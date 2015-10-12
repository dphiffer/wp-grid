<?php

require_once __DIR__ . '/lib/php-ico/class-php-ico.php';

$source = __DIR__ . '/favicon.png';
$destination = __DIR__ . '/favicon.ico';
$ico_lib = new PHP_ICO($source);
$ico_lib->save_ico($destination);
