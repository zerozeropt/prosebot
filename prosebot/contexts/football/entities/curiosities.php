<?php

/**
 * Class for curiosities
 *
 * @author zerozero.pt
 */
class Curiosity
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
	 * Value before
	 * @var float
	 */
    private $pre_value;
    /**
	 * Value after
	 * @var float
	 */
    private $post_value;
    /**
	 * Default value to use
	 * @var string
	 */
    private $default;

    /**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
    /**
     * @param TeamData $team        Team
     * @param string   $key         Key
     * @param float    $pre_value   Value before
     * @param float    $post_value  Value after
     * @param string   $default     Default value to use
     */
    function __construct($team, $key, $pre_value, $post_value, $default = 'post')
    {
        $this->team = $team;
        $this->key = $key;
        $this->pre_value = intval($pre_value);
        $this->post_value = intval($post_value);
        $this->default = $default;
    }

    /**
     * Getters
     */
    public function get_team()
    {
        return $this->team;
    }

    public function get_key()
    {
        return $this->key;
    }

    public function get_pre_value()
    {
        return $this->pre_value;
    }

    public function get_post_value()
    {
        return $this->post_value;
    }

    public function get_value()
    {
        return $this->default == 'post' ? $this->post_value : $this->pre_value;
    }
}
