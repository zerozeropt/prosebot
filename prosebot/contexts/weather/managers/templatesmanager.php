<?php

require_once(__DIR__.'/../../../global_vars.php');
require_once(__DIR__.'/../../templatesmanager.php');
require_once(__DIR__.'/../entities/cities.php');
require_once('entitiesmanager.php');
require_once('propertiesmanager.php');

/**
 * Enum of summaries' parts
 * 
 * @author zerozero.pt
 */
abstract class SummaryParts
{
    const TITLE = "title";
    const INTRO = "intro";
	const STATS = "stats";
    const FINAL_ = "final";
    const LONG_TEXT = "longtext";
}

/**
 * Class for templates management and articles production for Weather context
 * 
 * @author zerozero.pt
 */
class TemplatesManagerWeather extends TemplatesManager
{
    /**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
    /**
	 * Array of templates with a fixed structure
	 * @var Template[]
	 */
    private $fixed_templates;
    /**
	 * Array of templates files names with a fixed structure and no hyperlinks allowed
	 * @var SummaryParts[]
	 */
    private static $fixed_templates_no_links_paths = [
        SummaryParts::TITLE
    ];
    /**
	 * Array of templates files names with a fixed structure and hyperlinks allowed
	 * @var SummaryParts[]
	 */
	private static $fixed_templates_paths = [
        SummaryParts::INTRO,
        SummaryParts::FINAL_,
		SummaryParts::STATS
    ];

    /**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
	 * @param string $language Language
	 */
	function __construct($language)
	{
        parent::__construct($language, "weather");

		$name = "EntitiesManager" . ucfirst(static::$context);
		$this->entities_manager = new $name($language);
		PropertiesManagerWeather::construct_properties();

        $this->fixed_templates = parent::read_template_files(static::$fixed_templates_no_links_paths);
		$this->fixed_templates = array_merge($this->fixed_templates, parent::read_template_files(static::$fixed_templates_paths));
    }

    /**
	 * Generate summary
	 * @param string $city_id Id of the city
	 * @return array Array with each part of the summary
	 */
	public function build_summary($city_id)
	{
        $summary_array = array();
		$city = new CityData($city_id, static::$grammar_class);

        // Fixed structure paragraphs with no links allowed
        foreach (static::$fixed_templates_no_links_paths as $path) {
            $chunk_tmp = parent::sanitize($this->build_fixed_paragraph($city, $this->fixed_templates[$path], "entry_point"));
            $chunk = strip_tags($chunk_tmp, '<em>');
            $summary_array[$path] = $chunk;
        }

		// Fixed structure paragraphs
        foreach (static::$fixed_templates_paths as $path) {
            $chunk = parent::sanitize($this->build_fixed_paragraph($city, $this->fixed_templates[$path], "entry_point"));
			$summary_array[$path] = $chunk;
        }

        // Build summary long text
        $summary_array[SummaryParts::LONG_TEXT] = $summary_array[SummaryParts::INTRO]."\n".$summary_array[SummaryParts::STATS]."\n".$summary_array[SummaryParts::FINAL_];

        // Add match object
        $summary_array["city"] = $city;

        return $summary_array;
	}
}
