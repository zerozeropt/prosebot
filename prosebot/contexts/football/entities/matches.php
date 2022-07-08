<?php

require_once(__DIR__.'/../fetcher/data_fetcher.php');
require_once('events.php');
require_once('players.php');
require_once('teams.php');
require_once('competitions.php');
require_once(__DIR__.'/../../entities.php');

/**
 * Class for a match
 *
 * @author zerozero.pt
 */
class MatchData extends MainEntityData
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Gender
	 * @var NameGender
	 */
	private $gender;
	/**
	 * Grammar
	 * @var Grammar
	 */
	private $grammar;
	/**
	 * Teams
	 * @var TeamData[]
	 */
	private $teams;
	/**
	 * Head to head data
	 * @var array
	 */
	private $h2h;
	/**
	 * Statistics
	 * @var array
	 */
	private $stats;
	/**
	 * Curiosities
	 * @var array
	 */
	private $curiosities;
	/**
	 * Players
	 * @var PlayerData[]
	 */
	private $players;
	/**
	 * Coaches
	 * @var CoachData[]
	 */
	private $coaches;
	/**
	 * Date
	 * @var DateTime
	 */
	private $date;
	/**
	 * Final result
	 * @var string
	 */
	private $final_result;
	/**
	 * League
	 * @var string
	 */
	private $league;
	/**
	 * Competition
	 * @var CompetitionData
	 */
	private $competition;
	/**
	 * Stadium
	 * @var string
	 */
	private $stadium;
	/**
	 * Local
	 * @var string
	 */
	private $local;
	/**
	 * Fixture
	 * @var int
	 */
	private $fixture;
	/**
	 * Stage
	 * @var array
	 */
	private $stage;
	/**
	 * Match id
	 * @var string
	 */
	private $match_id;
	/**
	 * Edition id
	 * @var int
	 */
	private $edition;
	/**
	 * Edition hyperlink
	 * @var string
	 */
	private $edition_link;
	/**
	 * If >0 it is senior, if ==0 it is not senior
	 * @var int
	 */
	private $senior;
	/**
	 * Number of people in the crowd
	 * @var int
	 */
	private $crowd;
	/**
	 * Capacity of the stadium
	 * @var int
	 */
	private $stadium_capacity;
	/**
	 * Relative frequency between the crowd and the capacity of the stadium
	 * @var float
	 */
	private $crowd_ratio;
	/**
	 * Whether the match happens on a neutral field. If "1" it is a neutral field, else if "" it is not.
	 * @var string
	 */
	private $neutral_ground;
	/**
	 * Duration of an half time
	 * @var int
	 */
	private $half_duration;
	/**
	 * Full match duration
	 * @var int
	 */
	private $duration;
	/**
	 * Type of competition
	 * @var string
	 */
	private $competition_type;
	/**
	 * When it is a knockout, the number of the hand
	 * @var string
	 */
	private $competition_hand;
	/**
	 * The number of hands in a knockout phase
	 * @var string
	 */
	private $competition_num_hands;
	/**
	 * Id of the winner team of a knockout
	 * @var string
	 */
	private $competition_elim_winner;
	/**
	 * Whether it is the first game in the competition for both teams. It can be '1' or '2'
	 * @var string
	 */
	private $first_competition_game;
	/**
	 * Relevant players
	 * @var PlayerData[]
	 */
	private $relevant_players;
	/**
	 * Whether there was a turnaround in the score
	 * @var bool
	 */
	private $has_turnaround;
	/**
	 * List of players of the home team
	 * @var PlayerData[]
	 */
	private $home_players;
	/**
	 * List of players of the away team
	 * @var PlayerData[]
	 */
	private $away_players;
	/**
	 * List of players' names who scored for the home team
	 * @var string[]
	 */
	private $home_goals;
	/**
	 * List of players' names who scored for the away team
	 * @var string[]
	 */
	private $away_goals;
	/**
	 * Id of the best player in the match
	 * @var string
	 */
	private $best_player_id;
	/**
	 * Id of the decisive player of the match, that scored a decisive goal in the last minutes
	 * @var string
	 */
	private $decisive_player_id;
	/**
	 * If "AP" then there was extra time. If "" there was not.
	 * @var string
	 */
	private $extra_time;
	/**
	 * Number of penalty goals scored by the home team, when the match ends in penalties
	 * @var int
	 */
	private $home_pen_goals;
	/**
	 * Number of penalty goals scored by the away team, when the match ends in penalties
	 * @var int
	 */
	private $away_pen_goals;
	/**
	 * Array of statistics of the team in the edition
	 * @var array
	 */
	private $edition_team_stats;
	/**
	 * Array of negative statistics of the team in the edition
	 * @var array
	 */
	private $edition_team_negative_stats;
	/**
	 * Whether there was another match between the two teams for the same competition
	 * @var bool
	 */
	private $has_previous_match;
	/**
	 * Own goal initials for a specific language
	 * @var string
	 */
	private $own_goal_form;
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
	 * @param string  $match_id 	 Match id
	 * @param Grammar $grammar  	 Grammar
	 * @param string  $country		 Country initials
	 * @param int	  $country_index Country index
	 * @param string  $own_goal_form Own goal form for a specific language
	 */
	function __construct($match_id, $grammar, $country, $country_index, $own_goal_form)
	{
		//get full json
		$match_data = FootballFetcher::get_match_json($match_id)["data"];
		$h2h_data = FootballFetcher::get_h2h_json($match_id)["data"];

		// get match gender
		$this->gender = Utils::null_if_empty($match_data['INFO']["gender"]);
		$this->grammar = $grammar;
		$this->own_goal_form = $own_goal_form;

		$this->build_stats($match_data);

		$home_team_edition_stats = array_key_exists("TOP_EDITION_HOME_TEAM", $match_data['EDITIONSTATS']) ? $match_data['EDITIONSTATS']["TOP_EDITION_HOME_TEAM"] : [];
		$home_team_negative_edition_stats = array_key_exists("TOP_EDITION_HOME_TEAM", $match_data['EDITIONNEGATIVESTATS']) ? $match_data['EDITIONNEGATIVESTATS']["TOP_EDITION_HOME_TEAM"] : [];
		$away_team_edition_stats = array_key_exists("TOP_EDITION_AWAY_TEAM", $match_data['EDITIONSTATS']) ? $match_data['EDITIONSTATS']["TOP_EDITION_AWAY_TEAM"] : [];
		$away_team_negative_edition_stats = array_key_exists("TOP_EDITION_AWAY_TEAM", $match_data['EDITIONNEGATIVESTATS']) ? $match_data['EDITIONNEGATIVESTATS']["TOP_EDITION_AWAY_TEAM"] : [];

		//populate teams (0-home, 1-away)
		$this->teams = array(
			new TeamData(
				$grammar,
				intval($match_data['MATCHREPORT']["game"]["fk_home_team"]), // team id
				$match_data['HOMEGOALS'], //goals
				Utils::null_if_empty($match_data['MATCHREPORT']["coach_home"]['abrev']), // coach
				Utils::null_if_empty($match_data["LEAGUE"]), // league
				intval($match_data['CLASSIFHOME_PRE']), // pre_classification
				intval($match_data['CLASSIFHOME_POS']), // $pos_classification
				intval($match_data['POINTSHOME']), // points
				$match_data['FORMHOME'], // form
				$match_data['NEXTHOME'], // next matches,
				$match_data["FIXTURE"],
				$match_data['MATCHSTATS'],
				$home_team_edition_stats,
				$home_team_negative_edition_stats,
				$this->edition_team_stats,
				$this->edition_team_negative_stats,
				$match_data['INFO']["type_home_team"],
				$match_data['CURIOSITY']['PRE_MATCH_HOME'],
				$match_data['CURIOSITY']['POS_MATCH_HOME']
			),
			new TeamData(
				$grammar,
				intval($match_data['MATCHREPORT']["game"]["fk_away_team"]), // team id
				$match_data['AWAYGOALS'], //goals
				Utils::null_if_empty($match_data['MATCHREPORT']["coach_away"]['abrev']), // coach
				Utils::null_if_empty($match_data["LEAGUE"]), // league
				intval($match_data['CLASSIFAWAY_PRE']), // pre_classification
				intval($match_data['CLASSIFAWAY_POS']), // $pos_classification
				intval($match_data['POINTSAWAY']), // points
				$match_data['FORMAWAY'], // form
				$match_data['NEXTAWAY'],
				$match_data["FIXTURE"],
				$match_data['MATCHSTATS'],
				$away_team_edition_stats,
				$away_team_negative_edition_stats,
				$this->edition_team_stats,
				$this->edition_team_negative_stats,
				$match_data['INFO']["type_away_team"],
				$match_data['CURIOSITY']["PRE_MATCH_AWAY"],
				$match_data['CURIOSITY']["POS_MATCH_AWAY"]
			)
		);

		$this->h2h = $this->compute_h2h_data($h2h_data);
		$prev_match_goals = $this->compute_previous_match_goals($h2h_data);
		$this->has_previous_match = !empty($prev_match_goals);
		if ($this->has_previous_match) {
			$this->teams[0]->set_prev_match_goals($prev_match_goals[0]);
			$this->teams[1]->set_prev_match_goals($prev_match_goals[1]);
		}
		$this->teams[0]->add_h2h(array("pre_wins" => $this->h2h["pre_home_team_wins"], "post_wins" => $this->h2h["post_home_team_wins"]));
		$this->teams[1]->add_h2h(array("pre_wins" => $this->h2h["pre_away_team_wins"], "post_wins" => $this->h2h["post_away_team_wins"]));
		$this->stats = array_merge($this->stats, $this->teams[0]->get_stats(), $this->teams[1]->get_stats());
		$this->curiosities = array_merge($this->teams[0]->get_curiosities(), $this->teams[1]->get_curiosities());

		if ($this->teams[0]->get_city() === $this->teams[1]->get_city()) {
			$this->teams[0]->set_can_use_city(false);
			$this->teams[1]->set_can_use_city(false);
		}

		$home_players = array_key_exists("home_players", $match_data['MATCHREPORT']) ? $match_data['MATCHREPORT']["home_players"] : [];
		$away_players = array_key_exists("away_players", $match_data['MATCHREPORT']) ? $match_data['MATCHREPORT']["away_players"] : [];

		$starter_home = array_key_exists("starters", $home_players) ? $home_players["starters"] : [];
		$benched_home = array_key_exists("benched", $home_players) ? $home_players["benched"] : [];
		$starter_away = array_key_exists("starters", $away_players) ? $away_players["starters"] : [];
		$benched_away = array_key_exists("benched", $away_players) ? $away_players["benched"] : [];

		//populate players (player_id, player)
		$this->players = array();
		foreach (array_merge(
			$starter_home,
			$benched_home,
			$starter_away,
			$benched_away,
		)
			as $player) {
			$this->players[$player["fk_player"]] = new PlayerData($player, $this->gender);
		}

		foreach ($match_data['PLAYERSTATS'] as $stats) {
			if (array_key_exists('player_id', $stats) && array_key_exists($stats['player_id'], $this->players)) {
				$this->players[$stats['player_id']]->add_stats($stats);
			}
		}

		$this->coaches = array();
		$this->coaches[$match_data['MATCHREPORT']["coach_home"]['id']] = new CoachData($match_data['MATCHREPORT']["coach_home"], $this->gender);
		$this->coaches[$match_data['MATCHREPORT']["coach_away"]['id']] = new CoachData($match_data['MATCHREPORT']["coach_away"], $this->gender);

		//match data
		$date = DateTime::createFromFormat('Y-m-d H-i-s', $match_data['DATETIME'], timezone_open('Europe/Lisbon'));
		$this->date = date_timezone_set($date, timezone_open(get_globals()["languageToTimezone"][$country]));
		$this->final_result = $match_data['FINALRESULT'];
		$this->league = Utils::null_if_empty($match_data['LEAGUE']);
		$this->competition = new CompetitionData($match_data['INFO']);
		$this->stadium = Utils::null_if_empty($match_data['STADIUM']);
		$this->local = Utils::null_if_empty($match_data['INFO']['local']);
		$this->fixture = Utils::null_if_empty($match_data["FIXTURE"]);
		$this->stage = Utils::null_if_empty($match_data['INFO']['name_stage']);
		$this->match_id = $match_id;
		$this->edition = intval($match_data['MATCHREPORT']["edition"]['id']);
		$this->edition_link = FootballFetcher::edition_link . $this->edition . ">";
		$this->senior = Utils::null_if_empty($match_data['INFO']['senior']);
		$this->crowd = Utils::null_if_empty($match_data['INFO']['crowd']);
		$this->stadium_capacity = Utils::null_if_empty($match_data['INFO']['stadium_capacity']);
		$this->crowd_ratio = $this->stadium_capacity > 0 ? $this->crowd / $this->stadium_capacity : 0;
		$this->neutral_ground = Utils::null_if_empty($match_data['INFO']["neutral_field"]);
		$this->half_duration = Utils::null_if_empty($match_data['INFO']["duration_int"]);
		$this->duration = ($this->half_duration == null || $this->half_duration == 0) ? 90 : $this->half_duration * 2;

		$this->competition_type = Utils::null_if_empty($match_data['INFO']["phase_type"]);
		$this->competition_hand = Utils::null_if_empty($match_data['INFO']["hand"]);
		$this->competition_num_hands = Utils::null_if_empty($match_data['INFO']["phase_num_hands"]);
		$this->competition_elim_winner = Utils::null_if_empty($match_data['INFO']["phase_winner"]);

		//check if it is the first game in the competition for both teams
		$this->first_competition_game = Utils::boolstr(!Utils::boolstr($this->teams[0]->get_entity(null, "competition_form_sequence")) && !Utils::boolstr($this->teams[1]->get_entity(null, "competition_form_sequence")));
		if ($this->first_competition_game == '1' && ($this->stage != null && $this->stage[0] != 'J' && $this->stage[0] != 'G')) {
			$this->first_competition_game = '2';
		}

		//events
		$this->events = $this->construct_events($match_data['EVENTS']);
		$this->relevant_players = $this->get_relevant_players($country_index);

		if (count($this->events) > 0) {
			array_push($this->events, new HalfTimeEvent($this->teams[0]));
			array_push($this->events, new FinalTimeEvent($this->teams[0]));

			usort($this->events, function ($a, $b) {
				if ($a->get_minute()[0] == $b->get_minute()[0]) {
					return $a->get_minute()[1] > $b->get_minute()[1];
				}
				return $a->get_minute()[0] > $b->get_minute()[0];
			});

			$this->add_events_counts();
			$this->add_double_yellow_cards();
		}

		$this->has_turnaround = $this->compute_turnaround();

		//set home players
		$this->home_players = array();
		foreach (array_merge(
			$starter_home,
			$benched_home
		) as $player) {
			$this->home_players[$player["fk_player"]] = new PlayerData($player, $this->gender);
		}

		//set away players
		$this->away_players = array();
		foreach (array_merge(
			$starter_away,
			$benched_away
		) as $player) {
			$this->away_players[$player["fk_player"]] = new PlayerData($player, $this->gender);
		}

		//array with goals (name and number of goals)
		$this->home_goals = array();
		$this->away_goals = array();
		$this->compute_match_goals();
		$this->compute_starter_benched_players();

		//array with red cards (name and number of goals)
		$this->compute_red_cards();

		// besides returning best player (or null, if none), updates each player impact factor
		$this->best_player_id = $this->compute_players_positive_impact();
		$this->decisive_player_id = $this->compute_players_decisive_impact();

		$this->extra_time = Utils::null_if_empty($match_data['INFO']["extra_time"]);
		$this->home_pen_goals = intval($match_data['INFO']['home_pen_goals']);
		$this->away_pen_goals = intval($match_data['INFO']['away_pen_goals']);
	}

	/**
	 * Getters
	 */
	public function is_neutral_ground()
	{
		return $this->neutral_ground;
	}

	public function get_crowd()
	{
		return $this->crowd;
	}

	public function get_events()
	{
		return $this->events;
	}

	public function get_half_duration()
	{
		return $this->half_duration;
	}

	public function get_duration()
	{
		return $this->duration;
	}

	public function get_crowd_ratio()
	{
		return $this->crowd_ratio;
	}

	public function get_extra_time()
	{
		return $this->extra_time;
	}

	public function get_competition()
	{
		return $this->competition;
	}

	public function get_competition_type()
	{
		return $this->competition_type;
	}

	public function get_competition_hand()
	{
		return $this->competition_hand;
	}

	public function get_competition_num_hands()
	{
		return $this->competition_num_hands;
	}

	public function get_starters()
	{
		return $this->starter_players;
	}

	public function get_benched()
	{
		return $this->benched_players;
	}

	public function get_curiosities()
	{
		return $this->curiosities;
	}

	public function get_home_team_id()
	{
		return $this->teams[0]->get_id();
	}

	public function get_away_team_id()
	{
		return $this->teams[1]->get_id();
	}

	public function get_match_id()
	{
		return $this->match_id;
	}

	public function get_edition()
	{
		return $this->edition;
	}

	public function has_turnaround()
	{
		return $this->has_turnaround;
	}

	public function get_home_goals()
	{
		return count($this->home_goals);
	}

	public function get_away_goals()
	{
		return count($this->away_goals);
	}

	public function get_first_competition_game()
	{
		return $this->first_competition_game;
	}

	public function get_stats()
	{
		return $this->stats;
	}

	public function home_team()
	{
		return $this->teams[0];
	}

	public function get_stage()
	{
		return $this->stage;
	}

	public function away_team()
	{
		return $this->teams[1];
	}

	public function get_date()
	{
		return $this->date;
	}

	public function get_best_player()
	{
		return $this->best_player_id === null ? null : $this->players[$this->best_player_id];
	}

	public function get_decisive_player()
	{
		return $this->decisive_player_id === null ? null : $this->players[$this->decisive_player_id];
	}

	public function get_senior()
	{
		return $this->senior;
	}

	public function get_gender()
	{
		return $this->gender;
	}

	public function get_fixture()
	{
		return $this->fixture;
	}

	public function get_edition_link()
	{
		return $this->edition_link;
	}

	public function get_league()
	{
		return $this->league;
	}

	public function pen_score_sorted()
	{
		return $this->pen_score(true);
	}

	public function score_link_sorted()
	{
		return $this->score_link(true);
	}

	public function get_stadium()
	{
		return $this->stadium;
	}

	public function get_local()
	{
		return $this->local;
	}

	public function get_match_stat()
	{
		return $this->stats[$this->current_match_stat];
	}

	public function get_home_team_stat()
	{
		return $this->teams[0]->get_stat();
	}

	public function get_away_team_stat()
	{
		return $this->teams[1]->get_stat();
	}

	public function get_winner_stat()
	{
		return $this->winner()->get_stat();
	}

	public function get_loser_stat()
	{
		return $this->loser()->get_stat();
	}

	public function get_starter_players()
	{
		return $this->grammar::list_str($this->starter_players, $this->grammar::get_st_connector());
	}

	public function get_benched_players()
	{
		return $this->grammar::list_str($this->benched_players, $this->grammar::get_st_connector());
	}

	public function get_home_goals_list()
	{
		return $this->grammar::list_str($this->home_goals, $this->grammar::get_st_connector());
	}

	public function get_away_goals_list()
	{
		return $this->grammar::list_str($this->away_goals, $this->grammar::get_st_connector());
	}

	public function get_match_goals()
	{
		return strval($this->goals());
	}

	public function get_event_minute($event_key, $event)
	{
		return $event->get_minute()[0];
	}

	public function get_event_score_player($event_key, $event)
	{
		return $event->get_score_player();
	}

	public function get_event_assist_maker($event_key, $event)
	{
		return $event->get_assist_player();
	}

	public function get_stat_val($event_key, $event)
	{
		return $this->stats[$event_key]->get_value();
	}

	public function get_pre_curiosity_val($event_key, $event)
	{
		return $this->curiosities[$event_key]->get_pre_value();
	}

	public function get_post_curiosity_val($event_key, $event)
	{
		return $this->curiosities[$event_key]->get_post_value();
	}

	public function get_event_team($event_key, $event)
	{
		$team = $event->get_team();
		if ($this->teams[0] === $team) {
			return $this->teams[0];
		}
		if ($this->teams[1] === $team) {
			return $this->teams[1];
		}
		return null;
	}

	public function get_other_curiosity_team($event_key, $event)
	{
		$team = $this->curiosities[$event_key]->get_team();
		if ($this->teams[0] === $team) {
			return $this->teams[1];
		}
		return $this->teams[0];
	}

	public function get_stat_team($event_key, $event)
	{
		$team = $this->stats[$event_key]->get_team();
		if ($this->teams[0] === $team) {
			return $this->teams[0];
		}
		if ($this->teams[1] === $team) {
			return $this->teams[1];
		}
		return null;
	}

	public function get_event_team_goals_diff($event_key, $event)
	{
		return $event->team_goals_diff();
	}

	public function get_event_player($event_key, $event)
	{
		return $event->get_player();
	}

	public function get_event_goalkeeper($event_key, $event)
	{
		return $event->get_goalkeeper();
	}

	private function get_relevant_players($country_index)
	{
		$relevant_players_array = [];

		foreach ($this->players as $player) {
			if ($this->teams[0]->get_id() == $player->get_team_id()) {
				$player_team = $this->teams[0];
				$other_team = $this->teams[1];
			}
			else {
				$player_team = $this->teams[1];
				$other_team = $this->teams[0];
			}

			$is_national_team = $player_team->get_type() == 1;
			$is_club_player_relevant = $player->get_country_id() == $country_index && $player_team->get_country_id() != $country_index && $other_team->get_country_id() != $country_index;
			$is_nt_player_relevant = $player->get_country_id() != $country_index && $player->get_club_country_id() == $country_index;

			if ((!$is_national_team && $is_club_player_relevant) || ($is_national_team && $is_nt_player_relevant)) {
				$player->set_relevant(true);
				array_push($relevant_players_array, $player);
			}
		}

		return $relevant_players_array;
	}

	public function has_previous_match()
	{
		return $this->has_previous_match;
	}

	/**
	 * Construct stats
	 * @param object $match_data Decoded json data for a match
	 */
	function build_stats($match_data)
	{
		$this->stats = [];
		$top_edition_stats = [];
		$top_edition_negative_stats = [];
		$this->match_stats = array();
		$this->edition_team_stats = [];
		$this->edition_team_negative_stats = [];

		foreach ($match_data['MATCHSTATS'] as $stat) {
			if ($stat['key'] == "ballpo") {
				continue;
			}
			if (array_key_exists($stat['key'], $this->match_stats)) {
				$this->match_stats[$stat['key']] += $stat["value"];
			}
			else {
				$this->match_stats[$stat['key']] = $stat["value"];
			}
		}

		if (array_key_exists("TOP_EDITION_MATCH", $match_data['EDITIONSTATS'])) {
			foreach ($match_data['EDITIONSTATS']["TOP_EDITION_MATCH"] as $stat) {
				$top_edition_stats[$stat['key']] = $stat["value"];
			}
		}

		if (array_key_exists("TOP_EDITION_MATCH", $match_data['EDITIONNEGATIVESTATS'])) {
			foreach ($match_data['EDITIONNEGATIVESTATS']["TOP_EDITION_MATCH"] as $stat) {
				$top_edition_negative_stats[$stat['key']] = $stat["value"];
			}
		}

		foreach ($this->match_stats as $key => $stat) {
			if (array_key_exists($key, $top_edition_stats) && $stat > $top_edition_stats[$key]) {
				array_push($this->stats, new Stat(null, $key, $stat, false, true));
			}
			else if (array_key_exists($key, $top_edition_negative_stats) && $stat < $top_edition_negative_stats[$key]) {
				array_push($this->stats, new Stat(null, $key, $stat, false, false));
			}
		}

		if (array_key_exists("TOP_EDITION_TEAM", $match_data['EDITIONSTATS'])) {
			foreach ($match_data['EDITIONSTATS']["TOP_EDITION_TEAM"] as $stat) {
				$this->edition_team_stats[$stat['key']] = $stat["value"];
			}
		}

		if (array_key_exists("TOP_EDITION_TEAM", $match_data['EDITIONNEGATIVESTATS'])) {
			foreach ($match_data['EDITIONNEGATIVESTATS']["TOP_EDITION_TEAM"] as $stat) {
				$this->edition_team_negative_stats[$stat['key']] = $stat["value"];
			}
		}

		$this->current_match_stat = Utils::find($this->stats, function ($element) {
			return $element->get_team() == null && $element->is_relevant();
		});
	}

	/**
	 * Handle head to head data
	 * @param object $h2h_data Decoded json data for head to head
	 * @return array Head to head stats
	 */
	private function compute_h2h_data($h2h_data)
	{
		$pre_games = $h2h_data['SUMMARYTOTAL']["games"]["0"];
		$post_games = $pre_games + 1;
		$pre_home_team_wins = $h2h_data['SUMMARYTOTAL']["vict_1"]["0"];
		$post_home_team_wins = $pre_home_team_wins;
		$pre_away_team_wins = $h2h_data['SUMMARYTOTAL']["vict_2"]["0"];
		$post_away_team_wins = $pre_away_team_wins;
		$pre_draws = $h2h_data['SUMMARYTOTAL']["draws"]["0"];
		$post_draws = $pre_draws;

		if ($this->score_diff() == 0) {
			$post_draws = $pre_draws + 1;
		}
		else if ($this->winner() == $this->home_team()) {
			$post_home_team_wins = $pre_home_team_wins + 1;
		}
		else {
			$post_away_team_wins = $pre_away_team_wins + 1;
		}

		return array(
			"pre_games" => $pre_games,
			"pre_home_team_wins" => $pre_home_team_wins,
			"pre_away_team_wins" => $pre_away_team_wins,
			"pre_draws" => $pre_draws,
			"post_games" => $post_games,
			"post_home_team_wins" => $post_home_team_wins,
			"post_away_team_wins" => $post_away_team_wins,
			"post_draws" => $post_draws
		);
	}

	/**
	 * Handle previous match between the two teams for the same competition
	 * @param object $h2h_data Decoded json data for head to head
	 * @return array|null The number of goals for each team or null in case there is no previous match
	 */
	private function compute_previous_match_goals($h2h_data)
	{
		$all_matches = $h2h_data["ALLMATCHES"];
		if (count($all_matches) <= 1) {
			return [];
		}

		$current_competition_id = $all_matches[0]["competition"];
		for ($i = 1; $i < count($all_matches); $i++) {
			if ($all_matches[$i]["competition"] == $current_competition_id &&
				$this->home_team()->get_id() == $all_matches[$i]["awayteam_id"] &&
				$this->away_team()->get_id() == $all_matches[$i]["hometeam_id"])
			{
				$prev_match_goals = [];
				$prev_match_goals[0] = $all_matches[$i]["SCORED"];
				$prev_match_goals[1] = $all_matches[$i]["CONCEDED"];
				return $prev_match_goals;
			}
		}
		return [];
	}

	/**
	 * Get score of the match
	 * @param bool $sort Whether to sort the order of the score
	 * @return string Score
	 */
	public function score($sort = false)
	{
		if ($sort || $this->teams[0]->get_goals() > $this->teams[1]->get_goals()) {
			return $this->teams[0]->get_goals() . 'x' . $this->teams[1]->get_goals();
		}
		return $this->teams[1]->get_goals() . 'x' . $this->teams[0]->get_goals();
	}

	/**
	 * Get score of the previous match between the two teams for the same competition
	 * @param bool $sort Whether to sort the order of the score
	 * @return string Score
	 */
	public function prev_match_score($sort = false)
	{
		if ($sort || $this->teams[0]->get_prev_match_goals() > $this->teams[1]->get_prev_match_goals()) {
			return $this->teams[0]->get_prev_match_goals() . 'x' . $this->teams[1]->get_prev_match_goals();
		}
		return $this->teams[1]->get_prev_match_goals() . 'x' . $this->teams[0]->get_prev_match_goals();
	}

	/**
	 * Get score of the penalties
	 * @param bool $sort Whether to sort the order of the score
	 * @return string Score
	 */
	public function pen_score($sort = false)
	{
		if ($sort || $this->home_pen_goals > $this->away_pen_goals) {
			return $this->home_pen_goals . 'x' . $this->away_pen_goals;
		}
		return $this->away_pen_goals . 'x' . $this->home_pen_goals;
	}

	/**
	 * Get score hyperlink
	 * @param bool $sort Whether to sort the order of the score
	 * @return string Hyperlink
	 */
	public function score_link($sort = false)
	{
		return FootballFetcher::match_link . $this->match_id . ">" . $this->score($sort) . "</a>";
	}

	/**
	 * Get difference of goals
	 * @return int Difference of goals
	 */
	public function score_diff()
	{
		return $this->teams[1]->get_goals() - $this->teams[0]->get_goals();
	}

	/**
	 * Get difference of goals in the previous match between the two teams for the same competition
	 * @return int Difference of goals
	 */
	public function prev_match_score_diff()
	{
		return $this->teams[1]->get_prev_match_goals() - $this->teams[0]->get_prev_match_goals();
	}

	/**
	 * Get sum of goals
	 * @return int Sum of goals
	 */
	public function goals()
	{
		return $this->teams[0]->get_goals() + $this->teams[1]->get_goals();
	}

	/**
	 * Get winner team
	 * @return TeamData Winner team
	 */
	public function winner()
	{
		if ($this->teams[1]->get_goals() > $this->teams[0]->get_goals()) {
			return $this->teams[1];
		}
		return $this->teams[0];
	}

	/**
	 * Get winner team of the previous match between the same teams for the same competition
	 * @return TeamData Winner team
	 */
	public function prev_match_winner()
	{
		if ($this->teams[1]->get_prev_match_goals() > $this->teams[0]->get_prev_match_goals()) {
			return $this->teams[1];
		}
		return $this->teams[0];
	}

	/**
	 * Get loser team
	 * @return TeamData Loser team
	 */
	public function loser()
	{
		if ($this->teams[0]->get_goals() < $this->teams[1]->get_goals()) {
			return $this->teams[0];
		}
		return $this->teams[1];
	}

	/**
	 * Get loser team of the previous match between the same teams for the same competition
	 * @return TeamData Loser team
	 */
	public function prev_match_loser()
	{
		if ($this->teams[0]->get_prev_match_goals() < $this->teams[1]->get_prev_match_goals()) {
			return $this->teams[0];
		}
		return $this->teams[1];
	}

	/**
	 * Get winner team of the penalties
	 * @return TeamData Winner team
	 */
	public function pen_winner()
	{
		return $this->away_pen_goals > $this->home_pen_goals ? $this->teams[1] : $this->teams[0];
	}

	/**
	 * Get loser team of the penalties
	 * @return TeamData Loser team
	 */
	public function pen_loser()
	{
		return $this->home_pen_goals < $this->away_pen_goals ? $this->teams[0] : $this->teams[1];
	}

	/**
	 * Get winner team of the knockouts
	 * @return TeamData Winner team
	 */
	public function knockouts_winner()
	{
		return $this->teams[0]->get_id() == $this->competition_elim_winner ? $this->teams[0] : $this->teams[1];
	}

	/**
	 * Get loser team of the knockouts
	 * @return TeamData Loser team
	 */
	public function knockouts_loser()
	{
		return $this->teams[0]->get_id() != $this->competition_elim_winner ? $this->teams[0] : $this->teams[1];
	}

	/**
	 * Get best classified team after the match
	 * @return TeamData Best classified team
	 */
	public function best_classified_team_after()
	{
		if ($this->teams[1]->get_pos_classification() < $this->teams[0]->get_pos_classification()) {
			return $this->teams[1];
		}
		return $this->teams[0];
	}

	/**
	 * Get worst classified team after the match
	 * @return TeamData Worst classified team
	 */
	public function worst_classified_team_after()
	{
		if ($this->teams[1]->get_pos_classification() >= $this->teams[0]->get_pos_classification()) {
			return $this->teams[1];
		}
		return $this->teams[0];
	}

	/**
	 * Whether an even is a goal event
	 * @param key $event_n Key of an event
	 * @return bool Whether an even is a goal event
	 */
	public function is_goal_event($event_n)
	{
		if ($event_n === null) {
			return false;
		}
		return ($this->events[$event_n] instanceof GoalEvent);
	}

	/**
	 * Whether the score difference improved for the team of the event after the event happened
	 * @param key $n Key of an event
	 * @return bool Whether score difference improved
	 */
	public function improved_upon_event($n)
	{
		$team = $this->events[$n]->get_team();

		if ($team === $this->home_team()) {
			$final_diff = $this->teams[0]->get_goals() - $this->teams[1]->get_goals();
		} elseif ($team === $this->away_team()) {
			$final_diff = $this->teams[1]->get_goals() - $this->teams[0]->get_goals();
		} else {
			return false;
		}

		$diff = $this->events[$n]->team_goals_diff();
		if ($diff !== 0) {
			$diff /= abs($diff);
		}
		if ($final_diff !== 0) {
			$final_diff /= abs($final_diff);
		}

		return $final_diff > $diff;
	}

	/*
	1 - yellow RedCardEvent
	2 - red card
	3 - goal
	4 - sub in
	5 - sub out
	16 - converted penalty
	17 - missed penalty
	23 - saved penalty
	24 - assist
	*/

	/**
	 * Build events list
	 * @param object $data Decoded json data of events
	 * @return Event[] List of events
	 */
	private function construct_events($data)
	{
		$result = array();

		$tmp_events = array(
			'3' => array(),
			'4' => array(),
			'5' => array(),
			'24' => array()
		);

		$tmp_penalties = array(
			'17' => array(),
			'23' => array()
		);

		// loop over all events in data
		foreach ($data as $event) {
			$event_minute = array(intval($event['minute']), intval($event['minute_extra']));
			$event_type = $event["type"];
			$event_player = $event["fk_player"];
			$event_coach = $event["fk_coach"];

			// define team for goal event (just in case it is an own goal)
			if ($event_type == '3' && $event["goal_og"] == '1') {
				$event_team = intval($event["fk_opponent"]);
			}
			else {
				$event_team = intval($event["fk_team"]);
			}

			if ($event_team === $this->teams[0]->get_id()) {
				$event_team = $this->teams[0];
			}
			elseif ($event_team === $this->teams[1]->get_id()) {
				$event_team = $this->teams[1];
			}
			else {
				throw new Exception("undefined team id: " . $event_team . " is not one of " . $this->teams[0]->get_id() . " or " . $this->teams[1]->get_id());
			}

			// if yellow card, add directly
			if ($event_type == '1') {
				if (array_key_exists($event_player, $this->players)) {
					$this->players[$event_player]->set_appears_in_events(true);
					array_push($result, new YellowCardEvent($event_minute, $event_team, $this->players[$event_player]));
				} elseif ($event_coach != '0') {
					$coach = $this->coaches[$event_coach];
					array_push($result, new YellowCardEvent($event_minute, $event_team, $coach));
				}
			}
			// if red card, add directly
			elseif ($event_type == '2') {
				if (array_key_exists($event_player, $this->players)) {
					$this->players[$event_player]->set_appears_in_events(true);
					array_push($result, new RedCardEvent($event_minute, $event_team, $this->players[$event_player]));
				} elseif ($event_coach != '0') {
					$coach = $this->coaches[$event_coach];
					array_push($result, new RedCardEvent($event_minute, $event_team, $coach));
				}
			}
			// otherwise, need to perform event merging (eg. goal and assist should be part of the same event)
			elseif (array_key_exists($event_type, $tmp_events) && array_key_exists($event_player, $this->players)) {
				$key = $event_minute[0] . '.' . $event_minute[1] . '_' . $event_team->get_id();

				// if no event of that type for team, create new entry
				if (!array_key_exists($key, $tmp_events[$event_type])) {
					$tmp_events[$event_type][$key] = array();
				}

				$addable = $this->players[$event_player];

				$this->players[$event_player]->set_appears_in_events(true);

				// if it is goal, add partial information
				if ($event_type == '3') {
					$body_part = MatchData::get_goal_body_part($event);
					$zone = MatchData::get_goal_zone($event);
					$play_type = MatchData::get_goal_type($event);
					$addable = new GoalEvent(
						$event_minute,
						$event_team,
						$this->players[$event_player],
						null,
						$body_part,
						$zone,
						$play_type,
						($event["goal_og"] == '1' ? true : false)
					);
				}

				array_push($tmp_events[$event_type][$key], $addable);
			} elseif (array_key_exists($event_type, $tmp_penalties)) {
				$key = $event_minute[0] . '.' . $event_minute[1];
				// if no event of that type for team, create new entry
				if (!array_key_exists($key, $tmp_penalties[$event_type])) {
					$tmp_penalties[$event_type][$key] = array();
				}

				$addable = $this->players[$event_player];
				$this->players[$event_player]->set_appears_in_events(true);

				// if it is missed penalty
				if ($event_type == '17') {
					$addable = new MissedPenaltyEvent(
						$event_minute,
						$event_team,
						$this->players[$event_player],
						null
					);
				}
				array_push($tmp_penalties[$event_type][$key], $addable);
			}
		}

		// add substitutions
		$sub_ins = array_intersect_key($tmp_events['4'], $tmp_events['5']);
		foreach ($sub_ins as $sub_key => $players_in) {
			$players_out = $tmp_events['5'][$sub_key];
			// same number of players in and out for a given minute and team
			assert(count($players_in) == count($players_out));
			// key comes in the 'minute.minuteextra_teamid' format
			$sub_key = explode('_', $sub_key);
			$minute = explode('.', $sub_key[0]);
			$team_id = intval($sub_key[1]);
			if ($team_id === $this->teams[0]->get_id()) {
				$team = &$this->teams[0];
			}
			else if ($team_id === $this->teams[1]->get_id()) {
				$team = &$this->teams[1];
			}
			else {
				throw new Exception("undefined team id");
			}

			array_push($result, new SubstitutionEvent($minute, $team, $players_in, $players_out));
		}

		// add goals
		foreach ($tmp_events['3'] as $goal_key => $goals) {
			// grab first goal from goals list (should not be more than one goal in the same minute)
			$goal_event = $tmp_events['3'][$goal_key][0];

			// check if there is an associated assist
			if (array_key_exists($goal_key, $tmp_events['24'])) {
				$goal_event->set_assist_player($tmp_events['24'][$goal_key][0]);
			}

			// append to events
			array_push($result, $goal_event);
		}

		// add missed penalty
		foreach ($tmp_penalties['17'] as $penalty_key => $penalties) {
			// grab first missed penalty from penalties missed list (should not be more than one penalty in the same minute)
			$penalty_event = $tmp_penalties['17'][$penalty_key][0];

			// check if there is an associated goalkeeper save
			if (array_key_exists($penalty_key, $tmp_penalties['23'])) {
				$penalty_event->set_goalkeeper($tmp_penalties['23'][$penalty_key][0]);
			}

			// append to events
			array_push($result, $penalty_event);
		}

		return $result;
	}

	/**
	 * Add values to events
	 */
	function add_events_counts()
	{
		$tmp_data = array();
		$goals_home = 0;
		$goals_away = 0;

		foreach ($this->events as $event) {
			if (!array_key_exists($event::NAME, $tmp_data)) {
				$tmp_data[$event::NAME] = array('global' => 0);
			}

			// update number of events of this type in match
			$tmp_data[$event::NAME]['global'] += 1;
			$event->update_field('global', $tmp_data[$event::NAME]['global']);

			// update specific field counts
			foreach ($event->get_updatable_fields() as $field_name => $field_val) {
				if (!array_key_exists($field_name, $tmp_data[$event::NAME])) {
					$tmp_data[$event::NAME][$field_name] = array();
				}

				if (!array_key_exists($field_val, $tmp_data[$event::NAME][$field_name])) {
					$tmp_data[$event::NAME][$field_name][$field_val] = 0;
				}

				$tmp_data[$event::NAME][$field_name][$field_val] += 1;
				$event->update_field($field_name, $tmp_data[$event::NAME][$field_name][$field_val]);
			}

			// update event result
			if ($event->get_team()->get_id() === $this->get_home_team_id()) {
				$event->set_goals_team($goals_home);
				$event->set_goals_other($goals_away);
			} elseif ($event->get_team()->get_id() === $this->get_away_team_id()) {
				$event->set_goals_team($goals_away);
				$event->set_goals_other($goals_home);
			}

			// inc result
			if ($event::NAME == GoalEvent::NAME) {
				$inc_goals_home = 0;
				$inc_goals_away = 0;
				if ($event->get_team()->get_id() === $this->get_home_team_id()) {
					$inc_goals_home = 1;
				} elseif ($event->get_team()->get_id() === $this->get_away_team_id()) {
					$inc_goals_away = 1;
				}
				$goals_home += $inc_goals_home;
				$goals_away += $inc_goals_away;
			}
		}
	}

	/**
	 * Add double yellow cards flags to events
	 */
	function add_double_yellow_cards()
	{
		$pending = array();

		foreach ($this->events as $event) {
			if ($event::NAME == YellowCardEvent::NAME && $event->get_player_n() == 2) {
				$pending[$event->get_player()->get_id()] = true;
			}
		}

		foreach ($this->events as $event) {
			if ($event::NAME == RedCardEvent::NAME && array_key_exists($event->get_player()->get_id(), $pending)) {
				$event->set_second_yellow(true);
			}
		}
	}

	/**
	 * Get body part to a given event
	 * @param Event $event Event
	 * @return GoalBodyPart|null Body part
	 */
	private static function get_goal_body_part($event)
	{
		$elem = intval($event["goal_body"]);
		if (1 < $elem && $elem < 5) {
			return $elem;
		}
		return null;
	}

	/**
	 * Get zone to a given event
	 * @param Event $event Event
	 * @return GoalZone|null Zone
	 */
	private static function get_goal_zone($event)
	{
		$elem = intval($event["goal_zone"]);
		if (1 < $elem && $elem < 4) {
			return $elem;
		}
		return null;
	}

	/**
	 * Get goal type of a given event
	 * @param Event $event Event
	 * @return GoalType|null Goal type
	 */
	private static function get_goal_type($event)
	{
		if ($event["goal_gp"] == '1') {
			return GoalType::PENALTY;
		}
		$elem = intval($event["goal_origin"]);
		if (1 < $elem && $elem < 9) {
			return $elem;
		}
		return null;
	}

	/**
	 * Calculate benched players list in the beginning of the match
	 */
	private function compute_starter_benched_players()
	{
		$this->starter_players = array();
		$this->benched_players = array();

		foreach ($this->relevant_players as $player) {
			$minute_in = $player->get_minute_in();
			if ($minute_in == 0) {
				array_push($this->starter_players, strval($player->get_name()));
			}
			else if ($minute_in == 999) {
				array_push($this->benched_players, strval($player->get_name()));
			}
		}
	}

	/**
	 * Calculate the list of players who scored goals in the match for each team
	 */
	private function compute_match_goals()
	{
		foreach ($this->home_players as $player) {

			if ($player->get_goals() == 1) {
				$tmp = strval($player->get_name());
				array_push($this->home_goals, $tmp);
			} else if ($player->get_goals() > 1) {
				$tmp = strval($player->get_name()) . " " . strval($player->get_goals()) . "x";
				array_push($this->home_goals, $tmp);
			}

			if ($player->get_own_goals() != 0) {
				$tmp = strval($player->get_name()) . " " . $this->own_goal_form;
				array_push($this->away_goals, $tmp);
			}
		}

		foreach ($this->away_players as $player) {

			if ($player->get_goals() == 1) {
				$tmp = strval($player->get_name());
				array_push($this->away_goals, $tmp);
			} else if ($player->get_goals() > 1) {
				$tmp = strval($player->get_name()) . " " . strval($player->get_goals()) . "x";
				array_push($this->away_goals, $tmp);
			}

			if ($player->get_own_goals() != 0) {
				$tmp = strval($player->get_name()) . " " . $this->own_goal_form;
				array_push($this->home_goals, $tmp);
			}
		}
	}

	/**
	 * Calculate whether there was a turn around in the score
	 * @return bool Whether there was a turn around in the score
	 */
	private function compute_turnaround()
	{
		if (count($this->events) == 0) {
			return false;
		}

		$last_item = $this->events[0];
		$tmp_home = array();
		foreach ($this->events as $event) {
			if ($event->get_event_name() == "goal" && $event->get_team()->get_name() == $this->teams[0]->get_name()) {
				array_push($tmp_home, $event);
			}
		}

		foreach ($tmp_home as $event) {
			if ($last_item->team_goals_diff() == -1 && $event->team_goals_diff() == 0) {
				return true;
			}
			$last_item = $event;
		}

		$last_item = $this->events[0];
		$tmp_away = array();
		foreach ($this->events as $event) {
			if ($event->get_event_name() == "goal" && $event->get_team()->get_name() == $this->teams[1]->get_name()) {
				array_push($tmp_away, $event);
			}
		}

		foreach ($tmp_away as $event) {
			if ($last_item->team_goals_diff() == -1 && $event->team_goals_diff() == 0) {
				return true;
			}
			$last_item = $event;
		}

		return false;
	}

	/**
	 * Set red cards flags for the list of events
	 */
	private function compute_red_cards()
	{
		foreach ($this->events as $event) {
			if ($event->get_event_name() == "red_card" && !$event->coach()) {
				$event->get_team()->set_final_players($event->get_team()->get_final_players() - 1);
				$event->get_team()->set_has_red_card(true);
			}
		}
	}

	/**
	 * Calculate the decisive impact of each player in events and get the maximum impact player key
	 * @return key|null Player key or null if no player had a sufficient decisive impact
	 */
	private function compute_players_decisive_impact()
	{
		$decisive_possibilities = array();
		foreach ($this->events as $idx => $event) {
			// if it is not own goal
			if ($this->is_goal_event($idx) && !$event->get_own_goal()) {
				$factor = 1.0;

				// increase weight factor if goal is an important one
				if (
					$event->get_match_n() == $this->goals()
					&& 0 <= $this->score_diff()
					&& $this->score_diff() <= 1
					&& $event->get_minute()[0] > 85
				) {
					$factor *= 1.5;
				}
				$scorer_id = $event->get_score_player()->get_id();
				$decisive_possibilities[$scorer_id] = $factor;
			}
		}

		if (!empty($decisive_possibilities)) {
			// update players decisive score
			foreach ($decisive_possibilities as $key => $value) {
				$this->players[$key]->set_decisive_impact($value);
			}

			// get max score and return it if is distinctive enough
			$max_key = array_keys($decisive_possibilities, max($decisive_possibilities))[0];
			if ($decisive_possibilities[$max_key] >= 1.5) {
				return $max_key;
			}
		}
		return null;
	}

	/**
	 * Calculate the positive impact of each player in events and get the maximum impact player key
	 * @return key|null Player key or null if no player had a sufficient positive impact
	 */
	private function compute_players_positive_impact()
	{
		$possibilities = array();

		foreach ($this->events as $idx => $event) {
			// if it is a not own goal
			if ($this->is_goal_event($idx) && !$event->get_own_goal()) {
				$factor = 1.0;

				// increase weight factor if goal is an important one
				if (
					$event->get_match_n() == $this->goals()
					&& 0 <= $this->score_diff()
					&& $this->score_diff() <= 1
					&& $event->get_minute()[0] > 85
				) {
					$factor *= 1.5;
				}

				// add goal to the equation
				$scorer_id = $event->get_score_player()->get_id();
				if (!array_key_exists($scorer_id, $possibilities)) {
					$possibilities[$scorer_id] = 0.0;
				}
				$possibilities[$scorer_id] += 5 * $factor;

				// add assist to the equation
				if ($event->get_assist_player() !== null) {
					$assister_id = $event->get_assist_player()->get_id();
					if (!array_key_exists($assister_id, $possibilities)) {
						$possibilities[$assister_id] = 0.0;
					}
					$possibilities[$assister_id] += 3;
				}
			}
		}

		if (!empty($possibilities)) {
			// update players score
			foreach ($possibilities as $key => $value) {
				$this->players[$key]->set_positive_impact($value);
			}

			// get max score and return it if distinctive enough
			$max_key = array_keys($possibilities, max($possibilities))[0];
			if ($possibilities[$max_key] >= 9) {
				return $max_key;
			}
		}
		return null;
	}

	/**
	 * Whether the loser of the match moves on to the next phase of knockout
	 * @return bool Whether the loser of the match moves on to the next phase of knockout
	 */
	public function loser_moves_on()
	{
		return $this->knockouts_loser()->get_id() == $this->winner()->get_id() && $this->score_diff() != 0;
	}

	/**
	 * Whether the winner of the match moves on to the next phase of knockout
	 * @return bool Whether the winner of the match moves on to the next phase of knockout
	 */
	public function winner_moves_on()
	{
		return $this->knockouts_winner()->get_id() == $this->winner()->get_id() && $this->score_diff() != 0;
	}

	/**
	 * Override
	 * @return string Match mention
	 */
	public function __toString()
	{
		return $this->teams[0]->get_name() . ' ' . $this->final_result . ' ' . $this->teams[1]->get_name();
	}

	/**
	 * Get full entity object.
     * @param EntitiesManagerFootball $manager  Manager that handles the entity
	 * @param key|null				  $event_n  Event key
	 * @return EntityData Full entity object
     */
	public function get_full_entity($entity, $event_n = null)
	{
		$pos = strpos($entity, ".");
		$lookup = substr($entity, 0, $pos);

		$entities_array = array(
			"competition" => $this->competition,
			"best_player" => $this->get_best_player(),
			"decisive_player" => $this->get_decisive_player(),
			"winner" => $this->winner(),
			"loser" => $this->loser(),
			"prev_match_winner" => $this->prev_match_winner(),
			"prev_match_loser" => $this->prev_match_loser(),
			"pen_winner" => $this->pen_winner(),
			"pen_loser" => $this->pen_loser(),
			"knockouts_winner" => $this->knockouts_winner(),
			"knockouts_loser" => $this->knockouts_loser(),
			"home_team" => $this->home_team(),
			"away_team" => $this->away_team(),
			"team_best_after" => $this->best_classified_team_after(),
			"team_worst_after" => $this->worst_classified_team_after(),
		);

		if ($event_n !== null) {
			switch ($lookup) {
				case "stat_team":
					return $this->stats[$event_n]->get_team();
				case "other_curiosity_team":
					$team = $this->curiosities[$event_n]->get_team();
					if ($this->teams[0] === $team) {
						return $this->teams[1];
					}
					return $this->teams[0];
				case "scorer":
					return $this->events[$event_n]->get_score_player();
				case "assist_maker":
					return $this->events[$event_n]->get_assist_player();
				case "team":
					return $this->events[$event_n]->get_team();
				case "player":
					return $this->events[$event_n]->get_player();
				case "goalkeeper":
					return $this->events[$event_n]->get_goalkeeper();
			}
		}

		return $entities_array[$lookup];
	}

	/**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
            "edition" => new EntityGetterFlat("get_edition"),
			"@edition_link" => new EntityGetterFlat("get_edition_link"),
			"match_id" => new EntityGetterFlat("get_match_id"),
			"weekday" => new EntityGetterManager("get_weekday", "get_date"),
			"fixture" => new EntityGetterFlat("get_fixture"),
			"stage" => new EntityGetterFlat("get_stage"),
			"league" => new EntityGetterFlat("get_league"),
			"competition" => new EntityGetterSub("get_competition", "CompetitionData"),
			"final_score" => new EntityGetterFlat("score_link_sorted"),
			"pen_score" => new EntityGetterFlat("pen_score_sorted"),
			"stadium" => new EntityGetterFlat("get_stadium"),
			"local" => new EntityGetterFlat("get_local"),
			"crowd" => new EntityGetterFlat("get_crowd"),
			"match_stat" => new EntityGetterSub("get_match_stat", "Stat"),
			"home_team_stat" => new EntityGetterSub("get_home_team_stat", "Stat"),
			"away_team_stat" => new EntityGetterSub("get_away_team_stat", "Stat"),
			"winner_stat" => new EntityGetterSub("get_winner_stat", "Stat"),
			"loser_stat" => new EntityGetterSub("get_loser_stat", "Stat"),
			"starter_players" => new EntityGetterFlat("get_starter_players"),
			"benched_players" => new EntityGetterFlat("get_benched_players"),
			"home_goals" => new EntityGetterFlat("get_home_goals_list"),
			"away_goals" => new EntityGetterFlat("get_away_goals_list"),
			"match_goals" => new EntityGetterFlat("get_match_goals"),
			"winner" => new EntityGetterSub("winner", "TeamData"),
			"loser" => new EntityGetterSub("loser", "TeamData"),
			"prev_match_winner" => new EntityGetterSub("prev_match_winner", "TeamData"),
			"prev_match_loser" => new EntityGetterSub("prev_match_loser", "TeamData"),
			"prev_match_score" => new EntityGetterFlat("prev_match_score"),
			"pen_winner" => new EntityGetterSub("pen_winner", "TeamData"),
			"pen_loser" => new EntityGetterSub("pen_loser", "TeamData"),
			"knockouts_winner" => new EntityGetterSub("knockouts_winner", "TeamData"),
			"knockouts_loser" => new EntityGetterSub("knockouts_loser", "TeamData"),
			"home_team" => new EntityGetterSub("home_team", "TeamData"),
			"away_team" => new EntityGetterSub("away_team", "TeamData"),
			"team_best_after" => new EntityGetterSub("best_classified_team_after", "TeamData"),
			"team_worst_after" => new EntityGetterSub("worst_classified_team_after", "TeamData"),
			"decisive_player" => new EntityGetterSub("get_decisive_player", "PlayerData"),
			"best_player" => new EntityGetterSub("get_best_player", "PlayerData"),
			"minute" => new EntityGetterFlat("get_event_minute", true),
			"scorer" => new EntityGetterSub("get_event_score_player", "PlayerData", true),
			"assist_maker" => new EntityGetterSub("get_event_assist_maker", "PlayerData", true),
			"stat_val" => new EntityGetterFlat("get_stat_val", true),
			"pre_curiosity_val" => new EntityGetterFlat("get_pre_curiosity_val", true),
			"post_curiosity_val" => new EntityGetterFlat("get_post_curiosity_val", true),
			"team" => new EntityGetterSub("get_event_team", "TeamData", true),
			"other_curiosity_team" => new EntityGetterSub("get_other_curiosity_team", "TeamData", true),
			"stat_team" => new EntityGetterSub("get_stat_team", "TeamData", true),
			"team_goals_diff" => new EntityGetterFlat("get_event_team_goals_diff", true),
			"player" => new EntityGetterSub("get_event_player", "PlayerData", true),
			"goalkeeper" => new EntityGetterSub("get_event_goalkeeper", "PlayerData", true)
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

	/**
	 * Get entity from main entity. Every entity should implement this function
     * @param EntitiesManagerFootball $manager   		Manager that handles the entity
     * @param string          		  $entity    		Entity or property name to be handled
	 * @param key|null				  $event_n   		Event key
	 * @param bool					  $report_mention	Whether to report
	 * @return string Content to be written for the entity
     */
	public function get_entity_from_main($manager, $entity, $event_n = null, $report_mention = false)
	{
		$used_step = PHP_INT_MAX;
		if ($report_mention && $event_n !== null) {
			$used_step = $event_n;
		}

		$event = null;
		if ($event_n !== null && $event_n < count($this->events)) {
			$event = $this->events[$event_n];
		}

		return parent::get_entity($manager, $entity, $used_step, $event_n, $event);
	}
}
