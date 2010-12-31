<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base mimetype model
 */
class Model_Base_Mimetype extends Model_Base { 
	
	// Relationships
	protected $_has_many = array(
		'assets' => array('model' => 'asset'), 
	);	
	
	// Validation rules
	protected $_rules = array(
	);
	
	// Validation callbacks
	protected $_callbacks = array(
	);
	
}