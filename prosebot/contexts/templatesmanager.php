<?php

require_once('templates.php');
require_once(__DIR__.'/../utils.php');
require_once(__DIR__.'/../global_vars.php');
require_once('entities.php');
require_once(__DIR__.'/../exceptions.php');

/**
 * Enum of scoring mechanics for evaluation of the generated content
 * 
 * @author zerozero.pt
 */
abstract class GenerationAnalyser
{
	const LEXICAL_DIVERSITY = "lexical_diversity";
	const SENTENCE_SIZE = "sentence_size";
	const SENTENCE_MEDIUM_SIZE = "sentence_medium_size";
	const POINTS = "points";
}

/**
 * Class for templates management and articles production
 * 
 * @author zerozero.pt
 */
abstract class TemplatesManager
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Context to be handled (one context at a time)
	 * @var string
	 */
	protected static $context = "";
	/**
	 * If the templates weights were calculated
	 * @var bool
	 */
	protected static $weighted = True;
	/**
	 * If the templates weights need to be calculated
	 * @var bool
	 */
	protected static $update_weight = True;
	/**
	 * Periods replacement for PHP variables creation
	 * @var string
	 */
	protected static $escape_period = "#$#";
	/**
	 * Generic entity
	 * @var string
	 */
	protected static $arg_str = "#arg";
	/**
	 * Templates directory
	 * @var string
	 */
	protected static $templates_dir;
	/**
	 * Language directory
	 * @var string
	 */
	protected static $language_dir;
	/**
	 * Grammar class
	 * @var string
	 */
	public static $grammar_class;

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
	 * @param string $language Language
	 * @param string $context  Context
	 */
	protected function __construct($language, $context)
	{
		static::$context = $context;
		static::$language_dir = $language;
		$success = require_once(__DIR__.'/../grammars/'.static::$language_dir.'/grammar.php');
		if (!$success) {
			throw new UndefinedLanguageException("Language does not exist.");
		}

		static::$templates_dir = __DIR__.'/'.$context.'/templates/'.static::$language_dir.'/';
		static::$grammar_class = "Grammar" . strtoupper(static::$language_dir);
	}

	/**
	 * Load templates files
	 * @param array $files Array of files names
	 * @return array Array of arrays of templates for each file
	 */
	protected static function read_template_files($files)
	{
		$templates = array();
		foreach ($files as $file) {
			$templates[$file] = static::read_templates_file(static::$templates_dir.$file.".json");
		}

		return $templates;
	}

	/**
	 * Parse templates file
	 * @param string $file File path
	 * @return Template[] Array of templates
	 */
	protected static function read_templates_file($file)
	{
		$result = array();
		$json_array = Utils::json_validate(file_get_contents($file));

		foreach ($json_array as $key => $val) {
			if ($key !== null) {
				foreach ($val as $tmp) {
					array_push($result, new Template($tmp->text, $key, $tmp->condition, static::compute_weight($tmp->condition)));
				}
			}
		}
		return $result;
	}

	/**
	 * Generate fixed structure paragraph
	 * @param mixed      $main_entity The core entity of the context
	 * @param Template[] $templates   Array of templates
	 * @param string     $init_string The entry point of the template file
	 * @return string Paragraph
	 */
	protected function build_fixed_paragraph($main_entity, $templates, $init_string)
	{
		$filtered_templates = TemplatesManager::filter_valid_templates($templates, $main_entity, null, null, $init_string);
		$filtered_templates = TemplatesManager::filter_max_weight_templates($filtered_templates);
		$template = TemplatesManager::get_random_template_by_type($filtered_templates, $init_string);
		return TemplatesManager::template_recursive($templates, $template, $main_entity, null);
	}

	/**
	 * Filter templates holding valid conditions
	 * @param Template[]     $templates    Array of templates
	 * @param MainEntityData $main_entity  The core entity of the context
	 * @param string|null 	 $event_key    Key of an event or null if there is no event
	 * @param string|null 	 $arg_replace  Entity to replace #arg
	 * @param string|null 	 $template_key Key of the template file to validate
	 * @return string Paragraph
	 */
	protected static function filter_valid_templates($templates, $main_entity, $event_key = null, $arg_replace = null, $template_key = null)
	{
		$result = array();
		foreach (array_filter($templates, function ($elem) use ($template_key) {
			return $elem->get_type() == $template_key || $template_key == null;
		}) as $template) {
			if (static::is_valid_template($template->get_condition(), $main_entity, $event_key, $arg_replace)) {
				$result[] = $template;
			}
		}

		//tell the template it passed in validation (for selection weights update)
		foreach ($result as $template) {
			$template->validated();
		}

		return $result;
	}

	/**
	 * Filter maximum weight templates
	 * @param Template[] $templates Array of templates
	 * @return array Array of templates with the maximum weight
	 */
	protected static function filter_max_weight_templates($templates)
	{
		$max_templates = array();

		foreach ($templates as $template) {
			$found_arg = strpos($template->get_condition(), static::$arg_str) !== false;

			//if first of its type, or the most valuable so far, overwrite
			if (
				!array_key_exists($template->get_type(), $max_templates)
				|| ($template->get_weight() > $max_templates[$template->get_type()][0]->get_weight()
					&& !$found_arg)
			) {
				$max_templates[$template->get_type()] = array($template);
			}
			//if as value as the most value of its type, append
			elseif ($template->get_weight() == $max_templates[$template->get_type()][0]->get_weight() || $found_arg) {
				array_push($max_templates[$template->get_type()], $template);
			}
		}
		//flatten results array
		return call_user_func_array('array_merge', $max_templates);
	}

	/**
	 * Get a random template by calculating templates weights
	 * @param Template[] $templates     Array of templates
	 * @param Template   $result		Empty template
	 * @return Template Random template
	 */
	private static function get_random_template_by_weight($templates, $result) {
		//find the sum of weights
		$sum = array_reduce($templates, function ($i, $template) {
			return $i += $template->get_weight();
		});

		if ($sum == 0) {
			$result = $templates[array_rand($templates)];
		}
		else {
			//get random number from 0 to computed sum
			$r = Utils::get_rand() * $sum;

			//go through weights to pick corresponding template
			$idx = 0;
			foreach ($templates as $template) {
				$idx += $template->get_weight();
				if ($idx > $r) {
					$result = $template;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Get a random template for a given type
	 * @param Template[] $templates     Array of templates
	 * @param string     $template_type Type
	 * @return Template Random template
	 */
	protected static function get_random_template_by_type($templates, $template_type)
	{
		//filter templates by type
		$templates_filtered = array();
		foreach ($templates as $template) {
			if ($template->get_type() == $template_type) {
				$templates_filtered[] = $template;
			}
		}
		$templates = $templates_filtered;

		$result = new Template("", null, null);
		if (empty($templates)) {
			return $result;
		}

		//weighted choice
		if (static::$weighted) {
			$result = static::get_random_template_by_weight($templates, $result);
		}
		//completely random choice
		else {
			$result = $templates[array_rand($templates)];
		}

		//update weight of chosen template
		if (static::$update_weight) {
			$result->used();
		}

		return $result;
	}

	/**
	 * Evaluates a condition as an integer value, where 0 is false and 1 is true
	 * @param string $condition Condition
	 * @return int Condition value
	 */
	protected static function condition_eval($condition)
	{
		return eval("return (int)(" . $condition . ");");
	}

	/**
	 * Cleans the text, by removing unnecessary tags and special character
	 * @param string $text Condition
	 * @return string Clean text
	 */
	public static function sanitize($text)
	{
		$result = $text;

		if ($result == '') {
			return $result;
		}

		$result = preg_replace("/\r\n\t*/", "", $result);
		$result = preg_replace("/<\/*p>\n*/", "", $result);
		$result = preg_replace("/ +/", ' ', $result);
		$result = preg_replace("/ +,/", ',', $result);
		$result = preg_replace("/,+/", ',', $result);
		$result = preg_replace("/,? *\.+/", '.', $result);
		$result = preg_replace("/\.+/", '.', $result);
		$result = preg_replace("/\n +/", "\n", $result);
		$result = preg_replace("/,\s*,/", ',', $result);
		$result = preg_replace("/\.\s*\./", '.', $result);
		$result = preg_replace("/\s+\./", '.', $result);
		$result = trim($result);
		
		//tokenize sentences
		preg_match_all('/(?<=[.?!])\s+(?=(\w|<em>))/u', $result, $out, PREG_OFFSET_CAPTURE);
		$result = preg_split('/(?<=[.?!])\s+(?=(\w|<em>))/u', $result);

		//capitalize first letter
		foreach ($result as &$sentence) {
			if ($sentence == "") {
				continue;
			}

			$has_italic = false;
			$has_hyperlink = false;

			$after_tags = $sentence;
			preg_match('/(^<a[^>]*+>)(.*<\/a>.*$)/u', $sentence, $after_a_tag_array);
			if (count($after_a_tag_array) > 2) {
				$after_tags = $after_a_tag_array[2];
				$has_hyperlink = true;
			}
			preg_match('/(^<em>)(.*<\/em>.*$)/u', trim($after_tags), $after_i_tag_array);
			if (count($after_i_tag_array) > 2) {
				$after_tags = $after_i_tag_array[2];
				$has_italic = true;
			}

			$trimmed_str = trim($after_tags);
			$first_char = mb_substr($trimmed_str, 0, 1, 'UTF-8');
			$prev_chars = $has_hyperlink ? $after_a_tag_array[1] : "";
			$prev_chars .= $has_italic ? $after_i_tag_array[1] : "";
			$pos_chars = mb_substr($trimmed_str, 1, strlen($trimmed_str), 'UTF-8');
			$sentence = $prev_chars . mb_strtoupper($first_char, 'UTF-8') . $pos_chars;
		}

		$result = implode(" ", $result);
		$result = str_replace(static::$escape_period, ".", $result);
		$result = trim($result);
		return str_replace(static::$escape_period, ".", $result);
	}

	/**
	 * Find {tokens} in a text content
	 * @param string $text Text
	 * @return array Tokens
	 */
	protected static function find_entity_tokens($text)
	{
		preg_match_all("/{[^}]+}/", $text, $tokens, PREG_OFFSET_CAPTURE);
		return $tokens[0];
	}

	/**
	 * Traverse templates tree recursively and build text content by replacing tokens with values
	 * @param Template[]  $templates    		Array of templates
	 * @param Template    $template     		Current template being evaluated
	 * @param mixed       $main_entity  		The core entity of the context
	 * @param string|null $event_key    		Key of an event or null if there is no event
	 * @param string|null $args         		#arg entity passed
	 * @param array|null  $used_entities_parent Used entities parent   
	 * @param bool        $update_entities      Whether to update used_entities_parent
	 * @return string Generated text from template
	 */
	protected function template_recursive($templates, $template, $main_entity, $event_key = null, $args = null, &$used_entities_parent = null, $update_entities = false)
	{
		//find tokens
		$tokens = static::find_entity_tokens($template);

		if (empty($tokens)) {
			return $template->get_text();
		}

		$result = "";
		$used_entities = array();
		if ($used_entities_parent) {
			$used_entities = $used_entities_parent;
		}

		//treat replacing tokens
		$last_index = 0;
		foreach ($tokens as $token) {
			//extract entity
			$entity = substr($token[0], 1, -1);

			//append previous text to result
			$result .= substr($template->get_text(), $last_index, $token[1] - $last_index);

			//update index to first position after token
			$last_index = $token[1] + strlen($token[0]);

			//find arguments, if any (after %)
			$elems = explode("%", $entity);
			$elem = $elems[0];
			$passing_args = null;
			if (count($elems) == 2) {
				$passing_args = $elems[1];
			}

			//if it is sub template, call recursive interpretation function
			if (strpos($elem, "template.") === 0) {
				$entity = substr($elem, strlen("template."));

				$update_entities = false;
				//if arg passed, retransmit received args
				if ($passing_args !== null && $passing_args === static::$arg_str) {
					$update_entities = true;
					$passing_args = $args;
				}
				$filtered_templates = TemplatesManager::filter_valid_templates($templates, $main_entity, $event_key, $passing_args, $entity);
				$subsentence = TemplatesManager::get_random_template_by_type($filtered_templates, $entity);
				$result .= TemplatesManager::template_recursive($templates, $subsentence, $main_entity, $event_key, $passing_args, $used_entities, $update_entities);
			}
			else {
				if ($passing_args === null) {
					$entity = $elem;
					$func = null;
				} else {
					$entity = $passing_args;
					$func = mb_strtolower($elem);
				}

				//replace passed arguments
				$entity = str_replace(static::$arg_str, $args, $entity);
				
				//if entity was not used
				if (!array_key_exists($entity, $used_entities)) {

					// grab entity
					$parsed_entity = $main_entity->get_entity_from_main($this->entities_manager, $entity, $event_key, true);

					if ($parsed_entity === null) {
						$result .= "<error>";
						continue;
					}

					//put in cache
					$used_entities[$entity] = $parsed_entity;
				}

				$class_name = static::$grammar_class;

				//if there is no function, get entity and escape periods
				if ($func === null) {
					$parsed_entity = $used_entities[$entity];

					// Insert hyperlink
					if(strpos($entity, ".name") !== false && method_exists($main_entity, "get_full_entity")){
						$full_entity = $main_entity->get_full_entity($entity, $event_key);

						// Whether it was not already mentioned
						if(!$full_entity->has_mention()) {
							// Whether it has an hyperlink for entity
							if ($full_entity->has_link()) {
								$parsed_entity = $full_entity->get_link();
							}
							$full_entity->set_has_mention(true);
						}
					}

					$entity_str = $class_name::treat_entity($parsed_entity);
					$result .= str_replace(".", static::$escape_period, $entity_str);
				}
				//if there is a function, apply it
				else {
					$result .= $class_name::apply_func($func, $used_entities[$entity]);
				}
			}
		}

		if ($update_entities) {
			$used_entities_parent = $used_entities;
		}

		//append remaining string
		$result .= substr($template->get_text(), $last_index);

		return $result;
	}

	/**
	 * Replace variables in conditions by their values
	 * @param array       $filtered_properties	Array of properties that are present in condition
	 * @param mixed       $main_entity  		The core entity of the context
	 * @param string|null $event_key    		Key of an event or null if there is no event
	 * @param string      $condition    		Condition
	 * @param string|null $arg_replace  		Entity to replace #arg
	 * @return string Condition with variables replaced by their values
	 */
	private static function replace_properties_condition($filtered_properties, $main_entity, $event_key, $condition, $replacing_arg = null)
	{
		foreach ($filtered_properties as $property) {
			try {
				$function_name = $property->func;
				$value = "";
				if ($replacing_arg === null) {
					$value = $function_name($main_entity, $event_key);
				}
				else {
					$value = $function_name($replacing_arg, $main_entity, $event_key);
				}
				$value = is_numeric($value) ? $value : "\"" . $value . "\"";
				$condition = str_replace($property->name, $value, $condition);
			} catch (Error $e) {
				Utils::printP($e->getMessage());
			} catch (ErrorException $e2) {
				Utils::printP($e2->getMessage());
			}
		}

		return $condition;
	}

	/**
	 * Test codition to see if the template is valid
	 * @param string      $condition    Condition
	 * @param mixed       $main_entity  The core entity of the context
	 * @param string|null $event_key    Key of an event or null if there is no event
	 * @param string|null $arg_replace  Entity to replace #arg
	 * @return int If 0 then template is invalid, if >0 then is valid
	 */
	protected static function is_valid_template($condition, $main_entity, $event_key, $arg_replace = null)
	{
		if ($condition === null || $condition === "") {
			return true;
		}
		$found_arg = strpos($condition, static::$arg_str) !== false;

		//if in arg mode and no arg found, return true
		if ($arg_replace !== null && !$found_arg) {
			return true;
		}

		//if #arg in condition, we will deal with it later
		if ($found_arg) {
			if ($arg_replace === null) {
				return true;
			}

			//passed arg
			$replacing_arg = $main_entity->get_entity_from_main(null, $arg_replace, $event_key);
			$filtered_properties = array_filter(PropertiesManager::get_template_arg_properties(), function ($elem) use ($condition) {
				return preg_match('/'.$elem->name.'\b/u', $condition) === 1;
			});
			$condition = static::replace_properties_condition($filtered_properties, $main_entity, $event_key, $condition, $replacing_arg);
		}
		
		//use new handler, so we can catch undefined index errors (when $event_key is null)
		set_error_handler("Utils::exceptions_error_handler");
		
		//regular condition (no #arg)
		$filtered_properties = array_filter(PropertiesManager::get_template_properties(), function ($elem) use ($condition) {
			return preg_match('/\b'.$elem->name.'\b/u', $condition) === 1;
		});
		$condition = static::replace_properties_condition($filtered_properties, $main_entity, $event_key, $condition);

		if (strpos($condition, static::$arg_str) !== false) {
			return 0;
		}

		//use new handler, so we can catch undefined constants (when evaluating unexisting properties)
		set_error_handler("Utils::undefined_constant_handler", E_NOTICE);

		try {
			$res = static::condition_eval($condition);
		} catch (ErrorException $e) {
			$res = 0;
		}
		//restore default handler
		restore_error_handler();

		return $res;
	}

	/**
	 * Calculate condition's weight
	 * @param string $condition Condition
	 * @return int Weight of the condition
	 */
	protected static function compute_weight($condition)
	{
		if ($condition === null) {
			return 1;
		}

		$result = -1;
		$properties = PropertiesManager::get_template_properties();
		foreach ($properties as $property) {
			if (strpos($condition, $property->name) !== false) {
				$result = max($result, $property->weight);
			}
		}

		if ($result === -1) {
			$result = 1;
		}

		return $result;
	}

	/**
	 * Calculate the lexical diversity of the text
	 * @param string $text Text
	 * @return int Lexical diversity
	 */
	protected function text_diversity_lexical($text)
	{
		$array_count = array_count_values(explode(' ', strtolower($text)));
		arsort($array_count, SORT_NUMERIC);
		return round(100 / (array_sum(array_values($array_count)) / count($array_count)), 3);
	}

	/**
	 * Calculate the sentence with the biggest size
	 * @param string $text Text
	 * @return array Sentence with the biggest size [content, size]
	 */
	protected function text_sentence_size($text)
	{
		$tmp = strip_tags($text);
		$array = explode('.', $tmp);
		foreach ($array as $key => $value) {
			$tmp2 = count(preg_split('/\s+/', $value)) - 1;
			$lengths[$key] = $tmp2;
		}
		$maxs = array_keys($lengths, max($lengths));
		return array($array[$maxs[0]], $lengths[$maxs[0]]);
	}

	/**
	 * Calculate sentence medium size of the text
	 * @param string $text Text
	 * @return int|float Sentence medium size
	 */
	protected function text_sentence_medium_size($text)
	{
		$tmp = strip_tags($text);
		$array = explode('.', $tmp);
		$number_words = 0;
		$sentence_number = 0;
		foreach ($array as $value) {
			$tmp2 = count(preg_split('/\s+/', $value)) - 1;
			if ($tmp2 < 3) {
				$number_words += $tmp2;
			} else {
				$number_words += $tmp2;
				$sentence_number += 1;
			}
		}
		return $sentence_number !== 0 ? $number_words / $sentence_number : 0;
	}

	/**
	 * Calculate article score
	 * @param int $arg1 Lexical diversity
	 * @param int $arg2 Biggest sentence size
	 * @return float Points
	 */
	protected function points($arg1, $arg2)
	{
		//calculate points per diversity normal in range (45-75)
		$diversity = $arg1;
		$diversity_points = ($diversity - 40) / 30;

		//calculate points per max sentence length, ideal is 25, normal in range (15-35)
		$length = $arg2;
		$length_points = 1 - (abs($length - 25) / 10);

		$points = ($diversity_points * 0.65 + $length_points * 0.35) * 10;
		return round($points, 2);
	}

	/**
	 * Calculate text stats
	 * @param string $title     Article's title
	 * @param string $sub_title Article's sub title
	 * @param string $text      Full article's text
	 * @return array Stats
	 */
	protected function calculate_stats($title, $sub_title, $text)
	{
		$count = $this->text_diversity_lexical($title . $sub_title . $text);
		$article = $sub_title . $text;
		$sentence_size = $this->text_sentence_size($article);
		$setence_medium_size = $this->text_sentence_medium_size($article);
		$points = $this->points($count, $sentence_size[1]);

		return [
			GenerationAnalyser::LEXICAL_DIVERSITY => $count,
			GenerationAnalyser::SENTENCE_SIZE => $sentence_size,
			GenerationAnalyser::SENTENCE_MEDIUM_SIZE => $setence_medium_size,
			GenerationAnalyser::POINTS => $points
		];
	}
}
