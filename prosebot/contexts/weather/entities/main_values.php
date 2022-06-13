<?php

require_once(__DIR__.'/../../entities.php');

/**
 * Class for main values of temperature, humidity and pressure
 *
 * @author zerozero.pt
 */
class MainValuesData extends EntityData
{
    /**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Current temperature
	 * @var float
	 */
    private $temperature;
    /**
	 * Minimum temperature
	 * @var float
	 */
    private $min_temperature;
    /**
	 * Maximum temperature
	 * @var float
	 */
    private $max_temperature;
    /**
	 * Pressure
	 * @var float
	 */
    private $pressure;
    /**
	 * Humidity
	 * @var float
	 */
    private $humidity;
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
	 * @param array $json_data Main values of temperature, humidity and pressure
     */
    function __construct($json_data) {
        parent::__construct(null, "main", null);
        $this->temperature = $json_data["temp"];
        $this->min_temperature = $json_data["temp_min"];
        $this->max_temperature = $json_data["temp_max"];
        $this->humidity = $json_data["humidity"];
        $this->pressure = $json_data["pressure"];
    }

    /**
     * Getters
     */
    public function get_temperature()
    {
        return $this->temperature;
    }

    public function get_min_temperature()
    {
        return $this->min_temperature;
    }

    public function get_max_temperature()
    {
        return $this->max_temperature;
    }

    public function get_pressure()
    {
        return $this->pressure;
    }

    public function get_humidity()
    {
        return $this->humidity;
    }

    /**
	 * Get if it is a hot day. Used on #arg conditions
	 * @return bool If it is a hot day
     */
    public function is_hot()
    {
        return $this->temperature >= 20 && $this->max_temperature >= 25 && $this->min_temperature >= 10;
    }

    /**
	 * Get if it is a cold day. Used on #arg conditions
	 * @return bool If it is a cold day
     */
    public function is_cold()
    {
        return $this->temperature <= 10 && $this->max_temperature <= 15 && $this->min_temperature <= 5;
    }

    /**
	 * Get if it is a neutral day. Used on #arg conditions
	 * @return bool If it is neither hot nor cold
     */
    public function is_neutral()
    {
        return !$this->is_hot() && !$this->is_cold();
    }

    /**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
            "temperature" => new EntityGetterFlat("get_temperature"),
            "min_temperature" => new EntityGetterFlat("get_min_temperature"),
            "max_temperature" => new EntityGetterFlat("get_max_temperature"),
            "pressure" => new EntityGetterFlat("get_pressure"),
            "humidity" => new EntityGetterFlat("get_humidity")
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

?>