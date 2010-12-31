<?php defined('SYSPATH') or die('No direct script access.');

abstract class Asset {
	
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
	
	public static function pdfthumb($file_in, $file_out, $width, $height, $crop)
	{
		exec('convert -quality 85 '.$file_in.'[0] '.$file_out);
		
		static::resize($file_out, $file_out, $width, $height, $crop);
	}
	
}