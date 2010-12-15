<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base page model
 */
class Model_Base_Page extends ORM {
	
	protected $_belongs_to = array(
		'parent' => array('model' => 'page', 'foreign_key' => 'parent_id'),
	);	
	
	protected $_has_many = array(
		'children' => array('model' => 'page', 'foreign_key' => 'parent_id'),
	);
	
	// Validation rules
	protected $_rules = array(
		'parent_id' => array(
			'not_empty' => NULL,
		),
		'description' => array(
			'trim' => NULL,
			'max_length' => array('128'),
		),
		'title' => array(
			'not_empty'  => NULL,
			'min_length' => array(4),
			'max_length' => array(32),
		),
		'uri' => array(
			'not_empty'  => NULL,
			'min_length' => array(3),
			'max_length' => array(128),
			'regex'      => array('/^[-\pL\pN_.]++$/uD'),
		),
		'body' => array(
			'not_empty'  => NULL,
		),
	);
	
	// Validaiton callbacks
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
	
	public function tree_select($indent = 4, $start_id = 0, $pages = array())
	{
		$start = $this->where('parent_id', '=', $start_id);
		
		$this->recurse_tree_select($start->find_all(), $pages, $indent);
		
		return $pages;
	}
	
	public function tree_list_html($view_path = NULL, $start_id = 0, $list_html = '')
	{
		$start = $this->where('parent_id', '=', $start_id);

		$this->recurse_tree_list_html($start->find_all(), $list_html, $view_path);
		
		return $list_html;
	}
	
	private function recurse_tree_select($pages, & $array = array(), $indent = 4, & $depth = 0)
	{
		foreach($pages as $page)
		{
			$array[$page->id] = str_repeat('&nbsp;', ($depth * $indent)).$page->title;

			$children = $page->children->find_all();
			
			if (count($children))
			{
				$child_depth = $depth + 1;
				
				$this->recurse_tree_select($children, $array, $indent, $child_depth);
			}
		}		
	}
	
	private function recurse_tree_list_html($pages, & $html = '', $view_path = 'tree', & $depth = -1)
	{
		$depth++;
		
		$has_pages = (count($pages) > 0);
		
		$has_pages AND $html .= View::factory($view_path.'/list_open');

		foreach($pages as $page)
		{
			$html .= View::factory($view_path.'/item_open')->set('page', $page);

			$children = $page->children->find_all();
			
			$this->recurse_tree_list_html($children, $html, $view_path, $depth);
			
			$html .= View::factory($view_path.'/item_close');
		}
		
		$has_pages AND $html .= View::factory($view_path.'/list_close');
	}	
	
} // End Model_Base_Page