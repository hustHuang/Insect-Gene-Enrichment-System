<?php
require_once '../common.php';
include 'StrainDataFetcher.class.php';

$file1 = "smf_t26_130417.txt";
$file2 = "smf_t30_130417.txt";
$instance = new StrainDataFetcher();
/*echo 'start <br/>';
$instance->insertFitness($file1,26);
echo 'end  <br/>';

echo 'start <br/>';
$instance->insertFitness($file2,30);
echo 'end  <br/>';
*/
$files = array('26'=>$file1,'30'=>$file2);
echo 'start <br/>';

foreach ($files as $key=>$value) {   
    $instance->insertFitness($value,$key);
}

echo 'end  <br/>';
?>
