<?php defined('SYSPATH') or die('No direct script access.');

class Model_Activity extends Model_Base_Activity { 
	
	protected $_belongs_to = array('user' => array());
}