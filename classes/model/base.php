<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base model
 */
class Model_Base extends ORM {
	
	public $_table_columns = NULL;
	
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
			->where($this->_primary_key, '!=', $this->pk())
			->execute($this->_db)
			->get('total_count');
	}
	
	/**
	 * Returns a single dimension array with indented values representing the items tree
	 *
	 * @param   integer		the number of space chars used for indenting
	 * @param   integer		parent id of items to start from 
	 * @param	array 		items array
	 * @param	string		key name
	 * @return  array
	 */
	public function tree_select($indent = 4, $start_id = 0, $items = array(), $depth = 0, $key = 'name')
	{
		$start = $this
			->where('parent_id', '=', $start_id)
			->find_all();

		$this->recurse_tree_select($start, $items, $indent, $depth, $key);

		return $items;
	}
		
	/**
	 * Returns a HTML tree
	 *
	 * @param   string		path to tree views directory
	 * @param   integer		parent id of items to start from 
	 * @param	string 		items HTML string
	 * @return  string
	 */
	public function tree_list_html($view_path = NULL, $start_id = 0, $open_items = array(), $list_html = '')
	{
		$start = $this
			->where('parent_id', '=', $start_id)
			->find_all();

		$this->recurse_tree_list_html($start, $list_html, $view_path, $open_items);
		
		return $list_html;
	}
	
	/**
	 * Recursive function to append tree level items 
	 *
	 * @param	DB result 	items db result
	 * @param	array 		items array
	 * @param   integer		the number of space chars used for indenting
	 * @param   integer		the recursion depth
	 * @param	string		key name
	 */
	private function recurse_tree_select($items, & $array = array(), $indent = 4, & $depth = 0, $key = 'name')
	{
		foreach($items as $item)
		{
			$array[$item->id] = str_repeat('&nbsp;', ($depth * $indent)).$item->$key;

			$children = $item->children->find_all();
			
			if (count($children))
			{
				$child_depth = $depth + 1;
				
				$this->recurse_tree_select($children, $array, $indent, $child_depth, $key);
			}
		}		
	}
	
	/**
	 * Recursive function to concat tree level items HTML
	 *
	 * @param	DB result 	items db result
	 * @param	string 		items HTML string
	 * @param	string 		path to tree views directory
	 * @param   integer		the recursion depth
	 */
	private function recurse_tree_list_html($items, & $html = '', $view_path = 'tree', $open_items = array(), & $depth = -1)
	{		
		$depth++;
		
		$has_items = (count($items) > 0);
		
		if ($has_items)
		{
			$html .= View::factory($view_path.'/list_open')
				->set('open_items', $open_items);
		}
		foreach($items as $item)
		{
			$html .= View::factory($view_path.'/item_open')
				->set(Inflector::singular($this->_table_name), $item)
				->set('open_items', $open_items);

			$this->recurse_tree_list_html($item->children->find_all(), $html, $view_path, $open_items, $depth);
			
			$html .= View::factory($view_path.'/item_close');
		}		
		if ($has_items)
		{
			$html .= View::factory($view_path.'/list_close');
		}
	}
}