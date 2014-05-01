<?php
require_once '../common.php';
include 'AutoCompleteData.class.php';
$instance = new AutoCompleteData();
//$instance->get_simple_autocomplete_data('test-data.js', 'a+');
echo 'start';
$instance->get_simple_data('simple-data.js', 'a+');
echo 'end';
?>
