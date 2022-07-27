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
		$expressions = array(
			"coach" => new TextStructure("%s's team", NameGender::NEUTRAL, NameNumber::SINGULAR),
			"city" => new TextStructure("%s's team", NameGender::NEUTRAL, NameNumber::SINGULAR),
			"country" => new TextStructure("%s's national team", NameGender::NEUTRAL, NameNumber::SINGULAR)
		);
		$variations = parent::construct_team_name_options("en", $team, static::$team_name_version, $expressions);
		return $this->sequential_name($team->get_id(), $variations[0], $variations[1]);
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
