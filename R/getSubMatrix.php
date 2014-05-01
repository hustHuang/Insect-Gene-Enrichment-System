<?php


//exec("/heap/opt/bin/Rscript heatmap.R", $result); 

exec("/heap/opt/bin/Rscript getsubmatrix.R", $result); 
echo var_dump($result);
echo '<br/>-----------------------------------------------------<br/>';
echo($result[0]);
echo '<br/>-----------------------------------------------------<br/>';

?>
