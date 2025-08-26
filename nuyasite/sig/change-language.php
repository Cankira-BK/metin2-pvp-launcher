<?php
require_once __DIR__ . '/include/app.php';
if (isset($_POST["language"])) {
	$_SESSION["language"] = $_POST["language"];
} else {
	exit();
}
?>
