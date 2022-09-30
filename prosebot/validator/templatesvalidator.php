<?php
require_once(__DIR__ . '/../utils.php');
require_once(__DIR__ . '/../global_vars.php');
require_once(__DIR__ . "/../contexts/entities.php");
require_once(__DIR__ . "/../exceptions.php");

/**
 * Class to validate writing correction of templates
 * 
 * @author zerozero.pt
 */
class TemplatesValidator
{
	const TEXT_TYPE = "text";
	const CONDITION_TYPE = "condition";
	const CONTEXTS_DIR = __DIR__."/../contexts/";
	const BOOL_EXPR = "boolean expression";

	/**
	 * @param string $language Language
	 * @param string $context  Context
	 */
	function __construct($language, $context)
	{
		// Construct validator object
		$this->templates_array = [];
		$this->language = $language;  // language of the template to validate
		$this->context = $context;
		$this->dictionary = self::CONTEXTS_DIR . $context . "/dictionary/vars.json";
		$this->unused_templates = [];					// to generate warnings of unused templates with hierarchy
		$this->templates_queue = [];					// queue of templates to be parsed next		
		$this->valid_templates = [];					// all templates
		$this->check_entities = true;					// check if entities are defined
		$this->check_templates = true;					// check if templates are defined
		$this->get_entities = false;					// get entities in file
		$this->file_name = "";							// name of the file being evaluated
		$this->vars_array = Utils::json_validate('{
			"properties": {
				"text": [],
				"condition": []
			},
			"entities": {},
			"connectors": []
		}'); // to print obtained entities, when get_entities = true
	}

	/**
	 * Store template json file data
	 * @param array $file_array File data
	 */
	public function set_file_data($file_array)
	{
		// Add properties
		$this->templates_array = $file_array;
		$this->unused_templates = array_keys((array)$this->templates_array);
		if (count($this->unused_templates) == 0) {
			$this->throw_error_expected_found("", "templates tree structure", "null"); // error if no templates
		}
		$this->templates_queue = array($this->unused_templates[0]);
	}

	/**
	 * Load template json file from path
	 * @param string $file_path File path
	 */
	public function set_file_path($file_path)
	{
		// Check for json validaty
		$json_array = Utils::json_validate(file_get_contents($file_path));
		$this->set_file_data($json_array);
	}

	/**
	 * Load variables dictionary json file
	 * @param string $file File path
	 */
	private function set_vars($file)
	{
		$this->vars_array = Utils::json_validate(file_get_contents($file));
	}

	/**
	 * Validate everything - setting 1
	 * @param bool $has_hierarchy Whether it follows a hierarchic structure
	 */
	public function validate_full($has_hierarchy)
	{
		$this->set_vars($this->dictionary);
		$this->validate_template($has_hierarchy);
	}

	/**
	 * Validate everything except if entities are defined - setting 2
	 * @param bool $has_hierarchy Whether it follows a hierarchic structure
	 */
	public function validate_no_entities_check($has_hierarchy)
	{
		$this->check_entities = false;
		$this->validate_template($has_hierarchy);
	}

	/**
	 * Validate everything except if entities are defined and get entities list - setting 3.
	 * Print entities.
	 * @param bool $has_hierarchy Whether it follows a hierarchic structure
	 */
	public function validate_get_entities($has_hierarchy)
	{
		$this->get_entities = true;
		$this->validate_no_entities_check($has_hierarchy);

		if ($this->file_name !== "") {
			$length = strlen($this->file_name);
			echo "<br>--------------------<br>";
			echo mb_substr($this->file_name, 0, $length > 3 ? $length - 3 : $length) . "<br>";
			echo "--------------------<br>";
		}

		echo "<br>Conditions:<br>";
		foreach ($this->vars_array->properties->condition as $property) {
			echo "-- " . $property . "<br>";
		}

		echo "<br>Properties:<br>";
		foreach ($this->vars_array->properties->text as $property) {
			echo "-- " . $property . "<br>";
		}

		echo "<br>Entities:<br>";
		foreach ($this->vars_array->entities as $entity => $properties) {
			echo "-- " . $entity . "<br>";
			foreach ($properties as $property) {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;." . $property . "<br>";
			}
		}

		echo "<br>Connectors:<br>";
		echo $this->language . ":<br>";
		foreach ($this->vars_array->connectors as $connector) {
			echo "-- " . $connector . "<br>";
		}
	}

	/**
	 * Execute one of the methods of validation available
	 * @param string $option		One of the execution methods
	 * @param bool 	 $has_hierarchy Whether it follows a hierarchic structure
	 */
	public function execution_method($option, $has_hierarchy)
	{
		switch ($option) {
			case "no_entities":
				$this->validate_no_entities_check($has_hierarchy);
				break;
			case "get_entities":
				$this->validate_get_entities($has_hierarchy);
				break;
			default:
				$this->validate_full($has_hierarchy);
				break;
		}
	}

	/**
	 * Validate input text and condition - action 2
	 * @param string $text      Text
	 * @param string $condition Condition
	 */
	public function validate_inputs($text, $condition)
	{
		$this->set_vars($this->dictionary);
		$this->check_templates = false;
		$this->validate_text("", $text);
		$this->validate_condition("", $condition);
	}

	/**
	 * Validate all template files in the system - action 3
	 * @param bool 	 $has_hierarchy Whether it follows a hierarchic structure
	 * @param string $option		One of the execution methods
	 */
	public function validate_all_files($has_hierarchy, $option)
	{
		$dir = self::CONTEXTS_DIR . $this->context . "/templates/" . $this->language . "/";
		$templates = scandir($dir);
		if ($templates === false) {
			echo "Warning: Directory is empty<br>";
			return;
		}

		// For each template
		foreach ($templates as $template_name) {
			// Skip . and ..
			if ($template_name == "." || $template_name == "..") {
				continue;
			}
			// Validate file
			try {
				$this->file_name = "[" . $template_name . "] -> ";
				$this->set_file_path($dir . $template_name);
				$this->execution_method($option, $has_hierarchy);
			} catch (Exception $e) {
				// If error, throw exception with file name 
				throw new ValidationErrorException($e->getMessage(), "", $template_name);
			}
		}
	}

	/**
	 * Validate input text and condition and return status - action 5
	 * @param object $template Template composed of text and condition properties
	 */
	public function validate_template_get_status($template)
	{
		$this->set_vars($this->dictionary);
		$this->check_templates = false;
		$status = array(1, "Success");

		try {
			$this->validate_text("", $template->text);
			$this->validate_condition("", $template->condition);
		} catch (Exception $e) {
			$status[0] = 0;
			$status[1] = $e->getMessage();
			echo $status[1] . "<br>";
		}

		return $status;
	}

	/**
	 * Validate entire template json file
	 * @param bool $has_hierarchy Whether it follows a hierarchic structure
	 */
	private function validate_template($has_hierarchy)
	{
		$current_path = "";

		// Check if each json root key has a valid variable name
		foreach ($this->unused_templates as $unused) {
			$this->validate_template_variable("JSON root key", $unused);
		}

		$this->valid_templates = $this->unused_templates;
		if ($has_hierarchy) { // Walk up the tree starting in the root node
			$this->validate_tree($current_path, $this->unused_templates[0]);
		}
		else {
			$this->validate_object($current_path); // Validate each json value
		}

		// Print unused templates as warnings
		foreach ($this->unused_templates as $unused_key) {
			echo $this->file_name . "Warning: \"$unused_key\" defined but never used.<br>";
		}
	}

	/**
	 * Validate json tree structure recursively
	 * @param string $path        Local in the json tree structure
	 * @param string $current_key Current key being evaluated
	 */
	private function validate_tree($path, $current_key)
	{
		// Stop condition - all templates used
		if (empty($this->unused_templates)) {
			return;
		}

		// Stop condition - no more templates in queue
		if (empty($this->templates_queue)) {
			return;
		}

		// Get current value
		$current_value = $this->templates_array->{$current_key};
		// Validate current element
		$this->validate_element($path . "[" . $current_key . "]", $current_value);

		// Remove current template from arrays
		$removed_unused_key = array_search($current_key, $this->unused_templates);
		if ($removed_unused_key !== false) {
			array_splice($this->unused_templates, $removed_unused_key, 1);
		}
		$removed_template_key = array_search($current_key, $this->templates_queue);
		if ($removed_template_key !== false) {
			array_splice($this->templates_queue, $removed_template_key, 1);
		}

		// For each template in queue start a new recursion
		foreach ($this->templates_queue as $key) {
			$this->validate_tree($path, $key);
		}
	}

	/**
	 * Validate json structure whithout hierarchy format
	 * @param string $path Local in the json structure
	 */
	private function validate_object($path)
	{
		// For each template
		foreach ($this->valid_templates as $current_key) {
			// Get value
			$current_value = $this->templates_array->{$current_key};
			// Validate element
			$this->validate_element($path . "[" . $current_key . "]", $current_value);
		}

		// Clear unused templates, no warnings, no hierarchy
		$this->unused_templates = [];
	}

	/**
	 * Validate each text-condition object
	 * @param string $path    Local in the json structure
	 * @param array  $element An array of template objects
	 */
	private function validate_element($path, $element)
	{
		$count = 0;
		foreach ($element as $template) {
			$path_elem = $path . "[" . $count . "]";

			// An element must have text and condition keys
			if (!isset($template->text)) {
				$this->throw_error_missing_text($path_elem);
			}
			if (!isset($template->condition)) {
				$this->throw_error_missing_condition($path_elem);
			}

			// Validate text prop.
			$this->validate_text($path_elem, $template->text);
			// Validate condition prop.
			$this->validate_condition($path_elem, $template->condition);
			$count++;
		}
	}

	/**
	 * Validate text
	 * @param string $path Local in the json structure
	 * @param string $text Text
	 */
	public function validate_text($path, $text)
	{
		$path .= "[text]";

		// Validate links
		if (preg_match('/@.+link/u', $text) && !preg_match('/@.*link.*<\/a>/u', $text)) {
			$this->throw_error_bad_link($path, $text, "missing closing hyperlink with </a>");
		}

		// Split text through {tokens}
		$text_array = preg_split('/({(.*?)})/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// No tokens, regular string
		if (count($text_array) < 2) {
			$this->regular_string($path, $text);
			return;
		}

		// For each chunk
		foreach ($text_array as $chunk) {

			$first_char = mb_substr($chunk, 0, 1, 'UTF-8');

			// Left bracket found, start of a token
			if ($first_char == "{") {
				// Split {token} through "{" and "}"
				$token_array = preg_split('/({)|(})/u', $chunk, -1, PREG_SPLIT_NO_EMPTY);
				$strlen = mb_strlen($chunk, 'UTF-8');
				// Get token inside brackets
				$token_str = mb_substr($chunk, 1, $strlen - 2, 'UTF-8');
				// Validate token
				$this->token($path, $token_str, $token_array);
			}
			// Regular string
			else {
				$this->regular_string($path, $chunk);
			}
		}
	}

	/**
	 * Validate regular string
	 * @param string $path   Local in the json structure
	 * @param string $string Regular string
	 */
	private function regular_string($path, $string)
	{
		// Regular string cannot have opening brackets
		if (preg_match('/{/u', $string)) {
			$this->throw_error_missing_bracket($path, "closing bracket }", $string);
		}
		// Regular string cannot have closing brackets
		else if (preg_match('/}/u', $string)) {
			$this->throw_error_missing_bracket($path, "opening bracket {", $string);
		}
	}

	/**
	 * Validate token
	 * @param string $path        Local in the json structure
	 * @param string $token_str   Token string
	 * @param array  $token_array Token array
	 */
	private function token($path, $token_str, $token_array)
	{
		// Token cannot be null
		if (empty($token_array)) {
			$this->throw_error_expected_found($path, "token", "null");
		}
		// Token cannot have opening brackets
		else if (preg_match('/{/u', $token_str)) {
			$this->throw_error_bad_construct($path, $token_str, "duplicate opening bracket {", "token");
		}
		// Token cannot have closing brackets
		else if (preg_match('/}/u', $token_str)) {
			$this->throw_error_bad_construct($path, $token_str, "duplicate closing bracket }", "token");
		}

		$token = $token_array[0];
		// Token cannot have whitespaces or if a connector is used it cannot have whitespaces after %
		if (preg_match('/\s(?!.*%)/u', $token)) {
			$this->throw_error_bad_construct($path, $token, "token declaration cannot have whitespaces", "token");
		}

		if (preg_match('/template\./u', $token)) {	// {template.template_name}
			$this->token_template($path, $token);
		}
		else if (preg_match('/\./u', $token)) {		// {entity.property}
			$this->token_dot_common($path, $token);
		}
		else {										// {property}
			$this->token_common($path, $token);	
		}
	}

	/**
	 * Validate token in dot format for "template".template
	 * @param string $path  Local in the json structure
	 * @param string $token Token
	 */
	private function token_template($path, $token)
	{
		$token_array = $this->token_dot($path, $token);
		if ($token_array[0] != "template") { // If not "template"
			$this->throw_error_bad_construct($path, $token, "missing \"template\"", "token");
		}
		$this->token_function_template($path, $token_array[1]); // template%entity
	}

	/**
	 * Validate token in dot format for entity.property
	 * @param string $path  Local in the json structure
	 * @param string $token Token
	 */
	private function token_dot_common($path, $token)
	{
		$token_array = $this->token_dot($path, $token);
		$entity = $this->token_function_entity($path, $token_array[0]); // connector%entity or s/f:|p/m:%entity

		// If entity.@link...</a>
		if (strlen($token_array[1]) > 1 && mb_substr($token_array[1], 0, 1) == "@") {
			$this->validate_link_variable($path, $token);
		}
		$this->is_entity_dot_property_defined($path, $entity, $token_array[1]); // entity.property

		return $token_array;
	}

	/**
	 * Validate token without dot for property
	 * @param string $path  Local in the json structure
	 * @param string $token Token
	 */
	private function token_common($path, $token)
	{
		// On s:|p:%property or f:|m:%property format
		if ($this->is_property_sp_fm($path, $token)) {
			return;
		}

		$variables = $this->token_function($path, $token);
		// On connector%property format
		if (count($variables) == 2) {
			$this->is_connector_defined($path, $variables[0]);
			$this->is_property_defined($path, $variables[1], self::TEXT_TYPE);
		}
		// On @link....</a> format
		else if (strlen($variables[0]) > 1 && mb_substr($variables[0], 0, 1) == "@") {
			$link = mb_substr($variables[0], 1);
			$this->validate_link_variable($path, $link);
			$this->is_property_defined($path, $variables[0], self::TEXT_TYPE);
		}
		// On property format
		else {
			$this->is_property_defined($path, $variables[0], self::TEXT_TYPE);
		}
	}

	/**
	 * Validate token in dot format
	 * @param string $path  Local in the json structure
	 * @param string $token Token
	 */
	private function token_dot($path, $token)
	{
		$token_array = preg_split("/\./", $token);
		if (count($token_array) != 2) {
			$this->throw_error_bad_construct($path, $token, "not in \"variable.variable\" format", "token");
		}

		return $token_array;
	}

	/**
	 * Validate token fuction for template%entity
	 * @param string $path           Local in the json structure
	 * @param string $token_function Token function
	 */
	private function token_function_template($path, $token_function)
	{
		// Validate variable%variable
		$variables = $this->token_function($path, $token_function);
		if (empty($variables)) { // Token cannot be null
			$this->throw_error_bad_construct($path, $token_function, "null token", "token");
		}

		// Check if template is defined
		$this->is_template_defined($path, $variables[0]);
		if (count($variables) == 2) { // If template%entity, check if entity is defined
			$this->is_entity_defined($path, $variables[1]);
		}

		// Push to queue if in list of not used and not in queue already
		if (in_array($variables[0], $this->unused_templates) && !in_array($variables[0], $this->templates_queue)) {
			array_push($this->templates_queue, $variables[0]);
		}
	}

	/**
	 * Validate token fuction for connector%entity or s/f:|p/m:%entity
	 * @param string $path           Local in the json structure
	 * @param string $token_function Token function
	 */
	private function token_function_entity($path, $token_function)
	{
		$variables = $this->token_function($path, $token_function); // connector%entity
		if (empty($variables)) {
			$this->throw_error_bad_construct($path, $token_function, "null token", "token");
		}

		// Connector or singular|plural or feminine|masculine
		if (count($variables) == 2) {
			$this->is_connector_defined($path, $variables[0]);
			$this->is_entity_defined($path, $variables[1]);
			return $variables[1];
		} // entity
		else {
			$this->is_entity_defined($path, $variables[0]);
		}

		return $variables[0];
	}

	/**
	 * Validate token fuction
	 * @param string $path           Local in the json structure
	 * @param string $token_function Token function
	 */
	private function token_function($path, $token_function)
	{
		// Split through %
		$function_array = preg_split("/%/", $token_function);
		if (count($function_array) > 2) {
			$this->throw_error_bad_construct($path, $token_function, "too many % functions", "token");
		}

		return $function_array;
	}

	/**
	 * Validate condition
	 * @param string $path      Local in the json structure
	 * @param string $condition Condition
	 */
	public function validate_condition($path, $condition)
	{
		$path .= "[condition]";

		// If condition equals "", then valid
		if ($condition == "") {
			return;
		}

		// If condition begins or ends with signs, then invalid
		if (preg_match('/^(\s)*(==|===|!=|>=|>|<=|<|&&|\|\|)/u', $condition)) {
			$this->throw_error_bad_construct($path, $condition, "condition cannot start with signs", "condition");
		}
		if (preg_match('/(==|===|!=|>=|>|<=|<|&&|\|\|)(\s)*$/u', $condition)) {
			$this->throw_error_bad_construct($path, $condition, "condition cannot end with signs", "condition");
		}

		// Split through ( and )
		$condition_array = preg_split("/(\()|(\))/u", $condition, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$nr_par = 0; // counter for number of mismatched parenthesis
		$can_use_and_or = false; // bool to check if can use && or ||
		$can_use_signs = false; // bool to check if can use ==, !=, >=, >, <= or <
		$can_open_parenthesis = true; // bool to check if can use (

		// For each expression
		foreach ($condition_array as $expr) {
			switch ($expr) {
				case "(":
					if (!$can_open_parenthesis) { // cannot do )(
						$this->throw_error_expected_found($path, self::BOOL_EXPR, $expr);
					}
					$nr_par++;
					break;
				case ")":
					$can_use_and_or = true;
					$can_use_signs = true;
					$can_open_parenthesis = false;
					$nr_par--;
					break;
				default:
					$this->validate_boolean_and_or($path, $expr, $can_use_and_or, $can_use_signs);
					$can_open_parenthesis = true;
					break;
			}
			if ($nr_par < 0) {
				$this->throw_error_expected_found($path, "opening parenthesis (", $expr);
			}
		}
		if ($nr_par > 0) {
			$this->throw_error_expected_found($path, "closing parenthesis )", $expr);
		}
	}

	/**
	 * Validate boolean expression for && and ||
	 * @param string $path       	 Local in the json structure
	 * @param string $expression 	 Booelan expression string
	 * @param bool   $can_use_and_or Whether it can be used && or ||
	 * @param bool   $can_use_signs  Whether it can be used <,<=,>,>=,== or !=
	 */
	private function validate_boolean_and_or($path, $expression, &$can_use_and_or, &$can_use_signs)
	{
		// Split through || and &&
		$condition_array = preg_split("/( \|\| )|( \&\& )/", $expression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$first_char = $condition_array[0];
		$is_and_or = trim($first_char) == "&&" || trim($first_char) == "||";
		if ($is_and_or && !$can_use_and_or) { // cannot use && or || but used them
			$this->throw_error_expected_found($path, self::BOOL_EXPR, $first_char);
		}

		foreach ($condition_array as $expr) {
			switch ($expr) {
				case " && ":
					$this->and_or($path, $expr, $can_use_and_or);
					break;
				case " || ":
					$this->and_or($path, $expr, $can_use_and_or);
					break;
				default:
					$this->validate_boolean_expression($path, $expr, $can_use_signs);
					$can_use_and_or = true;
					break;
			}
		}
	}

	/**
	 * Consume && or ||
	 * @param string $path       	 Local in the json structure
	 * @param string $expression 	 Booelan expression string
	 * @param bool   $can_use_and_or Whether it can be used && or ||
	 */
	private function and_or($path, $expression, &$can_use_and_or)
	{
		if (!$can_use_and_or) {
			$this->throw_error_expected_found($path, self::BOOL_EXPR, $expression);
		}
		$can_use_and_or = false; // used && or ||, cannot use again
	}

	/**
	 * Validate boolean expression
	 * @param string $path       	 Local in the json structure
	 * @param string $expression 	 Booelan expression string
	 * @param bool   $can_use_signs  Whether it can be used <,<=,>,>=,== or !=
	 */
	private function validate_boolean_expression($path, $expression, &$can_use_signs)
	{
		// Split through sign
		$express_array = preg_split('/<=|>=|<|>|===|==|!=/u', $expression);

		// A boolean expression cannot be just a number or a string
		$pattern = '/(?<!.)-?(\d|".*")+$/u';
		if (preg_match($pattern, $expression)) {
			$this->throw_error_bad_construct($path, $expression, "not a boolean expression", "condition");
		}

		// Cannot use <=|>=|<|>|===|==|!= but used them
		$first_elem = trim($express_array[0]);
		$is_sign = $first_elem == ">=" || $first_elem == ">" || $first_elem == "<=" || $first_elem == "<" || $first_elem == "===" || $first_elem == "==" || $first_elem == "!=";
		if ($is_sign && !$can_use_signs) {
			$this->throw_error_expected_found($path, self::BOOL_EXPR, "equation sign");
		}

		// Too many expression signs
		if (count($express_array) > 2) {
			$this->throw_error_bad_boolean($path, $expression);
		}

		// For each side of expression
		foreach ($express_array as $expr) {
			$exp = trim($expr);
			// If not a number or a string
			if (!preg_match($pattern, $exp) && !($exp == "" && $can_use_signs)) {
				$this->negation($path, trim($expr)); // !entity.property or !property
			}
		}
	}

	/**
	 * Consume !
	 * @param string $path    Local in the json structure
	 * @param string $express Booelan expression string
	 */
	private function negation($path, $express)
	{
		$strlen = mb_strlen($express, 'UTF-8');
		if ($strlen > 0 && $express[0] == "!") {
			// Consume !
			$express = mb_substr($express, 1, $strlen - 1, 'UTF-8');
			// If a number or string, throw error
			if (preg_match('/(?<!.)-?(\d|".*")+$/u', $express)) {
				$this->throw_error_bad_construct($path, $express, "\"!\" sign should be followed by a variable", "condition");
			}
		}

		// If not a number
		if (!preg_match('/(?<!.)-?(\d)+$/u', $express)) {
			$this->validate_arithmetic($path, $express); // entity.property
		}
	}

	/**
	 * Validate arithmetic signals
	 * @param string $path    				Local in the json structure
	 * @param string $arithmetic_expression Arithmetic booelan expression string
	 */
	private function validate_arithmetic($path, $arithmetic_expression)
	{
		// Split through +, -, %, /
		$operands = preg_split("/ \+ | - |\s?%\s?|\s?\/\s?|\s?\*\s?/", $arithmetic_expression, -1, PREG_SPLIT_NO_EMPTY);

		// For each operand
		foreach ($operands as $operand) {
			// If not a number
			if (!preg_match('/(?<!.)-?(\d)+$/u', $operand)) {
				$this->condition_dot($path, $operand);
			}
		}
	}

	/**
	 * Validate condition in dot format
	 * @param string $path    	Local in the json structure
	 * @param string $condition Condition
	 */
	private function condition_dot($path, $condition)
	{
		// Split through .
		$condition_array = preg_split("/\./", $condition);
		$nr_args = count($condition_array);
		if ($nr_args > 2 || $condition == "") { // Too many dots
			$this->throw_error_bad_construct($path, $condition, "not in \"variable.variable\" format", "condition");
		}

		if ($nr_args == 2) { // #arg.property
			$this->is_arg_condition_defined($path, $condition_array[0], $condition_array[1]);
		}
		else { // property - no dots
			$this->validate_variable($path, $condition_array[0]);
			$this->is_property_defined($path, $condition_array[0], self::CONDITION_TYPE);
		}
	}

	/**
	 * Validate variable name
	 * @param string $path    	Local in the json structure
	 * @param string $variable  Variable name
	 */
	private function validate_variable($path, $variable)
	{
		if ($variable == "") {
			$this->throw_error_bad_variable($path, "empty variable");
		}

		if (!preg_match('/(?<!.)(([@A-Za-z][\w$]*(\.[\w$]+)?(\[\d+])?)|(^#arg))$(?!.)/u', $variable)) {
			$this->throw_error_bad_variable($path, $variable);
		}
	}

	/**
	 * Validate template variable name
	 * @param string $path    	Local in the json structure
	 * @param string $variable  Variable name
	 */
	private function validate_template_variable($path, $variable)
	{
		if ($variable == "") {
			$this->throw_error_bad_variable($path, "variable is empty");
		}

		if (!preg_match('/(?<!.)[A-Za-z][\w$]*(\.[\w$]+)?(\[\d+])?$(?!.)/u', $variable)) {
			echo $this->file_name . "Warning: " . $path . " - Invalid variable name: \"$variable\"<br>";
		}
	}

	/**
	 * Validate link name
	 * @param string $path    	Local in the json structure
	 * @param string $variable  Variable name
	 */
	private function validate_link_variable($path, $variable)
	{
		if ($variable == "") {
			$this->throw_error_bad_variable($path, "empty variable");
		}

		if (!preg_match('/(?<!.)([A-Za-z#][\w]+\.@)?[A-Za-z#][\w$]*(\.[\w$]+)?(\[\d+])?$(?!.)/u', $variable)) {
			$this->throw_error_bad_variable($path, $variable);
		}
	}

	/**
	 * Check if property is defined according to the dictionary
	 * @param string $path    	Local in the json structure
	 * @param string $property  Property name
	 * @param string $type      Either a text or a condition type
	 */
	private function is_property_defined($path, $property, $type)
	{
		$this->validate_variable($path, $property);
		if ($this->check_entities && !in_array($property, $this->vars_array->properties->{$type})) {
			$this->throw_error_bad_construct($path, $property, "property not defined for [" . $type . "]", "token");
		}

		// Push to properties list
		if ($this->get_entities && !in_array($property, $this->vars_array->properties->{$type})) {
			array_push($this->vars_array->properties->{$type}, $property);
		}
	}

	/**
	 * Check if #arg.property condition is defined according to the dictionary
	 * @param string $path    	Local in the json structure
	 * @param string $entity  	#arg
	 * @param string $property  Property name
	 */
	private function is_arg_condition_defined($path, $entity, $property)
	{
		$full_condition = $entity . "." . $property;
		if ($entity !== "#arg") {
			$this->throw_error_bad_construct($path, $full_condition, "not in #arg.property format", "condition");
		}
		$this->validate_variable($path, $property);
		if ($this->check_entities && !in_array($full_condition, $this->vars_array->properties->condition)) {
			$this->throw_error_bad_construct($path, $full_condition, "#arg.property not defined", "token");
		}

		// Push to properties list
		if ($this->get_entities && !in_array($full_condition, $this->vars_array->properties->condition)) {
			array_push($this->vars_array->properties->condition, $full_condition);
		}
	}

	/**
	 * Check if entity is defined according to the dictionary
	 * @param string $path    Local in the json structure
	 * @param string $entity  Entity name
	 */
	private function is_entity_defined($path, $entity)
	{
		$this->validate_variable($path, $entity);
		$entities_keys = array_keys((array)$this->vars_array->entities);
		if ($this->check_entities && !in_array($entity, $entities_keys)) {
			$this->throw_error_bad_construct($path, $entity, "entity not defined", "token");
		}

		// Push to entities list
		if ($this->get_entities && !in_array($entity, $entities_keys)) {
			$this->vars_array->entities->{$entity} = [];
		}
	}

	/**
	 * Check if entity.property is defined according to the dictionary
	 * @param string $path     Local in the json structure
	 * @param string $entity   Entity name
	 * @param string $property Property name
	 */
	private function is_entity_dot_property_defined($path, $entity, $property)
	{
		$this->is_entity_defined($path, $entity);
		$this->validate_variable($path, $property);

		// Check entities
		if ($this->check_entities) {
			$has_error = true;

			// enitity not specified (= #arg)
			if ($entity == "#arg") {
				foreach ($this->vars_array->entities->entities_properties as $properties) {
					if (in_array($property, $properties)) {
						$has_error = false;
						break;
					}
				}
			}
			// entity specified
			else if (in_array($property, $this->vars_array->entities->entities_properties->{$this->vars_array->entities->{$entity}})) {
				$has_error = false;
			}

			// if error, throw exception
			if ($has_error) {
				$this->throw_error_bad_construct($path, $entity . "." . $property, $entity . " does not have property \"" . $property . "\"");
			}
		}

		// Push to entity.properties list
		if ($this->get_entities && property_exists($this->vars_array->entities, $entity) && !in_array($property, $this->vars_array->entities->{$entity})) {
			array_push($this->vars_array->entities->{$entity}, $property);
		}
	}

	/**
	 * Check if template is defined according to the dictionary
	 * @param string $path     Local in the json structure
	 * @param string $template Template name
	 */
	private function is_template_defined($path, $template)
	{
		$this->validate_template_variable($path, $template);
		if (!in_array($template, $this->valid_templates) && $this->check_templates) {
			echo $this->file_name . "Warning: " . $path . " - Wrong token construction: \"{" . $template . "}\", template not defined<br>";
		}
	}

	/**
	 * Check if property is in singular|plural format
	 * @param string $path   Local in the json structure
	 * @param string $entity Entity name
	 */
	private function is_property_sp_fm($path, $entity)
	{
		$entity_array = preg_split('/\|/u', $entity);
		if (count($entity_array) != 2 && count($entity_array) != 3) {
			return false;
		}

		$has_singular = preg_match('/^s:(.(?!:))*$/u', $entity_array[0]);
		$has_plural = preg_match('/^p:(.(?!:))+$/u', $entity_array[1]);
		$incorrect_form_sp = ($has_singular + $has_plural) == 1;

		$has_f = preg_match('/^f:(.(?!:))+$/u', $entity_array[0]);
		$has_m = preg_match('/^m:(.(?!:))+$/u', $entity_array[1]);
		$has_n = 0;
		$incorrect_form_fm = false;
		if (count($entity_array) == 3) {
			$has_n = preg_match('/^n:(.(?!:))+$/u', $entity_array[2]);
			$incorrect_form_fm = ($has_f + $has_m + $has_n) == 1 || ($has_f + $has_m + $has_n) == 2;
		}
		else {
			$incorrect_form_fm = ($has_f + $has_m) == 1;
		}

		if ($incorrect_form_sp) {
			$this->throw_error_bad_construct($path, $entity, "should be s:string|p:string", "token");
		}
		if ($incorrect_form_fm) {
			$this->throw_error_bad_construct($path, $entity, "should be f:string|m:string(|n:string)", "token");
		}

		$is_singular_plural = ($has_singular + $has_plural) == 2;
		$is_f_m = ($has_f + $has_m + $has_n) == 2 || ($has_f + $has_m + $has_n) == 3;
		return $is_singular_plural || $is_f_m;
	}

	/**
	 * Check if connector is defined
	 * @param string $path   	Local in the json structure
	 * @param string $connector Connector name
	 */
	private function is_connector_defined($path, $connector)
	{
		if ($this->is_property_sp_fm($path, $connector)) {
			return;
		}

		if ($this->check_entities && !in_array(strtolower($connector), $this->vars_array->connectors->{$this->language})) {
			$this->throw_error_bad_construct($path, $connector, "connector not defined", "token");
		}

		// Push to connectors list
		if ($this->get_entities && !in_array(strtolower($connector), $this->vars_array->connectors)) {
			array_push($this->vars_array->connectors, strtolower($connector));
		}
	}

	/**
	 * Throw errors
	 */
	/**
	 * Throw missing text key exception
	 * @param string $path Local in the json structure
	 */
	private function throw_error_missing_text($path)
	{
		throw new ValidationErrorException("Missing \"text\" key on element", $path);
	}

	/**
	 * Throw missing condition key exception
	 * @param string $path Local in the json structure
	 */
	private function throw_error_missing_condition($path)
	{
		throw new ValidationErrorException("Missing \"condition\" key on element", $path);
	}

	/**
	 * Throw missing bracket exception
	 * @param string $path    Local in the json structure
	 * @param string $bracket Bracket missing
	 * @param string $chunk   Text chunk where it is missing
	 */
	private function throw_error_missing_bracket($path, $bracket, $chunk)
	{
		throw new ValidationErrorException("Missing \"" . $bracket . "\" on chunk \"" . $chunk . "\"", $path);
	}

	/**
	 * Throw expected/found exception
	 * @param string $path     Local in the json structure
	 * @param string $expected Expected string
	 * @param string $found    But found string
	 */
	private function throw_error_expected_found($path, $expected, $found)
	{
		throw new ValidationErrorException("Expected \"" . $expected . "\", found \"" . $found . "\"", $path);
	}

	/**
	 * Throw invalid construction exception
	 * @param string $path    		Local in the json structure
	 * @param string $construct   	Wrongly constructed string
	 * @param string $explanation   Explanation of why it is wrongly constructed
	 * @param string $type			The type of the construction (token, condition or "")
	 */
	private function throw_error_bad_construct($path, $construct, $explanation, $type = "")
	{
		if ($type !== "") {
			$type .= " ";
		}
		throw new ValidationErrorException("Wrong " . $type . "construction: \"{" . $construct . "}\", " . $explanation, $path);
	}

	/**
	 * Throw invalid variable name exception
	 * @param string $path     Local in the json structure
	 * @param string $variable Invalid variable name
	 */
	private function throw_error_bad_variable($path, $variable)
	{
		throw new ValidationErrorException("Invalid variable name: \"" . $variable . "\"", $path);
	}

	/**
	 * Throw invalid boolean expression construction exception
	 * @param string $path     Local in the json structure
	 * @param string $express  Wrongly constructed boolean expression
	 */
	private function throw_error_bad_boolean($path, $express)
	{
		throw new ValidationErrorException("Wrong boolean expression construction: \"" . $express . "\"", $path);
	}

	/**
	 * Throw invalid link construction exception
	 * @param string $path    		Local in the json structure
	 * @param string $link   	    Wrongly constructed link
	 * @param string $explanation   Explanation of why it is wrongly constructed
	 */
	private function throw_error_bad_link($path, $link, $explanation)
	{
		throw new ValidationErrorException("Wrong link construction: \"" . $link . "\", " . $explanation, $path);
	}

	/**
	 * Generate dictionary for context
	 * @param string $main_entity_class Main entity class name
	 */
	public static function generate_dictionary($context)
	{
		$main_entity = get_globals()["main_entities_classes"][$context];
		require_once(self::CONTEXTS_DIR . $context . "/managers/propertiesmanager.php");
		require_once(self::CONTEXTS_DIR . $context . "/entities/" . $main_entity[1] . ".php");

		$dictionary = array();
		$properties_manager = "PropertiesManager" . ucfirst($context);
		$properties_manager::construct_properties();
		$properties_conditions = array_map(
			function ($property) {
				return $property->name;
			},
			$properties_manager::get_template_properties()
		);
		$properties_arg_conditions = array_map(
			function ($property) {
				return $property->name;
			},
			$properties_manager::get_template_arg_properties()
		);
		$dictionary["properties"]["condition"] = array_merge($properties_conditions, $properties_arg_conditions);

		$dictionary["entities"] = [];
		$dictionary["properties"]["text"] = [];
		$dictionary["entities"]["#arg"] = "any";
		$entities = $main_entity[0]::get_entities_list();
		foreach ($entities as $entity_name => $entity_getter) {
			switch (get_class($entity_getter)) {
				case EntityGetterManager::class:
				case EntityGetterFlat::class: {
					array_push($dictionary["properties"]["text"], $entity_name);
					break;
				}
				case EntityGetterSub::class: {
					array_push($dictionary["properties"]["text"], $entity_name);
					$props = "_properties";
					$dictionary["entities"][$entity_name] = $entity_getter->classname . $props;
					$dictionary["entities"]["entities_properties"][$entity_getter->classname . $props] = [];
					$entities_list = $entity_getter->classname::get_entities_list();
					foreach ($entities_list as $prop_entity_name => $prop_entity_getter) {
						array_push($dictionary["entities"]["entities_properties"][$entity_getter->classname . $props], $prop_entity_name);
					}
					break;
				}
				default:
					break;
			}
		}

		$grammars = scandir(__DIR__ . "/../grammars");
		foreach ($grammars as $lang) {

			if (!in_array($lang, array_keys(get_globals()["languages"]))) {
				continue;
			}

			require_once(__DIR__ . "/../grammars/" . $lang . "/grammar.php");
			$grammar_class_name = "Grammar" . strtoupper($lang);
			$dictionary["connectors"][$lang] = array_map(
				function ($connector) {
					return $connector;
				},
				array_keys($grammar_class_name::get_connectors())
			);
		}

		// Write file
		$dictionary_file = fopen(self::CONTEXTS_DIR . $context . "/dictionary/vars.json", "w");
		fwrite($dictionary_file, json_encode($dictionary));
	}
}
