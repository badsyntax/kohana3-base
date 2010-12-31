<?php defined('SYSPATH') or die('No direct script access.');

class Model_Mimetype extends Model_Base_Mimetype { 
	
	protected $_belongs_to = array('asset' => array());
}