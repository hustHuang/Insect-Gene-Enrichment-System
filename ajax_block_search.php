<?php

set_time_limit(0);
include 'SearchAction.php';
$query_names = $_REQUEST['query_names'];
//$query_names = 'YMR115W YOR350C YCL001W YOL131W YLR081W YPR118W YHR199C';
$epsilon_neg = '-0.08';
$pvalue_neg = '0.05';
$epsilon_pos = '0.08';
$pvalue_pos = '0.05';
$rvalue = 'significant';
$view = 'nv';
$type = 'i';
//$database=$_REQUEST['database'];
$query_type = 'within';
$num = '100';
$search_action = new SearchAction();
$search_action->set_search_params($query_names, $epsilon_neg, $pvalue_neg, $epsilon_pos, $pvalue_pos, $rvalue, $query_type);
$search_action->set_search_type($view, $type);
$search_action->execute_search($num);
$result = array();
$result['cw_node_data'] = $search_action->get_cw_node_data();
$result['cw_edge_data'] = $search_action->get_cw_edge_data();
echo json_encode($result);
?>
