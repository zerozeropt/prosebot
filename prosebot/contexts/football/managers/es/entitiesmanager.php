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
		$expressions = array(
			"coach" => new TextStructure("equipo de %s", NameGender::FEMALE, NameNumber::SINGULAR),
			"city" => new TextStructure("equipo de %s", NameGender::FEMALE, NameNumber::SINGULAR),
			"country" => new TextStructure("selección de %s", NameGender::FEMALE, NameNumber::SINGULAR)
		);
		$variations = parent::construct_team_name_options("es", $team, static::$team_name_version, $expressions);
		return $this->sequential_name($team->get_id(), $variations[0], $variations[1]);
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