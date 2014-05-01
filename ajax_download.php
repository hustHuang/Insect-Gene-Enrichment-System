<?php

set_time_limit(0);
include './SearchAction.php';
require_once './searchgene.php';
require_once './class/download.php';
$query_names = $_POST['query_names'];
$epsilon_neg = $_POST['epsilon_neg'];
$pvalue_neg = $_POST['pvalue_neg'];
$epsilon_pos = $_POST['epsilon_pos'];
$pvalue_pos = $_POST['pvalue_pos'];
$rvalue = $_POST['rvalue'];
$view = 'tv';
$num = 20;
$type = $_POST['type'];
$query_type = $_POST['query_type'];
$interactionType=$_POST['interactionType'];

//$query_names = "YMR115W YOR350C YCL001W YOL131W YLR081W YPR118W YHR199C";
//$epsilon_neg = "-0.08";
//$pvalue_neg = "0.05";
//$epsilon_pos = "0.08";
//$pvalue_pos = "0.05";
//$rvalue = "significant";
//$view = "tv";
//$type = 'n';
//$query_type ="with";
$search_action = new SearchAction();
$download = new download();
$search_action->set_search_params($query_names, $epsilon_neg, $pvalue_neg, $epsilon_pos, $pvalue_pos, $rvalue, $query_type);
$search_action->set_search_type($view, $type);
$filename = '';
$prefix = './data/';
if ($type == 'n') {
    $timetamp = mktime();
    $filename = $timetamp . 'data.txt';
    $file = $prefix . $filename;
    $search_action->execute_search($num);
    $data = $search_action->get_table_export_data();
    $download->getdowndata($file, $data, $type);
} else if ($type == 'p') {
    $timetamp = mktime();
    $filename = $timetamp . 'data.txt';
    $file = $prefix . $filename;
    $search_action->execute_search($num);
    $data = $search_action->get_table_export_data();
    $download->getdowndata($file, $data, $type);
} else if ($type == 'c') {
    $timetamp = mktime();
    $filename = $timetamp . 'data.txt';
    $file = $prefix . $filename;
    $search_action->execute_search($num);
    $data = $search_action->get_table_export_data();
    $download->getdowndata($file, $data, $type);
} else if ($type == 'm') {
    $timetamp = mktime();
    $filename = $timetamp . 'data.txt';
    $file = $prefix . $filename;
    $searchgene=new searchgene();
    $searchgene->set_search_genereltype($interactionType);
    $searchgene->set_search_params($query_names, $query_type);
    $searchgene->set_rvalue($rvalue);
    $data=$searchgene->execute_search_t_other_download();
    $download->getdowndata($file, $data, $type);
}
if (!file_exists($file)) {
    echo 'Sorry. File not exist.';
    exit();
} else {
    header('Content-Description: File Transfer');
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header("Content-Length: " . filesize($file));
    readfile($file);
}

?>
