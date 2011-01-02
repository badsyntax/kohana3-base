<?php defined('SYSPATH') or die('No direct script access.');
/**
 * An example database configuration reader.
 */
class Config_Database extends Kohana_Config_Reader {

	protected $_cache_lifetime = NULL;
	
	protected $_database_instance = 'default';
	
	protected $_database_table = 'config';

	public static $_cache_key = 'database_config';

	public function __construct(array $config = NULL)
	{
		if ($this->_cache_lifetime === NULL)
		{
			$this->_cache_lifetime = PHP_INT_MAX;
		}
		
		if (isset($config['instance']))
		{
			$this->_database_instance = $config['instance'];
		}

		if (isset($config['table']))
		{
			$this->_database_table = $config['table'];
		}
		
		self::$_cache_key = sha1(self::$_cache_key);

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
		if ( $config !== NULL OR $group === 'database' OR $group === 'cache')
		{
			return parent::load($group, $config);
		}
		
		// Try get the config from cache
		$cache = Cache::instance()->get(self::$_cache_key);	
	
		if (!$cache)
		{
			// Load all of the configuration values
			$query = DB::select('config_key', 'config_value', 'group_name')
				->from($this->_database_table)
				->execute();
				
			if (count($query) > 0)
			{
				$cache = array();
					
				// Build the cache configuration array that contains ALL the config entries
				foreach($query as $entry)
				{
					if (!isset($cache[$entry['group_name']]))
					{
						$cache[$entry['group_name']] = array();
					}
						
					$cache[$entry['group_name']][$entry['config_key']] = unserialize($entry['config_value']);
				}
				
				// Save the configuration in cache
				Cache::instance()->set(self::$_cache_key, $cache, $this->_cache_lifetime);
			}
		}
		
		// Use the group config if it exists
		$config = @$cache[$group];
				
		return parent::load($group, $config);
	}

} // End Config_Database
