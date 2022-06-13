<?php

require_once(__DIR__.'/../fetcher/data_fetcher.php');
require_once('cloud_values.php');
require_once('main_values.php');
require_once('weather_types.php');
require_once('wind_values.php');
require_once(__DIR__.'/../../entities.php');


/**
 * Class for building entities weather data according to a city id
 *
 * @author zerozero.pt
 */
class CityData extends MainEntityData
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Grammar
	 * @var Grammar
	 */
	private $grammar;
	/**
	 * Country
	 * @var string
	 */
    private $country_initials;
	/**
	 * Main values data entity
	 * @var MainValuesData
	 */
    private $main_values;
	/**
	 * Wind values data entity
	 * @var WindValuesData
	 */
    private $wind_values;
	/**
	 * Clouds values data entity
	 * @var CloudsValuesData
	 */
    private $clouds_values;
	/**
	 * Weather types data entity
	 * @var WeatherTypesData
	 */
    private $weather_types;
	/**
	 * List of entities
	 * @var EntityGetter[]
	 */
	protected static $entities = [];

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
	 * @param string $city_id Id of a city
	 * @param Grammar Grammar object
     */
	function __construct($city_id, $grammar)
	{
        $this->grammar = $grammar;

		//get full xml
		//$data = WeatherFetcher::get_weather_xml($city_id);
		//get full json
		$data = WeatherFetcher::get_weather_json($city_id);
		parent::__construct($city_id, $data["city"], null);

        //populate city
		$this->country_initials = $data["country"];

        //populate main values
        $this->main_values = new MainValuesData($data["main"]);
		
		//populate wind values
        $this->wind_values = new WindValuesData($data["wind"]);

        // populate cloud values
        $this->clouds_values = new CloudsValuesData($data["clouds"]);

        //populate weather types
        $this->weather_types = new WeatherTypesData($data["types"]);
	}

	/**
	 * Getters
	 */
	public function get_country_initials()
	{
		return $this->country_initials;
	}

	public function get_city()
	{
		return $this->name;
	}

	public function get_weather_types()
	{
		return $this->weather_types;
	}

	public function get_clouds_values()
	{
		return $this->clouds_values;
	}

	public function get_wind_values()
	{
		return $this->wind_values;
	}

	public function get_main_values()
	{
		return $this->main_values;
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
			"city" => new EntityGetterFlat("get_city"),
			"country" => new EntityGetterManager("get_country_name"),
			"types" => new EntityGetterSub("get_weather_types", "WeatherTypesData"),
			"clouds" => new EntityGetterSub("get_clouds_values", "CloudsValuesData"),
			"wind" => new EntityGetterSub("get_wind_values", "WindValuesData"),
			"main" => new EntityGetterSub("get_main_values", "MainValuesData")
		];
	}

	/**
     * Get entities list
     */
    public static function get_entities_list()
    {
        if (empty(static::$entities))
            static::compute_entities();
        return static::$entities;
    }
}
