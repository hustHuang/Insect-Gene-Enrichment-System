<?php

include '../SearchAction.php';
$name = $_POST['id'];
$searchaction = new SearchAction();
$featureName = $searchaction->get_feature_name($name);
echo json_encode($featureName);
?>
