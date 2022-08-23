<?php

require_once(__DIR__.'/../entitiesmanagers.php');

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
		parent::set_entity_lang_name($competition, "pt");
		return parent::get_competition_name($competition);
	}

	/**
	 * Team properties and methods
	 */
	public static $team_name_version = ["other_name", "coach", "city_country"];

	public function get_team_name($team)
	{
		$expressions = array(
			"coach" => new TextStructure("equipa de %s", NameGender::FEMALE, NameNumber::SINGULAR),
			"city" => new TextStructure("equipa de %s", NameGender::FEMALE, NameNumber::SINGULAR),
			"country" => new TextStructure("seleção de %s", NameGender::FEMALE, NameNumber::SINGULAR)
		);
		$variations = parent::construct_team_name_options("pt", $team, static::$team_name_version, $expressions);
		return $this->sequential_name($team->get_id(), $variations[0], $variations[1]);
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
		PlayerPosition::FUTSAL_DEFENDER_PIVOT => [NameGender::MALE => 'fixo pivot', NameGender::FEMALE => 'fixo pivot']
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

	public static function get_entity_lang_name($name, $name_array, $lang="pt")
	{
		return parent::get_entity_lang_name($name, $name_array, $lang);
	}
}
