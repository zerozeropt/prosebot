<?php

require_once(__DIR__.'/../entitiesmanagers.php');
require_once(__DIR__.'/../../../../grammars/en/grammar.php');

/**
 * Class for entities data management for Football context in English
 * 
 * @author zerozero.pt
 */
class EntitiesManagerFootballEN extends EntitiesManagerFootball
{
	/**
	 * Competition properties and methods
	 */
	public function get_competition_name($competition)
	{
		$name_array = $competition->get_name_array();
		$name = $competition->get_name();
		if (array_key_exists("en", $name_array)) {
			$name = $name_array["en"];
		}
		$competition->set_name($competition->construct_competition_name($name, $competition->get_name_gender()));
		return parent::get_competition_name($competition);
	}

	/**
	 * Team properties and methods
	 */
	public static $team_name_version = ["other_name", "coach", "city_country"];

	public function get_team_name($team)
	{
		$name_array = $team->get_name_array();
		$name = $team->get_name();
		if (array_key_exists("en", $name_array)) {
			$name = $name_array["en"];
			$team->set_name($name);
		}
		$options = array(
			$name,
		);

		$term = array("%s");

		foreach (static::$team_name_version as $strat) {
			switch ($strat) {
				case "other_name":
					$other_name = $team->get_other_name();
					if ($other_name != null) {
						array_push($options, new TextStructure("<em>".$other_name."</em>", $other_name->gender, $other_name->number));
						array_push($term, "%s");
					}
					break;
				case "coach":
					$coach = $team->get_coach();
					if ($coach != null) {
						array_push($options, new TextStructure($team->get_coach(), NameGender::NEUTRAL, NameNumber::SINGULAR));
						array_push($term, "%s's team");
					}
					break;
				case "city_country":
					if ($team->get_can_use_city()) {
						$origin = $team->get_type() == 1 ? $team->get_name() : $team->get_city();

						if ($origin != null) {
							$team_term =  "%s's ".($team->get_type() == 1 ? "national team" : "team");
							array_push($term, $team_term);
							array_push($options, new TextStructure($origin, NameGender::NEUTRAL, NameNumber::SINGULAR));
						}
					}
					break;
				default:
					break;
			}
		}

		return $this->sequential_name($team->get_id(), $options, $term);
	}

	/**
	 * Player properties and methods
	 */
	private static $player_positions = array(
		PlayerPosition::GOALKEEPER => 'goalkeeper',
		PlayerPosition::DEFENDER => 'defender',
		PlayerPosition::MIDFIELDER => 'midfielder',
		PlayerPosition::FORWARD => 'forward'
	);

	public static function get_player_positions()
	{
		return static::$player_positions;
	}

	/**
	 * Auxiliar entities properties and methods
	 */
	private static $own_goal = "o.g.";

	public static function get_own_goal_form()
	{
		return static::$own_goal;
	}

	private static $day_name = array(
		WeekDay::SUNDAY => ["Sunday", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::MONDAY => ["Monday", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::TUESDAY => ["Tuesday", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::WEDNESDAY => ["Wednesday", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::THURSDAY => ["Thursday", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::FRIDAY => ["Friday", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::SATURDAY => ["Saturday", NameGender::MALE, NameNumber::SINGULAR]
	);

	public static function get_week_days()
	{
		return static::$day_name;
	}
}
