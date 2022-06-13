<?php

require_once(__DIR__.'/../utils.php');

/**
 * Class for templates
 * 
 * @author zerozero.pt
 */
class Template
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Key
	 * @var string
	 */
	private $type;
	/**
	 * Content
	 * @var string
	 */
	private $text;
	/**
	 * Boolean expression in string format
	 * @var string
	 */
	private $condition;
	/**
	 * Weight
	 * @var int
	 */
	private $weight;
	/**
	 * Number of times the template was valid but not used at the expense of another
	 * @var int
	 */
	private $last_used;

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
	 * @param string $text       Text
	 * @param string $name       Key
	 * @param string $condition  Condition
	 * @param int    $weight     Weight
	 */
	function __construct($text, $name, $condition, $weight = 1)
	{
		$this->text = $text;
		$this->type = $name;
		$this->condition = $condition;
		$this->weight = $weight;
		$this->last_used = PHP_INT_MAX;
	}

	/**
	 * Getters
	 */
	function get_text()
	{
		return $this->text;
	}

	function get_type()
	{
		return $this->type;
	}

	function get_condition()
	{
		return $this->condition;
	}

	/**
	 * Calculate weight at each moment
	 * @return int Weight according to the initial weight and the number of times the template was not used
	 */
	function get_weight()
	{
		return $this->weight * (1 - exp(-0.2 * $this->last_used));
	}

	/**
	 * Increment the number of times the template was not used
	 */
	function validated()
	{
		$this->last_used += 1;
	}

	/**
	 * Reset the number of times the template was not used
	 */
	function used()
	{
		$this->last_used = 0;
	}

	/**
	 * Override
	 * @return string Template text-condition
	 */
	public function __toString()
	{
		return $this->text . " - " . $this->condition;
	}
}
