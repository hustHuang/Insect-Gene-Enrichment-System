<?php
set_time_limit(0);
require_once './common.php';
$genes = $_POST['gene'];
    global $global_sga_conn;
    $sql = 'SELECT g.feature_name FROM SC_gene g WHERE g.feature_name = ? OR g.standard_gene_name = ?';
    $query = 'SELECT hs_gene_id FROM `SCgene2HSgene` h where h.sc_gene_name=?';
    $items = explode(',', $genes);
    $feature_names = '';
    $names='';
    $geneid = '';
    $geneid_arr = array();
    $geneidlist = "";
    $feature_name_a=array();
    foreach ($items as $gene) {
        $feature_name = $global_sga_conn->GetOne($sql, array($gene, $gene));
	if (!in_array($feature_name, $feature_name_a)) {
                   array_push($feature_name_a, $feature_name);
                    $names.=($feature_name. ',');
               }
        $geneid = $global_sga_conn->GetOne($query, array($feature_name));
        if ($geneid !== NULL) {
            $geneids = explode("|", $geneid);
            foreach ($geneids as $id) {
                if (!in_array($id, $geneid_arr)) {
                    array_push($geneid_arr, $id);
                    $feature_names.=($id . ',');
                }
            }
        }
    }
    $names = rtrim($names, ',');
    $feature_names = rtrim($feature_names, ',');
    $feature_names=$feature_names."|".$genes;
    echo $feature_names;

?>

