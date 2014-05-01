<html>
<body>
<h1>It works!</h1>
<?php 
//$result = system("Rscript C:/Users/cyg_server/Desktop/R/heatmap.R", $result1);
exec("/heap/opt/bin/Rscript heatmap.R", $result); 
echo var_dump($result);
echo '<br/>-----------------------------------------------------<br/>';
echo($result[0]);
echo '<br/>-----------------------------------------------------<br/>';
?>
</body>
</html>