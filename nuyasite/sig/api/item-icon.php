<?php
require_once __DIR__ . '/../include/app.php';
header('Content-Type: application/json');

$vnums = isset($_GET['vnums']) ? explode(',', $_GET['vnums']) : [];
$result = [];

foreach ($vnums as $vnum) {
	$icon = GetItemIcon($vnum);
	$result[$vnum] = $icon;
}

echo json_encode($result);
