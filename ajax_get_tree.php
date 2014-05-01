<?php

set_time_limit(0);
include 'SearchAction.php';
$query_names = $_POST['query_names'];
$epsilon_neg = $_POST['epsilon_neg'];
$pvalue_neg = $_POST['pvalue_neg'];
$epsilon_pos = $_POST['epsilon_pos'];
$pvalue_pos = $_POST['pvalue_pos'];
$rvalue = $_POST['rvalue'];
$analysis_type = $_POST['analysis_type'];
$search_action = new SearchAction();
$result = array();
$search_action->set_search_params($query_names, $epsilon_neg, $pvalue_neg, $epsilon_pos, $pvalue_pos, $rvalue, 'with');
if ($analysis_type == 'n' || $analysis_type == 'p'){
    $search_action->set_search_type('tv', $analysis_type);
    $search_action->execute_search(20);
    $result['tree_query_data'] = $search_action->get_tree_user_data();
    $result['tree_result_data'] = $search_action->get_tree_result_data();
} else if ($analysis_type == 'all'){
    $search_action->set_search_type('tv', 'n');
    $search_action->execute_search(20);
    $result['tree_query_data_n'] = $search_action->get_tree_user_data();
    $result['tree_result_data_n'] = $search_action->get_tree_result_data();
    
    $search_action->set_search_type('tv', 'p');
    $search_action->execute_search(20);
    $result['tree_query_data_p'] = $search_action->get_tree_user_data();
    $result['tree_result_data_p'] = $search_action->get_tree_result_data();
}

echo json_encode($result);
