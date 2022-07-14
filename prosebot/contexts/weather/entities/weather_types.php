<?php

require_once(__DIR__.'/../../entities.php');

/**
 * Class for weather types data
 *
 * @author zerozero.pt
 */
class WeatherTypesData extends EntityData
{
	/**
	 * Type name
	 * @var string
	 */
    private $main;
	/**
	 * Type description
	 * @var string
	 */
    private $description;
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
	 * @param array $json_data Weather types data
     */
    function __construct($json_data) {
		parent::__construct(null, "types", null);
        $this->main = $json_data[0]["main"];
        $this->description = $json_data[0]["description"];
    }

	/**
	 * Getters
	 */
	public function get_main()
	{
		return $this->main;
	}

	public function get_description()
	{
		return $this->description;
	}

	/**
	 * Get if it is a clear day. Used on #arg conditions
	 * @return bool If it is a clear day
     */
	public function is_clear()
	{
		return $this->main == "Clear" || $this->main == "800";
	}

	/**
	 * Get if it is a cloudy day. Used on #arg conditions
	 * @return bool If it is a cloudy day
     */
	public function is_cloudy()
	{
		return $this->main == "Clouds" || preg_match('/^80[1-4]$/', $this->main);
	}

	/**
	 * Get if it is snowing. Used on #arg conditions
	 * @return bool If it is snowing
     */
	public function is_snowy()
	{
		return $this->main == "Snow" || preg_match('/^6\d\d$/', $this->main);
	}

	/**
	 * Get if it is a rainy day. Used on #arg conditions
	 * @return bool If it is a rainy day
     */
	public function is_rainy()
	{
		return $this->main == "Rain" || $this->main == "Drizzle" || preg_match('/^5\d\d$/', $this->main) || preg_match('/^3\d\d$/', $this->main);
	}

	/**
	 * Get if it is a stormy day. Used on #arg conditions
	 * @return bool If it is a stormy day
     */
	public function is_stormy()
	{
		return $this->main == "Thunderstorm" || preg_match('/^2\d\d$/', $this->main);
	}

	/**
	 * Get if it is a day of adverse atmosphere conditions. Used on #arg conditions
	 * @return bool If it is a day of adverse atmosphere conditions
     */
	public function is_atmosphere()
	{
		return $this->main == "Atmosphere" || preg_match('/^7\d\d$/', $this->main);
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
			"main" => new EntityGetterFlat("get_main"),
			"description" => new EntityGetterFlat("get_description")
		];
	}

	/**
     * Get entities list
     */
    public static function get_entities_list()
    {
        if (empty(static::$entities)) {
            static::compute_entities();
		}
        return static::$entities;
    }
}

?>