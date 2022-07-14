<?php

require_once(__DIR__.'/../entitiesmanagers.php');
require_once(__DIR__.'/../../../../grammars/pt/grammar.php');

/**
 * Class for entities data management for Football context in Portuguese
 * 
 * @author zerozero.pt
 */
class EntitiesManagerFootballPT extends EntitiesManagerFootball
{
	/**
	 * Competition properties and methods
	 */
	public function get_competition_name($competition)
	{
		$name_array = $competition->get_name_array();
		$name = $competition->get_name();
		if (array_key_exists("pt", $name_array)) {
			$name = $name_array["pt"];
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
		if (array_key_exists("pt", $name_array)) {
			$name = $name_array["pt"];
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
						array_push($term, "equipa de %s");
					}
					break;
				case "city_country":
					if ($team->get_can_use_city()) {
						$origin = $team->get_type() == 1 ? $team->get_country() : $team->get_city();

						if ($origin != null) {
							$team_term = ($team->get_type() == 1 ? "seleção" : "equipa") . " de %s";
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
		PlayerPosition::GOALKEEPER => [NameGender::MALE => 'guarda-redes', NameGender::FEMALE => 'guarda-redes'],
		PlayerPosition::DEFENDER => [NameGender::MALE => 'defesa', NameGender::FEMALE => 'defesa'],
		PlayerPosition::MIDFIELDER => [NameGender::MALE => 'médio', NameGender::FEMALE => 'médio'],
		PlayerPosition::FORWARD => [NameGender::MALE => 'avançado', NameGender::FEMALE => 'avançada'],
		PlayerPosition::FUTSAL_GOALKEEPER => [NameGender::MALE => 'guarda-redes', NameGender::FEMALE => 'guarda-redes'],
		PlayerPosition::FUTSAL_DEFENDER => [NameGender::MALE => 'fixo', NameGender::FEMALE => 'fixo'],
		PlayerPosition::FUTSAL_DEFENDER_WINGER => [NameGender::MALE => 'fixo ala', NameGender::FEMALE => 'fixo ala'],
		PlayerPosition::FUTSAL_UNIVERSAL => [NameGender::MALE => 'universal', NameGender::FEMALE => 'universal'],
		PlayerPosition::FUTSAL_PIVOT => [NameGender::MALE => 'pivot', NameGender::FEMALE => 'pivot'],
		PlayerPosition::FUTSAL_WINGER => [NameGender::MALE => 'ala', NameGender::FEMALE => 'ala'],
		PlayerPosition::FUTSAL_PIVOT_WINGER => [NameGender::MALE => 'pivot ala', NameGender::FEMALE => 'pivot ala'],
		PlayerPosition::FUTSAL_DEVENDER_PIVOT => [NameGender::MALE => 'fixo pivot', NameGender::FEMALE => 'fixo pivot']
	);

	public static function get_player_positions()
	{
		return static::$player_positions;
	}

	/**
	 * Auxiliar entities properties and methods
	 */
	private static $own_goal = "p.b.";

	public static function get_own_goal_form()
	{
		return static::$own_goal;
	}

	private static $day_name = array(
		WeekDay::SUNDAY => ["domingo", NameGender::MALE, NameNumber::SINGULAR],
		WeekDay::MONDAY => ["segunda-feira", NameGender::FEMALE, NameNumber::SINGULAR],
		WeekDay::TUESDAY => ["terça-feira", NameGender::FEMALE, NameNumber::SINGULAR],
		WeekDay::WEDNESDAY => ["quarta-feira", NameGender::FEMALE, NameNumber::SINGULAR],
		WeekDay::THURSDAY => ["quinta-feira", NameGender::FEMALE, NameNumber::SINGULAR],
		WeekDay::FRIDAY => ["sexta-feira", NameGender::FEMALE, NameNumber::SINGULAR],
		WeekDay::SATURDAY => ["sábado", NameGender::MALE, NameNumber::SINGULAR]
	);

	public static function get_week_days()
	{
		return static::$day_name;
	}
}
