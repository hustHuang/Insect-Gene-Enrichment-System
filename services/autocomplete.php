<?php
$DB_HOST = '115.156.216.80';
$DB_USER = 'root';
$DB_PASS = 'a1S@fg';
$DB_NAME = 'cellmapb';
$QUERY_SQL = "SELECT g.Feature_Name, g.Standard_Gene_Name FROM gene g";
$SQL_QUERY_PATHWAY = 'SELECT DISTINCT Biochemical_Pathway_Name FROM biochemical_pathway';
$SQL_QUERY_SLIM_MAPPING = 'SELECT DISTINCT GO_Slim_Mapping_Slim_GO_Slim_Term FROM go_slim_mapping';
$SQL_QUERY_COMPLEX_SLIM = 'SELECT DISTINCT GO_Protein_Complex_Slim_Ontology_GO_Term FROM go_protein_complex_slim';
$result_array = array();
$result_data = 'var names = [';
$dbc = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die("Error: Could not connect to MySQL server");
$result = mysqli_query($dbc, $QUERY_SQL);
while ($row = mysqli_fetch_array($result)) {
    $result_data .= '"' . $row[0] . '", ';
    if ($row[1] != null && $row[1] != ''){
        $result_data .= '"' . $row[1] . '", ';
    }
}

mysqli_close($dbc);
$result_data = rtrim($result_data,", ");
$result_data .= ']';
$file = fopen("../inc/js/localdata.js","a");
fwrite($file,$result_data);
fclose($file);
?>
