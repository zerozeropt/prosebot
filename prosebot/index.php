<?php
require_once('global_vars.php');
date_default_timezone_set("Europe/Lisbon");
header('Content-Type: text/html; charset=utf8');
if (!empty($_GET["context"])) {
	header('Location: contexts/' . $_GET["context"] . '/index.php');
	exit;
}
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf8">
	<meta name="Generator" content="">
	<meta name="Author" content="">
	<meta name="Keywords" content="">
	<meta name="Description" content="">
	<title>Document</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
	<a href="/validator/index.php">Validate templates</a>
	<form>
		<div>
			<h2>Choose context</h2>
			<select name="context">
				<?php
				foreach (get_globals()["contexts"] as $context_key => $value) {
					echo "<option value=\"" . $context_key . "\">" . $value . "</option>";
				}
				?>
			</select>
			<a href="contexts/<?php echo isset($_GET["context"]) ? $_GET["context"] : "" ?>/index.php">
				<input type="submit" value="Go">
			</a>
		</div>
	</form>
	<h5>VERSION - <?php echo get_globals()["version"]; ?></h5>
</body>
</html>