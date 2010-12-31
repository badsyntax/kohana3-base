<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base group model
 */
class Model_Base_Group extends Model_Base {
	
	// Relationships
	protected $_has_many = array(
		'users' => array('through' => 'groups_users'), 
		'children' => array('model' => 'group', 'foreign_key' => 'parent_id')
	);	
	protected $_belongs_to = array(
		'parent' => array('model' => 'group', 'foreign_key' => 'parent_id'),
	);	
	
	// Validation rules
	protected $_rules = array(
		'parent_id' => array(
			'not_empty' => NULL,
		),
		'name' => array(
			'trim' => NULL,
			'not_empty' => NULL,
			'max_length' => array('128'),
		)
	);
	
	// Validation callbacks
	protected $_callbacks = array(
		'name' => array('callback_name_available')
	);
	
	public function callback_name_available(Validate $array, $field)
	{
		if ($this->unique_key_exists($array[$field], 'name'))
		{
			$array->error($field, 'group_available', array($array[$field]));
		}		
	}
	
} // End Model_Base_Group