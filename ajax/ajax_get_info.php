<?php
set_time_limit(0);
require_once '../common.php';
require_once ABSPATH . '/class/SearchAction.class.php';
$id = $_POST['id'];
$view_type = $_POST['viewType'];
$group = $_POST['group'];
$search_action = new SearchActionClass();

if ($group == 'edges'){
    $score_neg = $_POST['score_neg'];
    $score_pos = $_POST['score_pos'];
    $p_neg = $_POST['p_neg'];
    $p_pos = $_POST['p_pos'];
    $rvalue = $_POST['rvalue'];
    echo $search_action->get_click_target_info($view_type, $group, $id, $score_neg, $score_pos, $p_neg, $p_pos, $rvalue);
} else {
    echo $search_action->get_click_target_info($view_type, $group, $id);
}

