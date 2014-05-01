<?php

/**
 * @author zhongmingmao 2012-10-10 17:00
 * @since php 5.2.17
 * @see http://hi.baidu.com/ljezyl/item/9aec3ee328e8ab10585dd8af 
 */
class imagick_lib
{
    //Imagick object
    private $image = null;
    //默认图片类型PNG
    private $type = 'png';

    // load image
    // initialize image and type
    public function open($path)
    {
        if(!file_exists($path)){
            echo $path.' is not existed!';
            exit(-1);
        }
        
        $this->image = new Imagick( $path );
        if($this->image)
        {
            $this->type = strtolower($this->image->getImageFormat());
            if($this->type != 'png'){
                echo 'This is not a png file';
                exit(-1);
            }
        }
        return $this->image;
    }


    //crop the image  start point（x , y） ，size（width , height）
    public function crop($x = 0, $y = 0, $width , $height)
    {
        //if error ,quit
        if( $x < 0 || $y < 0 || $width <= 0 || $height <= 0 
                || ($x + $width) > $this-> get_width() || ($y + $height) > $this-> get_height() ) {
            echo 'x | y | width | height  : parameter error';
            exit(-1);
        }

        $this->image->cropImage($width, $height, $x, $y);

    }
    

    // save to certain path
    public function save_to( $path )
    {
        if($this->type == 'png'){
            $this->image->writeImage($path);
        }
    }
    
  /**
     *@see http://www.oschina.net/code/snippet_54100_5028
     */
    function smart_resize_image( $file, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false )
    {
        if ( $height <= 0 && $width <= 0 ) {
            return false;
        }
        $info = getimagesize($file);
        $image = '';
 
        $final_width = 0;
        $final_height = 0;
        list($width_old, $height_old) = $info;
 
        if ($proportional) {
            if ($width == 0) $factor = $height/$height_old;
            elseif ($height == 0) $factor = $width/$width_old;
            else $factor = min ( $width / $width_old, $height / $height_old);  
            $final_width = round ($width_old * $factor);
            $final_height = round ($height_old * $factor);
 
        }
        else {       
            $final_width = ( $width <= 0 ) ? $width_old : $width;
            $final_height = ( $height <= 0 ) ? $height_old : $height;
        }
 
        switch ($info[2] ) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);
            break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
            break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
            break;
            default:
                return false;
        }
 
        $image_resized = imagecreatetruecolor( $final_width, $final_height );
 
        if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
            $trnprt_indx = imagecolortransparent($image);
            // If we have a specific transparent color
            if ($trnprt_indx >= 0) {
                // Get the original image's transparent color's RGB values
                $trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
                // Allocate the same color in the new image resource
                $trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                // Completely fill the background of the new image with allocated color.
                imagefill($image_resized, 0, 0, $trnprt_indx);
                // Set the background color for new image to transparent
                imagecolortransparent($image_resized, $trnprt_indx);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif ($info[2] == IMAGETYPE_PNG) {
                // Turn off transparency blending (temporarily)
                imagealphablending($image_resized, false);
                // Create a new transparent color for image
                $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
 
                // Completely fill the background of the new image with allocated color.
                imagefill($image_resized, 0, 0, $color);
 
                // Restore transparency blending
                imagesavealpha($image_resized, true);
            }
        }
 
        imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
 
        if ( $delete_original ) {
            if ( $use_linux_commands )
                exec('rm '.$file);
            else
                @unlink($file);
        }
 
        switch ( strtolower($output) ) {
            case 'browser':
                $mime = image_type_to_mime_type($info[2]);
                header("Content-type: $mime");
                $output = NULL;
            break;
            case 'file':
                $output = $file;
            break;
            case 'return':
                return $image_resized;
            break;
            default:
            break;
        }
 
        switch ($info[2] ) {
            case IMAGETYPE_GIF:
                imagegif($image_resized, $output);
            break;
            case IMAGETYPE_JPEG:
                imagejpeg($image_resized, $output);
            break;
            case IMAGETYPE_PNG:
                imagepng($image_resized, $output);
            break;
            default:
                return false;
        }
 
        return true;
    }
    

    //out put the image
    public function output()
    {
        echo $this->image->getImagesBlob();		
    }


    //get width of the image
    public function get_width()
    {
        $size = $this->image->getImagePage(); 
        return $size['width'];
    }

    //get height of the image
    public function get_height()
    {
        $size = $this->image->getImagePage(); 
        return $size['height'];
    }

     
    //get current time
    function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}


//how to use

//$image = new imagick_lib();
//$before = $image->getMillisecond();
//
//$image->open('fg30_2.png');
//$image->crop($image->get_width()*0.75, $image->get_height()*0.75 , $image->get_width()/4, $image->get_height()/4);

//$image->getImage()->scaleImage($image->get_width()/2, $image->get_height()/2, true); 

//$image->save_to('159.png');

//$image->smart_resize_image("159.png", $image->get_width()/2, $image->get_height()/2);

//$image->output();

//$after = $image->getMillisecond();
//
//echo '<br>TotalTime : '.($after - $before).'ms<br>';

?>