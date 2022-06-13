<?php

require_once(__DIR__.'/../../data_fetcher.php');

/**
 * Class for fetching Weather data
 *
 * @author zerozero.pt
 */
class WeatherFetcher extends DataFetcher
{
    /**
	 * Samples endpoints
	 */
    const WEATHER_DATA_JSON_PATH = __DIR__.'/../samples/json/{city_id}.json';
    const WEATHER_DATA_XML_PATH = __DIR__.'/../samples/xml/{city_id}.xml';

    /**
	 * Fetch weather data in json for a given city
	 * @param string $city_id Id of the city
	 * @return array Weather data
	 */
    public static function get_weather_json($city_id)
    {
        $api = preg_replace('/{city_id}/', $city_id, self::WEATHER_DATA_JSON_PATH);
        $json = static::get_json($api);

        if($json == null)
            throw new Exception("Error: City " . $city_id . " does not exist.");

        return [
            "city" => $json["name"],
            "country" => $json["sys"]["country"],
            "main" => $json["main"],
            "wind" => $json["wind"],
            "clouds" => $json["clouds"],
            "types" => $json["weather"]
        ];
    }

    /**
	 * Fetch weather data in xml for a given city
	 * @param string $city_id Id of the city
	 * @return array Weather data
	 */
    public static function get_weather_xml($city_id)
    {
        $api = preg_replace('/{city_id}/', $city_id, self::WEATHER_DATA_XML_PATH);
        $xml = static::get_xml($api);

        if($xml == null)
            throw new Exception("Error: City " . $city_id . " does not exist.");

        $city = $xml->city->attributes()->name->__toString();
        $country = $xml->city->country->__toString();
        
        $temp_attributes = $xml->temperature->attributes();
        $main = [
            "temp" => floatval($temp_attributes->value),
            "temp_min" => floatval($temp_attributes->min),
            "temp_max" => floatval($temp_attributes->max),
            "humidity" => floatval($xml->humidity->attributes()->value),
            "pressure" => floatval($xml->pressure->attributes()->value),
        ];
        $wind = [
            "speed" => floatval($xml->wind->speed->attributes()->value),
            "deg" => floatval($xml->wind->direction->attributes()->value)
        ];
        $clouds = [
            "all" => floatval($xml->clouds->attributes()->value)
        ];
        $weather = [[
            "main" => $xml->weather->attributes()->number->__toString(),
            "description" => $xml->weather->attributes()->value->__toString()
        ]];

        return [
            "city" => $city,
            "country" => $country,
            "main" => $main,
            "wind" => $wind,
            "clouds" => $clouds,
            "types" => $weather
        ];
    }
}
?>