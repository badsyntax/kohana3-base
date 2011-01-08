<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base role model
 */
class Model_Base_Role extends Model_Auth_Role {
       
       // Validation callbacks
       protected $_callbacks = array(
               'name' => array('callback_name_available')
       );
       
       /**
        * Tests if a unique key value exists in the database.
        *
        * @param   mixed    the value to test
        * @param   string   field name
        * @return  boolean
        */
       public function unique_key_exists($value, $field = NULL)
       {
               if ($field === NULL)
               {
                       // Automatically determine field by looking at the value
                       $field = $this->unique_key($value);
               }

               return (bool) DB::select(array('COUNT("*")', 'total_count'))
                       ->from($this->_table_name)
                       ->where($field, '=', $value)
                       ->execute($this->_db)
                       ->get('total_count');
       }       
       
       public function callback_name_available(Validate $array, $field)
       {
               if ($this->unique_key_exists($array[$field], 'name'))
               {
                       $array->error($field, 'role_unavailable', array($array[$field]));
               }               
       }
}
