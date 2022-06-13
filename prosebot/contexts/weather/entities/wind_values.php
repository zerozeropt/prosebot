<?php

require_once(__DIR__.'/../../entities.php');

/**
 * Class for wind values of speed and direction
 *
 * @author zerozero.pt
 */
class WindValuesData extends EntityData
{
	/**
	 * Speed
	 * @var float
	 */
    private $speed;
	/**
	 * Speed
	 * @var float
	 */
    private $degree;
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
	 * @param array $json_data Wind values of speed and direction
     */
    function __construct($json_data) {
		parent::__construct(null, "wind", null);
        $this->speed = $json_data["speed"];
        $this->degree = $json_data["deg"];
    }

	/**
	 * Getters
	 */
	public function get_speed()
	{
		return $this->speed;
	}

	public function get_degree()
	{
		return $this->degree;
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
			"speed" => new EntityGetterFlat("get_speed"),
			"degree" => new EntityGetterFlat("get_degree")
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