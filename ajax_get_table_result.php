<?php
require_once './common.php';
require_once ABSPATH . '/class/DataFormator.class.php';
//$num='1354759029';
$num=$_POST['num'];
$flag=$_POST['flag'];
$genes=$_POST['genes'];
//echo $num;
$data_formator_service=new DataFormator($num);
echo $data_formator_service->get_table_data($flag,$genes);

?>
