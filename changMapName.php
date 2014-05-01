<?php
/*
 * 
 */
include './SearchAction.php';
$searchaction = new SearchAction();
$names = array("s_cols_back", "s_rows_back", "t_cols_back", "t_rows_back");
foreach ($names as $name) {
    $contents = '';
    $file = file("data/" . $name . ".txt");
    foreach ($file as &$line) {
        $genes = explode("_", trim($line));
        $standardName = $searchaction->get_standard_gene_name(trim($genes[0]));
        $strain = $genes[1];
        $contents.=$standardName . "_" . $strain . "\r\n";
    }
    //echo $contents;
    $outputFile = fopen('data/' . $name . '_1.txt', 'wb');
    fwrite($outputFile, $contents);
    fclose($outputFile);
}
?>
