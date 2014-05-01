<?php

include './SearchAction.php';
$name = $_POST['id'];
$searchaction = new SearchAction();
$standardName = $searchaction->get_standard_gene_name($name);
echo json_encode($standardName);
?>
