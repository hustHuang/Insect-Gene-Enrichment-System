<?php

/*
 * @author: Kegui Huang
 * @time: 2013-10-26
 * @theme: get the node names in booneGraph as in the xgmml file
 */

$objXML=new DOMDocument();
$objXML->load('./Science_2010_map.xgmml', LIBXML_NOBLANKS);
$nodes=$objXML->getElementsByTagName('node');

$result='var names = [';
foreach ($nodes as $node) {
    $id=$node->getAttribute('id');
    $name=$node->getAttribute('label');
    $result.='{"id":"'.$id.'","name":"'.$name.'"},';
}
$result=rtrim($result, ',');
$result.='];';
$file = fopen("Science_2010_node_name.js", "wb");
fwrite($file, $result,  strlen($result));
fclose($file);

?>
