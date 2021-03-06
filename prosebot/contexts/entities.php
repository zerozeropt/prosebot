<?php
require_once(__DIR__.'/../exceptions.php');

/**
 * Class for handling the different types of ways to get an entity
 * 
 * @author zerozero.pt
 */
abstract class EntityGetter
{
    /**
     * @param string  $getter_function Getter function name. If "", then there is no need for a getter function.
     */
    function __construct($getter_function = "")
    {
        $this->getter_function = $getter_function;
    }
}

/**
 * Class for handling the FLAT type.
 * FLAT for properties of the main entity.
 * 
 * @author zerozero.pt
 */
class EntityGetterFlat extends EntityGetter
{
    /**
     * @param string  $getter_function      Getter function name
     * @param bool    $has_only_index       Whether the getter function as an event key as argument, but not an event itself
     */
    function __construct($getter_function, $has_only_index = false)
    {
        parent::__construct($getter_function);
        $this->has_only_index = $has_only_index;
    }
}

/**
 * Class for handling the SUB type.
 * SUB for properties of sub entities.
 * 
 * @author zerozero.pt
 */
class EntityGetterSub extends EntityGetter
{
    /**
     * @param string  $getter_function  Getter function name
     * @param string  $classname        Sub entity class name
     * @param bool    $has_only_index   Whether the getter function as an event key as argument, but not an event itself
     */
    function __construct($getter_function, $classname, $has_only_index = false)
    {
        parent::__construct($getter_function);
        $this->classname = $classname;
        $this->has_only_index = $has_only_index;
    }
}

/**
 * Class for handling the MANAGER type.
 * MANAGER for properties with variation handled by entities manager.
 * 
 * @author zerozero.pt
 */
class EntityGetterManager extends EntityGetter
{
    /**
     * @param string   $manager_function    Entities manager function name
     * @param string   $arg_getter_function Getter function for manager arguments
     */
    function __construct($manager_function, $arg_getter_function = "")
    {
        parent::__construct();
        $this->manager_function = $manager_function;
        $this->arg_getter_function = $arg_getter_function;
    }
}

/**
 * Super class for entities data
 * On principle all entites should extend this class
 * 
 * @author zerozero.pt
 */
abstract class EntityData
{
    /**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
    /**
	 * Entity id
	 * @var string
	 */
    protected $id;
    /**
	 * Entity name
	 * @var TextStructure|string
	 */
    protected $name;
    /**
	 * Entity hyperlink, divided in beginning, middle and ending
	 * @var string[]
	 */
    protected $link;
    /**
	 * If entity has been mentioned
	 * @var bool
	 */
    protected $has_mention;

    /**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
    /**
	 * @param string        $id         Entity id
	 * @param TextStructure $name       Entity name
	 * @param string        $start_link Beginning of the entity's hyperlink
	 */
    function __construct($id, $name = "", $start_link = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->link = [];
        if ($start_link !== null) {
            array_push($this->link, $start_link . $this->id . ">");
            array_push($this->link, $this->name);
            array_push($this->link, "</a>");
        }
        $this->has_mention = false;
    }

    /**
     * Getters
     */
    public function get_id()
    {
        return $this->id;
    }

    public function get_name()
    {
        return $this->name;
    }

    public function get_link()
    {
        $hyperlink = "";
        foreach ($this->link as $part) {
            $hyperlink .= $part;
        }
        return $hyperlink;
    }

    public function has_link()
    {
        return $this->link !== [];
    }

    public function has_mention()
    {
        return $this->has_mention;
    }

    /**
     * Setters
     */
    protected function set_id($id)
    {
        $this->id = $id;
    }

    public function set_name($name)
    {
        $this->name = $name;
        $this->upadte_link_name();
    }

    protected function upadte_link_name()
    {
        if ($this->has_link()) {
            if ($this->name instanceof TextStructure) {
                $this->link[1] = $this->name->text;
            }
            else {
                $this->link[1] = $this->name;
            }
        }
    }

    protected function set_link($link_b)
    {
        $this->link = [];
        array_push($this->link, $link_b);
        if ($this->name instanceof TextStructure) {
            array_push($this->link, $this->name->text);
        }
        else {
            array_push($this->link, $this->name);
        }
        array_push($this->link, "</a>");
    }

    public function set_has_mention($bool)
    {
        $this->has_mention = $bool;
    }

    /**
	 * Override
	 * @return TextStructure|string Entity name
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
	 * Construct entities list being used by get_entity
     */
    abstract protected static function compute_entities();

    /**
     * Get entities list
     * @return array Entities list
     */
    abstract static function get_entities_list();

    /**
     * Get flat entity
     * @param string          $entity          Entity or property name to be handled
     * @param EntityGetter    $entity_getter   Entity getter object
     * @param string          $getter_function Getter function name
     * @param object   		  $event           Event
     * @param key|null		  $event_n         Event key
	 * @return string|object Content to be written for the entity or a sub-entity object
     */
    private function get_flat_entity($entity_getter, $getter_function, $event, $event_n) {
        if ($entity_getter->has_only_index) {
            return $this->$getter_function($event_n);
        }
        return $this->$getter_function($event, $event_n);
    }

    /**
	 * Get entity.
     * @param EntitiesManager $manager      Manager that handles the entity
     * @param string          $entity       Entity or property name to be handled
     * @param int             $used_step    Used step
     * @param key|null		  $event_n      Event key
     * @param object   		  $event        Event
	 * @return string Content to be written for the entity
     */
	public function get_entity($manager, $entity, $used_step = PHP_INT_MAX, $event_n = null, $event = null)
	{
        if ($entity === null) {
            return $this;
        }
            
		$pos = strpos($entity, ".");
		$lookup = $entity;
		$params = null;

		if ($pos !== false) {
			$lookup = substr($entity, 0, $pos);
			$params = substr($entity, $pos + 1);
		}

        $entities_list = static::get_entities_list();
        $entity_value = "";
        if (array_key_exists($lookup, $entities_list)) {
			$entity_getter = $entities_list[$lookup];
            $getter_function = $entity_getter->getter_function;
            
            switch (get_class($entity_getter)) {
                case EntityGetterFlat::class:
                {
                    $entity_value = $this->get_flat_entity($entity_getter, $getter_function, $event, $event_n);
                    break;
                }
                case EntityGetterSub::class:
                {
                    $prop = $this->get_flat_entity($entity_getter, $getter_function, $event, $event_n);
                    $entity_value = $prop === null ? null : $prop->get_entity($manager, $params, $used_step, $event_n, $event);
                    break;
                }
                case EntityGetterManager::class:
                {
                    $manager_function = $entity_getter->manager_function;
                    $arg_getter_function = $entity_getter->arg_getter_function;
                    if ($arg_getter_function === "") {
                        $entity_value = $manager->$manager_function($this);
                    }
                    else {
                        $entity_value = $manager->$manager_function($this->$arg_getter_function());
                    }
                    break;
                }
                default:
                    break;
            }
		}

        return $entity_value;
	}
}

/**
 * Super class for main entities data
 * On principle all main entites should extend this class
 * 
 * @author zerozero.pt
 */
abstract class MainEntityData extends EntityData
{
    /**
	 * List of events
	 * @var object[]
	 */
	protected $events = [];

    /**
	 * Get entity form main entity. Every main entity should implement this function
     * @param EntitiesManager  $manager   		Manager that handles the entity
     * @param string           $entity    		Entity or property name to be handled
	 * @param key|null		   $event_n   		Event key
	 * @param bool			   $report_mention	Whether to report
	 * @return string Content to be written for the entity
     */
	public function get_entity_from_main($manager, $entity, $event_n = null, $report_mention = false)
	{
		return parent::get_entity($manager, $entity);
	}
}