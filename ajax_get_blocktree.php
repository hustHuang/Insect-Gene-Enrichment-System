<?php
/*
 * get the block treeview
 */

set_time_limit(0);
require_once './common.php';
require_once ABSPATH . './class/BlockData.class.php';
$query_names = $_REQUEST['query_names'];
$block_service = new BlockData();
$result_data_array = $block_service->get_feature_name($query_names);
$result=$result_data_array;
echo json_encode($result);

?>
