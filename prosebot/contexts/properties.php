<?php

/**
 * Class of variables to be used in conditons
 * 
 * @author zerozero.pt
 */
class Property
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Name
	 * @var string
	 */
	public $name;
	/**
	 * Condition function
	 * @var function
	 */
	public $func;
	/**
	 * Weight
	 * @var int
	 */
	public $weight;
	
	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
	 * @param string   $name   Name
	 * @param function $func   Condition function
	 * @param int      $weight Weight
	 */
	function __construct($name, $func, $weight = 1)
	{
		$this->name = $name;
		$this->func = $func;
		$this->weight = $weight;
	}
}
?>