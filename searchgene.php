<?php

error_reporting(E_ALL ^ E_NOTICE);
require_once './common/DBCxn.php';
require_once './GeneRelation.php';
//require_once 'GeneData.php';
//require_once '../SearchAction.php';
require_once './Relation.php';

//require_once ABSPATH . 'class/Search.class.php';

class searchgene {

    private $view;
    private $type;
    private $query_type;
    private $rvalue;
    //private $query_name;
    //private $disease_name_map;
    private $table_list_data = '';
    private $cw_node_data = '[';
    private $cw_edge_data = '[';
    private $result_count = 0;
    private $m = 0;
    private $n = 0;

    //private $test='co_expression';
    function __construct() {
        
    }

    function set_search_type($view) {
        $this->view = $view;
    }

    function set_search_genereltype($type) {
        $this->type = $type;
    }

    function set_m($m) {
        $this->m = $m;
    }

    function set_n($n) {
        $this->n = $n;
    }

    function set_rvalue($rvalue) {
        $this->rvalue = $rvalue;
    }

    function set_search_params($query_names, $query_type) {
        $this->query_names = $query_names;
        $this->query_type = $query_type;
    }

    function remove_same_name($name_array) {
        $result_array = array();
        foreach ($name_array as $name) {
            $name = trim($name);
            if ($name == '' || $name == ' ')
                continue;
            if (!in_array($name, $result_array)) {
                array_push($result_array, $name);
            } else {
                continue;
            }
        }
        return $result_array;
    }

    function execute_search_t_other() {

        $relation = new GeneRelation();
        if ($this->query_type == 'within') {
            $this->query_names = $this->get_name_from_keywords();
        }
        $keys = array_filter(explode(' ', $this->query_names));
        $keys = $this->remove_same_name($keys);
        //echo json_encode($keys);

        $mix_name_array = array();
        $tmp_array = array();
        $tmp_result_array = array();
        if ($this->query_type == 'with') {
            foreach ($keys as $gene) {
                if ($gene == ' ' || is_null($gene) || strlen($gene) == 0)
                    continue;
                $tmp_array = $relation->getWithCorrelationGene($gene, $this->rvalue);
                $mix_name_array = array_merge($mix_name_array, $tmp_array);
            }
        }else {
            $mix_name_array = $keys;
        }
        $mix_name_array = $this->remove_same_name($mix_name_array);
        $rel_array = array();
        $result_t_array = array();
        $query_result_array = array();
        $limit = 0;
        $count = count($mix_name_array);
        for ($i = $this->m; $i < $count; $i++) {
            //foreach ($mix_name_array as $gene1 ) {

            if ($mix_name_array[$i] == ' ' || is_null($mix_name_array[$i]) || strlen($mix_name_array[$i]) == 0)
                continue;
            //foreach ($mix_name_array as $gene2 ) {
            for ($j = $this->n; $j < $count; $j++) {
                if ($j >= $i + 1) {
                    if ($mix_name_array[$j] == ' ' || is_null($mix_name_array[$j]) || strlen($mix_name_array[$j]) == 0)
                        continue;
                    if ($mix_name_array[$i] == $mix_name_array[$j]) {
                        continue;
                    }

                    $intercorr1 = $relation->getGeneIDBYGeneName($mix_name_array[$i]);

                    $intercorr2 = $relation->getGeneIDBYGeneName($mix_name_array[$j]);
                    //echo "here1";exit;
                    if ($intercorr1 == -1 || $intercorr2 == -1)
                        return;
                    $type1 = 'SC_' . $this->type . '_network';
                    $query = "SELECT adt.gene_a,adt.gene_b,adt.score,nw.network_name,nw.pubmed_id from " . $type1 . " adt,SC_networks_source nw WHERE ((adt.gene_a=$intercorr1 and adt.gene_b=$intercorr2) or (adt.gene_a=$intercorr2 and adt.gene_b=$intercorr1))and nw.id=adt.networkid ";

                    $dbc = DBCxn::get_conn();
                    if ($dbc == null) {
                        return null;
                    }
                    $result = mysqli_query($dbc, $query);

                    if (!$result) {
                        
                    }
                    $tamp_array = array();
                    while ($row = mysqli_fetch_row($result)) {
                        $limit++;
                        $tamp_array['g1Symbol'] = $mix_name_array[$i];
                        $tamp_array['g2Symbol'] = $mix_name_array[$j];
                        $tamp_array['weight'] = $row[2];
                        $tamp_array['type'] = $this->type;
                        $tamp_array['network'] = $row[3];
                        $tamp_array['pubmedid'] = $row[4];
						if($tamp_array!=null&&!in_array($tamp_array,$rel_array))
                        array_push($rel_array, $tamp_array);
                    }                    
                    if ($limit >= 20) {
                        if($rel_array!=null){
                        $this->table_list_data = $rel_array;
                        $this->result_count = count($rel_array);
                        $this->m = $i;
                        $this->n = $j;
                        }
                        return;
                    }
                }
            }
        }
        if ($rel_array != null) {
            $this->table_list_data = $rel_array;
            $this->result_count = count($rel_array);
            $this->m = $i;
            $this->n = $j;
        }
    }

    function get_view_table_data() {
        return $this->table_list_data;
    }

    function get_cw_node_data() {
        return $this->cw_node_data;
    }

    function get_cw_edge_data() {
        return $this->cw_edge_data;
    }

    function get_result_count() {
        return $this->result_count;
    }

    function get_m() {
        return $this->m;
    }

    function get_n() {
        return $this->n;
    }

    function execute_search_t_other_download() {
        $relation1 = new GeneRelation();
        if ($this->query_type == 'within') {
            $this->query_names = $this->get_name_from_keywords();
        }
        $keys = array_filter(explode(' ', $this->query_names));
        $keys = $this->remove_same_name($keys);
        $mix_name_array = array();
        $tmp_array = array();
        $tmp_result_array = array();
        if ($this->query_type == 'with') {
            foreach ($keys as $gene) {
                if ($gene == ' ' || is_null($gene) || strlen($gene) == 0)
                    continue;
                $tmp_array = $relation1->getWithCorrelationGene($gene, $this->rvalue);
                $mix_name_array = array_merge($mix_name_array, $tmp_array);
            }
        }else {
            $mix_name_array = $keys;
        }
        //$mix_name_array=$keys;
        $mix_name_array = $this->remove_same_name($mix_name_array);
        $tamp_array = array();
        $query_result_array = array();
        $count = count($mix_name_array);
        //$relation1 = new GeneRelation();
        for ($i = 0; $i < $count; $i++) {
            //foreach ($mix_name_array as $gene1 ) {

            if ($mix_name_array[$i] == ' ' || is_null($mix_name_array[$i]) || strlen($mix_name_array[$i]) == 0)
                continue;
            //foreach ($mix_name_array as $gene2 ) {
            for ($j = 0; $j < $count; $j++) {
                if ($j >= $i + 1) {
                    if ($mix_name_array[$j] == ' ' || is_null($mix_name_array[$j]) || strlen($mix_name_array[$j]) == 0)
                        continue;
                    if ($mix_name_array[$i] == $mix_name_array[$j]) {
                        continue;
                    }
                    $intercorr1 = $relation1->getGeneIDBYGeneName($mix_name_array[$i]);

                    $intercorr2 = $relation1->getGeneIDBYGeneName($mix_name_array[$j]);
                    //echo "here1";exit;
                    if ($intercorr1 == -1 || $intercorr2 == -1)
                        return;
                    $type1 = 'SC_' . $this->type . '_network';
                    $query = "SELECT adt.gene_a,adt.gene_b,adt.score,nw.network_name,nw.pubmed_id from " . $type1 . " adt,SC_networks_source nw WHERE ((adt.gene_a=$intercorr1 and adt.gene_b=$intercorr2) or (adt.gene_a=$intercorr2 and adt.gene_b=$intercorr1))and nw.id=adt.networkid ";

                    $dbc = DBCxn::get_conn();
                    if ($dbc == null) {
                        return null;
                    }
                    $result = mysqli_query($dbc, $query);

                    if (!$result) {
                        continue;
                    }

                    while ($row = mysqli_fetch_row($result)) {
                        
                        array_push($tamp_array,$mix_name_array[$i]);
                        array_push($tamp_array,$mix_name_array[$j]);
                        array_push($tamp_array,$row[3]);
                        array_push($tamp_array,$this->type);
                        array_push($tamp_array,$row[2]);
                        //array_push($tamp_array,$row[4]);
                        array_push($tamp_array, "||");
                    }
                }
            }
        }
        //echo json_encode($rel_array);
        if ($tamp_array != null)
            return json_encode($tamp_array);
    }

    function get_name_from_keywords() {

        //echo "here";

        function opp1($var) {
            return $var != ' ' && $var != '';
        }

        $this->query_names = trim($this->query_names, " ");
        $original_names = array_filter(explode(' ', $this->query_names), "opp1");
        $result_array = array();
        $tmp_array1 = array();
        $tmp_array2 = array();
        $tmp_array3 = array();
        $tmp_array4 = array();
        $result_names = "";
        $sql = array(
            'SELECT g.feature_name, g.standard_gene_name FROM SC_gene g WHERE g.feature_name = ? OR g.standard_gene_name = ?',
            'SELECT g.feature_name, g.standard_gene_name FROM SC_gene g, SC_annotation_biochemical_pathway b WHERE g.standard_gene_name = b.gene_name AND b.name = ?',
            'SELECT g.feature_name, g.standard_gene_name FROM SC_gene g, SC_annotation_go_protein_complex_slim s WHERE g.feature_name = s.orf AND s.go_term = ?',
            'SELECT g.feature_name, g.standard_gene_name FROM SC_gene g, SC_annotation_go_slim_mapping m WHERE g.standard_gene_name = m.gene_name AND m.term = ?');
        global $global_sga_conn;
        foreach ($original_names as $name) {
            $tmp_result1 = get_array_from_resultset($global_sga_conn->Execute($sql[0], array($name, $name)));
            $tmp_result2 = get_array_from_resultset($global_sga_conn->Execute($sql[1], $name));
            $tmp_result3 = get_array_from_resultset($global_sga_conn->Execute($sql[2], $name));
            $tmp_result4 = get_array_from_resultset($global_sga_conn->Execute($sql[3], $name));
            for ($i = 0; $i < 4; $i++) {
                $var = 'tmp_result' . $i;
                if ($$var != null) {
                    foreach ($$var as $item) {
                        $tmp_name = $item[standard_gene_name] == '' || $item[standard_gene_name] == null ? $item[feature_name] : $item[standard_gene_name];
                        if (!in_array($tmp_name, $result_array)) {
                            array_push($result_array, $tmp_name);
                            $result_names .= $tmp_name . ' ';
                        }
                    }
                }
            }
        }
        return trim($result_names);
    }

}