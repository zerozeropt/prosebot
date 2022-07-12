<?php
require_once(__DIR__.'/../utils.php');

/**
 * Gender enum
 * 
 * @author zerozero.pt
 */
abstract class NameGender
{
	const MALE = 1;
	const FEMALE = 2;
	const NEUTRAL = 3;
}

/**
 * Number enum
 * 
 * @author zerozero.pt
 */
abstract class NameNumber
{
	const SINGULAR = 4;
	const PLURAL = 5;
}

/**
 * Structure composed of text (content), gender (NameGender) and number (NameNumber)
 *
 * @author zerozero.pt
 */
class TextStructure
{
	/**
	 * @param string     $text   Text
	 * @param NameGender $gender Gender
	 * @param NameNumber $number Singular or Plural
     */
	function __construct($text, $gender, $number)
	{
		$this->text = $text;
		$this->gender = $gender;
		$this->number = $number;
	}

	/**
	 * Get content
	 * @return string text
     */
	function get_text()
	{
		return $this->text;
	}

	/**
	 * Get gender
	 * @return NameGender gender
     */
	function get_gender()
	{
		return $this->gender;
	}

	/**
	 * Get number
	 * @return NameNumber number
     */
	function get_number()
	{
		return $this->number;
	}

	/**
	 * Get content
	 * @return string
     */
	function __toString()
	{
		return $this->text;
	}
}

/**
 * Class to handle language grammar structures
 * 
 * @author zerozero.pt
 */
abstract class Grammar
{
	/**
	 * Chars used in x:|y: forms
	 * @var array[string=>NameGender]
	 */
	private static $elem_chars = array(
		"m" => NameGender::MALE,
		"f" => NameGender::FEMALE,
		"s" => NameNumber::SINGULAR,
		"p" => NameNumber::PLURAL,
		"n" => NameGender::NEUTRAL
	);

	/**
	 * Get standard connector
	 * @return string Standard connector for each language
     */
	abstract static function get_st_connector();

	/**
	 * Get element from array if exists
	 * @param array	 $array  Generic array of elements
	 * @param string $key    Key of the element
	 * @return TextStructure Element of the array or null if it does not exist
     */
	public static function get_elem($array, $key)
	{
		if (!array_key_exists($key, $array) || $array[$key] == "") {
			return null;
		}

		$name = $array[$key];

		$gender = NameGender::NEUTRAL;
		$new_key = $key . "_GENDER";
		if (array_key_exists($new_key, $array)) {
			$elem = $array[$new_key];
			if ($elem == '0') {
				$gender = NameGender::MALE;
			}
			elseif ($elem == '1') {
				$gender = NameGender::FEMALE;
			}
		}

		$number = null;
		$new_key = $key . "_PLURAL";
		if (array_key_exists($new_key, $array)) {
			$elem = $array[$new_key];
			if ($elem == '0') {
				$number = NameNumber::SINGULAR;
			}
			elseif ($elem == '1') {
				$number = NameNumber::PLURAL;
			}
		}

		return new TextStructure($name, $gender, $number);
	}

	/**
	 * Get entity text content
	 * @param string or TextStructure $entity Entity to be treated
	 * @return string Content of the entity
     */
	public static function treat_entity($entity)
	{
		if ($entity instanceof TextStructure) {
			return $entity->get_text();
		}
		return $entity;
	}

	/**
	 * Pick element of a x:|y: form
	 * @param  array      $elems  Array of elems chars
	 * @param  NameGender $gender Gender of the element
	 * @param  NameNumber $gender Number of the element
	 * @return string The element char or null if incorrect form
     */
	protected static function pick_elem($elems, $gender, $number)
	{
		foreach ($elems as $elem) {
			$tmp = explode(":", $elem);
			if ((count($tmp) == 2 || count($tmp) == 3) && static::evaluate_elem($tmp[0], $gender, $number)) {
				return $tmp[1];
			}
		}
		return null;
	}

	/**
	 * Evaluate element of a x:|y: form
	 * @param  string     $elem   One of the elements chars
	 * @param  NameGender $gender Gender of the element
	 * @param  NameGender $gender Number of the element
	 * @return bool True if element is valid, false otherwise
     */
	private static function evaluate_elem($elem, $gender, $number)
	{
		foreach (str_split($elem) as $char) {
			if (!array_key_exists($char, static::$elem_chars)) {
				return false;
			}

			$x = static::$elem_chars[$char];
			if ($x != $gender && $x != $number) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Converts array to a string like list separated by commas
	 * @param array  $list 		   Array of elements
	 * @param string $st_connector Standard connector
	 * @return TextStructure List of elements in a string separated by commas
	 */
	public static function list_str($list, $st_connector)
	{
		$last = array_pop($list);
		$plural = count($list) > 0;

		$string = $plural ? implode(", ", $list) . $st_connector . $last : $last;
		return new TextStructure($string, NameGender::NEUTRAL, $plural ? NameNumber::PLURAL : NameNumber::SINGULAR);
	}

	/**
	 * Get correct connector form or apply connector function
	 * @param string     $connector_name Name of a connector
	 * @param NameGender $gender         Gender of the connector
	 * @param NameNumber $number         Number of the connector
	 * @param string     $text           Content
	 * @return string  Connector form
	 */
	private static function find_connector($connector_name, $gender, $number, $text)
	{
		$connector = static::get_connectors()[$connector_name];
		if (is_array($connector)) {
			$index = ($number == NameNumber::SINGULAR || $gender == NameGender::NEUTRAL) ? ((int)$gender - 1) : Utils::clamp(((int)$number - 3 + (int)$gender), 0, 4);
			return $connector[$index];
		}
		
		return static::$connector($gender, $number, $text);
	}

	/**
	 * Apply connector function
	 * @param string               $func   One of the available connector forms
	 * @param TextStructure|string $entity Entity to whom the connector relates
	 * @return string Name of the connector function
	 */
	public static function apply_func($func, $entity)
	{
		$elems = explode("|", $func);
		$gender = null;
		$number = null;
		$text = $entity;
		if ($entity instanceof TextStructure) {
			$gender = $entity->get_gender();
			$number = $entity->get_number();
			$text = $entity->get_text();
		}

		if (count($elems) > 1) {
			if (is_numeric($entity)) {
				$number = $entity !== 1 ? NameNumber::PLURAL : NameNumber::SINGULAR;
			}
			return static::pick_elem($elems, $gender, $number);
		}
		return static::find_connector($func, $gender, $number, $text);
	}

	/**
	 * Get connectors list in corresponding language
	 * @return array List of connectors
	 */
	abstract static function get_connectors();
}
