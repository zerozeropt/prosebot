<?php

/**
 * Super class for fetching data given an API link or a file path.
 * It has support for JSON and XML extensions
 *
 * @author zerozero.pt
 */
abstract class DataFetcher
{
	/**
	 * Fetch json data
	 * @param string $link API link or file path for JSON structured data
	 * @return JSON Decoded json data
     */
	protected static function get_json($link)
	{
		$json = file_get_contents($link);
		return json_decode($json, true);
	}

	/**
	 * Fetch xml data
	 * @param string $link API link or file path for XML structured data
	 * @return SimpleXMLElement|false XML data parsed into an object
     */
	protected static function get_xml($link)
	{
		$file = file_get_contents($link);
		return simplexml_load_string($file);
	}
}
?>