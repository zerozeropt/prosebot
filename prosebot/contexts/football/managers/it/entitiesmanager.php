<?php

require_once(__DIR__.'/../entitiesmanagers.php');
require_once(__DIR__.'/../../../../grammars/it/grammar.php');

/**
 * Class for entities data management for Football context in Portuguese
 * 
 * @author zerozero.pt
 */
class EntitiesManagerFootballIT extends EntitiesManagerFootball
{
	/**
	 * Competition properties and methods
	 */
	public function get_competition_name($competition)
	{
		$name_array = $competition->get_name_array();
		$name = $competition->get_name();
		if (array_key_exists("it", $name_array)) {
			$name = $name_array["it"];
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
		if (array_key_exists("it", $name_array)) {
			$name = $name_array["it"];
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
						array_push($options, new TextStructure("<em>".$other_name->text."</em>", $other_name->gender, $other_name->number));
						array_push($term, "%s");
					}
					break;
				case "coach":
					$coach = $team->get_coach();
					if ($coach != null) {
						array_push($options, new TextStructure($team->get_coach(), NameGender::FEMALE, NameNumber::SINGULAR));
						array_push($term, "squadra di %s");
					}
					break;
				case "city_country":
					if ($team->get_can_use_city()) {
						$origin = $team->get_type() == 1 ? $team->get_name() : $team->get_city();
						if ($origin != null) {
							$team_term = "";
							if ($team->get_type() == 1) {
								$team_term = "nazionale";
								$team_term .= $team->get_name_gender() === NameGender::MALE ? " del %s" : " della %s";
							}
							else {
								$team_term = "squadra di %s";
							}
							array_push($term, $team_term);
							array_push($options, new TextStructure($origin, NameGender::FEMALE, NameNumber::SINGULAR));
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
		PlayerPosition::GOALKEEPER => 'portiere',
		PlayerPosition::DEFENDER => 'difensore',
		PlayerPosition::MIDFIELDER => 'centrocampista',
		PlayerPosition::FORWARD => 'attaccante',
		PlayerPosition::FUTSAL_GOALKEEPER => 'portiere',
		PlayerPosition::FUTSAL_DEFENDER => 'difensore',
		PlayerPosition::FUTSAL_DEFENDER_WINGER => 'esterno difensivo',
		PlayerPosition::FUTSAL_UNIVERSAL => 'universale',
		PlayerPosition::FUTSAL_PIVOT => 'pivot',
		PlayerPosition::FUTSAL_WINGER => 'esterno ofensivo',
		PlayerPosition::FUTSAL_PIVOT_WINGER => 'pivot ala',
		PlayerPosition::FUTSAL_DEFENDER_PIVOT => 'pivot fisso'
	);

	public static function get_player_positions()
	{
		return static::$player_positions;
	}

	/**
	 * Auxiliar entities properties and methods
	 */
	private static $own_goal = "aut.";

	public static function get_own_goal_form()
	{
		return static::$own_goal;
	}

	private static $day_name = array(
		WeekDay::SUNDAY => ["domenica", NameGender::FEMALE, NameNumber::SINGULAR],
		WeekDay::MONDAY => ["lùnedi", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::TUESDAY => ["martedì", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::WEDNESDAY => ["mercoledì", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::THURSDAY => ["giovedì", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::FRIDAY => ["venerdì", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::SATURDAY => ["sabato", NameGender::MALE, NameNumber::SINGULAR]
	);

	public static function get_week_days()
	{
		return static::$day_name;
	}
}
