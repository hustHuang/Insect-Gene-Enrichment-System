<?php 
$row1 = $_REQUEST['row1'];
$row2 = $_REQUEST['row2'];
$col1 = $_REQUEST['col1'];
$col2 = $_REQUEST['col2'];
exec("Rscript /var/www/SGA/heatmap/desc.R $row1 $row2 $col1 $col2", $result);
//echo json_encode($result);
$res=explode("/",$result[2]);
echo json_encode($res[5]); 
//echo json_encode($result); 
?>
