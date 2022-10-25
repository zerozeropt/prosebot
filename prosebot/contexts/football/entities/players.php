<?php

require_once(__DIR__.'/../fetcher/data_fetcher.php');
require_once(__DIR__.'/../../entities.php');

/**
 * Class for people
 *
 * @author zerozero.pt
 */
abstract class PersonData extends EntityData
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Used step
	 * @var int
	 */
	protected $used_step;
	/**
	 * Whether it appears in events
	 * @var bool
	 */
	protected $appears_in_events;
	/**
	 * Whether it is relevant
	 * @var bool
	 */
	protected $is_relevant;
	/**
	 * Gender
	 * @var NameGender
	 */
	protected $gender;

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
     * @param array 	 $json_data Decoded json data for a person
	 * @param NameGender $gender Gender
     */
	function __construct($json_data, $gender)
	{
		parent::__construct(null, $json_data["abrev"], null);
		$this->used_step = PHP_INT_MAX;
		$this->appears_in_events = false;
		$this->is_relevant = false;
		$this->gender = $gender;
	}

	/**
	 * Getters
	 */
	public function get_appears_in_events()
	{
		return $this->appears_in_events;
	}

	public function is_relevant()
	{
		return $this->is_relevant;
	}

	public function get_gender()
	{
		return $this->gender;
	}

	/**
	 * Setters
	 */
	public function set_appears_in_events($bool)
	{
		$this->appears_in_events = $bool;
	}

	public function set_relevant($bool)
	{
		$this->is_relevant = $bool;
	}

	/**
	 * Whether it is a coach
	 * @return bool  Whether it is a coach
	 */
	abstract function is_coach();

	/**
	 * Get entity. Every entity should implement this function
     * @param EntitiesManagerFootball $manager   Manager that handles the entity
     * @param string          		  $entity    Entity or property name to be handled
	 * @param int                     $used_step Used step
	 * @param key|null				  $event_n   Event key
	 * @return string Content to be written for the entity
     */
	public function get_entity($manager, $entity, $used_step = PHP_INT_MAX, $event_n = null, $event = null)
	{
		if ($used_step < $this->used_step) {
			$this->used_step = $used_step;
		}

		return parent::get_entity($manager, $entity, $used_step, $event_n, $event);
	}
}

/**
 * Class for a coach
 *
 * @author zerozero.pt
 */
class CoachData extends PersonData
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * List of entities
	 * @var EntityGetter[]
	 */
	protected static $entities = [];
	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
     * @param array 	 $json_data Decoded json data for a coach
	 * @param NameGender $gender Gender
     */
	function __construct($json_data, $gender)
	{
		parent::__construct($json_data, $gender);
		parent::set_id($json_data["id"]);
		parent::set_link(FootballFetcher::COACH_LINK . $this->id . ">");
	}

	/**
	 * Whether it is a coach
	 * @return bool True
	 */
	public function is_coach()
	{
		return true;
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
            "name" => new EntityGetterManager("get_player_name")
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

/**
 * Class for players
 *
 * @author zerozero.pt
 */
class PlayerData extends PersonData
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Position index
	 * @var int
	 */
	private $position;
	/**
	 * Number of goals
	 * @var int
	 */
	private $goals;
	/**
	 * Number of tmp goals
	 * @var int
	 */
	private $tmp_goals;
	/**
	 * Team id
	 * @var string
	 */
	private $team_id;
	/**
	 * Number of assists
	 * @var int
	 */
	private $assists;
	/**
	 * Positive impact score
	 * @var int
	 */
	private $positive_impact;
	/**
	 * Decisive impact score
	 * @var int
	 */
	private $decisive_impact;
	/**
	 * Minute in which the player entered the match
	 * @var int
	 */
	private $minute_in;
	/**
	 * Minute in which the player left the match
	 * @var int
	 */
	private $minute_out;
	/**
	 * Number of goals in the season
	 * @var int
	 */
	private $season_goals;
	/**
	 * Number of consecutive matches scoring
	 * @var int
	 */
	private $consecutive_matches_scoring;
	/**
	 * Country id
	 * @var string
	 */
	private $country_id;
	/**
	 * Country id of the club
	 * @var string
	 */
	private $club_country_id;
	/**
	 * List of entities
	 * @var EntityGetter[]
	 */
	protected static $entities = [];

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	/**
     * @param array 	 $json_data Decoded json data for a player
	 * @param NameGender $gender Gender
     */
	function __construct($json_data, $gender)
	{
		parent::__construct($json_data, $gender);
		parent::set_id($json_data["fk_player"]);
		parent::set_link(FootballFetcher::PLAYER_LINK . $this->id . ">");
		$this->position = intval($json_data["position"]);
		$this->goals = intval($json_data["sgoals"]);
		$this->tmp_goals = intval($json_data["goals"]);
		$this->team_id = $json_data["fk_team"];

		$this->own_goals = 0;
		if ($this->goals == 0 && $this->tmp_goals != 0) {
			$this->own_goals = 1;
		}

		$this->assists = intval($json_data['sassists']);
		$this->positive_impact = 0;
		$this->decisive_impact = 0;
		$this->minute_in = intval($json_data["min_in"]);
		$this->minute_out = intval($json_data["min_out"]);

		$this->season_goals = 0;
		$this->consecutive_matches_scoring = 0;
		$this->country_id = intval($json_data["fk_player_country"]);
		$this->club_country_id = null;
	}

	/**
	 * Getters
	 */
	public function get_team_id()
	{
		return $this->team_id;
	}

	public function get_club_country_id()
	{
		return $this->club_country_id;
	}

	public function get_country_id()
	{
		return $this->country_id;
	}

	public function get_goals()
	{
		return $this->goals;
	}

	public function get_position()
	{
		return $this->position;
	}

	public function get_own_goals()
	{
		return $this->own_goals;
	}

	public function get_assists()
	{
		return $this->assists;
	}

	public function get_decisive_impact()
	{
		return $this->decisive_impact;
	}

	public function get_positive_impact()
	{
		return $this->positive_impact;
	}

	public function get_minute_in()
	{
		return $this->minute_in;
	}

	public function get_consecutive_matches_scoring()
	{
		return $this->consecutive_matches_scoring;
	}

	public function get_season_goals($event)
	{
		if ($event !== null) {
			$add = $event->get_scorer_n();
			return $this->season_goals + $add;
		}
		return $this->season_goals;
	}

	/**
	 * Setters
	 */
	public function set_decisive_impact($val)
	{
		$this->decisive_impact = $val;
	}

	public function set_positive_impact($val)
	{
		$this->positive_impact = $val;
	}

	/**
	 * Whether it is a coach
	 * @return bool False
	 */
	public function is_coach()
	{
		return false;
	}

	/**
	 * Whether it was the first mention to the player
	 * @param int $n Minimum step
	 * @return bool Whether it was the first mention to the player
	 */
	public function is_first_mention($n)
	{
		return $this->used_step >= $n;
	}

	/**
	 * Add stats values
	 * @param array $json_data Decoded json data for stats
	 */
	public function add_stats($json_data)
	{
		$this->season_goals = intval($json_data["player_total_goals"]) - $this->goals;
		$this->consecutive_matches_scoring = intval($json_data["player_total_consec_match_scoring"]);
		$this->club_country_id = intval($json_data["club_country_id"]);
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
            "name" => new EntityGetterManager("get_player_name"),
			"name_gender" => new EntityGetterManager("get_player_name_gender"),
			"position" => new EntityGetterManager("get_player_position"),
			"goals" => new EntityGetterFlat("get_goals"),
			"assists" => new EntityGetterFlat("get_assists"),
			"minute_in" => new EntityGetterFlat("get_minute_in"),
			"consecutive_matches_scoring" => new EntityGetterFlat("get_consecutive_matches_scoring"),
			"season_goals" => new EntityGetterFlat("get_season_goals")
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
