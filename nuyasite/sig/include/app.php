<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$share_path  = realpath(__DIR__ . '/../share');
$locale_path = realpath(__DIR__ . '/../share/locale');

$item_icon_array = ReadItemIconList();
$item_desc_array = ReadItemDescriptionList();

session_start();
?>
