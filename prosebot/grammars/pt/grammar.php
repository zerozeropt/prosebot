<?php

require_once(__DIR__.'/../grammars.php');

/**
 * Class to handle language grammar structures in Portuguese
 * 
 * @author zerozero.pt
 */
class GrammarPT extends Grammar
{
	/**
	 * Standard connector between expressions
	 * @var string
	 */
	private static $st_connector = " e ";

	/**
	 * Get standard connector
	 * @return string Standard connector for Portuguese expressions
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
		1 => array('um', 'uma'),
		2 => array('dois', 'duas'),
		3 => 'três',
		4 => 'quatro',
		5 => 'cinco',
		6 => 'seis',
		7 => 'sete',
		8 => 'oito',
		9 => 'nove',
		10 => 'dez',
		11 => 'onze',
		12 => 'doze',
		13 => 'treze',
		14 => 'catorze',
		15 => 'quinze',
		16 => 'dezasseis',
		17 => 'dezassete',
		18 => 'dezoito',
		19 => 'dezanove',
		20 => 'vinte',
		30 => 'trinta',
		40 => 'quarenta',
		50 => 'cinquenta',
		60 => 'sessenta',
		70 => 'setenta',
		80 => 'oitenta',
		90 => 'noventa',
		100 => 'cento',
		200 => 'duzentos',
		300 => 'trezentos',
		400 => 'quatrocentos',
		500 => 'quinhentos',
		600 => 'seiscentos',
		700 => 'setecentos',
		800 => 'oitocentos',
		900 => 'novecentos'
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

		if ($int == 100) {
			return "cem";
		}

		if (array_key_exists($int, static::$cardinals)) {
			return parent::get_gender_form(static::$cardinals, $gender, $int);
		}

		$div = pow(10, $exp);
		$left = "";
		$key = intdiv($int, $div) * $div;
		if (array_key_exists($key, static::$cardinals)) {
			$left = parent::get_gender_form(static::$cardinals, $gender, $key);
		}
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
	private static $ordinals = array(
		0 => '',
		1 => 'primeir',
		2 => 'segund',
		3 => 'terceir',
		4 => 'quart',
		5 => 'quint',
		6 => 'sext',
		7 => 'sétim',
		8 => 'oitav',
		9 => 'non',
		10 => 'décim',
		20 => 'vigésim',
		30 => 'trigésim',
		40 => 'quadragésim',
		50 => 'quinquagésim',
		60 => 'sexagésim',
		70 => 'septuagésim',
		80 => 'octogésim',
		90 => 'nonagésim',
		100 => 'centésim',
		200 => 'ducentésim',
		300 => 'tricentésim',
		400 => 'quadrigentésim',
		500 => 'quingentésim',
		600 => 'seiscentésim',
		700 => 'septingentésim',
		800 => 'octingengésim',
		900 => 'nongentésim',
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
		// Name => [Singular Masculine, Singular Feminine, Neutral, Plural Masculine, Plural Feminine]
		"a" => ["ao", "à", "a", "aos", "às"],
		"de" => ["do", "da", "de", "dos", "das"],
		"em" => ["no", "na", "em", "nos", "nas"],
		"o" => ["o", "a", "", "os", "as"],
		"por" => ["pelo", "pela", "por", "pelos", "pelas"],
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
