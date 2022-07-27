<?php

require_once(__DIR__.'/../grammars.php');

/**
 * Class to handle language grammar structures in Portuguese
 * 
 * @author zerozero.pt
 */
class GrammarIT extends Grammar
{
	/**
	 * Standard connector between expressions
	 * @var string
	 */
	private static $st_connector = " e ";

	/**
	 * Get standard connector
	 * @return string Standard connector for Italian expressions
     */
	public static function get_st_connector()
	{
		return static::$st_connector;
	}

	/**
	 * List of cardinal numbers
	 * @var array
	 */
	private static $cardinali = array(
		0 => '',
		1 => array('un', 'una'),
		2 => 'due',
		3 => 'tre',
		4 => 'quattro',
		5 => 'cinque',
		6 => 'sei',
		7 => 'sette',
		8 => 'otto',
		9 => 'nove',
		10 => 'dieci',
		11 => 'undici',
		12 => 'dodici',
		13 => 'tredici',
		14 => 'quattordici',
		15 => 'quindici',
		16 => 'sedici',
		17 => 'diciassette',
		18 => 'diciotto',
		19 => 'dicianove',
		20 => 'venti',
		30 => 'trenta',
		40 => 'quaranta',
		50 => 'cinquanta',
		60 => 'sessanta',
		70 => 'settanta',
		80 => 'ottanta',
		90 => 'novanta',
		100 => 'cento',
		200 => 'duecento',
		300 => 'trecento',
		400 => 'quattrocento',
		500 => 'cinquecento',
		600 => 'seicento',
		700 => 'settecento',
		800 => 'ottocento',
		900 => 'novecento'
	);

	/**
	 * Get full written cardinal number for cardinals below 1000
	 * @param int 		 $int     Cardinal integer
	 * @param NameGender $gender  Gender
	 * @param int        $min_val Minimum value to evaluate
	 * @param int        $exp     Exponent
	 * @return string Full written cardinal number
     */
	private static function get_cardinale($int, $gender, $min_val = 0, $exp = 2)
	{
		if ($int < $min_val || $int >= 1000) {
			return "";
		}

		if ($int == 0) {
			return "zero";
		}

		if (array_key_exists($int, static::$cardinali)) {
			$res = static::$cardinali[$int];
			if (is_array($res)) {
				if ($gender === NameGender::FEMALE) {
					return $res[1];
				}
				return $res[0];
			}
			return $res;
		}

		$div = pow(10, $exp);
		$left = static::$cardinali[intdiv($int, $div) * $div];
		$right = static::get_cardinale($int % $div, $gender, $min_val, $exp - 1);

		if ($exp === 1 && ($right === "un" || $right === "una" || $right === "otto")) {
			$left = mb_substr($left, 0, strlen($left) - 1);

			if ($right === "un") {
				$right .= "o";
			}
		}

		return $left . $right;
	}

	/**
	 * Get full written cardinal number
	 * @param string     $text   Cardinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Full written cardinal number
     */
	public static function cardinale($text, $number, $gender)
	{
		$num = intval($text);
		$left_int = intdiv($num, 1000);
		$right_int = $num % 1000;

		$left = "";
		$right = "";
		if ($left_int > 1) {
			$left = static::get_cardinale($left_int, $gender, 2);
			$left .= "milla";
		}
		else if ($left_int === 1) {
			$left .= "mille";
		}

		if ($right_int > 0) {
			$right = static::get_cardinale($right_int, $gender);
		}

		return $left . $right;
	}

	/**
	 * Get full written cardinal number in feminine form
	 * @param string     $text   Cardinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @return string Full written cardinal number in feminine form
     */
	public static function cardinale_fem($text, $number)
	{
		return static::cardinale($text, $number, NameGender::FEMALE);
	}

	/**
	 * List of ordinal numbers
	 * @var array
	 */
	private static $ordinali = array(
		0 => '',
		1 => 'prim',
		2 => 'second',
		3 => 'terz',
		4 => 'quartt',
		5 => 'quint',
		6 => 'sest',
		7 => 'settim',
		8 => 'ottav',
		9 => 'non',
		10 => 'decim',
		11 => 'undicesim',
		12 => 'dodicesim',
		13 => 'tredicesim',
		14 => 'quattordicesim',
		15 => 'quindicesim',
		16 => 'sedicesim',
		17 => 'diciassenttesim',
		18 => 'diciaottesim',
		19 => 'dicianovesim',
		20 => 'ventesim'
	);

	/**
	 * Get full written ordinal number
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Full written ordinal number
     */
	public static function ordinale($text, $number, $gender)
	{
		$num = intval($text);
		$res = "";

		// Masculine Singular, Masculine Plural
		// Feminine Singular, Feminine Plural
		$article = array(
			NameGender::MALE => ["o", "i"],
			NameGender::FEMALE => ["a", "e"]
		);

		if ($num <= 20 || array_key_exists($num, static::$ordinali)) {
			$res = static::$ordinali[$num] . $article[$gender][(int)$number - 4];
		}
		else {
			$res = static::cardinale($text, $number, $gender);
			$last_char = mb_substr($res, -1);
			$not_end_tre = mb_substr($res, -3) !== "tre";
			if ($last_char === "a" || ($last_char === "e" && $not_end_tre) || $last_char === "o" || $last_char === "i") {
				$res = mb_substr($res, 0, strlen($res) - 1);
			}
			if ($gender === NameGender::MALE) {
				if ($number === NameNumber::SINGULAR) {
					$res .= "esimo";
				}
				else {
					$res .= "esimi";
				}
			}
			else {
				if ($number === NameNumber::SINGULAR) {
					$res .= "esima";
				}
				else {
					$res .= "esime";
				}
			}
		}

		return trim($res);
	}

	/**
	 * Get full written ordinal number in feminine form
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @return string Full written ordinal number in feminine form
     */
	public static function ordinale_fem($text, $number)
	{
		return static::ordinale($text, $number, NameGender::FEMALE);
	}

	/**
	 * Get ordinal number in "no/na/ni/ne" form
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Full ordinal number in "no/na/ni/ne" form
     */
	public static function ordinale_num($text, $number, $gender)
	{
		$num = intval($text);

		if ($gender == NameGender::MALE) {
			if ($number == NameNumber::SINGULAR) {
				$num .= "o";
			}
			else {
				$num .= "i";
			}
		}
		else if ($gender == NameGender::FEMALE) {
			if ($number == NameNumber::SINGULAR) {
				$num .= "a";
			}
			else {
				$num .= "e";
			}
		}

		return trim($num);
	}

	/**
	 * Get ordinal number in "na/ne" form
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @return string Full ordinal number in "na/ne" form
     */
	public static function ordinale_fem_num($text, $number)
	{
		return static::ordinale_num($text, $number, NameGender::FEMALE);
	}

	/**
	 * List of available connectors
	 * @var array
	 */
	private static $connectors = array(
		// Name => [Singular Male, Singular Female, Neutral, Plural Male, Plural Female]
		"a" => ["al", "alla", "a", "ai", "alle"],
		"il" => ["il", "la", "", "i", "le"],
		"di" => ["del", "della", "di", "dei", "delle"],
		"da" => ["dal", "dalla", "da", "dai", "dalle"],
		"in" => ["nel", "nella", "in", "nei", "nelle"],
		// Name => linguistic_function
		"cardinale" => "cardinale",
		"cardinale_fem" => "cardinale_fem",
		"ordinale" => "ordinale",
		"ordinale_fem" => "ordinale_fem",
		"ordinale_fem_num" => "ordinale_fem_num",
		"ordinale_num" => "ordinale_num"
	);

	/**
	 * Get list of connectors
	 * @return array List of connectors
     */
	public static function get_connectors()
	{
		return static::$connectors;
	}
}
