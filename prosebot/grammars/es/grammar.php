<?php

require_once(__DIR__ . '/../grammars.php');

/**
 * Class to handle language grammar structures in Spanish
 * 
 * @author zerozero.pt
 */
class GrammarES extends Grammar
{
	/**
	 * Standard connector between expressions
	 * @var string
	 */
	private static $st_connector = " y ";

	/**
	 * Get standard connector
	 * @return string Standard connector for Spanish expressions
	 */
	public static function get_st_connector()
	{
		return static::$st_connector;
	}

	/**
	 * List of cardinal numbers
	 * @var array
	 */
	private static $cardinals  = array(
		0 => '',
		1 => array('uno', 'una'),
		2 => 'dos',
		3 => 'tres',
		4 => 'cuatro',
		5 => 'cinco',
		6 => 'seis',
		7 => 'siete',
		8 => 'ocho',
		9 => 'nueve',
		10 => 'diez',
		11 => 'once',
		12 => 'doce',
		13 => 'trece',
		14 => 'catorce',
		15 => 'quince',
		16 => 'dieciséis',
		17 => 'diecisiete',
		18 => 'dieciocho',
		19 => 'diecinueve',
		20 => 'veinte',
		21 => 'veintiuno',
		22 => 'veintidós',
		23 => 'veintitrés',
		24 => 'veintcuatro',
		25 => 'veinticinco',
		26 => 'veintiséis',
		27 => 'veintisiete',
		28 => 'veintiocho',
		29 => 'veintinueve',
		30 => 'treinta',
		40 => 'cuarenta',
		50 => 'cincuenta',
		60 => 'sesenta',
		70 => 'setenta',
		80 => 'ochenta',
		90 => 'noventa',
		100 => 'ciento',
		200 => 'doscientos',
		300 => 'trescientos',
		400 => 'cuatrocientos',
		500 => 'quinientos',
		600 => 'seiscientos',
		700 => 'setecientos',
		800 => 'ochocientos',
		900 => 'novecientos'
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
			return "cero";
		}

		if ($int == 100) {
			return "cien";
		}

		if (array_key_exists($int, static::$cardinals)) {
			$res = static::$cardinals[$int];
			if (is_array($res)) {
				if ($gender === NameGender::FEMALE) {
					return $res[1];
				}
				return $res[0];
			}
			else {
				return $res;
			}
		}

		$div = pow(10, $exp);
		$left = static::$cardinals[intdiv($int, $div) * $div];
		$right = static::get_cardinal($int % $div, $gender, $min_val, $exp - 1);

		return $left . ($left === "" ? "" : static::$st_connector) . $right;
	}

	/**
	 * Get full written cardinal number
	 * @param string     $text   Cardinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Full written cardinal number
	 */
	public static function cardinal($text, $number, $gender)
	{
		$num = intval($text);
		$left_int = intdiv($num, 1000);
		$right_int = $num % 1000;

		$left = "";
		$right = "";
		if ($left_int > 1) {
			$left = static::get_cardinal($left_int, $gender, 2) . " ";
		}

		if ($left_int > 0) {
			$left .= "mil";
		}

		if ($right_int > 0) {
			$right = static::get_cardinal($right_int, $gender);
		}

		return $left . ($left !== "" && $right !== "" ? static::$st_connector : "") . $right;
	}

	/**
	 * Get full written cardinal number in feminine form
	 * @param string     $text   Cardinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @return string Full written cardinal number in feminine form
	 */
	public static function cardinal_fem($text, $number)
	{
		return static::cardinal($text, $number, NameGender::FEMALE);
	}

	/**
	 * List of ordinal numbers
	 * @var array
	 */
	private static $ordinals  = array(
		0 => '',
		1 => 'primer',
		2 => 'segund',
		3 => 'tercer',
		4 => 'cuart',
		5 => 'quint',
		6 => 'sext',
		7 => 'séptim',
		8 => 'octav',
		9 => 'noven',
		10 => 'décim',
		20 => 'vigésim',
		30 => 'trigésim',
		40 => 'cuadragésim',
		50 => 'quincuagésim',
		60 => 'sexagésim',
		70 => 'septuagésim',
		80 => 'octogésim',
		90 => 'nonagésim',
		100 => 'centésim',
		200 => 'ducentésim',
		300 => 'tricentésim',
		400 => 'cuadringentésim',
		500 => 'quingentésim',
		600 => 'sexcentésim',
		700 => 'septingentésim',
		800 => 'octingengésim',
		900 => 'noningentésim',
		1000 => 'milésim'
	);

	/**
	 * Get full written ordinal number
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Full written ordinal number
	 */
	public static function ordinal($text, $number, $gender)
	{
		$num = intval($text);
		$res = "";
		$iter = 0;

		while ($num > 0) {
			$current = static::$ordinals[$num % 10 * (pow(10, $iter))];

			if ($current !== "") {
				if ($gender == NameGender::FEMALE) {
					$current .= "a";
				}
				else {
					$current .= "o";
				}

				if ($current !== "" && $number == NameNumber::PLURAL) {
					$current .= "s";
				}
			}

			$res = $current . " " . $res;
			$num = intdiv($num, 10);
			$iter += 1;
		}

		return trim($res);
	}

	/**
	 * Get full written ordinal number in feminine form
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @return string Full written ordinal number in feminine form
	 */
	public static function ordinal_fem($text, $number)
	{
		return static::ordinal($text, $number, NameGender::FEMALE);
	}

	/**
	 * Get ordinal number in "nº/nª(s)" form
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @param NameGender $gender Gender
	 * @return string Full ordinal number in "nº/nª(s)" form
	 */
	public static function ordinal_num($text, $number, $gender)
	{
		$num = intval($text);

		if ($gender == NameGender::FEMALE) {
			$num .= "ª";
		}
		else {
			$num .= "º";
		}

		if ($num !== "" && $number == NameNumber::PLURAL) {
			$num .= "s";
		}

		return trim($num);
	}

	/**
	 * Get ordinal number in "nª(s)" form
	 * @param string     $text   Ordinal number
	 * @param NameNumber $number Number (Singular or Plural)
	 * @return string Full ordinal number in "nª(s)" form
	 */
	public static function ordinal_fem_num($text, $number)
	{
		return static::ordinal_num($text, $number, NameGender::FEMALE);
	}

	/**
	 * List of available connectors
	 * @var array
	 */
	private static $connectors = array(
		// Name => [Singular Male, Singular Female, Neutral, Plural Male, Plural Female]
		"a" => ["al", "a", "a", "a los", "a las"],
		"de" => ["del", "de la", "de", "de los", "de las"],
		"el" => ["el", "la", "", "los", "las"],
		// Name => linguistic_function
		"cardinal" => "cardinal",
		"cardinal_fem" => "cardinal_fem",
		"ordinal" => "ordinal",
		"ordinal_fem" => "ordinal_fem",
		"ordinal_fem_num" => "ordinal_fem_num",
		"ordinal_num" => "ordinal_num"
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
