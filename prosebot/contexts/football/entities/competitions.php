<?php

require_once(__DIR__.'/../fetcher/data_fetcher.php');
require_once(__DIR__.'/../../entities.php');
require_once(__DIR__.'/../../../grammars/grammars.php');

/**
 * Class for competitions
 *
 * @author zerozero.pt
 */
class CompetitionData extends EntityData
{
    /**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
    /**
	 * Name of the competition for each language
	 * @var string[]
	 */
    private $name_array;
    /**
	 * Gender of the name of the competition
	 * @var string
	 */
    private $name_gender;
    /**
	 * Other name for the competition
	 * @var string
	 */
    private $other_name;
    /**
	 * List of entities
	 * @var EntityGetter[]
	 */
	protected static $entities = [];

    /**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
    /**
     * @param Grammar $grammar   Grammar
     * @param array   $json_data Decoded json data for a competition
     */
    function __construct($grammar, $json_data)
    {
        parent::__construct($json_data['edition_id'], $json_data['competition'], FootballFetcher::edition_link);
        $this->name_array = $json_data['competition_name'];
        $this->name_gender = $json_data['competition_gender_feminine'];
        $this->other_name = $this->construct_competition_name($json_data['competition_known_as'], $json_data['competition_gender_feminine_known_as'], $json_data['competition_number_known_as']);
    }

    /**
     * Construct the name of a competition with text, gender and number
     * @param string $name          Name
     * @param bool   $is_female     Whether is a female name
     * @param bool   $is_singular   Whether is a singular name
     * @return TextStructure Competition name
     */
    public function construct_competition_name($name, $is_female, $is_singular=true)
	{
        $gender = !$is_female ? NameGender::MALE : NameGender::FEMALE;
        $number = $is_singular ? NameNumber::SINGULAR : NameNumber::PLURAL;
		return new TextStructure($name, $gender, $number);
	}

    /**
     * Set competition name
     * @param string $name Name
     */
    public function set_name($name)
    {
        parent::set_name($name);
    }

    /**
     * Get the names of the competition for each language
     * @return string[] Names of the competition for each language
     */
    public function get_name_array()
    {
        return $this->name_array;
    }

    /**
     * Get the other name of the competition
     * @return string Competition other name
     */
    public function get_other_name()
    {
        return $this->other_name;
    }

    /**
     * Get the gender of the name of the competition
     * @return string Gender of the name of the competition
     */
    public function get_name_gender()
    {
        return $this->name_gender;
    }

    /**
	 * Construct entities list being used by get_entity
     */
	protected static function compute_entities()
	{
		static::$entities = [
            "name" => new EntityGetterManager("get_competition_name")
		];
	}

    /**
     * Get entities list
     */
    public static function get_entities_list()
    {
        if (empty(static::$entities))
            static::compute_entities();
        return static::$entities;
    }
}