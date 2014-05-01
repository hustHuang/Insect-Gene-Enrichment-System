<?php

/*
 * @author: Kegui Huang
 * @time: 2013-06-24
 * @theme: get the node json file booneGraph needed from xgmml file
 */

$objXML=new DOMDocument();
$objXML->load('./Science_2010_map.xgmml', LIBXML_NOBLANKS);
$nodes=$objXML->getElementsByTagName('node');

$result='{"nodes":[';
foreach ($nodes as $node) {
    $id=$node->getAttribute('id');
    $name=$node->getAttribute('label');
    $result.='{"id":"'.$id.'","orf":"'.$name.'"},';
}
$result=rtrim($result, ',');
$result.=']}';
$file = fopen("Science_2010_node.json", "wb");
fwrite($file, $result,  strlen($result));
fclose($file);
?>
