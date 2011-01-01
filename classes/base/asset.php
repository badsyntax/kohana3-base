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

		$path = Kohana::config('admin/asset.upload_path').'/'.$filename.'.'.$asset->mimetype->extension;

		if ($asset->mimetype->subtype == 'image' AND $width AND $height)
		{
			$image = Image::factory($path, static::$driver);

			if ($image->height > $height AND $image->width > $width)
			{
				$crop = (string) (int) $crop;

				$filename = preg_replace('/^'.$asset->id.'_/', '', $filename);

				$filename = $asset->id."_{$width}_{$height}_{$crop}_{$filename}";

				$path = Kohana::config('admin/asset.upload_path').'/resized/'.$filename.'.'.$asset->mimetype->extension;	
			}
		}
		elseif ($asset->mimetype->subtype == 'application' AND $asset->mimetype->type == 'pdf' AND $width AND $height)
		{
			$crop = (string) (int) $crop;

			$filename = preg_replace('/^'.$asset->id.'_/', '', $filename);

			$filename = $asset->id."_{$width}_{$height}_{$crop}_{$filename}";

			$path = Kohana::config('admin/asset.upload_path').'/resized/'.$filename.'.png';
		}
		
		return $path;
	}
	
	public static function image_url($asset, $width = NULL, $height = NULL, $crop = NULL, $full_path = FALSE)
	{
		$path = self::docpath($asset, $width, $height, $crop);
			
		return URL::site($path, $full_path);
	}
	
	public static function image_path($asset, $width = NULL, $height = NULL, $crop = NULL, $full_path = FALSE)
	{
		$path = self::docpath($asset, $width, $height, $crop);
			
		return ($full_path)
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