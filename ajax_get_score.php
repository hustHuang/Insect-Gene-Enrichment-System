<?php

include 'SearchAction.php';
$queryname=$_POST['query'];
$arrayname=$_POST['array'];
//$queryname = 'ARO7';
//$arrayname = 'CDC48';

//$queryname = 'ARO7_sn4274';
//$arrayname = 'CDC48_tsq1186';
$searchaction = new SearchAction();
$score = $searchaction->get_interaction_score($queryname, $arrayname);
echo json_encode($score);
?>