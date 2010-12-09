<?php defined('SYSPATH') or die('No direct script access.');
/**
 * An example database configuration reader.
 */
class Config_Database extends Kohana_Config_Database {

	protected $_cache_lifetime = NULL;

	protected $_database_instance = 'default';

	protected $_database_table = 'config';

	public function __construct(array $config = NULL)
	{
		// Set the cache lifetime
		if ($this->_cache_lifetime === NULL)
		{
			$this->_cache_lifetime = PHP_INT_MAX;
		}

		// Load the empty array
		parent::__construct($config);
	}

	/**
	 * Query the configuration table for all values for this group,
	 * and store the data in cache.
	 *
	 * @param   string  group name
	 * @param   array   configuration array
	 * @return  $this   clone of the current object
	 */
	public function load($group, array $config = NULL)
	{
		$cache_key = sha1("database_config_{$group}");
	
		if ($config === NULL AND $group !== 'database' AND $group !== 'cache' AND !$config = Cache::instance()->get($cache_key))
		{
			// Load all of the configuration values for this group
			$query = DB::select('config_key', 'config_value')
				->from($this->_database_table)
				->where('group_name', '=', $group)
				->execute($this->_database_instance);
					
			if (count($query) > 0)
			{
				$config = $query->as_array('config_key', 'config_value');
				
				// Save the configuration in cache
				Cache::instance()->set($cache_key, $config, $this->_cache_lifetime);
			}				
		} 
		
		return parent::load($group, $config);
	}

} // End Config_Database
