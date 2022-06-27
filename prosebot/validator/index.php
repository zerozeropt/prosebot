<?php
require_once('templatesvalidator.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("Europe/Lisbon");

function printP($text)
{
	echo $text;
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
	<title>Validate Templates</title>
</head>

<body>
	<a href="../index.php">Back</a>
	<div>
		<h2>Validate Templates</h2>
		<form method="POST" enctype="multipart/form-data">
			<fieldset style="margin-bottom: 2rem;">
				<legend>Execution Settings</legend>
				<select name="context">
					<?php
					foreach (get_globals()["contexts"] as $context_key => $value) {
						echo "<option value=\"" . $context_key . "\">" . $value . "</option>";
					}
					?>
				</select>
				<select name="lang">
					<?php
					foreach (get_globals()["languages"] as $lang_key => $value) {
						echo "<option value=\"" . $lang_key . "\">" . $value . "</option>";
					}
					?>
				</select>
				<input type="checkbox" id="hierarchy" name="hierarchy" checked>
				<label for="hierarchy">Hierarchy</label>
			</fieldset>
			<fieldset style="margin-bottom: 2rem;">
				<legend>Execution Methods</legend>
				<input type="radio" id="full" name="validation_option" value="full" checked>
				<label for="full">Full</label>

				<input type="radio" id="no_entities" name="validation_option" value="no_entities">
				<label for="full">Without checking entities definition</label>

				<input type="radio" id="get_entities" name="validation_option" value="get_entities">
				<label for="full">Get entities</label>
			</fieldset>
			<fieldset style="margin-bottom: 2rem;">
				<legend>Actions</legend>
				<div style="display: flex; flex-direction: column;">
					<label>Validate input file: </label>
					<div>
						<input type="file" name="file" accept="application/json">
						<input type="submit" name="submitFile" value="Validate">
					</div>
				</div>
				<br><br>
				<div style="display: flex; flex-direction: column;">
					<label>Validate text-condition pair: </label>
					<div>
						<input type="text" name="text" placeholder="Text">
						<input type="text" name="condition" placeholder="Condition">
						<input type="submit" name="submitTextCondition" value="Validate">
					</div>
				</div>
				<br><br>
				<div>
					<label>Validate all template files for context and language: </label>
					<input type="submit" name="submitAllFiles" value="Validate">
				</div>
				<br><br>
				<div>
					<label>Generate dictionary for context: </label>
					<input type="submit" name="submitDictionaries" value="Generate">
				</div>
			</fieldset>
		</form>
		<?php
		if (isset($_POST["submitFile"])) {
			if (file_exists($_FILES["file"]["tmp_name"])) {
				try {
					$validator = new TemplatesValidator($_POST["lang"], $_POST["context"]);
					$validator->set_file_path($_FILES["file"]["tmp_name"]);
					$option = $_POST["validation_option"];
					$validator->execution_method($option, isset($_POST["hierarchy"]));
				} catch (Exception $e) {
					printP($e->getMessage());
				}
			}
		} else if (isset($_POST["submitTextCondition"])) {
			try {
				$validator = new TemplatesValidator($_POST["lang"], $_POST["context"]);
				$text = $_POST["text"];
				$condition = $_POST["condition"];
				echo "<br>Text: " . $text . "<br>";
				echo "Condition: " . $condition . "<br>";
				$validator->validate_inputs($text, $condition);
			} catch (Exception $e) {
				printP($e->getMessage());
			}
		} else if (isset($_POST["submitAllFiles"])) {
			try {
				$validator = new TemplatesValidator($_POST["lang"], $_POST["context"]);
				$option = $_POST["validation_option"];
				$validator->validate_all_files(isset($_POST["hierarchy"]), $option);
			} catch (Exception $e) {
				printP($e->getMessage());
			}
		} else if (isset($_POST["submitDictionaries"])) {
			try {
				TemplatesValidator::generate_dictionary($_POST["context"]);
			} catch (Exception $e) {
				printP($e->getMessage());
			}
		}
		?>
	</div>
</body>

</html>