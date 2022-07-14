<?php

require_once(__DIR__.'/../fetcher/data_fetcher.php');
require_once(__DIR__.'/../../../utils.php');
require_once('stats.php');
require_once('curiosities.php');
require_once(__DIR__.'/../../entities.php');

/**
 * Class for teams
 *
 * @author zerozero.pt
 */
class TeamData extends EntityData
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Type
	 * @var int
	 */
	private $type;
	/**
	 * Number of goals in the match
	 * @var int
	 */
	private $goals;
	/**
	 * Number of goals in the previous match between the two teams for the same competition
	 * @var int
	 */
	private $prev_match_goals;
	/**
	 * Coach name
	 * @var string
	 */
	private $coach;
	/**
	 * Classification before the match
	 * @var string
	 */
	private $pre_classification;
	/**
	 * Classification after the match
	 * @var string
	 */
	private $pos_classification;
	/**
	 * Points in the league
	 * @var string
	 */
	private $points;
	/**
	 * If it had at least one red card during the match
	 * @var bool
	 */
	private $has_red_card;
	/**
	 * Number of players of the team in the field when match ends (max 11)
	 * @var int
	 */
	private $final_players;
	/**
	 * Match day index
	 * @var int
	 */
	private $match_day;
	/**
	 * Country
	 * @var string
	 */
	private $country;
	/**
	 * Country id
	 * @var string
	 */
	private $country_id;
	/**
	 * City
	 * @var string
	 */
	private $city;
	/**
	 * Global sequence of winnings (V), drawings (E) or defeats (D)
	 * @var array
	 */
	private $form_global;
	/**
	 * Number of matches in a global sequence
	 * @var int
	 */
	private $form_number_global;
	/**
	 * Sequence of winnings (V), drawings (E) or defeats (D) in the competition
	 * @var array
	 */
	private $form_competition;
	/**
	 * Number of matches in a competition sequence
	 * @var int
	 */
	private $form_number_competition;
	/**
	 * Next global opponent
	 * @var string
	 */
	private $next_global_opponent;
	/**
	 * Next global match place
	 * @var string
	 */
	private $next_global_match_where;
	/**
	 * Next global match id
	 * @var string
	 */
	private $next_global_match_id;
	/**
	 * Next global match day
	 * @var string
	 */
	private $next_global_match_day;
	/**
	 * Next global match link
	 * @var string
	 */
	private $next_global_match_link;
	/**
	 * Next opponent in the competition
	 * @var string
	 */
	private $next_competition_opponent;
	/**
	 * Next match day in the competition
	 * @var string
	 */
	private $next_competition_match_day;
	/**
	 * Next match place in the competition
	 * @var string
	 */
	private $next_competition_match_where;
	/**
	 * Next match id in the competition
	 * @var string
	 */
	private $next_competition_match_id;
	/**
	 * Next match hyperlink in the competition
	 * @var string
	 */
	private $next_competition_match_link;
	/**
	 * Long name
	 * @var string
	 */
	private $long_name;
	/**
	 * Nick name
	 * @var TextStructure
	 */
	private $other_name;
	/**
	 * Name of the team in each language
	 * @var string[]
	 */
	private $name_array;
	/**
	 * Whether can write the city
	 * @var bool
	 */
	private $can_use_city;
	/**
	 * Current statistic key
	 * @var string
	 */
	private $current_stat;
	/**
	 * List of entities
	 * @var EntityGetter[]
	 */
	protected static $entities = [];

	/**
	 * @param Grammar 	$grammar 						Grammar
	 * @param string  	$team_id 						Team id
	 * @param int	  	$goals   						Number of goals
	 * @param int	  	$prev_match_goals   			Number of goals in the previous match between the two teams for the same competition
	 * @param string	$coach   						Coach name
	 * @param string    $competition					Competition name
	 * @param string	$pre_classification				Classification before the match
	 * @param string	$pos_classification				Classification after the match
	 * @param string    $points							Points in the league
	 * @param array     $form_json						Json data of sequences of matches of the team
	 * @param array     $next_matches_json				Json data of the next matches of the team
	 * @param int	    $match_day						Match day index
	 * @param array     $match_stats					Array of match stats
	 * @param array     $edition_stats					Array of edition stats
	 * @param array     $edition_negative_stats			Array of edition negative stats
	 * @param array     $edition_team_stats				Array of team stats in the edition
	 * @param array     $edition_team_negative_stats	Array of team negative stats in the edition
	 * @param string    $type							Type
	 * @param array     $pre_game_curiosities			Array of curiosities before the match
	 * @param array     $post_game_curiosities			Array of curiosities after the match
	 */
	function __construct(
		$grammar,
		$team_id,
		$goals,
		$coach,
		$competition,
		$pre_classification,
		$pos_classification,
		$points,
		$form_json,
		$next_matches_json,
		$match_day,
		$match_stats,
		$edition_stats,
		$edition_negative_stats,
		$edition_team_stats,
		$edition_team_negative_stats,
		$type,
		$pre_game_curiosities,
		$post_game_curiosities
	) {
		# get full json
		$team_data = FootballFetcher::get_team_json($team_id)["data"];
		parent::__construct($team_id, $grammar::get_elem($team_data['PROFILE'], 'SHORT_NAME'), FootballFetcher::TEAM_LINK);
		$this->name_array = [];
		$name_gender = $team_data['PROFILE']['SHORT_NAME_GENDER'] === '0' ? NameGender::MALE : NameGender::FEMALE;
		$name_number = $team_data['PROFILE']['SHORT_NAME_PLURAL'] === '0' ? NameNumber::SINGULAR : NameNumber::PLURAL;
		foreach($team_data['PROFILE']['SHORT_NAME_ARRAY'] as $lang => $name) {
			$this->name_array[$lang] = new TextStructure($name, $name_gender, $name_number);
		}
		$this->type = intval($type);
		$this->goals = $goals;
		$this->prev_match_goals = 0;
		$this->coach = $coach;
		$this->pre_classification = $pre_classification;
		$this->pos_classification = $pos_classification;
		$this->points = $points;
		$this->has_red_card = false;
		$this->final_players = 11;
		$this->match_day = intval($match_day);
		$this->country = $team_data['PROFILE']['COUNTRY'];

		$this->form_global = static::get_form($form_json);
		$this->form_number_global = 0;
		foreach ($this->form_global as $result) {
			if ($result == $this->form_global[0]) {
				$this->form_number_global++;
			}
			else {
				break;
			}
		}

		$this->form_competition = static::get_form($form_json, $competition);
		$this->form_number_competition = 0;
		foreach ($this->form_competition as $result) {
			if ($result == $this->form_competition[0]) {
				$this->form_number_competition++;
			}
			else {
				break;
			}
		}

		$this->next_global_opponent = null;
		$this->next_global_match_where = null;
		$this->next_global_match_id = null;
		$this->next_global_match_day = null;
		$this->next_competition_opponent = null;
		$this->next_competition_match_day = null;
		$this->next_competition_match_where = null;
		$this->next_competition_match_id = null;

		$next = static::get_next_opponent($grammar, $next_matches_json);
		if ($next !== null) {
			$this->next_global_opponent = $next[0];
			$this->next_global_match_where = $next[1];
			$this->next_global_match_id = $next[2];
			$this->next_global_match_day = $next[3];
		}

		$next = static::get_next_opponent($grammar, $next_matches_json, $competition);
		if ($next !== null) {
			$this->next_competition_opponent = $next[0];
			$this->next_competition_match_where = $next[1];
			$this->next_competition_match_id = $next[2];
			$this->next_competition_match_day = $next[3];
			$this->next_competition_match_neutral = $next[4];
		}

		$this->next_competition_match_link = FootballFetcher::MATCH_LINK . $this->next_competition_match_id . ">";
		$this->next_global_match_link = FootballFetcher::MATCH_LINK . $this->next_global_match_id . ">";

		$this->long_name = Utils::null_if_empty($team_data['PROFILE']['NAME']);
		$this->other_name = $grammar::get_elem($team_data['PROFILE'], 'OTHERNAME');
		$this->city = Utils::null_if_empty($team_data['PROFILE']['CITY']);

		$this->country = Utils::null_if_empty($team_data['PROFILE']['COUNTRY']);
		$this->country_id = intval($team_data['PROFILE']['COUNTRYID']);

		$this->build_stats($match_stats, $edition_stats, $edition_team_stats, $edition_negative_stats, $edition_team_negative_stats);

		$this->can_use_city = true;

		$this->compute_curiosities($pre_game_curiosities, $post_game_curiosities);
	}

	/**
	 * Getters
	 */
	public function get_curiosity($key)
	{
		$index = Utils::find($this->curiosities, function ($elem) use ($key) {
			return $elem->get_key() == $key;
		});

		return $index !== false ? $this->curiosities[$index] : 0;
	}

	public function get_curiosities()
	{
		return $this->curiosities;
	}

	public function get_final_players()
	{
		return $this->final_players;
	}

	public function get_form_competition()
	{
		return $this->form_competition;
	}

	public function get_form_number_competition()
	{
		return $this->form_number_competition;
	}

	public function get_match_stats()
	{
		return $this->match_stats;
	}

	public function get_stats()
	{
		return $this->stats;
	}

	private static function get_form($data, $competition = null)
	{
		$result = array();

		foreach ($data as $game) {
			if ($competition === null || $competition === $game["descr_edition"]) {
				array_push($result, $game["state"]);
			}
		}

		return $result;
	}

	public function get_country()
	{
		return $this->country;
	}

	public function get_country_id()
	{
		return $this->country_id;
	}

	public function get_type()
	{
		return $this->type;
	}

	public function get_can_use_city()
	{
		return $this->can_use_city;
	}

	private static function get_next_opponent($grammar, $data, $competition = null)
	{
		foreach ($data as $game) {
			if ($competition === null || $competition === $game["descr_edition"]) {
				return array($grammar::get_elem($game, "opponent_descr"), $game["where"], $game["id"], $game["fixture"], $game["neutral_field"]);
			}
		}
		return null;
	}

	public function get_long_name()
	{
		return $this->long_name;
	}

	public function get_other_name()
	{
		return $this->other_name;
	}

	public function get_name_array()
	{
		return $this->name_array;
	}

	public function get_coach()
	{
		return $this->coach;
	}

	public function get_city()
	{
		return $this->city;
	}

	public function get_goals()
	{
		return $this->goals;
	}

	public function get_prev_match_goals()
	{
		return $this->prev_match_goals;
	}

	public function get_points()
	{
		return $this->points;
	}

	public function get_pre_classification()
	{
		return $this->pre_classification;
	}

	public function get_pos_classification()
	{
		return $this->pos_classification;
	}

	public function has_red_card()
	{
		return $this->has_red_card;
	}

	public function get_next_competition_match_id()
	{
		return $this->next_competition_match_id;
	}

	public function get_stat()
	{
		return $this->stats[$this->current_stat];
	}

	public function get_current_stat()
	{
		return $this->current_stat;
	}

	public function get_stat_val()
	{
		return $this->stat_val;
	}

	public function get_next_global_opponent()
	{
		return $this->next_global_opponent;
	}

	public function get_next_competition_opponent()
	{
		return $this->next_competition_opponent;
	}

	public function get_form_number_global()
	{
		return $this->form_number_global;
	}

	public function get_next_global_match_id()
	{
		return $this->next_global_match_id;
	}

	public function get_next_competition_match_link()
	{
		return $this->next_competition_match_link;
	}

	public function get_next_global_match_link()
	{
		return $this->next_global_match_link;
	}

	public function get_winningless_streak()
	{
		$curiosity = $this->get_curiosity("winningless_streak");
		return $curiosity !== 0 ? $curiosity->get_value() : 0;
	}

	public function get_lossless_streak()
	{
		$curiosity = $this->get_curiosity("lossless_streak");
		return $curiosity !== 0 ? $curiosity->get_value() : 0;
	}

	public function get_no_goals_conceded_streak()
	{
		$curiosity = $this->get_curiosity("no_goals_conceded_streak");
		return $curiosity !== 0 ? $curiosity->get_value() : 0;
	}

	public function get_goals_scored_streak()
	{
		$curiosity = $this->get_curiosity("goals_scored_streak");
		return $curiosity !== 0 ? $curiosity->get_value() : 0;
	}

	/**
	 * Setters
	 */
	public function set_prev_match_goals($goals)
	{
		$this->prev_match_goals = $goals;
	}

	public function set_final_players($final_players)
	{
		$this->final_players = $final_players;
	}

	public function set_can_use_city($can_use_city)
	{
		$this->can_use_city = $can_use_city;
	}

	public function set_has_red_card($red_card)
	{
		$this->has_red_card = $red_card;
	}

	/**
	 * Conditions
	 */
	public function is_competition_winning_sequence()
	{
		return $this->form_competition[0] == "V";
	}

	public function is_competition_drawing_sequence()
	{
		return $this->form_competition[0] == "E";
	}

	public function is_competition_losing_sequence()
	{
		return $this->form_competition[0] == "D";
	}

	public function is_global_winning_sequence()
	{
		return $this->form_global[0] == "V";
	}

	public function is_global_drawing_sequence()
	{
		return $this->form_global[0] == "E";
	}

	public function is_global_losing_sequence()
	{
		return $this->form_global[0] == "D";
	}

	public function is_next_global_away()
	{
		return $this->next_global_match_where === "away";
	}

	public function is_next_global_home()
	{
		return $this->next_global_match_where === "home";
	}

	public function is_next_match_day()
	{
		return $this->next_competition_match_day == $this->match_day + 1;
	}

	public function is_last_match_day()
	{
		return $this->next_competition_match_day == null;
	}

	public function is_next_competition_away()
	{
		return $this->next_competition_match_where === "away" &&
		($this->next_competition_match_neutral == "" || $this->next_competition_match_neutral == "0");
	}

	public function is_next_competition_home()
	{
		return $this->next_competition_match_where === "home" &&
		($this->next_competition_match_neutral == "" || $this->next_competition_match_neutral == "0");
	}

	public function is_next_competition_neutral()
	{
		return $this->next_competition_match_neutral == "1";
	}

	/**
	 * Calculate curiosities
	 * @param array $pre_game_curiosities  Array of curiosities before the match
	 * @param array $post_game_curiosities Array of curiosities after the match
	 */
	private function compute_curiosities($pre_game_curiosities, $post_game_curiosities)
	{
		$this->curiosities = array();
		$keys = [
			"consecutive_without_win" => ["winningless_streak", "pre", ["consecutive_loss", "consecutive_draws"]],
			"consecutive_matches_without_conced" => ["no_goals_conceded_streak", "post"],
			"consecutive_without_lose" => ["lossless_streak", "post", ["consecutive_vict", "consecutive_draws"]],
			"consecutive_matches_scoring" => ["goals_scored_streak", "post"],
			"consecutive_loss" => ['losing_streak', "post"],
			"consecutive_vict" => ['winning_streak', "post"],
			"consecutive_draws" => ['drawing_streak', "post"],
			"loss" => ['losses', "pre"],
			"vict" => ['wins', "pre"],
			"draws" => ['draws', "pre"]
		];

		foreach ($keys as $api_key => $actual_key) {
			$skip = false;
			$pre_val = array_key_exists($api_key, $pre_game_curiosities) ? $pre_game_curiosities[$api_key] : null;
			$post_val = array_key_exists($api_key, $post_game_curiosities) ? $post_game_curiosities[$api_key] : null;

			if (count($actual_key) == 3) {
				foreach ($actual_key[2] as $related) {
					$pre_val_related = array_key_exists($related, $pre_game_curiosities) ? $pre_game_curiosities[$related] : null;
					$post_val_related = array_key_exists($related, $post_game_curiosities) ? $post_game_curiosities[$related] : null;

					if (($post_val > 0 && $post_val_related >= $post_val) || ($post_val == 0 && $pre_val_related >= $pre_val)) {
						$skip = true;
						break;
					}
				}
			}

			if ($skip) {
				continue;
			}

			array_push($this->curiosities, new Curiosity($this, $actual_key[0], $pre_val, $post_val, $actual_key[1]));
		}

		$this->compute_composite_curiosity($pre_game_curiosities, $post_game_curiosities, ["draws", "loss"], "games_without_winning");
	}

	/**
	 * Add head to head curiosity
	 * @param array $h2h Head to head decoded json data
	 */
	public function add_h2h($h2h)
	{
		array_push($this->curiosities, new Curiosity($this, "wins_against_opponent", $h2h["pre_wins"], $h2h["post_wins"], "post"));
	}

	/**
	 * Calculate composite curiosity
	 * @param array  $pre_game_curiosities  Array of curiosities before the match
	 * @param array  $post_game_curiosities Array of curiosities after the match
	 * @param array  $keys                  Curiosities keys
	 * @param string $composite_key			Composite key
	 */
	private function compute_composite_curiosity($pre_game_curiosities, $post_game_curiosities, $keys, $composite_key)
	{
		$pre_values = 0;
		$post_values = 0;

		foreach ($keys as $key) {

			$pre_val = array_key_exists($key, $pre_game_curiosities) ? $pre_game_curiosities[$key] : 0;
			$post_val = array_key_exists($key, $post_game_curiosities) ? $post_game_curiosities[$key] : 0;

			$pre_values += $pre_val;
			$post_values += $post_val;
		}

		array_push($this->curiosities, new Curiosity($this, $composite_key, $pre_values, $post_values));
	}

	/**
	 * Calculate statistics
	 * @param array $match_stats   					Stats of the match
	 * @param array $edition_stats 					Stats of the edition
	 * @param array $edition_team_stats 			Stats of the team in the edition
	 * @param array $edition_negative_stats 		Negative stats of the edition
	 * @param array $edition_team_negative_stats 	Negative stats of the team in the edition
	 */
	private function build_stats($match_stats, $edition_stats, $edition_team_stats, $edition_negative_stats, $edition_team_negative_stats)
	{
		$this->match_stats = [];

		foreach ($match_stats as $stat) {
			if ($stat["fk_team"] == $this->id) {
				$this->match_stats[$stat['key']] = $stat["value"];
			}
		}

		$this->edition_stats = [];
		foreach ($edition_stats as $stat) {
			$this->edition_stats[$stat['key']] = $stat["value"];
		}

		$this->negative_edition_stats = [];
		foreach ($edition_negative_stats as $stat) {
			$this->negative_edition_stats[$stat['key']] = $stat["value"];
		}

		$this->stats = array();

		foreach ($this->match_stats as $key => $stat) {
			if (array_key_exists($key, $edition_team_stats) && $stat > $edition_team_stats[$key]) {
				array_push($this->stats, new Stat($this, $key, $stat, true, true));
			}
			else if (array_key_exists($key, $this->edition_stats) && $stat > $this->edition_stats[$key]) {
				array_push($this->stats, new Stat($this, $key, $stat, false, true));
			}
			else if (array_key_exists($key, $edition_team_negative_stats) && $stat < $edition_team_negative_stats[$key]) {
				array_push($this->stats, new Stat($this, $key, $stat, true, false));
			}
			else if (array_key_exists($key, $this->negative_edition_stats) && $stat < $this->negative_edition_stats[$key]) {
				array_push($this->stats, new Stat($this, $key, $stat, false, false));
			}
		}

		$this->current_stat = Utils::find($this->stats, function ($element) {
			return $element->is_relevant();
		});
	}

	/**
	 * Whether it was the first mention to the team
	 * @param int $n Minimum step
	 * @return bool False
	 */
	public function is_first_mention()
	{
		return false;
	}

	/**
	 * Override
	 * @return string Name of the team
	 */
	public function __toString()
	{
		return $this->name->text;
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
            "name" => new EntityGetterManager("get_team_name"),
			"winningless_streak" => new EntityGetterFlat("get_winningless_streak"),
			"lossless_streak" => new EntityGetterFlat("get_lossless_streak"),
			"no_goals_conceded_streak" => new EntityGetterFlat("get_no_goals_conceded_streak"),
			"goals_scored_streak" => new EntityGetterFlat("get_goals_scored_streak"),
			"goals" => new EntityGetterFlat("get_goals"),
			"prev_match_goals" => new EntityGetterFlat("get_prev_match_goals"),
			"final_players" => new EntityGetterFlat("get_final_players"),
			"coach" => new EntityGetterFlat("get_coach"),
			"points" => new EntityGetterFlat("get_points"),
			"stat_val" => new EntityGetterFlat("get_stat_val"),
			"pre_classification" => new EntityGetterFlat("get_pre_classification"),
			"pos_classification" => new EntityGetterFlat("get_pos_classification"),
			"next_opponent_global" => new EntityGetterFlat("get_next_global_opponent"),
			"next_opponent_competition" => new EntityGetterFlat("get_next_competition_opponent"),
			"competition_form_sequence" => new EntityGetterFlat("get_form_number_competition"),
			"global_form_sequence" => new EntityGetterFlat("get_form_number_global"),
			"next_global_match_id" => new EntityGetterFlat("get_next_global_match_id"),
			"next_competition_match_id" => new EntityGetterFlat("get_next_competition_match_id"),
			"@next_competition_match_link" => new EntityGetterFlat("get_next_competition_match_link"),
			"@next_global_match_link" => new EntityGetterFlat("get_next_global_match_link"),
		];
	}

	/**
     * Get entities list
     */
    public static function get_entities_list()
    {
        if (empty(static::$entities)) {
            static::compute_entities();
		}
        return static::$entities;
    }
}
