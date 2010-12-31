<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base page model
 */
class Model_Base_Page extends Model_Base {
	
	protected $_belongs_to = array(
		'parent' => array('model' => 'page', 'foreign_key' => 'parent_id'),
	);	
	
	protected $_has_many = array(
		'children' => array('model' => 'page', 'foreign_key' => 'parent_id'),
	);
	
	// Validation rules
	protected $_rules = array(
		'parent_id' => array(
			'trim' => NULL,
			'not_empty' => NULL,
		),
		'description' => array(
			'trim' => NULL,
			'max_length' => array('128'),
		),
		'title' => array(
			'trim' => NULL,
			'not_empty'  => NULL,
			'min_length' => array(4),
			'max_length' => array(32),
		),
		'uri' => array(
			'trim' => NULL,
			'not_empty'  => NULL,
			'min_length' => array(3),
			'max_length' => array(128),
			'regex'      => array('/^[-\pL\pN_.]++$/uD'),
		),
		'body' => array(
			'trim' => NULL,
			'not_empty'  => NULL,
		),
		'visible_from' => array(
			'trim' => NULL,
			'not_empty'	=> NULL
		),
		'visible_to' => array(
			'trim' => NULL
		)
	);
	
	// Validation callbacks
	protected $_callbacks = array(
		
	);
	
	public function delete($id = NULL)
	{
		if ($id === NULL)
		{
			// Use the the primary key value
			$id = $this->pk();
		}
		
		if ( ! empty($id) OR $id === '0')
		{
			foreach ($this->children->find_all() as $child)
			{
				$child->delete();
			}

			parent::delete($id);
		}

		return $this;
	}
	
	
} // End Model_Base_Page