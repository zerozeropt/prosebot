<?php
require_once(__DIR__.'/managers/templatesmanager.php');
require_once(__DIR__.'/../../global_vars.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("Europe/Lisbon");

function printP($text)
{
	echo $text;
}

function printList($list, $title)
{
	echo '<h4>' . $title . '</h4>';
	if (count($list) > 0) {
		echo '<div>';
		echo '<li>' . implode('</li><li>', $list) . '</li>';
		echo '</ul>';
	}
}

header('Content-Type: text/html; charset=utf8');
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
				<input type="checkbox" id="stats" name="stats">
				<label for="stats">Show stats</label>
				<select name="lang">
				<?php 
					foreach (get_globals()["languages"] as $lang_key => $value) {
						echo "<option value=\"".$lang_key."\" ".(isset($_GET["lang"]) && $_GET["lang"] == $lang_key ? "selected" : "").">".$value."</option>";
					}
				?>
				</select>
				<input type="text" name="match" value="<?php echo empty($_GET["match"]) ? "1" : $_GET["match"] ?>"><br>
				<input type="submit" value="Submit">
			</div>
		</form>
		<?php
		if (!empty($_GET["lang"]) && !empty($_GET["match"])) {
			try {
				$manager = new TemplatesManagerFootball($_GET["lang"]);
				$with_stats = isset($_GET["stats"]);
				$summary = $manager->build_summary($_GET["match"], $with_stats);
				$title = $summary[SummaryParts::TITLE];
				$sub_title = $summary[SummaryParts::SUB_TITLE];
				$small_text = $summary[SummaryParts::SMALL_TEXT];
				$article = $summary[SummaryParts::LONG_TEXT];
				$article = str_replace("\n", "<BR>", $article);
				$stats = $summary[SummaryParts::STATS];
				$match = $summary["match"];
				if ($with_stats && isset($summary[SummaryParts::ANALYSIS])) {
					$analysis = $summary[SummaryParts::ANALYSIS];
					$count = $summary[GenerationAnalyser::LEXICAL_DIVERSITY];
					$sentence_size = $summary[GenerationAnalyser::SENTENCE_SIZE];
					$points = $summary[GenerationAnalyser::POINTS];
					$medium_sentence_size = $summary[GenerationAnalyser::SENTENCE_MEDIUM_SIZE];
				}
		?>
				<div>
					<h1><?php printP($title); ?></h1>
					<h3><?php printP($sub_title); ?></h3>
					<p><?php printP($small_text); ?></p>
					<p><?php printP($article); ?></p>
					<h3>Curiosities</h3>
					<?php printList($stats['match_stats'], "Jogo") ?>
					<?php printList($stats['home_team_stats'], $match->home_team()->get_name()) ?>
					<?php printList($stats['away_team_stats'], $match->away_team()->get_name()) ?>
					<?php
					if ($with_stats) {
					?>
					<div>
						<h5>Lexical Diversity (1-100)</h5>
						<p><?php printP($count); ?></p>
					</div>
					<div>
						<h5>Sentences medium size (1-100)</h5>
						<p><?php printP($medium_sentence_size); ?></p>
					</div>
					<div>
						<?php
						if ($sentence_size[1] > 29) {
						?>
							<h5 style="color:red;">Biggest sentence - <?php print_r($sentence_size[1]) ?> words - TOO LONG</h5>
						<?php
						} else {
						?>
							<h5>Biggest sentence - <?php print_r($sentence_size[1]) ?> words</h5>
						<?php
						}
						?>
						<p><?php print_r($sentence_size[0]) ?></p>
					</div>
				</div>
				<div>
					<h5>Score (1-10)</h5>
					<p><?php printP($points); ?></p>
				</div>
				<?php } ?>
				<h5>VERSION - <?php echo get_globals()["version"]; ?></h5>
		<?php
			} catch (Exception $e) {
				printP($e->getMessage());
			}
		} else {
			printP("");
			exit();
		}
		?>
	</body>
</html>