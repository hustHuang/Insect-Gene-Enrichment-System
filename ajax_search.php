<?php
//set_time_limit(0);
include 'SearchAction.php';
$query_names = $_POST['query_names'];
$epsilon_neg = $_POST['epsilon_neg'];
$pvalue_neg = $_POST['pvalue_neg'];
$epsilon_pos = $_POST['epsilon_pos'];
$pvalue_pos = $_POST['pvalue_pos'];
$rvalue = $_POST['rvalue'];
$view = $_POST['view'];
$type = $_POST['type'];
$num = $_POST['num'];
//$database = $_POST['database'];
$query_type = $_POST['query_type'];

$search_action = new SearchAction();
$search_action->set_search_params($query_names, $epsilon_neg, $pvalue_neg, $epsilon_pos, $pvalue_pos, $rvalue, $query_type);
$search_action->set_search_type($view, $type);
$search_action->execute_search($num);
$result = array();
if ($view == 'nv' && $num != 20) {
    $result['cw_node_data'] = $search_action->get_cw_node_data();
    $result['cw_edge_data'] = $search_action->get_cw_edge_data();
    $result['tree_result_data'] = $search_action->get_tree_result_data();
} else {
    switch ($view) {
        case 'tv': {
                $result['tree_query_data'] = $search_action->get_tree_user_data();
                $result['tree_result_data'] = $search_action->get_tree_result_data();
                $result['table_list_data'] = $search_action->get_view_table_data();
                $result['table_list_count'] = $search_action->get_result_count();
            }break;
        case 'nv': {
                $result['tree_query_data'] = $search_action->get_tree_user_data();
                $result['tree_result_data'] = $search_action->get_tree_result_data();
                $result['cw_node_data'] = $search_action->get_cw_node_data();
                $result['cw_edge_data'] = $search_action->get_cw_edge_data();
            }break;
        default:
            break;
    }
}
echo json_encode($result);
?>
