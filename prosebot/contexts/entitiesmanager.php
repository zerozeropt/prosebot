<?php

/**
 * Super class for entities data management
 * It handles different versions of entities' properties according to the language
 * 
 * @author zerozero.pt
 */
abstract class EntitiesManager
{
	/**
     *-----------------------------------------------------
	 * Properties
	 *-----------------------------------------------------
    */
	/**
	 * Cache for previous mentions to entities
	 * @var array
	 */
	protected $cache;

	/**
     *-----------------------------------------------------
	 * Methods
	 *-----------------------------------------------------
    */
	function __construct()
	{
		$this->cache = array();
	}

	/**
	 * Reset cache
     */
	public function reset()
	{
		$this->cache = array();
	}
	
	/**
	 * Read last used value, if any
	 * @param string $suffix Suffix to add to key word
	 * @return int|null Cached value
     */
	protected function read_cache($suffix="")
	{
		$key = debug_backtrace()[1]['function'] . $suffix;
		if (array_key_exists($key, $this->cache)) {
			return $this->cache[$key];
		}
		return null;
	}

	/**
	 * Write last used value
	 * @param string $value  Last value used
	 * @param string $suffix Suffix to add to key word
     */
	protected function write_cache($value, $suffix="")
	{
		$key = debug_backtrace()[1]['function'] . $suffix;
		$this->cache[$key] = $value;
	}

	/**
	 * Use cached values to know which text content version should be written next, sequentially
	 * @param string $key     Entity id
	 * @param array  $options Various versions of writing the entity
	 * @param string $term	  Text content with %s to be replaced by one of the options
	 * @return TextStructure Result to be written composed by content, gender and number
	 */
	protected function sequential_name($key, $options, $term)
	{
		$result = "";
		// read last used value, if any
		$cached_val = $this->read_cache($key);

		if ($cached_val === null) {
			$cached_val = count($options) - 1;
		}

		// iterate through values to pick one
		for ($i = 0; $i < count($options); $i++) {
			$cached_val = ($cached_val + 1) % count($options);
			$result = $options[$cached_val];
			if ($result !== null) {
				$result = new TextStructure(sprintf($term[$cached_val], $result->text), $result->gender, $result->number);
				break;
			}
		}

		// write value to cache
		$this->write_cache($cached_val, $key);

		return $result;
	}
}

?>