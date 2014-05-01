<?php
set_time_limit(0);
require_once './common.php';
$genes = $_POST['gene'];


    global $global_sga_conn;
    $sql = 'SELECT g.feature_name FROM SC_gene g WHERE g.feature_name = ? OR g.standard_gene_name = ?';
    $items = explode(',', $genes);
    $feature_names = '';
    foreach ($items as $gene) {
        $feature_name = $global_sga_conn->GetOne($sql, array($gene, $gene));
        $feature_names .= ($feature_name . ',');
    }
    $feature_names = rtrim($feature_names, ',');
echo $feature_names;

