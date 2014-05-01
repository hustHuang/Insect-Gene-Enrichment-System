<?php

/*
 * @author: kegui huang
 * @time:   2013-07-13
 * @theme: create the gml file booneGraph needed from xgmml file
 */

$t1=  time();
$objXML=new DOMDocument();
$objXML->load('./Science_2010_map.xgmml', LIBXML_NOBLANKS);
$nodes=$objXML->getElementsByTagName('node');
$edges=$objXML->getElementsByTagName('edge');
$result="Creator\t\"Cytoscape\"\nVersion\t1.0\ngraph\t[\n";
foreach ($nodes as $node) {
    $id=$node->getAttribute('id');
    $name=$node->getAttribute('label');
    $graphics=$node->getElementsByTagName('graphics')->item(0);
    $x=$graphics->getAttribute('x');
    $y=$graphics->getAttribute('y');
    $result.="\tnode\t[\n\t\tid\t".$id."\n\t\tgraphics\t[\n\t\t\tx\t".$x."\n\t\t\ty\t".$y."\n\t\t\tw\t40.0\n\t\t\th\t40.0\n\t\t\tfill\t\"#ccccff\"\n\t\t\ttype\t\"ellipse\"\n\t\t\toutline\t\"#000000\"\n\t\t\toutline_width\t0.0\n\t\t]\n\t\tlabel\t\"".$name."\"\n\t]\n";
}
$edge_id=0;
foreach($edges as $edge){
     $id=$edge_id++;
     $target=$edge->getAttribute('target');
     $source=$edge->getAttribute('source');
     $attr_node=$edge->getElementsByTagName('att');
     $correlation=$attr_node->item(1)->getAttribute('value');
     $result.="\tedge\t[\n\t\tid\t".$id."\n\t\ttarget\t".$target."\n\t\tsource\t".$source."\n\t\tgraphics\t[\n\t\t\twidth\t1.0\n\t\t\tfill\t\"#00cc00\"\n\t\t\ttype\t\"line\"\n\t\t\tLine\t[\n\t\t\t]\n\t\t\tsource_arrow\t0\n\t\t\ttarget_arrow \t0\n\t\t]\n\t\tvalue\t".$correlation."\n\t]\n";
}
$result.="]\nTitle\t\"level1\"";
$file =fopen("Science_2010_map.gml","wb");
fwrite($file,$result,strlen($result));
fclose($file);
$t2=  time();
$time=($t2-$t1);
echo 'succeess!  costs time '.$time.' seconds.';
?>
