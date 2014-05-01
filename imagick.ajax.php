<?php

require_once 'New_imagick.class.php';

//$before = $image->getMillisecond();
$startX = $_POST['x'];
$startY = $_POST['y'];
$width = $_POST['w'];
$height = $_POST['h'];
$name=$_POST['image'];
$image = new imagick_lib();
//$startX=112;
//$startY=256;
//$width=21;
//$height=50;
$image->open('./iipmooviewer2/'.$name.'.png');
$image->crop($startX, $startY, $width, $height);
//$image->crop(0, 0, 200, 200);
$random = rand(10, 10000);
$t = time();
$url = $t . '_' . $random;
$imageFile = './iipmooviewer2/new_' . $url . '.png';
$image->save_to($imageFile);
//$image->smart_resize_image($imageFile, 8 * $width, 8 * $height);
$cmd='java -Xmx1024m -cp ResizeImg.jar ResizeImg "'.$imageFile.'" "'.$imageFile.'" "JPEG" "8"';
exec($cmd, $output);
echo json_encode($url);
//$image->output();
//$after = $image->getMillisecond();
//echo '<br>TotalTime : '.($after - $before).'ms<br>';
?>
