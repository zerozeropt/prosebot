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
	const FUTSAL_DEFENDER_PIVOT = 327;
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
	 * Construct team name variations
	 * @param string   			$lang 				Language initials
	 * @param TeamData			$team				Team
	 * @param string[] 			$team_name_versions	Variation types
	 * @param TextStructure[]	$expressions		Referring expressions for each variation type
	 * @return array Options and term
	 */
	protected function construct_team_name_options($lang, $team, $team_name_versions, $expressions) {
		$this->set_entity_lang_name($team, $lang);
		$options = array(
			$team->get_name(),
		);

		$term = array("%s");

		foreach ($team_name_versions as $strat) {
			switch ($strat) {
				case "other_name":
					$this->construct_team_other_name_option($team, $options, $term);
					break;
				case "coach":
					$this->construct_team_coach_option($team, $expressions, $options, $term);
					break;
				case "city_country":
					$this->construct_team_city_country_option($team, $expressions, $options, $term);
					break;
				default:
					break;
			}
		}

		return [$options, $term];
	}

	/**
	 * Construct team's other name option
	 * @param TeamData 			$team	 Team
	 * @param TextStructure[]	$options Options for team's name
	 * @param string[]			$term    Expressions to be completed with the correct option 
	 */
	protected function construct_team_other_name_option($team, &$options, &$term) {
		$other_name = $team->get_other_name();
		if ($other_name != null) {
			array_push($options, new TextStructure("<em>".$other_name->text."</em>", $other_name->gender, $other_name->number));
			array_push($term, "%s");
		}
	}

	/**
	 * Construct team's coach option
	 * @param TeamData 			$team	 	 Team
	 * @param TextStructure[]	$expressions Referring expressions for each variation type
	 * @param TextStructure[]	$options 	 Options for team's name
	 * @param string[]			$term    	 Expressions to be completed with the correct option 
	 */
	protected function construct_team_coach_option($team, $expressions, &$options, &$term) {
		$type = "coach";
		$coach = $team->get_coach();
		if ($coach != null) {
			array_push($options, new TextStructure($team->get_coach(), $expressions[$type]->get_gender(), $expressions[$type]->get_number()));
			array_push($term, $expressions[$type]->get_text());
		}
	}

	/**
	 * Construct team's city/country option
	 * @param TeamData 			$team	 	 Team
	 * @param TextStructure[]	$expressions Referring expressions for each variation type
	 * @param TextStructure[]	$options 	 Options for team's name
	 * @param string[]			$term    	 Expressions to be completed with the correct option 
	 */
	protected function construct_team_city_country_option($team, $expressions, &$options, &$term) {
		if (!$team->get_can_use_city()) {
			return;
		}
		$city_type = "city";
		$country_type = "country";
		$origin = $team->get_type() == 1 ? $team->get_name() : $team->get_city();
		if ($origin != null) {
			$team_term = $team->get_type() == 1 ? $expressions[$country_type]->get_text() : $expressions[$city_type]->get_text();
			array_push($term, $team_term);
			array_push($options, new TextStructure($origin, $expressions[$city_type]->get_gender(), $expressions[$city_type]->get_number()));
		}
	}

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
			if ($cached_val === null) {
				$cached_val = count($options) - 1;
			}

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

	/**
	 * Get element from array if exists
	 * @param array	 $array  Generic array of elements
	 * @param string $key    Key of the element
	 * @return TextStructure Element of the array or null if it does not exist
     */
	public static function get_elem($array, $key)
	{
		if (!array_key_exists($key, $array) || $array[$key] == "") {
			return null;
		}

		$name = $array[$key];

		$gender = NameGender::NEUTRAL;
		$new_key = $key . "_GENDER";
		if (array_key_exists($new_key, $array)) {
			$elem = $array[$new_key];
			if ($elem == '0') {
				$gender = NameGender::MALE;
			}
			elseif ($elem == '1') {
				$gender = NameGender::FEMALE;
			}
		}

		$number = NameNumber::SINGULAR;
		$new_key = $key . "_PLURAL";
		if (array_key_exists($new_key, $array)) {
			$elem = $array[$new_key];
			if ($elem == '0') {
				$number = NameNumber::SINGULAR;
			}
			elseif ($elem == '1') {
				$number = NameNumber::PLURAL;
			}
		}

		return new TextStructure($name, $gender, $number);
	}

	/**
	 * Set entity name for specific language if exists
	 * @param CompetitionData|TeamData $entity  Entity object
	 * @param string 	 			   $lang    Language
     */
	public static function set_entity_lang_name(&$entity, $lang)
	{
		$name_array = [];
		if (method_exists($entity, "get_name_array")) {
			$name_array = $entity->get_name_array();
		}
		$name = $entity->get_name();
		if (array_key_exists($lang, $name_array)) {
			$name = $name_array[$lang];
		}
		$entity->set_name($name);
	}

	/**
	 * Get entity name for specific language if exists
	 * @param TextStructure $name   	 Standard entity name
	 * @param string[]      $name_array  Array of entity's names for each language
	 * @param string		$lang		 Language initials
	 * @return TextStructure Entity's name
     */
	public static function get_entity_lang_name($name, $name_array, $lang)
	{
		if (array_key_exists($lang, $name_array)) {
			return new TextStructure($name_array[$lang], $name->get_gender(), $name->get_number());
		}
		return $name;
	}
}

?>