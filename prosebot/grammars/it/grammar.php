<?php

require_once(__DIR__ . '/../grammars.php');

/**
 * Class to handle language grammar structures in Portuguese
 * 
 * @author zerozero.pt
 */
class GrammarIT extends Grammar
{
	const VOWELS = ['a', 'e', 'i', 'o', 'u'];
	const SPECIAL_CONSONANTS = ['s', 'z'];

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
	private static $cardinals = array(
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
	private static function get_cardinal($int, $gender, $min_val = 0, $exp = 2)
	{
		if ($int < $min_val || $int >= 1000) {
			return "";
		}

		if ($int == 0) {
			return "zero";
		}

		if (array_key_exists($int, static::$cardinals)) {
			return parent::get_gender_form(static::$cardinals, $gender, $int);
		}

		$div = pow(10, $exp);
		$left = "";
		if (array_key_exists($int, static::$cardinals)) {
			$left = parent::get_gender_form(static::$cardinals, $gender, intdiv($int, $div) * $div);
		}
		$right = static::get_cardinal($int % $div, $gender, $min_val, $exp - 1);

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
			$left = static::get_cardinal($left_int, $gender, 2);
			$left .= "milla";
		} else if ($left_int === 1) {
			$left .= "mille";
		}

		if ($right_int > 0) {
			$right = static::get_cardinal($right_int, $gender);
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
	private static $ordinals = array(
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

		if ($num <= 20 || array_key_exists($num, static::$ordinals)) {
			$res = static::$ordinals[$num] . $article[$gender][(int)$number - 4];
		} else {
			$res = static::cardinale($text, $number, $gender);
			$last_char = mb_substr($res, -1);
			$not_end_tre = mb_substr($res, -3) !== "tre";
			if ($last_char === "a" || ($last_char === "e" && $not_end_tre) || $last_char === "o") {
				$res = mb_substr($res, 0, strlen($res) - 1);
			}
			if ($gender === NameGender::MALE) {
				if ($number === NameNumber::SINGULAR) {
					$res .= "esimo";
				} else {
					$res .= "esimi";
				}
			} else {
				if ($number === NameNumber::SINGULAR) {
					$res .= "esima";
				} else {
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
	 * @return string Ordinal number in "no/na/ni/ne" form
	 */
	public static function ordinale_num($text, $number, $gender)
	{
		$num = intval($text);

		if ($gender == NameGender::MALE) {
			if ($number == NameNumber::SINGULAR) {
				$num .= "o";
			} else {
				$num .= "i";
			}
		} else if ($gender == NameGender::FEMALE) {
			if ($number == NameNumber::SINGULAR) {
				$num .= "a";
			} else {
				$num .= "e";
			}
		}

		return trim($num);
	}

	/**
	 * Get ordinal number in "na/ne" form
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @return string Ordinal number in "na/ne" form
	 */
	public static function ordinale_fem_num($text, $number)
	{
		return static::ordinale_num($text, $number, NameGender::FEMALE);
	}

	/**
	 * Get correct "e/ed" form
	 * @param string  $text Text
	 * @return string Correct "e/ed" form
	 */
	public static function e($text)
	{
		$first_char = mb_strtolower(mb_substr($text, 0, 1));
		if ($first_char === "i" || $first_char === "e") {
			return "ed";
		}
		return "e";
	}

	/**
	 * Get correct "preposition + il" form according to context
	 * @param string     $text   	 Text
	 * @param NameNumber $number 	 Number (Singular or Plural)
	 * @param NameGender $gender 	 Gender
	 * @param string[]   $variations List of different variations for the composite connector
	 * @return string Correct "il" form according to context
	 */
	private static function composite_connector($text, $number, $gender, $variations)
	{
		$first_char = mb_strtolower(mb_substr($text, 0, 1));
		$second_char = mb_strtolower(mb_substr($text, 1, 1));
		$res = "";

		// Singular
		if ($number === NameNumber::SINGULAR) {
			// Beginning in vowel
			if (in_array($first_char, self::VOWELS)) {
				$res = $variations["l'"];
			}
			// Feminine beginning in consonant
			else if ($gender === NameGender::FEMALE) {
				$res = $variations["la"];
			}
			// Masculine beginning in s or z + consonant
			else if (in_array($first_char, self::SPECIAL_CONSONANTS) && !in_array($second_char, self::VOWELS)) {
				$res = $variations["lo"];
			}
			// Other masculine words
			else {
				$res = $variations["il"];
			}

			return $res;
		}

		// Plural
		// Feminine
		if ($gender === NameGender::FEMALE) {
			$res = $variations["le"];
		}
		// Masculine beginning in vowel or in s or z + consonant
		else if (
			in_array($first_char, self::VOWELS)
			|| (in_array($first_char, self::SPECIAL_CONSONANTS) && !in_array($second_char, self::VOWELS))
		) {
			$res = $variations["gli"];
		}
		// Other masculine words
		else {
			$res = $variations["i"];
		}

		return $res;
	}

	/**
	 * Get correct "il" form according to context
	 * @param string     $text   Text
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Correct "il" form according to context
	 */
	public static function il($text, $number, $gender)
	{
		$variations = array(
			"l'" => "l'",
			"la" => "la",
			"lo" => "lo",
			"il" => "il",
			"le" => "le",
			"gli" => "gli",
			"i" => "i"
		);
		return static::composite_connector($text, $number, $gender, $variations);
	}

	/**
	 * Get correct "e + il" form according to context
	 * @param string     $text   Text
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Correct "e + il" form according to context
	 */
	public static function e_il($text, $number, $gender)
	{
		$variations = array(
			"l'" => "e l'",
			"la" => "e la",
			"lo" => "e lo",
			"il" => "ed il",
			"le" => "e le",
			"gli" => "e gli",
			"i" => "ed i"
		);
		return static::composite_connector($text, $number, $gender, $variations);
	}

	/**
	 * Get correct "a + il" form according to context
	 * @param string     $text   Text
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Correct "a + il" form according to context
	 */
	public static function a_il($text, $number, $gender)
	{
		$variations = array(
			"l'" => "all'",
			"la" => "alla",
			"lo" => "allo",
			"il" => "al",
			"le" => "alle",
			"gli" => "agli",
			"i" => "ai"
		);
		return static::composite_connector($text, $number, $gender, $variations);
	}

	/**
	 * Get correct "da + il" form according to context
	 * @param string     $text   Text
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Correct "da + il" form according to context
	 */
	public static function da_il($text, $number, $gender)
	{
		$variations = array(
			"l'" => "dall'",
			"la" => "dalla",
			"lo" => "dallo",
			"il" => "dal",
			"le" => "dalle",
			"gli" => "dagli",
			"i" => "dai"
		);
		return static::composite_connector($text, $number, $gender, $variations);
	}

	/**
	 * Get correct "di + il" form according to context
	 * @param string     $text   Text
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Correct "di + il" form according to context
	 */
	public static function di_il($text, $number, $gender)
	{
		$variations = array(
			"l'" => "dell'",
			"la" => "della",
			"lo" => "dello",
			"il" => "del",
			"le" => "delle",
			"gli" => "degli",
			"i" => "dei"
		);
		return static::composite_connector($text, $number, $gender, $variations);
	}

	/**
	 * Get correct "in + il" form according to context
	 * @param string     $text   Text
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Correct "in + il" form according to context
	 */
	public static function in_il($text, $number, $gender)
	{
		$variations = array(
			"l'" => "nell'",
			"la" => "nella",
			"lo" => "nello",
			"il" => "nel",
			"le" => "nelle",
			"gli" => "negli",
			"i" => "nei"
		);
		return static::composite_connector($text, $number, $gender, $variations);
	}

	/**
	 * Get correct "su + il" form according to context
	 * @param string     $text   Text
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Correct "su + il" form according to context
	 */
	public static function su_il($text, $number, $gender)
	{
		$variations = array(
			"l'" => "sull'",
			"la" => "sulla",
			"lo" => "sullo",
			"il" => "sul",
			"le" => "sulle",
			"gli" => "sugli",
			"i" => "sui"
		);
		return static::composite_connector($text, $number, $gender, $variations);
	}

	/**
	 * List of available connectors
	 * @var array
	 */
	private static $connectors = array(
		// Name => [Singular Masculine, Singular Feminine, Neutral, Plural Masculine, Plural Feminine]
		"a" => ["al", "alla", "a", "ai", "alle"],
		"di" => ["del", "della", "di", "dei", "delle"],
		"da" => ["dal", "dalla", "da", "dai", "dalle"],
		"in" => ["nel", "nella", "in", "nei", "nelle"],
		// Name => grammar_function
		"il" => "il",
		"e" => "e",
		"e_il" => "e_il",
		"a_il" => "a_il",
		"da_il" => "da_il",
		"di_il" => "di_il",
		"in_il" => "in_il",
		"su_il" => "su_il",
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
