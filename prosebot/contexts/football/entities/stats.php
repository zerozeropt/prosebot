<?php

require_once(__DIR__.'/../../entities.php');

/**
 * Class for statistics
 *
 * @author zerozero.pt
 */
class Stat extends EntityData
{
    /**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
    /**
	 * Team
	 * @var TeamData
	 */
    private $team;
    /**
	 * Key
	 * @var string
	 */
    private $key;
    /**
	 * Value
	 * @var float
	 */
    private $value;
    /**
	 * Whether is a global statistic
	 * @var bool
	 */
    private $is_global;
    /**
	 * Whether is a positive statistic
	 * @var bool
	 */
    private $is_positive;
    /**
	 * Whether is a relevant statistic
	 * @var bool
	 */
    private $is_relevant;
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
     * @param TeamData $team        Team
     * @param string   $key         Key
     * @param float    $value       Value
     * @param bool     $is_global   Whether is a global statistic
     * @param bool     $is_positive Whether is a positive statistic
     */
    function __construct($team, $key, $value, $is_global, $is_positive)
    {
        $this->team = $team;
        $this->key = $key;
        $this->value = intval($value);
        $this->is_global = $is_global;
        $this->is_positive = $is_positive;
        $this->is_relevant = $key == "shot" || $key == "ballpo" || $key == "corner" || $key == "gksave" || $key == "shotgo";
    }

    /**
     * Getters
     */
    public function get_team()
    {
        return $this->team;
    }

    public function is_positive()
    {
        return $this->is_positive;
    }

    public function is_relevant()
    {
        return $this->is_relevant;
    }

    public function is_global()
    {
        return $this->is_global;
    }

    public function get_key()
    {
        return $this->key;
    }

    public function get_value()
    {
        return $this->value;
    }

    /**
	 * Override
	 * @return string Stat value
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
            "value" => new EntityGetterFlat("get_value")
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
