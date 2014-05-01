<?php

set_time_limit(0);
include 'SearchAction.php';
$id = $_POST['id'];
$view_type = $_POST['viewType'];
$group = $_POST['group'];
$search_action = new SearchAction();

if ($group == 'edges'){
    $score_neg = $_POST['score_neg'];
    $score_pos = $_POST['score_pos'];
    $p_neg = $_POST['p_neg'];
    $p_pos = $_POST['p_pos'];
    $rvalue = $_POST['rvalue'];
    echo $search_action->get_click_target_info($view_type, $group, $id , $score_neg, $score_pos, $p_neg, $p_pos, $rvalue);
} else {
    echo $search_action->get_click_target_info($view_type, $group, $id);
}

?>
