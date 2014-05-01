<?php

/**
 * @author zhongmingmao 2012-10-10 17:00
 * @since php 5.2.17
 * @see http://hi.baidu.com/ljezyl/item/9aec3ee328e8ab10585dd8af 
 */
class imagick_lib
{
    //Imagick对象
    private $image = null;
    //默认图片类型PNG
    private $type = 'png';

    // 载入图像
    // 初始化成员变量image和type
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
                echo 'This is not a tif file';
                exit(-1);
            }
        }
        return $this->image;
    }


    //裁剪结果图像 起点坐标（x , y） ，被裁图像大小（width , height）
    public function crop($x = 0, $y = 0, $width , $height)
    {
        //参数有误 则退出
        if( $x < 0 || $y < 0 || $width <= 0 || $height <= 0 
                || ($x + $width) > $this-> get_width() || ($y + $height) > $this-> get_height() ) {
            echo 'x | y | width | height  : parameter error';
            exit(-1);
        }

        $this->image->cropImage($width, $height, $x, $y);

    }


    // 保存到指定路径
    public function save_to( $path )
    {
        if($this->type == 'png'){
            $this->image->writeImage($path);
        }
    }

    // 输出图像
    public function output()
    {
        echo $this->image->getImagesBlob();		
    }


    //获取图像宽度
    public function get_width()
    {
        $size = $this->image->getImagePage(); 
        return $size['width'];
    }

    //获取图像高度
    public function get_height()
    {
        $size = $this->image->getImagePage(); 
        return $size['height'];
    }

     
    //获取当前时间（毫秒）
    function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}


//how to use

//$image = new imagick_lib();
//$before = $image->getMillisecond();
//
//$image->open('./iipmooviewer2/fg30_2.png');
//$image->crop($image->get_width()*0.75, $image->get_height()*0.75 , $image->get_width()/4, $image->get_height()/4);
//
//$image->save_to('./iipmooviewer2/new.png');
//
////$image->output();
//
//$after = $image->getMillisecond();
//
//echo '<br>TotalTime : '.($after - $before).'ms<br>';

?>