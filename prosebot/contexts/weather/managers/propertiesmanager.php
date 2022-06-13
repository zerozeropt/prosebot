<?php

require_once(__DIR__.'/../../propertiesmanager.php');
require_once(__DIR__.'/../../../utils.php');

/**
 * Class to manage conditions variables for Weather context
 * 
 * @author zerozero.pt
 */
class PropertiesManagerWeather extends PropertiesManager
{
	/**
	 * Set properties for conditions
     */
	public static function construct_properties()
	{
		// List of properties
		static::$template_properties = array();
		usort(static::$template_properties, function ($a, $b) {
			return strlen($b->name) - strlen($a->name);
		});

		// List of #arg.properties
		static::$template_arg_properties = array(
			new Property('#arg.is_clear', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_clear());
			}, 10),
			new Property('#arg.is_stormy', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_stormy());
			}, 10),
			new Property('#arg.is_snowy', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_snowy());
			}, 10),
			new Property('#arg.is_rainy', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_rainy());
			}, 10),
			new Property('#arg.is_cloudy', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_cloudy());
			}, 10),
			new Property('#arg.is_atmosphere', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_atmosphere());
			}, 10),
			new Property('#arg.is_hot', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_hot());
			}, 10),
			new Property('#arg.is_cold', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_cold());
			}, 10),
			new Property('#arg.is_neutral', function ($data, $n, $arg) {
				return Utils::boolstr($arg->is_neutral());
			}, 10)
		);
		usort(static::$template_arg_properties, function ($a, $b) {
			return strlen($b->name) - strlen($a->name);
		});
	}
}
?>