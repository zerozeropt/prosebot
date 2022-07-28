<?php

require_once(__DIR__.'/../entitiesmanagers.php');
require_once(__DIR__.'/../../../../grammars/br/grammar.php');

/**
 * Class for entities data management for Football context in Brazilian Portuguese
 * 
 * @author zerozero.pt
 */
class EntitiesManagerFootballBR extends EntitiesManagerFootball
{
	/**
	 * Competition properties and methods
	 */
	public function get_competition_name($competition)
	{
		parent::set_entity_lang_name($competition, "br");
		return parent::get_competition_name($competition);
	}

	/**
	 * Team properties and methods
	 */
	public static $team_name_version = ["other_name", "coach_equipe", "coach_time", "city_country"];

	public function get_team_name($team)
	{
		parent::set_entity_lang_name($team, "br");
		$options = array(
			$team->get_name(),
		);
		$term = array("%s");

		$city_country_expressions = array(
			"city" => new TextStructure("equipe de %s", NameGender::FEMALE, NameNumber::SINGULAR),
			"country" => new TextStructure("seleção de %s", NameGender::FEMALE, NameNumber::SINGULAR)
		);

		foreach (static::$team_name_version as $strat) {
			switch ($strat) {
				case "other_name":
					parent::construct_team_other_name_option($team, $options, $term);
					break;
				case "coach_equipe":
					$coach = $team->get_coach();
					if ($coach != null) {
						array_push($options, new TextStructure($team->get_coach(), NameGender::FEMALE, NameNumber::SINGULAR));
						if (random_int(0, 1)) {
							array_push($term, "equipe do técnico %s");
						}
						else {
							array_push($term, "equipe comandada por %s");
						}
					}
					break;
				case "coach_time":
					$coach = $team->get_coach();
					if ($coach != null) {
						array_push($options, new TextStructure($team->get_coach(), NameGender::MALE, NameNumber::SINGULAR));
						if (random_int(0, 1)) {
							array_push($term, "time do técnico %s");
						}
						else {
							array_push($term, "time treinado por %s");
						}
					}
					break;
				case "city_country":
					parent::construct_team_city_country_option($team, $city_country_expressions, $options, $term);
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
		PlayerPosition::GOALKEEPER => [NameGender::MALE => 'goleiro', NameGender::FEMALE => 'goleira'],
		PlayerPosition::DEFENDER => [NameGender::MALE => 'defensor', NameGender::FEMALE => 'defensora'],
		PlayerPosition::MIDFIELDER => [NameGender::MALE => 'meia', NameGender::FEMALE => 'meia'],
		PlayerPosition::FORWARD => [NameGender::MALE => 'atacante', NameGender::FEMALE => 'atacante']
	);

	public static function get_player_positions()
	{
		return static::$player_positions;
	}

	/**
	 * Auxiliar entities properties and methods
	 */
	private static $own_goal = "g.c.";

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

	public static function get_entity_lang_name($name, $name_array, $lang="br")
	{
		return parent::get_entity_lang_name($name, $name_array, $lang);
	}
}
