<?php
require_once __DIR__ . '/../include/app.php';
if (isset($_POST["vnum"])) {
	echo GetItemDescription($_POST["vnum"]);
} else {
	exit();
}
?>
