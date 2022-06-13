<?php

require_once(__DIR__.'/../../entitiesmanager.php');

/**
 * Class for entities data management for Weather context
 * 
 * @author zerozero.pt
 */
class EntitiesManagerWeather extends EntitiesManager
{
	/**
	 * Language
	 * @var string
	 */
	private static $language;
	/**
	 * Initials to country
	 * @var array
	 */
	private static $initialsToCountry = [
		"br" => [
			"PT" => "Portugal",
			"NO" => "Noruega",
			"US" => "Estados Unidos da América"
		],
		"pt" => [
			"PT" => "Portugal",
			"NO" => "Noruega",
			"US" => "Estados Unidos da América"
		],
		"en" => [
			"PT" => "Portugal",
			"NO" => "Norway",
			"US" => "United States of America"
		],
		"es" => [
			"PT" => "Portugal",
			"NO" => "Noruega",
			"US" => "Estados Unidos de América"
		],
	];

	function __construct($language)
	{
		parent::__construct();
		static::$language = $language;
	}

	public function get_country_name($city)
	{
		return static::$initialsToCountry[static::$language][$city->get_country_initials()];
	}
}

?>