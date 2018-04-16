<?php
class ImageLibrary
{   
    public static function thumb($sourceFile, $filePath)
    {           
        $ext = strrchr($sourceFile, '.');
        $fileName = basename($sourceFile, $ext);
        $path = dirname($sourceFile);
        $thumbFile = $path . '/' . $fileName . '_thumb' . $ext;   
        $rs = self::scale($sourceFile, $thumbFile, IMAGE_THUMB_MAX_X, IMAGE_THUMB_MAX_Y);
        if ( ! $rs){
            return false;
        }                        
        $thumbPath = $filePath . '/' . $fileName . '_thumb' . $ext;                 
        return $thumbPath;
    } 
    
    public static function scale($source, $destination, $width, $height)
    {
        $imgInfo = getimagesize($source);
        if ( ! $imgInfo){
            return FALSE;
        }
        $exts = array(1 => 'gif', 2=> 'jpg', 3 => 'png');
        $info = array(
            'width' => $imgInfo[0], 
            'height' => $imgInfo[1], 
            'extension' => $exts[$imgInfo[2]]
        );         
        // don't scale up
        if ($width >= $info['width'] && $height >= $info['height']) {
            return '';
        }                    
        $aspect = $info['height'] / $info['width'];
        if ($aspect < $height / $width) {
            $width = (int)min($width, $info['width']);
            $height = (int)round($width * $aspect);
        } else {
            $height = (int)min($height, $info['height']);
            $width = (int)round($height / $aspect);
        }                   
        $extension = str_replace('jpg', 'jpeg', $info['extension']);
        $openFunc = 'imagecreatefrom' . $extension;
        if ( ! function_exists($openFunc)){
            return FALSE;
        }
        $im = $openFunc($source);     
                
        if ( ! $im){
            return FALSE;
        }
        
        $res = imagecreatetruecolor($width, $height);
        if ($info['extension'] == 'png'){
            $transparency = imagecolorallocatealpha($res, 0, 0, 0, 127);
            imagealphablending($res, FALSE);
            imagefilledrectangle($res, 0, 0, $width, $height, $transparency);
            imagealphablending($res, TRUE);
            imagesavealpha($res, TRUE);
        } elseif ($info['extension'] == 'gif') {
            // If we have a specific transparent color.
            $transparencyIndex = imagecolortransparent($im);
            if ($transparencyIndex >= 0){
                // Get the original image's transparent color's RGB values.
                $transparentColor = imagecolorsforindex($im, $transparencyIndex);
                // Allocate the same color in the new image resource.
                $transparencyIndex = imagecolorallocate($res, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
                // Completely fill the background of the new image with allocated color.
                imagefill($res, 0, 0, $transparencyIndex);
                // Set the background color for new image to transparent.
                imagecolortransparent($res, $transparencyIndex);
                // Find number of colors in the images palette.
                $numberColors = imagecolorstotal($im);
                // Convert from true color to palette to fix transparency issues.
                imagetruecolortopalette($res, TRUE, $numberColors);
            }
        }
        imagecopyresampled($res, $im, 0, 0, 0, 0, $width, $height, $info['width'], $info['height']);
        $result = self::gdClose($res, $destination, $info['extension']);              
        imagedestroy($res);
        imagedestroy($im);
        return $result;
    }
    
    public static function gdClose($res, $destination, $extension)
    {
        $extension = str_replace('jpg', 'jpeg', $extension);
        $closeFunc = 'image'. $extension;
        if ( ! function_exists($closeFunc)){
            return FALSE;
        }
        if ($extension == 'jpeg'){
            return $closeFunc($res, $destination, 100);
        } else {
            return $closeFunc($res, $destination);
        }
    }

	//Image function
	public static function imageGetInfo($file) {
	  if (!is_file($file)) {
	    return FALSE;
	  }

	  $details = FALSE;
	  $data = @getimagesize($file);
	  $file_size = @filesize($file);

	  if (isset($data) && is_array($data)) {
	    $extensions = array('1' => 'gif', '2' => 'jpg', '3' => 'png');
	    $extension = array_key_exists($data[2], $extensions) ?  $extensions[$data[2]] : '';
	    $details = array('width'     => $data[0],
	                     'height'    => $data[1],
	                     'extension' => $extension,
	                     'file_size' => $file_size,
	                     'mime_type' => $data['mime']);
	  }

	  return $details;
	}

	/**
	* GD helper function to create an image resource from a file.
	*/
	public static function imageGDopen($file, $extension) {
		$extension = str_replace('jpg', 'jpeg', $extension);
		$open_func = 'imageCreateFrom'. $extension;
		if (!function_exists($open_func)) {
			return FALSE;
		}
		return $open_func($file);
	}

	/**
	 * GD helper to write an image resource to a destination file.
	 */
	public static function imageGDclose($res, $destination, $extension) {
		$extension = str_replace('jpg', 'jpeg', $extension);
		$close_func = 'image'. $extension;
		if (!function_exists($close_func)) {
		return FALSE;
		}
		if ($extension == 'jpeg') {
		return $close_func($res, $destination, AVATAR_QUALITY);
		}
		else {
		return $close_func($res, $destination);
		}
	}

	/**
	 * Scale an image to the specified size using GD.
	 */
	public static function imageResize($source, $destination, $width, $height) {
		if (!file_exists($source)) {
			return FALSE;
		}

		$info = self::imageGetInfo($source);
		if (!$info) {
			return FALSE;
		}

		$im = self::imageGDopen($source, $info['extension']);
		if (!$im) {
			return FALSE;
		}

		$res = imageCreateTrueColor($width, $height);
		if ($info['extension'] == 'png') {
		$transparency = imagecolorallocatealpha($res, 0, 0, 0, 127);
		imagealphablending($res, FALSE);
		imagefilledrectangle($res, 0, 0, $width, $height, $transparency);
		imagealphablending($res, TRUE);
		imagesavealpha($res, TRUE);
		}
		imageCopyResampled($res, $im, 0, 0, 0, 0, $width, $height, $info['width'], $info['height']);
		$result = self::imageGDclose($res, $destination, $info['extension']);

		imageDestroy($res);
		imageDestroy($im);

		return $result;
	}
}