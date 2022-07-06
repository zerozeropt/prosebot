<?php

require_once(__DIR__.'/../entitiesmanagers.php');
require_once(__DIR__.'/../../../../grammars/es/grammar.php');

/**
 * Class for entities data management for Football context in Spanish
 * 
 * @author zerozero.pt
 */
class EntitiesManagerFootballES extends EntitiesManagerFootball
{
	/**
	 * Competition properties and methods
	 */
	public function get_competition_name($competition)
	{
		$name_array = $competition->get_name_array();
		$name = $competition->get_name();
		if (array_key_exists("es", $name_array)) {
			$name = $name_array["es"];
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
		if (array_key_exists("es", $name_array)) {
			$name = $name_array["es"];
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
						array_push($options, new TextStructure("<i>".$other_name->text."</i>", $other_name->gender, $other_name->number));
						array_push($term, "%s");
					}
					break;
				case "coach":
					$coach = $team->get_coach();
					if ($coach != null) {
						array_push($options, new TextStructure($team->get_coach(), NameGender::FEMALE, NameNumber::SINGULAR));
						array_push($term, "equipo de %s");
					}
					break;
				case "city_country":
					if ($team->get_can_use_city()) {
						$origin = $team->get_type() == 1 ? $team->get_country() : $team->get_city();

						if ($origin != null) {
							$team_term = ($team->get_type() == 1 ? "selección" : "equipo") . " de %s";
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
		PlayerPosition::GOALKEEPER => [NameGender::MALE => 'portero', NameGender::FEMALE => 'portera'],
		PlayerPosition::DEFENDER => [NameGender::MALE => 'defensa', NameGender::FEMALE => 'defensa'],
		PlayerPosition::MIDFIELDER => [NameGender::MALE => 'centrocampista', NameGender::FEMALE => 'centrocampista'],
		PlayerPosition::FORWARD => [NameGender::MALE => 'delantero', NameGender::FEMALE => 'delantera']
	);

	public static function get_player_positions()
	{
		return static::$player_positions;
	}

	/**
	 * Auxiliar entities properties and methods
	 */
	private static $own_goal = "p.p.";

	public static function get_own_goal_form()
	{
		return static::$own_goal;
	}

	private static $day_name = array(
		WeekDay::SUNDAY => ["domingo", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::MONDAY => ["lunes", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::TUESDAY => ["martes", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::WEDNESDAY => ["miércoles", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::THURSDAY => ["jueves", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::FRIDAY => ["viernes", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::SATURDAY => ["sábado", NameGender::MALE, NameNumber::SINGULAR]
	);

	public static function get_week_days()
	{
		return static::$day_name;
	}
}

?> 