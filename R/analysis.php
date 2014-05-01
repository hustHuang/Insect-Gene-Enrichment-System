<?php
// poorman.php
 
echo "<form action='analysis.php' method='POST'>";
echo "Gene Names: <input type='text' name='g' />";
echo "Ontology Type: <input type='text' name='t' />";
echo "Cutoff value: <input type='text' name='c' />";
echo "<input type='submit' />";
echo "</form>";
if(isset($_REQUEST['t']) && isset($_REQUEST['c']))
{
  $genes = $_REQUEST['g'];
  $type = $_REQUEST['t'];
  $cutoff = $_REQUEST['c'];
  $result;

  exec("/heap/opt/bin/Rscript analysis.R $genes $type $cutoff", $result);
  //echo "Count of result = " . count($result) . "<br/>";
  
  //foreach($result as $item){
    echo '<br/>-----------------------------------------------------<br/>';
    echo($result[0]);
    echo '<br/>-----------------------------------------------------<br/>';
  //}
}
?>
