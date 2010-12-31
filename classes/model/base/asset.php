<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Base asset model
 */
class Model_Base_Asset extends Model_Base { 
	
	protected $_belongs_to = array(
		'mimetype' => array('model' => 'mimetype', 'foreign_key' => 'mimetype_id'),
		'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
	);
	
	protected $_has_many = array(
		'sizes' => array('model' => 'asset_size', 'foreign_key' => 'asset_id'),
    );

	protected $_rules = array(
		// Validate the $_FILES array
		'upload' => array(
			'Upload::valid' => array(),
			'Upload::not_empty' => array(),
			'Upload::size' => array('10M')
		),
		'update' => array(
			'filename' => array(
				'trim' => NULL,
				'max_length' => array('128'),
			),
		)
	);
	
	// Validation callbacks
	protected $_callbacks = array(
		'extension' => array('callback_mimetype_exists')
	);
	
	// Check mimetype exists by extension
	public function callback_mimetype_exists(Validate $array, $field)
	{
		// Try find a matching mimetype
		$mimetype = ORM::factory('mimetype')
			->where('extension', '=', $array[$field])
			->find();
			
		if (!$mimetype->loaded())
		{
			$array->error($field, 'mimetype_not_allowed', array($array[$field]));
		}		
		
		$array['mimetype_id'] = $mimetype->id;
	}
	
	public function resize($path, $width = NULL, $height = NULL, $crop = NULL)
	{
		$file = DOCROOT.Kohana::config('admin/asset.upload_path').'/'.$this->filename;
		
		if (file_exists($file))
		{			
			Asset::resize($file, $path, $width, $height, $crop);
		}
	}	
	
	public function image_url($width = NULL, $height = NULL, $crop = NULL, $full_path = FALSE)
	{
		if (!$this->loaded()) exit;
		
		$pathinfo = pathinfo($this->filename);
		
		$filename = $pathinfo['filename'];
		
		$path = Kohana::config('admin/asset.upload_path').'/'.$filename.'.'.$this->mimetype->extension;
		
		if ($width AND $height AND ($this->mimetype->subtype == 'image' OR ($this->mimetype->subtype == 'application' AND $this->mimetype->type == 'pdf')))
		{
			$crop = (string) (int) $crop;
		
			$filename = preg_replace('/^'.$this->id.'_/', '', $filename);

			$filename = $this->id."_{$width}_{$height}_{$crop}_{$filename}";

			if ($this->mimetype->subtype === 'application' AND $this->mimetype->type == 'pdf')
			{
				$this->mimetype->extension = 'png';
			}
			
			$path = Kohana::config('admin/asset.upload_path').'/resized/'.$filename.'.'.$this->mimetype->extension;
		}
			
		if ($full_path)
		{
			$full_path = DOCROOT.$fullpath;
		}
				
		return $path;
	}
}