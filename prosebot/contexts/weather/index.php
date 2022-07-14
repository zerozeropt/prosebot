<?php
require_once(__DIR__.'/managers/templatesmanager.php');
require_once(__DIR__.'/../../global_vars.php');
require_once(__DIR__.'/../../utils.php');
date_default_timezone_set("Europe/Lisbon");
header('Content-Type: text/html; charset=utf8');

//Debug code
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

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
		<link rel="icon" type="image/x-icon" href="/favicon.ico">
		<style>
			li {
				list-style-type: none;
			}
		</style>
	</head>
	<body>
		<a href="../../index.php">Back</a>
		<form>
			<div>
				<h2>Generate Summary</h2>
				<select name="lang">
				<?php 
					foreach (get_globals()["languages"] as $lang_key => $value) {
						echo "<option value=\"".$lang_key."\" ".(isset($_GET["lang"]) && $_GET["lang"] == $lang_key ? "selected" : "").">".$value."</option>";
					}
				?>
				</select>
				<input type="text" name="city" value="<?php echo empty($_GET["city"]) ? "1" : $_GET["city"] ?>"><br>
				<input type="submit" value="Submit">
			</div>
		</form>
		<?php
		if (!empty($_GET["lang"]) && !empty($_GET["city"])) {
			try {
				$manager = new TemplatesManagerWeather($_GET["lang"]);
				$summary = $manager->build_summary($_GET["city"]);
				$title = $summary[SummaryParts::TITLE];
				$article = $summary[SummaryParts::LONG_TEXT];
				$article = str_replace("\n", "<BR>", $article);
				$city = $summary["city"];
		?>
				<div>
					<h1><?php Utils::printP($title); ?></h1>
					<p><?php Utils::printP($article); ?></p>
				</div>
				<h5>VERSION - <?php echo get_globals()["version"]; ?></h5>
		<?php
			} catch (Exception $e) {
				Utils::printP($e->getMessage());
			}
		} else {
			Utils::printP("");
			exit();
		}
		?>
	</body>
</html>