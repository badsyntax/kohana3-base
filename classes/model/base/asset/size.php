<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base asset size model
 */
class Model_Base_Asset_size extends Model_Base { 
	
	public $_table_name = 'assets_sizes';
		
	protected $_belongs_to = array(
		'asset' => array('model' => 'asset', 'foreign_key' => 'asset_id'),
	);	
}