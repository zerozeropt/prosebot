<?php
    /**
	 * Get list of global variables
	 * @return array Global variables
     */
    function get_globals()
    {
        return [
            "version" => 6.000,
            "contexts" => [
                "football" => "Football",
                "weather" => "Weather"
            ],
            "languages" => [
                "pt" => "Português",
                "br" => "Português do Brasil",
                "en" => "English",
                "es" => "Español"
            ],
            "languageToTimezone" => [
                "pt" => "Europe/Lisbon",
                "br" => "America/Sao_Paulo",
                "en" => "Europe/London",
                "es" => "Europe/Madrid"
            ],
            "main_entities_classes" => [
                "football" => ["MatchData", "matches"],
                "weather" => ["CityData", "cities"]
            ]
        ];
    }
?>