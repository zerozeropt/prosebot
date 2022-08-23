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
		parent::set_entity_lang_name($competition, "it");
		return parent::get_competition_name($competition);
	}

	/**
	 * Team properties and methods
	 */
	public static $team_name_version = ["other_name", "coach", "city_country"];

	public function get_team_name($team)
	{
		parent::set_entity_lang_name($team, "it");
		$options = array(
			$team->get_name(),
		);
		$term = array("%s");
		
		$coach_expressions = array(
			"coach" => new TextStructure("squadra di %s", NameGender::FEMALE, NameNumber::SINGULAR),
		);

		foreach (static::$team_name_version as $strat) {
			switch ($strat) {
				case "other_name":
					parent::construct_team_other_name_option($team, $options, $term);
					break;
				case "coach":
					parent::construct_team_coach_option($team, $coach_expressions, $options, $term);
					break;
				case "city_country":
					if ($team->get_can_use_city()) {
						$origin = $team->get_type() == 1 ? $team->get_name() : $team->get_city();
						if ($origin != null) {
							$team_term = "";
							if ($team->get_type() == 1) {
								$team_term = "nazionale ";
								$connector = GrammarIT::di_il($origin, NameNumber::SINGULAR, $team->get_name_gender());
								$team_term .= $connector . " %s";
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

	public static function get_entity_lang_name($name, $name_array, $lang="it")
	{
		return parent::get_entity_lang_name($name, $name_array, $lang);
	}
}
