<?php

require_once(__DIR__.'/../grammars.php');

/**
 * Class to handle language grammar structures in English
 * 
 * @author zerozero.pt
 */
class GrammarEN extends Grammar
{
	/**
	 * Standard connector between expressions
	 * @var string
	 */
	private static $st_connector = " and ";

	/**
	 * Get standard connector
	 * @return string Standard connector for English expressions
     */
	public static function get_st_connector()
	{
		return static::$st_connector;
	}

	/**
	 * Get full written ordinal number
	 * @param string     $text   Ordinal number
	 * @return string Full written ordinal number
     */
	public static function cardinal($text)
	{
		$f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
		return $f->format(intval($text));
	}

	/**
	 * Get ordinal number in "n^st/n^nd/n^rd/n^th" form
	 * @param string     $text   Ordinal number
	 * @return string Full ordinal number in "n^st/n^nd/n^rd/n^th" form
     */
	public static function ordinal_num($text)
	{
		$f = new NumberFormatter("en", NumberFormatter::ORDINAL);
		return $f->format(intval($text));
	}

	/**
	 * List of available connectors
	 * @var array
	 */
	private static $connectors = array(
		// Name => linguistic_function
		"cardinal" => "cardinal",
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
