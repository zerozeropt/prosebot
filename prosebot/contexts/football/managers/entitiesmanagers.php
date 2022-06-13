<?php

require_once(__DIR__.'/../../entitiesmanager.php');

/**
 * Enum of week days
 * 
 * @author zerozero.pt
 */
abstract class WeekDay
{
	const SUNDAY = 0;
	const MONDAY = 1;
	const TUESDAY = 2;
	const WEDNESDAY = 3;
	const THURSDAY = 4;
	const FRIDAY = 5;
	const SATURDAY = 6;
}

/**
 * Enum of player positions on the field
 * 
 * @author zerozero.pt
 */
abstract class PlayerPosition
{
	const GOALKEEPER = 1;
	const DEFENDER = 2;
	const MIDFIELDER = 3;
	const FORWARD = 4;
	const FUTSAL_GOALKEEPER = 52;
	const FUTSAL_DEFENDER = 53;
	const FUTSAL_DEFENDER_WINGER = 54;
	const FUTSAL_UNIVERSAL = 55;
	const FUTSAL_WINGER = 56;
	const FUTSAL_PIVOT_WINGER = 57;
	const FUTSAL_PIVOT = 58;
	const FUTSAL_DEVENDER_PIVOT = 327;
}

/**
 * Super class for entities data management for Football context
 * It handles different versions of entities' properties according to the language
 * 
 * @author zerozero.pt
 */
abstract class EntitiesManagerFootball extends EntitiesManager
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Array of ways to call a team
	 * @var array
	 */
	private static $team_name_version = [];

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Reset cache and shuffle array of ways to call a team
     */
	public function reset()
	{
		parent::reset();
		shuffle(static::$team_name_version);
	}

	/**
	 * Handle competition name variation (regular name or another one)
	 * @param CompetitionData $competition Competition entity
	 * @return TextStructure Mention of competition
	 */
	public function get_competition_name($competition)
	{
		$options = array(
			$competition->get_name(),
			$competition->get_other_name()
		);

		$term = array("%s", "%s");

		return $this->sequential_name($competition->get_id(), $options, $term);
	}

	/**
	 * Get competition hyperlink
	 * @param CompetitionData $competition Competition entity
	 * @return TextStructure Hyperlink of a competition
	 */
	protected function get_competition_link($competition)
	{
		$name = $competition->get_name();
		return new TextStructure(sprintf("%s", $competition->get_link()), $name->gender, $name->number);
	}

	/**
	 * Handle team name variation according to $team_name_version
	 * @param TeamData $team Team entity
	 * @return TextStructure Mention of team
	 */
	abstract function get_team_name($team);

	/**
	 * Get array of player positions in each language
	 * @return array Array of player positions
	 */
	abstract static function get_player_positions();

	/**
	 * Handle player name variation
	 * @param PlayerData $player Player entity
	 * @return TextStructure Mention of player
	 */
	public function get_player_name($player)
	{
		$result = "";

		$last_id = $this->read_cache("last_id");

		if ($last_id !== $player->get_id()) {
			$result = new TextStructure($player->get_name(), NameGender::NEUTRAL, NameNumber::SINGULAR);
			$this->write_cache(0, "last_method");
		} 
		else {
			$options = array($player->get_name());
			if(array_key_exists($player->get_position(), static::get_player_positions())) {
				$position_array = static::get_player_positions()[$player->get_position()];
				$position = is_array($position_array) ? $position_array[$player->get_gender()] : $position_array;
				$position_option = new TextStructure($position, $player->get_gender(), NameNumber::SINGULAR);
				array_push($options, $position_option);
			}

			$cached_val = $this->read_cache('last_method');
			if ($cached_val === null)
				$cached_val = count($options) - 1;

			for ($i = 0; $i < count($options); $i++) {
				$cached_val = ($cached_val + 1) % count($options);
				$result = $options[$cached_val];
				if ($result !== null) {
					$this->write_cache($cached_val, "last_method");
					break;
				}
			}
		}
		$this->write_cache($player->get_id(), "last_id");
		return $result;
	}

	/**
	 * Get player name with respective gender attached
	 * @param PlayerData $player Player entity
	 * @return TextStructure Player name with gender attached
	 */
	function get_player_name_gender($player)
	{
		return new TextStructure($player->get_name(), $player->get_gender(), NameNumber::SINGULAR);
	}

	/**
	 * Get player's position if it exists, else get player's name if
	 * @param PlayerData $player Player entity
	 * @return TextStructure Player position
	 */
	public function get_player_position($player)
	{
		$result = "";
		if(array_key_exists($player->get_position(), static::get_player_positions())){
			$result = new TextStructure(static::get_player_positions()[$player->get_position()][$player->get_gender()], $player->get_gender(), NameNumber::SINGULAR);
		}
		else {
			$result = new TextStructure($player->get_name(), NameGender::NEUTRAL, NameNumber::SINGULAR);
			$this->write_cache(0, "last_method");
		}

		return $result;
	}

	/**
	 * Get own goal initials for each language
	 * @return string Own goal initials
	 */
	abstract static function get_own_goal_form();

	/**
	 * Get array of week days for each language
	 * @return array Week days
	 */
	abstract static function get_week_days();
	
	/**
	 * Get week day for a specific date
	 * @param DateTime $date Date
	 * @return TextStructure Week day
	 */
	public function get_weekday($date)
	{
		$d = static::get_week_days()[$date->format('w')];
		return new TextStructure($d[0], $d[1], $d[2]);
	}
}

?>