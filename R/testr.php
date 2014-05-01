<?php
// poorman.php
 
echo "<form action='testr.php' method='get'>";
echo "Number 1: <input type='text' name='N1' />";
echo "Number 2: <input type='text' name='N2' />";
echo "<input type='submit' />";
echo "</form>";
if(isset($_REQUEST['N1']) && isset($_REQUEST['N2']))
{
  $N1 = $_REQUEST['N1'];
  $N2 = $_REQUEST['N2'];
  $result = 0;
  // execute R script from shell
  // this will save a plot at temp.png to the filesystem
  exec("/heap/opt/bin/Rscript my_rscript.R $N1 $N2", $result);
 
  // return image tag
//  $nocache = rand();
//  echo("<img src='temp.png?$nocache' />");
  echo "Result = " . $result[0] . '<br/>';
}
?>