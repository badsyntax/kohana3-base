<?php defined('SYSPATH') or die('No direct script access.');

class Base_Asset {
	
	public static $driver = 'imagemagick';
	
	public static function resize($file_in = NULL, $file_out = NULL, $width = NULL, $height = NULL, $crop = NULL)
	{
		if ($width AND $height)
		{
			$image = Image::factory($file_in, static::$driver);

			if ($crop)
			{
				if (($image->width / $image->height) > ($width / $height))
				{
					$resized_w = ($height / $image->height) * $image->width;
					$offset_x = round(($resized_w - $width) / 2);
					$offset_y = 0;			
					$image->resize(NULL, $height);
				}
				else
				{
					$resized_h = ($width / $image->width) * $image->height;
					$offset_x = 0;
					$offset_y = round(($resized_h - $height) / 2);			
					$image->resize($width, NULL);				
				}

				$image->crop($width, $height, $offset_x, $offset_y);
			}
			else
			{
				$image->resize($width, $height);
			}			
			
			$image->save($file_out);
		}	
	}
	
	public static function docpath($asset, $width = NULL, $height = NULL, $crop = NULL, $full_path = FALSE)
	{
		$pathinfo = pathinfo($asset->filename);

		$filename = $pathinfo['filename'];

		$crop = (string) (int) $crop;
		
		$path = Kohana::config('admin/asset.upload_path').'/'.$asset->filename;

		if ($asset->mimetype->subtype == 'image' AND $width AND $height)
		{
			try
			{
				$image = Image::factory($path, static::$driver);
			}
			catch(Kohana_Exception $e)
			{
				// Image does not exist on filesystem
				return NULL;
			}

			if ($image->height > $height OR $image->width > $width)
			{
				$filename = preg_replace('/^'.$asset->id.'_/', '', $filename);

				$filename = $asset->id."_{$width}_{$height}_{$crop}_{$filename}";

				$path = Kohana::config('admin/asset.upload_path').'/resized/'.$filename.'.'.$asset->mimetype->extension;	
			}
		}
		elseif ($asset->mimetype->subtype == 'application' AND $asset->mimetype->type == 'pdf' AND $width AND $height)
		{
			$filename = preg_replace('/^'.$asset->id.'_/', '', $filename);

			$filename = $asset->id."_{$width}_{$height}_{$crop}_{$filename}";

			$path = Kohana::config('admin/asset.upload_path').'/resized/'.$filename.'.png';
		}

		if (!file_exists($path) AND $width !== NULL AND $height !== NULL)
		{
			// Find the size in the db
			$size = ORM::factory('asset_size')
				->where('asset_id', '=', $asset->id)
				->where('width', '=', $width)
				->where('height', '=', $height)
				->where('crop', '=', $crop)
				->find();
			
			// If no size then create one, this is a security feature 
			// to disallow building image sizes anonymously
			if (!$size->loaded())
			{
				$size->asset_id = $asset->id;
				$size->width = $width;
				$size->height = $height;
				$size->crop = $crop;
				$size->filename = basename($path);
				$size->save();
			}
		}
		
		return $path;
	}
	
	public static function image_url($asset, $width = NULL, $height = NULL, $crop = NULL, $full_path = FALSE)
	{
		$path = self::docpath($asset, $width, $height, $crop, $full_path);
			
		return URL::site($path, $full_path);
	}
	
	public static function image_path($asset, $width = NULL, $height = NULL, $crop = NULL, $full_path = FALSE)
	{
		$path = self::docpath($asset, $width, $height, $crop);
			
		return ($full_path)
			? DOCROOT.$path
			: $path;
	}	

	public static function path($asset, $full = FALSE, $resized = '')
	{
	   $path = Kohana::config('admin/asset.upload_path').'/'.$resized.$asset->filename;

	   return ($full)
			   ? DOCROOT.$path
			   : $path;
	}
	
	public function url($asset, $full = FALSE)
	{
		$url = URL::site('media/assets/'.$asset->filename);
		
		return ($full)
			? URL::site($url, TRUE)
			: $url;
	}
	
	public static function rotate($file_in, $degrees)
	{
		Image::factory( $file_in )
		->rotate($degrees)
		->save();
	}
	
	public static function sharpen($file_in, $amount)
	{
		Image::factory( $file_in )
		->sharpen($amount)
		->save();
	}
	
	public static function flip_horizontal($file_in)
	{
		Image::factory( $file_in )
		->flip(Image::HORIZONTAL)
		->save();
	}
	
	public static function flip_vertical($file_in)
	{
		Image::factory( $file_in )
		->flip(Image::HORIZONTAL)
		->save();
	}
	
	public static function pdfthumb($file_in, $file_out, $width, $height, $crop)
	{
		exec('convert -quality 85 '.$file_in.'[0] '.$file_out);
		
		static::resize($file_out, $file_out, $width, $height, $crop);
	}	
}
