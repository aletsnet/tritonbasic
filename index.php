<?php

$carpeta = 'assets';
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$carpeta = 'protected/runtime';
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

require("vendor/autoload.php");


//require("vendor/pradosoft/prado/framework/prado.php");
$application=new Prado\TApplication();
//$application=new TApplication;
$application->run();