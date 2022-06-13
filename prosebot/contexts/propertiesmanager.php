<?php

require_once('properties.php');

/**
 * Super class to manage conditions variables
 * 
 * @author zerozero.pt
 */
abstract class PropertiesManager
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Isolated variables used for templates conditions
	 * @var Property[]
	 */
	protected static $template_properties;
	/**
	 * #arg.properties used for templates conditions
	 * @var Property[]
	 */
	protected static $template_arg_properties;

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
	 * Set properties for conditions of each context
     */
    abstract static function construct_properties();

	/**
	 * Get list of properties
	 * @return Property[] static::template_properties
     */
	public static function get_template_properties()
	{
		return static::$template_properties;
	}

	/**
	 * Get list of #arg.properties
	 * @return Property[] static::template_arg_properties
     */
	public static function get_template_arg_properties()
	{
		return static::$template_arg_properties;
	}
}
?>