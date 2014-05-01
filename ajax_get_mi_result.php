<?php

require_once './common.php';
require_once './searchgene.php';
$query_names = $_POST['query_names'];
//$query_names = 'YMD8 YDJ1';
$query_type=$_POST['query_type'];
$interactionType=$_POST['interactionType'];
//$interactionType = 'co_expression';
$rvalue = $_POST['rvalue'];
$m=$_POST['m'];
$n=$_POST['n'];
//$m = 0;
//$n = 0;
//$query_names='primary breast cancer';
//$query_type = 'with';
//$type='co_expression';
$result = array();
$search = new searchgene();
//$search->set_search_type($view);
$search->set_m($m);
$search->set_n($n);
$search->set_rvalue($rvalue);
$search->set_search_params($query_names, $query_type);
$search->set_search_genereltype($interactionType);
$search->execute_search_t_other();
$result['table_list_data'] = $search->get_view_table_data();
$result['table_list_count'] = $search->get_result_count();
$result['m'] = $search->get_m();
$result['n'] = $search->get_n();

echo json_encode($result);