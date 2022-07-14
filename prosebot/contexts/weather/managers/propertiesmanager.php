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
			new Property('#arg.is_clear', function ($arg) {
				return Utils::boolstr($arg->is_clear());
			}, 10),
			new Property('#arg.is_stormy', function ($arg) {
				return Utils::boolstr($arg->is_stormy());
			}, 10),
			new Property('#arg.is_snowy', function ($arg) {
				return Utils::boolstr($arg->is_snowy());
			}, 10),
			new Property('#arg.is_rainy', function ($arg) {
				return Utils::boolstr($arg->is_rainy());
			}, 10),
			new Property('#arg.is_cloudy', function ($arg) {
				return Utils::boolstr($arg->is_cloudy());
			}, 10),
			new Property('#arg.is_atmosphere', function ($arg) {
				return Utils::boolstr($arg->is_atmosphere());
			}, 10),
			new Property('#arg.is_hot', function ($arg) {
				return Utils::boolstr($arg->is_hot());
			}, 10),
			new Property('#arg.is_cold', function ($arg) {
				return Utils::boolstr($arg->is_cold());
			}, 10),
			new Property('#arg.is_neutral', function ($arg) {
				return Utils::boolstr($arg->is_neutral());
			}, 10)
		);
		usort(static::$template_arg_properties, function ($a, $b) {
			return strlen($b->name) - strlen($a->name);
		});
	}
}
?>