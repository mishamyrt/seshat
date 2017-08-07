<?php
header ('Content-Type: text/html; charset=utf-8');
error_reporting (E_ALL);
ini_set('display_errors', 1);
error_reporting(~0);

include './seshat/seshat.php';
include 'stemmer.php';
$db = new mysqli('localhost', 'root', 'root', 'seshat');
$db->set_charset('utf8');
$files = glob('tests/*.{txt}', GLOB_BRACE);
$St = new Stemmer();
$Seshat = new Seshat($db, '', $St);
foreach ($files as $file) {
    $content = file_get_contents($file);
    $Seshat->index($content, 'маат — богиня', 1);
}