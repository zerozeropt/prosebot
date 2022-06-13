<?php

require_once(__DIR__.'/../fetcher/data_fetcher.php');

/**
 * Super class for an event
 *
 * @author zerozero.pt
 */
abstract class Event
{
	const NAME = "default";
	/**
	 * Minute of the event
	 * @var int
	 */
	protected $minute;
	/**
	 * Team of the event
	 * @var TeamData
	 */
	protected $team;
	/**
	 * Index of a goal of a team (>= 1)
	 * @var int
	 */
	protected $team_n;
	/**
	 * Index of a goal of a match (>= 1)
	 * @var int
	 */
	protected $match_n;
	/**
	 * Number of goals of a team
	 * @var int
	 */
	protected $goals_team;
	/**
	 * Number of goals of the other team
	 * @var int
	 */
	protected $goals_other;

	/**
	 * @param int 		$minute Minute of the event
	 * @param TeamData 	$team 	Team of the event
	 */
	function __construct($minute, $team)
	{
		$this->minute = $minute;
		$this->team = $team;
		$this->team_n = 0; // n-th event of type for team
		$this->match_n = 0; // n-th event of type in match
		$this->goals_team = 0; // goals for team at the time of event
		$this->goals_other = 0; // goals for other team at the time of event
	}

	/**
	 * Getters
	 */
	public function get_team()
	{
		return $this->team;
	}

	public function get_team_n()
	{
		return $this->team_n;
	}

	public function get_match_n()
	{
		return $this->match_n;
	}

	public function get_event_name()
	{
		return $this::NAME;
	}

	public function get_minute()
	{
		return $this->minute;
	}

	public function get_updatable_fields()
	{
		if ($this->team !== null)
			return array('team_id' => $this->team->get_id());
		return array();
	}

	/**
	 * Setters
	 */
	public function set_goals_team($goals)
	{
		$this->goals_team = $goals;
	}

	public function set_goals_other($goals)
	{
		$this->goals_other = $goals;
	}

	public function update_field($field_name, $val)
	{
		if ($field_name == 'team_id')
			$this->team_n = $val;
		elseif ($field_name == 'global')
			$this->match_n = $val;
	}

	/**
	 * Throw exception if method does not exist
	 */
	public function __call($name, $arguments)
	{
		if (!method_exists($this, $name))
			throw new ErrorException($name . ' does not exist!');
	}

	/**
	 * Calculate difference of goals in the score at the moment of the event
	 * @return int Goals difference
	 */
	public function team_goals_diff()
	{
		return $this->goals_team - $this->goals_other;
	}

	/**
	 * Calculate total number of goals
	 * @return int Total number of goals
	 */
	public function total_goals()
	{
		return $this->goals_team + $this->goals_other;
	}

	/**
	 * Override
	 * @return string Event mention
	 */
	public function __toString()
	{
		return static::NAME . "@" . $this->minute[0] . "." . $this->minute[1] . "_" . $this->team->get_id();
	}
}

/**
 * Enum of body parts used to score a goal
 *
 * @author zerozero.pt
 */
abstract class GoalBodyPart
{
	const RIGHTFOOT = 2;
	const LEFTFOOT = 3;
	const HEAD = 4;
}

/**
 * Enum of goal zones
 *
 * @author zerozero.pt
 */
abstract class GoalZone
{
	const INSIDEBOX = 2;
	const OUTSIDEBOX = 3;
}

/**
 * Enum of goal types
 *
 * @author zerozero.pt
 */
abstract class GoalType
{
	const DIRECTFREEKICK = 2;
	const INDIRECTFREEKICK = 3;
	const CORNERKICK = 4;
	const RUNNINGBALL = 5;
	const DIRECTCORNERKICK = 6;
	const THROWIN = 7;
	const PENALTYREBOUND = 8;
	const PENALTY = 9;
}

/**
 * Class for a goal event
 *
 * @author zerozero.pt
 */
class GoalEvent extends Event
{
	const NAME = "goal";
	/**
	 * Body part
	 * @var GoalBodyPart
	 */
	private $body_part;
	/**
	 * Zone
	 * @var GoalZone
	 */
	private $zone;
	/**
	 * Zone
	 * @var GoalType
	 */
	private $play_type;
	/**
	 * Whether it is an own goal
	 * @var bool
	 */
	private $own_goal;
	/**
	 * Player who scored
	 * @var PlayerData
	 */
	private $score_player;
	/**
	 * Player who assisted
	 * @var PlayerData
	 */
	private $assist_player;
	/**
	 * Scorer id
	 * @var string
	 */
	private $scorer_n;
	/**
	 * Assister id
	 * @var string
	 */
	private $assist_n;

	/**
	 * @param int 		 		$minute 		Minute of the event
	 * @param TeamData	 		$team   		Team
	 * @param PlayerData 		$score_player	Player who scored
	 * @param PlayerData|null 	$assist_player  Player who assits
	 * @param GoalBodyPart		$body_part      Body part
	 * @param GoalZone			$zone           Goal zone
	 * @param GoalType			$play_type      Goal type
	 * @param bool				$own_goal       Whether it is an own goal	
	 */
	function __construct(
		$minute,
		$team,
		$score_player,
		$assist_player = null,
		$body_part,
		$zone,
		$play_type,
		$own_goal
	) {
		parent::__construct($minute, $team);
		$this->body_part = $body_part;
		$this->zone = $zone;
		$this->play_type = $play_type;
		$this->own_goal = $own_goal;
		$this->score_player = $score_player;
		$this->assist_player = $assist_player;
		$this->scorer_n = 0;
		$this->assist_n = 0;
	}

	/**
	 * Getters
	 */
	public function get_score_player()
	{
		return $this->score_player;
	}

	public function get_assist_player()
	{
		return $this->assist_player;
	}

	public function get_updatable_fields()
	{
		$result = array();
		if ($this->score_player != null)
			$result['score_player_id'] = $this->score_player->get_id();

		if ($this->assist_player != null)
			$result['assist_player_id'] = $this->assist_player->get_id();

		return $result + parent::get_updatable_fields();
	}

	public function get_own_goal()
	{
		return $this->own_goal;
	}

	public function get_assist_n()
	{
		return $this->assist_n;
	}

	public function get_scorer_n()
	{
		return $this->scorer_n;
	}

	/**
	 * Setters
	 */
	public function set_assist_player($player)
	{
		$this->assist_player = $player;
	}

	public function update_field($field_name, $val)
	{
		if ($field_name == 'score_player_id')
			$this->scorer_n = $val;
		elseif ($field_name == 'assist_player_id')
			$this->assist_n = $val;
		else parent::update_field($field_name, $val);
	}

	/**
	 * Conditions
	 */
	public function left_foot_goal()
	{
		return $this->body_part === GoalBodyPart::LEFTFOOT;
	}

	public function right_foot_goal()
	{
		return $this->body_part === GoalBodyPart::RIGHTFOOT;
	}

	public function head_goal()
	{
		return $this->body_part === GoalBodyPart::HEAD;
	}

	public function inside_box_goal()
	{
		return $this->zone === GoalZone::INSIDEBOX;
	}

	public function outside_box_goal()
	{
		return $this->zone === GoalZone::OUTSIDEBOX;
	}

	public function direct_free_kick_goal()
	{
		return $this->play_type === GoalType::DIRECTFREEKICK;
	}

	public function indirect_free_kick_goal()
	{
		return $this->play_type === GoalType::INDIRECTFREEKICK;
	}

	public function corner_kick_goal()
	{
		return $this->play_type === GoalType::CORNERKICK;
	}

	public function running_ball_goal()
	{
		return $this->play_type === GoalType::RUNNINGBALL;
	}

	public function direct_corner_kick_goal()
	{
		return $this->play_type === GoalType::DIRECTCORNERKICK;
	}

	public function throw_in_goal()
	{
		return $this->play_type === GoalType::THROWIN;
	}

	public function penalty_rebound_goal()
	{
		return $this->play_type === GoalType::PENALTYREBOUND;
	}

	public function penalty_goal()
	{
		return $this->play_type === GoalType::PENALTY;
	}
}

/**
 * Class for a yellow card event
 *
 * @author zerozero.pt
 */
class YellowCardEvent extends Event
{
	const NAME = "yellow_card";
	/**
	 * Player
	 * @var PlayerData
	 */
	private $player;
	/**
	 * Player id
	 * @var string
	 */
	private $player_n;

	/**
	 * @param int 		 $minute Minute of the event
	 * @param TeamData	 $team   Team
	 * @param PlayerData $player Player
	 */
	public function __construct($minute, $team, $player)
	{
		parent::__construct($minute, $team);
		$this->player = $player;
		$this->player_n = 0;
	}

	/**
	 * Getters
	 */
	public function get_player()
	{
		return $this->player;
	}

	public function get_player_n()
	{
		return $this->player_n;
	}

	public function get_updatable_fields()
	{
		$result = array();
		if ($this->player !== null)
			$result['player_id'] = $this->player->get_id();

		return $result + parent::get_updatable_fields();
	}

	/**
	 * Setters
	 */
	public function update_field($field_name, $val)
	{
		if ($field_name == 'player_id')
			$this->player_n = $val;
		else parent::update_field($field_name, $val);
	}

	/**
	 * Whether the event happened to a coach
	 * @return bool Whether the event happened to a coach
	 */
	public function coach()
	{
		return strpos($this->player->get_link(), "coach") !== false;
	}
}

/**
 * Class for a red card event
 *
 * @author zerozero.pt
 */
class RedCardEvent extends Event
{
	const NAME = "red_card";
	/**
	 * Player
	 * @var PlayerData
	 */
	private $player;
	/**
	 * Whether it was originated from a second yellow card
	 * @var bool
	 */
	private $second_yellow;

	/**
	 * @param int 		 $minute Minute of the event
	 * @param TeamData	 $team   Team
	 * @param PlayerData $player Player
	 */
	public function __construct($minute, $team, $player)
	{
		parent::__construct($minute, $team);
		$this->player = $player;
		$this->second_yellow = false;
	}

	/**
	 * Getters
	 */
	public function get_player()
	{
		return $this->player;
	}

	public function get_second_yellow()
	{
		return $this->second_yellow;
	}

	/**
	 * Setters
	 */
	public function set_second_yellow($second_yellow)
	{
		$this->second_yellow = $second_yellow;
	}

	/**
	 * Whether the event happened to a coach
	 * @return bool Whether the event happened to a coach
	 */
	public function coach()
	{
		return strpos($this->player->get_link(), "coach") !== false;
	}
}

/**
 * Class for a missed penalty event
 *
 * @author zerozero.pt
 */
class MissedPenaltyEvent extends Event
{
	const NAME = "penalty_missed";
	/**
	 * Player
	 * @var PlayerData
	 */
	private $player;
	/**
	 * Goalkeeper
	 * @var PlayerData
	 */
	private $goalkeeper;

	/**
	 * @param int 		 $minute 	      Minute of the event
	 * @param TeamData	 $team   		  Team
	 * @param PlayerData $player 		  Player
	 * @param PlayerData|null $goalkeeper Goalkeeper who saved the penalty
	 */
	public function __construct($minute, $team, $player, $goalkeeper = null)
	{
		parent::__construct($minute, $team);
		$this->player = $player;
		$this->goalkeeper = $goalkeeper;
	}

	/**
	 * Getters
	 */
	public function get_player()
	{
		return $this->player;
	}

	public function get_goalkeeper()
	{
		return $this->goalkeeper;
	}

	/**
	 * Setters
	 */
	public function set_goalkeeper($goalkeeper)
	{
		$this->goalkeeper = $goalkeeper;
	}
}

/**
 * Class for a substitution event
 *
 * @author zerozero.pt
 */
class SubstitutionEvent extends Event
{
	const NAME = "substitution";
	/**
	 * List of players entering the match
	 * @var array
	 */
	private $players_in;
	/**
	 * List of players leaving the match
	 * @var array
	 */
	private $players_out;

	/**
	 * @param int 		 $minute 	      Minute of the event
	 * @param TeamData	 $team   		  Team
	 * @param array		 $players_in 	  List of players entering the match
	 * @param array		 $players_out     List of players leaving the match
	 */
	function __construct($minute, $team, $players_in, $players_out)
	{
		parent::__construct($minute, $team);
		$this->players_in = $players_in;
		$this->players_out = $players_out;
	}

	/**
	 * Getters
	 */
	public function get_player()
	{
		$res = null;

		foreach ($this->players_in as $p) {

			if ($p->is_relevant()) {
				$res = $p;
				break;
			}

			if ($res === null || $p->get_positive_impact() > $res->get_positive_impact())
				$res = $p;
		}

		foreach ($this->players_out as $p) {
			if ($p->is_relevant()) {
				$res = $p;
				break;
			}
		}
		
		return $res;
	}

	/**
	 * Whether the substitution creates a positive impact in the match
	 * @return bool Whether the substitution creates a positive impact in the match
	 */
	public function has_positive_impact()
	{
		foreach ($this->players_in as $p) {
			if ($p->get_positive_impact() > 3)
				return true;
		}
		return false;
	}

	/**
	 * Check if any player in the substitution event is relevant
	 * @return int Return 1 if a player entering is relevant, 2 if a player leaving is relevant, or 0 if no one is relevant
	 */
	public function is_relevant_player()
	{
		foreach ($this->players_in as $player) {
			if ($player->is_relevant())
				return 1;
		}

		foreach ($this->players_out as $player) {
			if ($player->is_relevant())
				return 2;
		}

		return 0;
	}

	/**
	 * Override
	 * @return string Event mention
	 */
	public function __toString()
	{
		$post = '';
		if (count($this->players_in) > 1)
			$post = 'x' . count($this->players_in);
		return parent::__toString() . $post;
	}
}

/**
 * Class for a half-time event
 *
 * @author zerozero.pt
 */
class HalfTimeEvent extends Event
{
	const NAME = "half_time";

	/**
	 * @param TeamData $team Team
	 */
	function __construct($team)
	{
		parent::__construct(array(45, 9999), $team);
	}
}

/**
 * Class for the event of the end of the match
 *
 * @author zerozero.pt
 */
class FinalTimeEvent extends Event
{
	const NAME = "final_time";

	/**
	 * @param TeamData $team Team
	 */
	function __construct($team)
	{
		parent::__construct(array(1000, 9999), $team);
	}
}
