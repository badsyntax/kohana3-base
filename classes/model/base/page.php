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
			'min_length' => array(5),
			'max_length' => array(128),
			'regex'      => array('/^[-\pL\pN_.]++$/uD'),
		),
		'body' => array(
			'not_empty'  => NULL,
		),
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
	
	public function tree_select($indent = 4)
	{
		$start_pages = $this->where('parent_id', '=', 0)->find_all();
			
		$pages = array();
		$this->recurse_tree_select($start_pages, $pages, $indent);
		
		return $pages;
	}
	
	public function tree_list_html($view_path = NULL)
	{
		$start_pages = $this->where('parent_id', '=', 0)->find_all();
			
		$list = '';
		$this->recurse_tree_list_html($start_pages, $list, $view_path);
		
		return $list;
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
		
		if (count($pages))
		{
			$html .= View::factory($view_path.'/list_open');
		}
		foreach($pages as $page)
		{
			$html .= View::factory($view_path.'/item_open')->set('page', $page);

			$children = $page->children->find_all();
			
			$this->recurse_tree_list_html($children, $html, $view_path, $depth);
			
			$html .= View::factory($view_path.'/item_close');
		}
		if (count($pages))
		{
			$html .= View::factory($view_path.'/list_close');
		}		
	}	
	
} // End Model_Base_Page
