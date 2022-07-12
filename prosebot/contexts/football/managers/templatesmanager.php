<?php

require_once(__DIR__.'/../../templatesmanager.php');
require_once(__DIR__.'/../entities/matches.php');
require_once('propertiesmanager.php');
require_once(__DIR__.'/../../../exceptions.php');

/**
 * Enum of summaries' parts
 * 
 * @author zerozero.pt
 */
abstract class SummaryParts
{
    const TITLE = "title";
    const SUB_TITLE = "sub_title";
    const SMALL_TEXT = "small_text";
    const INTRO = "intro";
    const EVENTS = "events";
    const FINAL_ = "final";
	const STATS = "stats";
    const LONG_TEXT = "longtext";
    const ANALYSIS = "analysis";
}

/**
 * Class for templates management and articles production for Football context
 * 
 * @author zerozero.pt
 */
class TemplatesManagerFootball extends TemplatesManager
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * From language to country id (zerozero API)
	 * @var array
	 */
	private $languageToCountry = [
		"pt" => 1,
		"en" => 16,
		"br" => 6,
		"es" => 12,
		"fr" => 14,
		"us" => 127
	];
	/**
	 * Country
	 * @var string
	 */
	private $country;
	/**
	 * Country id
	 * @var int
	 */
	private $country_index;
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
        SummaryParts::TITLE,
        SummaryParts::SUB_TITLE,
        SummaryParts::SMALL_TEXT
    ];
	/**
	 * Array of templates files names with a fixed structure and hyperlinks allowed
	 * @var SummaryParts[]
	 */
	private static $fixed_templates_paths = [
        SummaryParts::INTRO,
        SummaryParts::FINAL_
    ];
	/**
	 * Array of templates with a variable structure
	 * @var Template[]
	 */
    private $variable_templates;
	/**
	 * Array of templates files names with a variable structure and hyperlinks allowed
	 * @var SummaryParts[]
	 */
    private static $variable_templates_paths = [
        SummaryParts::EVENTS,
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
        parent::__construct($language, "football");
		$this->country = $language;
		$this->country_index = $this->languageToCountry[$language];

		$success = require_once(static::$language_dir.'/entitiesmanager.php');
		if (!$success) {
			throw new UndefinedLanguageException("Language does not exist.");
		}

		$name = "EntitiesManager" . ucfirst(static::$context) . strtoupper(static::$language_dir);
		$this->entities_manager = new $name();
		PropertiesManagerFootball::construct_properties();

        $this->fixed_templates = parent::read_template_files(static::$fixed_templates_no_links_paths);
		$this->fixed_templates = array_merge($this->fixed_templates, parent::read_template_files(static::$fixed_templates_paths));
		$this->variable_templates = parent::read_template_files(static::$variable_templates_paths);
	}

	/**
	 * Generate summary
	 * @param string $match_id        Id of the match
	 * @param bool   $calculate_stats Whether to calculate stats
	 * @return array Array with each part of the summary
	 */
	public function build_summary($match_id, $calculate_stats)
	{
        $summary_array = array();
		$match = new MatchData($match_id, static::$grammar_class, $this->country, $this->country_index, $this->entities_manager::get_own_goal_form());

        // Fixed structure paragraphs with no links allowed
        foreach (static::$fixed_templates_no_links_paths as $path) {
            $chunk_tmp = parent::sanitize($this->build_fixed_paragraph($match, $this->fixed_templates[$path], "entry_point"));
            $chunk = strip_tags($chunk_tmp, '<em>');
            $summary_array[$path] = $chunk;
            $this->entities_manager->reset();
        }

		// Fixed structure paragraphs
        foreach (static::$fixed_templates_paths as $path) {
            $chunk = parent::sanitize($this->build_fixed_paragraph($match, $this->fixed_templates[$path], "entry_point"));
			$summary_array[$path] = $chunk;
            $this->entities_manager->reset();
        }

        // Events and stats
        foreach (static::$variable_templates_paths as $path) {
            $func = 'build_'.$path.'_paragraph';
            $summary_array[$path] = $this->$func($match);
            $this->entities_manager->reset();
        }

        // Build summary long text
        $summary_array[SummaryParts::LONG_TEXT] = $summary_array[SummaryParts::INTRO].($summary_array[SummaryParts::EVENTS] != "" ? "\n" : "").$summary_array[SummaryParts::EVENTS]."\n".$summary_array[SummaryParts::FINAL_];

        // Calculate diversity analysis
		if ($calculate_stats) {
			$summary_array[SummaryParts::ANALYSIS] = $this->calculate_stats($summary_array[SummaryParts::TITLE], $summary_array[SummaryParts::SUB_TITLE], $summary_array[SummaryParts::SMALL_TEXT].$summary_array[SummaryParts::LONG_TEXT]);
            $summary_array = array_merge($summary_array, $summary_array[SummaryParts::ANALYSIS]);
		}

        // Add match object
        $summary_array["match"] = $match;

        return $summary_array;
	}

	/**
	 * Generate events paragraph (variable structure)
	 * @param MatchData $match Match
	 * @return string Events paragraph
	 */
	private function build_events_paragraph($match)
	{
		$events = [
			0 => "",
			1 => "",
			2 => ""
		];

		$breakpoints = [
			0 => 45, 1 => 90, 2 => 1000
		];

		//go through all events and generate sentences accordingly
		foreach ($match->get_events() as $event_key => $event) {
			$filtered_templates = TemplatesManager::filter_valid_templates($this->variable_templates[SummaryParts::EVENTS], $match, $event_key, null, $event->get_event_name());

			$filtered_templates = TemplatesManager::filter_max_weight_templates($filtered_templates);

			$template = TemplatesManager::get_random_template_by_type($filtered_templates, $event->get_event_name());

			$text = TemplatesManager::template_recursive($this->variable_templates[SummaryParts::EVENTS], $template, $match, $event_key);

			if ($text != "") {
				foreach ($breakpoints as $index => $breakpoint) {
					if ($event->get_minute()[0] <= $breakpoint) {
						$events[$index] .= $text . " ";
						break;
					}
				}
			}
		}

		return implode("\n", array_map(function ($elem) {
			return TemplatesManager::sanitize($elem);
		}, array_filter($events, function ($elem) {
			return $elem != "";
		})));
	}

	/**
	 * Construct stats text for a given stats array
	 * @param MatchData $match  Match
	 * @param array     $stats  Array of stats (curiosities or statistics)
	 * @param array     $result Array of stats text (passed as reference)
	 */
	private function get_stats_text($match, $stats, &$result)
	{
		foreach ($stats as $stat_key => $stat) {
			$filtered_templates = TemplatesManager::filter_valid_templates($this->variable_templates[SummaryParts::STATS], $match, $stat_key, null, $stat->get_key());

			$filtered_templates = TemplatesManager::filter_max_weight_templates($filtered_templates);

			$template = TemplatesManager::get_random_template_by_type($filtered_templates, $stat->get_key());

			$text = TemplatesManager::sanitize(TemplatesManager::template_recursive($this->variable_templates[SummaryParts::STATS], $template, $match, $stat_key));

			if ($text != "") {
				$team = $stat->get_team();
				$type = "match_stats";

				if ($team === $match->home_team()) {
					$type = "home_team_stats";
				} else if ($team === $match->away_team()) {
					$type = "away_team_stats";
				}

				array_push($result[$type], $text);
			}
		}
	}

	/**
	 * Generate stats paragraph (variable structure)
	 * @param MatchData $match Match
	 * @return string Stats paragraph
	 */
	private function build_stats_paragraph($match)
	{
		$result = array('match_stats' => [], 'home_team_stats' => [], 'away_team_stats' => []);
		$this->get_stats_text($match, $match->get_stats(), $result);
		$this->get_stats_text($match, $match->get_curiosities(), $result);

		return $result;
	}
}
