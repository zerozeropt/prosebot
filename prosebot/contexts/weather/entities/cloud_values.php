<?php

require_once(__DIR__.'/../../entities.php');

/**
 * Class for clouds values
 *
 * @author zerozero.pt
 */
class CloudsValuesData extends EntityData
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Percentage of clouds in the sky
	 * @var float
	 */
    private $percentage;
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
	 * @param array $json_data Cloud values
     */
    function __construct($json_data) {
		parent::__construct(null, "clouds", null);
        $this->percentage = $json_data["all"];
    }

	/**
	 * Getters
	 */
	public function get_percentage()
	{
		return $this->percentage;
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
			"percentage" => new EntityGetterFlat("get_percentage")
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