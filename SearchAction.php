<?php

set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);
require_once './common.php';
include './common/DBCxn.php';
include 'GeneRelation.php';
include './util/GeneData.php';
include './util/Relation.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchAction
 *
 * @author GGCoke
 */
class SearchAction {

    //put your code here
    private $query_names;
    private $epsilon_neg;
    private $pvalue_neg;
    private $epsilon_pos;
    private $pvalue_pos;
    private $rvalue;
    private $view;
    private $type;
    private $query_type;
    private $tree_user_data = '[{"data": "User submission","attr":{"id":"userquery", "href":"javascript:void(0)"},"children":[';
    private $tree_result_data = '[{"data": "Search results","attr":{"id":"queryresult", "href":"javascript:void(0)"},"children":[';
    private $table_list_data = '';
    private $table_export_data = '';
    private $molecular_interaction_export_data = '';
    private $cw_node_data = '[';
    private $cw_edge_data = '[';
    private $result_count = 0;
    private $gene_name_id_map;
    private $gene_name_name_map;
    private $FUNCTION_DEFINED = FALSE;

    function __construct() {
        
    }

    /**
     * Set search params for the search action.
     * @param type $query_names
     * @param type $epsilon_neg
     * @param type $pvalue_neg
     * @param type $epsilon_pos
     * @param type $pvalue_pos
     * @param type $rvalue 
     * @param type $query_type with or within
     */
    function set_search_params($query_names, $epsilon_neg, $pvalue_neg, $epsilon_pos, $pvalue_pos, $rvalue, $query_type) {
        $this->query_names = $query_names;
        $this->epsilon_neg = $epsilon_neg;
        $this->pvalue_neg = $pvalue_neg;
        $this->epsilon_pos = $epsilon_pos;
        $this->pvalue_pos = $pvalue_pos;
        $this->rvalue = $rvalue;
        $this->query_type = $query_type;
    }

    /**
     * Set search type to get different result.
     * @param type $view tv(Table View) and nv(Network View)
     * @param type $type n(Negative Interaction for Table View), p(Positive Interaction for Table View), c(Correlation for Table View), i(Interaction for Cytoscape Web View) and c(Correlation for Cytoscape Web View).
     */
    function set_search_type($view, $type) {
        $this->view = $view;
        $this->type = $type;
    }

    function remove_same_name($name_array) {
        $result_array = array();
        foreach ($name_array as $name) {
            $name = trim($name);
            if ($name == '' || $name == ' ' || $name == null)
                continue;
            if (!in_array($name, $result_array)) {
                array_push($result_array, $name);
            } else {
                continue;
            }
        }
        return $result_array;
    }

    function execute_search($num) {
        if (!$this->FUNCTION_DEFINED) {

            function opp($var) {
                return $var != ' ' && $var != '';
            }

            $this->FUNCTION_DEFINED = TRUE;
        }
        // If the query type is 'within', then should get gene names from the key words.
        if ($this->query_type == 'within') {
            $this->query_names = $this->get_name_from_keywords();
        }
        $this->tree_user_data = '[{"data": "User submission","attr":{"id":"userquery", "href":"javascript:void(0)"},"children":[';
        if ($this->type == 'p') {
            $signal = ':(#)';
            $pointer = 'Positive';
        } else if ($this->type == 'n') {
            $signal = ':(#)';
            $pointer = 'Negative';
        }
//        else if($this->type == 'c'){
//            $signal='(c)'; 
//        }
        else {
            $signal = '';
            $pointer = 'Search';
        }
        $this->tree_result_data = '[{"data": "'.$pointer.' results '.$signal.'","attr":{"id":"queryresult", "href":"javascript:void(0)"},"children":[';
        $this->cw_node_data = '[';
        $this->cw_edge_data = '[';
        // Replace all separate symbols to space and remove the spaces in beganing and end.
        $mix_name_array = array_filter(explode(' ', $this->query_names), "opp");
        $mix_name_array = $this->remove_same_name($mix_name_array);
        $query_feature_names = array();
        foreach ($mix_name_array as $name) {
            $this->tree_user_data .= ('{"data":{"title":"' . $name . '","attr":{"id":"' . $name . '","href":"javascript:void(0)", "class":"treenode"}}},');
            $feature_name = $this->get_feature_name($name);
            if (!in_array($feature_name, $query_feature_names)) {
                array_push($query_feature_names, $feature_name);
            }
        }
        // Complete getting tree view data of user query.
        $this->tree_user_data = substr($this->tree_user_data, 0, strlen($this->tree_user_data) - 1);
        $this->tree_user_data .= '],"state":"open"}]';
        $query_result_array = array();
        $relation = new GeneRelation();
        $score = 0;
        $p_value = 0;
        switch ($this->view) {
            case 'tv':
                if ($this->type == 'n') {
                    $score = $this->epsilon_neg;
                    $p_value = $this->pvalue_neg;
                } elseif ($this->type == 'p') {
                    $score = $this->epsilon_pos;
                    $p_value = $this->pvalue_pos;
                }

                if ($this->query_type == 'with') {
                    foreach ($mix_name_array as $gene) {
                        if ($gene == ' ' || is_null($gene) || strlen($gene) == 0)
                            continue;
                        if ($this->type == 'n' || $this->type == 'p') {
                            $tmp_result_array = $relation->getWithInteractionData($gene, $this->type, $score, $p_value);
                        } else if ($this->type == 'c') {
                            $tmp_result_array = $relation->getWithCorrelationData($gene, $this->rvalue);
                        } else {
                            
                        }
                        $query_result_array = array_merge($query_result_array, $tmp_result_array);
                    }
                    
                } elseif ($this->query_type == 'within') {
                    foreach ($mix_name_array as $gene1) {
                        if ($gene1 == ' ' || is_null($gene1) || strlen($gene1) == 0)
                            continue;
                        foreach ($mix_name_array as $gene2) {
                            if ($gene2 == ' ' || is_null($gene2) || strlen($gene2) == 0)
                                continue;
                            if ($gene1 == $gene2) {
                                continue;
                            }
                            $rel = null;
                            if ($this->type == 'n' || $this->type == 'p') {
                                $rel = $relation->getWithInInteractionData($gene1, $gene2, $this->type, $score, $p_value);
                            } else if ($this->type == 'c') {
                                $rel = $relation->getWithInCorrelationData($gene1, $gene2, $this->rvalue);
                            } else {
                                
                            }

                            if ($rel == null) {
                                continue;
                            }
                            array_push($query_result_array, $rel);
                        }
                    }
                } else {  // Do nothing
                }
                $this->result_count = count($query_result_array);
                $this->get_table_result_from_query($query_result_array, $this->type, $query_feature_names); 
                break;
            case 'nv':
                if ($this->query_type == 'with') {
                    foreach ($mix_name_array as $gene) {
                        if ($gene == ' ' || is_null($gene) || strlen($gene) == 0)
                            continue;
                        $tmp_result_array = array();

                        if ($this->type == 'i') {
                            $tmp_result_array1 = $relation->getWithInteractionData($gene, 'n', $this->epsilon_neg, $this->pvalue_neg);
                            $tmp_result_array2 = $relation->getWithInteractionData($gene, 'p', $this->epsilon_pos, $this->pvalue_pos);
                            $tmp_result_array = array_merge($tmp_result_array1, $tmp_result_array2);
                            $query_result_array = array_merge($query_result_array, $tmp_result_array);
                        } else {
                            $tmp_result_array = $relation->getWithCorrelationData($gene, $this->rvalue);
                            $query_result_array = array_merge($query_result_array, $tmp_result_array);
                        }
                    }
                    $this->get_network_result_from_query($query_result_array, $this->type, $query_feature_names, $num);
                } elseif ($this->query_type == 'within') {
                    foreach ($mix_name_array as $gene1) {
                        if ($gene1 == ' ' || is_null($gene1) || strlen($gene1) == 0)
                            continue;
                        foreach ($mix_name_array as $gene2) {
                            if ($gene2 == ' ' || is_null($gene2) || strlen($gene2) == 0)
                                continue;
                            if ($gene1 == $gene2) {
                                continue;
                            }
                            $rel = null;
                            $rel2 = null;
                            if ($this->type == 'i') {
                                $rel = $relation->getWithInInteractionData($gene1, $gene2, 'p', $this->epsilon_pos, $this->pvalue_pos);
                                $rel2 = $relation->getWithInInteractionData($gene1, $gene2, 'n', $this->epsilon_neg, $this->pvalue_neg);
                            } elseif ($this->type == 'c') {
                                $rel = $relation->getWithInCorrelationData($gene1, $gene2, $this->rvalue);
                            }
                            if ($rel != null) {
                                array_push($query_result_array, $rel);
                            }
                            if ($rel2 != null) {
                                array_push($query_result_array, $rel2);
                            }
                        }
                    }

                    $this->get_network_result_from_query($query_result_array, $this->type, $query_feature_names, $num);
                } else {
                    // Do nothing
                }
                break;
            default: {
                    
                }
                break;
        }
    }

    function get_feature_name($name) {
        $query = 'SELECT g.feature_name FROM SC_gene g WHERE g.feature_name = "' . $name . '" OR g.standard_gene_name = "' . $name . '"';
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            return "";
        }

        $result = mysqli_query($dbc, $query);
        if (!$result)
            return "";
        if ($row = mysqli_fetch_row($result)) {
            return $row[0];
        }

        return "";
    }

    function get_standard_gene_name($name) {
        $query = 'SELECT g.feature_name,g.standard_gene_name  FROM SC_gene g WHERE g.feature_name = "' . $name . '" OR g.standard_gene_name = "' . $name . '"';
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            return "";
        }

        $result = mysqli_query($dbc, $query);
        if (!$result)
            return $name;
        if ($row = mysqli_fetch_row($result)) {
            $key = $row[1] == '' || $row[1] == null ? $row[0] : $row[1];
            return $key;
        }
        return $name;
    }

    /**
     * Get tree view data of user's query as json.
     * @return type Tree view of user query as json.
     */
    function get_tree_user_data() {
        return $this->tree_user_data;
    }

    /**
     * Get tree view data of query result as json.
     * @return type Tree view of query result as json.
     */
    function get_tree_result_data() {
        return $this->tree_result_data;
    }

    /**
     * Get table view data of Negative Interaction, Positive Interaction or Correlation as json.
     * @return type Table data as json.
     */
    function get_view_table_data() {
        return $this->table_list_data;
    }

    function get_table_export_data() {
        return $this->table_export_data;
    }

    function get_molecular_interaction_export_data() {
        return $this->molecular_interaction_export_data;
    }

    /**
     * Get node data of Cytoscape Web view as json.
     * @return type Node data of  Cytoscape Web as json
     */
    function get_cw_node_data() {
        return $this->cw_node_data;
    }

    /**
     * Get edge data of Cytoscape Web view as json.
     * @return type Edge data of  Cytoscape Web as json
     */
    function get_cw_edge_data() {
        return $this->cw_edge_data;
    }

    /**
     * Get the count of query result.
     * @return int Count of result.
     */
    function get_result_count() {
        return $this->result_count;
    }

    /**
     * Get gene name by the queried key words.
     * @return type string Return feature name if the gene has no standard gene name, or return the standard gene name.
     *  Return NULL if errors.
     */
    function get_name_from_keywords() {

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
            'SELECT g.feature_name, g.standard_gene_name FROM SC_gene g, SC_annotation_go_protein_complex_slim s WHERE g.feature_name = s.feature_name AND s.go_term = ?',
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

    function get_table_result_from_query($query_result_array, $type, $query_feature_names) {
        $result_array = array();
        $query_nodes = array();
        $result_array1 = array();
        foreach ($query_result_array as $relation) {
            $tmp_array = array();
            $tmp_array['g1Feature'] = $relation->gene1_feature_name;
            $tmp_array['g1Name'] = $relation->gene1_standard_gene_name;
            $tmp_array['g1Alias'] = $relation->gene1_alias;
            $tmp_array['g1Desc'] = $relation->gene1_description;
            $tmp_array['g2Feature'] = $relation->gene2_feature_name;
            $tmp_array['g2Name'] = $relation->gene2_standard_gene_name;
            $tmp_array['g2Alias'] = $relation->gene2_alias;
            $tmp_array['g2Desc'] = $relation->gene2_description;
            $tmp_array['s1Strain'] = $relation->strain1_name;
            $tmp_array['s2Strain'] = $relation->strain2_name;
            $tmp_array['s1Score'] = $relation->strain1_score;
            $tmp_array['s2Score'] = $relation->strain2_score;
            $tmp_array['score'] = $relation->score;
            $tmp_array['dataset'] = $relation->dataset;
            if ($type == 'n' || $type == 'p') {
                $tmp_array['pValue'] = $relation->p_value;
            }
            array_push($result_array1, $tmp_array['g1Feature'] . '(' . $tmp_array['g1Name'] . ')');
            array_push($result_array1, $tmp_array['g2Feature'] . '(' . $tmp_array['g2Name'] . ')');
            array_push($result_array1, $tmp_array['score']);
            if ($type != 'c') {
                array_push($result_array1, $tmp_array['pValue']);
            }
            array_push($result_array1, "||");
            array_push($result_array, $tmp_array);
            $node1 = $relation->gene1_standard_gene_name == '' || $relation->gene1_standard_gene_name == null ? $relation->gene1_feature_name : $relation->gene1_standard_gene_name;
            $node2 = $relation->gene2_standard_gene_name == '' || $relation->gene2_standard_gene_name == null ? $relation->gene2_feature_name : $relation->gene2_standard_gene_name;

            if (!in_array($relation->gene1_feature_name, $query_feature_names)) {
                if (key_exists($node1, $query_nodes)) {
                    $query_nodes[$node1]++;
                } else {
                    $query_nodes[$node1] = 1;
                }
            }
            if (!in_array($relation->gene2_feature_name, $query_feature_names)) {
                if (key_exists($node2, $query_nodes)) {
                    $query_nodes[$node2]++;
                } else {
                    $query_nodes[$node2] = 1;
                }
            }
        }

        $this->table_list_data = json_encode($result_array);
        $this->table_export_data = json_encode($result_array1);
        arsort($query_nodes);
        foreach ($query_nodes as $key => $value) {
            $this->tree_result_data .= ('{"data":{"title":"' . $key . '","attr":{"id":"' . $key . '","href":"javascript:void(0)", "class":"treenode"}}},');
        }

        if (count($query_nodes) != 0) {
            $this->tree_result_data = substr($this->tree_result_data, 0, strlen($this->tree_result_data) - 1);
        }
        $this->tree_result_data .= '],"state":"open"}]';
        //$this->result_count = count($query_result_array);
        $this->tree_result_data = str_replace( "(#)" , $this->result_count, $this->tree_result_data);
    }

    function get_network_result_from_query($query_result_array, $type, $query_feature_names, $num_limit) {
        $query_relation_nodes = array();
        $result_relation_nodes = array();
        $all_relation_edge_array = array();

        $this->gene_name_id_map = $this->get_gene_id_map();
        $this->gene_name_name_map = $this->get_gene_name_name_map();
        foreach ($query_result_array as $relation) {
            $feature_name1 = $relation->gene1_feature_name;
            $feature_name2 = $relation->gene2_feature_name;
            $score = '';
            if ($type == 'i') {
                $score = $relation->score . '_' . $relation->p_value;
            } else {
                $score = $relation->score . '_0';
            }
            $node1 = $relation->gene1_standard_gene_name == '' || $relation->gene1_standard_gene_name == null ? $relation->gene1_feature_name : $relation->gene1_standard_gene_name;
            $node2 = $relation->gene2_standard_gene_name == '' || $relation->gene2_standard_gene_name == null ? $relation->gene2_feature_name : $relation->gene2_standard_gene_name;
            $id = ($node1 . '_' . $node2);
            $all_relation_edge_array[$id] = $score;
            if (in_array($feature_name1, $query_feature_names)) {
                if (key_exists($node1, $query_relation_nodes)) {
                    $query_relation_nodes[$node1]++;
                } else {
                    $query_relation_nodes[$node1] = 1;
                }
            } else {
                if (key_exists($node1, $result_relation_nodes)) {
                    $result_relation_nodes[$node1]++;
                } else {
                    $result_relation_nodes[$node1] = 1;
                }
            }

            if (in_array($feature_name2, $query_feature_names)) {
                if (key_exists($node2, $query_relation_nodes)) {
                    $query_relation_nodes[$node2]++;
                } else {
                    $query_relation_nodes[$node2] = 1;
                }
            } else {
                if (key_exists($node2, $result_relation_nodes)) {
                    $result_relation_nodes[$node2]++;
                } else {
                    $result_relation_nodes[$node2] = 1;
                }
            }
        }

        $cutoff_node_array = array();
        arsort($query_relation_nodes);
        foreach ($query_relation_nodes as $key => $value) {
            $cutoff_node_array[$key] = $value;
            $this->cw_node_data .= ('{id:"' . $key . '", count:' . $value . ',ngc:"q"},');
        }

        foreach ($query_feature_names as $feature_name) {
            $tmp_name = array_search($feature_name, $this->gene_name_name_map);
            if (!key_exists($tmp_name, $query_relation_nodes)) {
                $cutoff_node_array[$tmp_name] = 1;
                $this->cw_node_data .= ('{id:"' . $tmp_name . '", count:1,ngc:"q"},');
            }
        }

        arsort($result_relation_nodes);
        $tmp_count = 0;
        $result_node_array = array();
        foreach ($result_relation_nodes as $key => $value) {
            $tmp_count++;
            if ($tmp_count > $num_limit)
                break;
            $cutoff_node_array[$key] = $value;
            $result_node_array[$key] = $value;
            $this->cw_node_data .= ('{id:"' . $key . '", count:' . $value . ',ngc:"r"},');
            $this->tree_result_data .= ('{"data":{"title":"' . $key . '","attr":{"id":"' . $key . '","href":"javascript:void(0)", "class":"treenode"}}},');
        }

        $edges_exist = array();
        foreach ($all_relation_edge_array as $key => $value) {
            $node_ids = explode('_', $key);
            $values = explode('_', $value);
            $tmp_key = $node_ids[1] . '_' . $node_ids[0];
            if (in_array($key, $edges_exist) || in_array($tmp_key, $edges_exist)) {
                continue;
            }
            array_push($edges_exist, $key);
            // $edge_type = $this->type == 'i' ? "ai" : "c";
            $edge_type = $this->type == 'i' ? ($values[0] > 0 ? "pai" : "nai") : "c";
            
            if ((array_key_exists($node_ids[0], $cutoff_node_array)) && (array_key_exists($node_ids[1], $cutoff_node_array))) {
                $this->cw_edge_data .= ('{id:"' . ($key . "_" . $edge_type) . '", distance:' . $values[0] . ',pvalue:' . $values[1] . ',egc:"' . $edge_type . '",target:"' . $node_ids[1] . '",source:"' . $node_ids[0] . '"},');
            }
        }

        //if ($this->query_type == 'with'||($this->query_type == 'within'&&$type=='i')) {
        $this->get_child_relation($result_node_array, "ai");
        $this->get_child_relation($cutoff_node_array, "coexp");
        $this->get_child_relation($cutoff_node_array, "coloc");
        $this->get_child_relation($cutoff_node_array, "pi");
        $this->get_child_relation($cutoff_node_array, "spd");
        // }else{
        if ($type == 'c') {
            $this->get_child_relation($cutoff_node_array, "c");
        }
        // }

        if (count($query_result_array) != 0) {
            $this->cw_node_data = substr($this->cw_node_data, 0, strlen($this->cw_node_data) - 1);
            $this->cw_edge_data = substr($this->cw_edge_data, 0, strlen($this->cw_edge_data) - 1);
        }
        if (strrpos($this->tree_result_data, '[') != (strlen($this->tree_result_data) - 1)) {
            $this->tree_result_data = substr($this->tree_result_data, 0, strlen($this->tree_result_data) - 1);
        }

        $this->cw_node_data .= ']';
        $this->cw_edge_data .= ']';
        $this->tree_result_data .= '],"state":"open"}]';
    }

    function init_gene_names($query_names) {

        function opp2($var) {
            return $var != ' ' && $var != '';
        }

        $result = "";
        $gene_within_names = array();
        $query_names = trim(str_replace(',', ' ', $query_names));
        $mix_name_array = array_filter(explode(' ', $query_names), "opp2");
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            return $query_names;
        }

        $query = 'SELECT g.feature_name, g.standard_gene_name FROM SC_gene g WHERE g.feature_name = ? OR g.standard_gene_name = ?';
        if ($stmt = mysqli_prepare($dbc, $query)) {
            mysqli_bind_param($stmt, "ss", $gene, $gene);
            foreach ($mix_name_array as $gene) {
                mysqli_stmt_execute($stmt);
                $col = array();
                mysqli_stmt_bind_result($stmt, $col[0], $col[1]);
                $tmp_name = $col[1] == '' || $col[1] == null ? $col[0] : $col[1];
                if (!in_array($tmp_name, $gene_within_names)) {
                    array_push($gene_within_names, $tmp_name);
                    $result .= $tmp_name . ",";
                }
            }

            return $result;
        }

        return $query_names;
    }

    function get_child_relation($result_node_array, $type) {
        $relation = new GeneRelation();
        $query_result_array = array();
        foreach ($result_node_array as $gene1 => $value1) {
            foreach ($result_node_array as $gene2 => $value2) {
                if ($gene1 == $gene2) {
                    continue;
                }
                $rel = null;
                $rel2 = null;
                if ($type == 'ai') {
                    if ($this->type == 'i') {
                        $rel = $relation->getWithInInteractionData($gene1, $gene2, 'p', $this->epsilon_pos, $this->pvalue_pos);
                        $rel2 = $relation->getWithInInteractionData($gene1, $gene2, 'n', $this->epsilon_neg, $this->pvalue_neg);
                    } elseif ($this->type == 'c') {
                        $rel = $relation->getWithInCorrelationData($gene1, $gene2, $this->rvalue);
                    }
                } else {
                    $gene1_id = $this->gene_name_id_map[$this->gene_name_name_map[$gene1]];
                    $gene2_id = $this->gene_name_id_map[$this->gene_name_name_map[$gene2]];
                    $rel = $relation->getAdditionalRelation($gene1_id, $gene2_id, $gene1, $gene2, $type);
                }

                if ($rel != null) {
                    array_push($query_result_array, $rel);
                }
                if ($rel2 != null) {
                    array_push($query_result_array, $rel2);
                }
            }
        }

        $edges_exist = array();
        foreach ($query_result_array as $value) {
            $node1 = $value->gene1_standard_gene_name == '' || $value->gene1_standard_gene_name == null ? $value->gene1_feature_name : $value->gene1_standard_gene_name;
            $node2 = $value->gene2_standard_gene_name == '' || $value->gene2_standard_gene_name == null ? $value->gene2_feature_name : $value->gene2_standard_gene_name;
            $key = $node1 . '_' . $node2;
            $tmp_key = $node2 . '_' . $node1;
            if (in_array($key, $edges_exist) || in_array($tmp_key, $edges_exist)) {
                continue;
            }
            array_push($edges_exist, $key);
            if ($this->type == 'i') {
                $i_type = $type == 'ai' ? ($value->score > 0 ? 'pai' : 'nai') : $type;
                $this->cw_edge_data .= ('{id:"' . ($key . "_" . $i_type) . '", distance:' . $value->score . ',pvalue:' . $value->p_value . ',egc:"' . $i_type . '",target:"' . $node2 . '",source:"' . $node1 . '"},');
            } elseif ($this->type == 'c') {
                if ($type != 'ai') {
                    $this->cw_edge_data .= ('{id:"' . ($key . "_" . $type) . '", distance:' . $value->score . ',pvalue:' . $value->p_value . ',egc:"' . $type . '",target:"' . $node2 . '",source:"' . $node1 . '"},');
                }
                else
                    $this->cw_edge_data .= ('{id:"' . ($key . "_c") . '", distance:' . $value->score . ',pvalue: 0,egc:"c",target:"' . $node2 . '",source:"' . $node1 . '"},');
            }
        }
    }

    function get_gene_name_name_map() {
        $gene_name_name_map = array();
        $query = "SELECT g.feature_name, g.standard_gene_name FROM SC_gene g";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }

        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            $key = $row[1] == '' || $row[1] == null ? $row[0] : $row[1];
            $value = $row[0];
            if (!array_key_exists($key, $gene_name_name_map)) {
                $gene_name_name_map[$key] = $value;
            }
        }

        return $gene_name_name_map;
    }

    function get_gene_id_map() {
        $gene_id_name_map = array();
        $query = "SELECT g.id, g.feature_name FROM SC_gene g";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }

        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            if (!array_key_exists($row[1], $gene_id_name_map)) {
                $gene_id_name_map[$row[1]] = $row[0];
            }
        }
        return $gene_id_name_map;
    }

    function get_interaction_score($query, $array) {
//      $query_id = $this->get_geneId_by_featureName(trim($query));
//      $array_id = $this->get_geneId_by_featureName(trim($array));
        $generelation = new GeneRelation();
        $query_id = $generelation->getStrainUniqueID($query);
        $array_id = $generelation->getStrainUniqueID($array);
        //$query = "SELECT i.score FROM SC_gi_interaction i WHERE  ((i.query =$query_id AND i.array =$array_id) OR (i.query =$array_id AND i.array =$query_id))";
        $query = "SELECT i.score FROM SC_gi_interaction i WHERE i.query =$query_id AND i.array =$array_id";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_array($result);
        if (!is_null($row)) {
            $score = $row[0];
        }
        return $score;
    }

    function get_geneId_by_featureName($name) {
        $query = "SELECT g.id FROM SC_gene g where g.feature_name='$name'";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_array($result);
        if (!is_null($row)) {
            $gene_id = $row[0];
        }
        return $gene_id;
    }

    /**
     * Get info of the clicked target, node or edge.
     * @param type $type    Interaction or Correlation
     * @param type $group   Nodes or Edges.
     * @param type $id      Id of the clicked target.
     * @return type JSON data of the clicked target.
     */
    function get_click_target_info($type, $group, $id, $score_neg = -0.08, $score_pos = 0.08, $p_neg = 0.05, $p_pos = 0.05, $rvalue = 'significant') {

        function opp3($var) {
            return $var != ' ' && $var != '';
        }

        $result_array = array();
        $col = array();
        $dbc = DBCxn::get_conn();
        $query = "";
        if ($group == 'nodes') {
            $query = "SELECT g.primary_sgdid, g.feature_name, g.standard_gene_name, g.alias, g.parent_feature_name, g.secondary_sgdid, g.chromosome, g.start_coordinate, g.stop_coordinate, g.strand, g.genetic_position, g.coordinate_version FROM SC_gene g WHERE g.feature_name = ? OR g.standard_gene_name = ?";
            if ($stmt = mysqli_prepare($dbc, $query)) {
                mysqli_stmt_bind_param($stmt, "ss", $id, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $count = mysqli_stmt_num_rows($stmt);
                if ($count > 0) {
                    mysqli_stmt_bind_result($stmt, $result_array['Primary_SGDID'], $result_array['Feature_Name'], $result_array['Standard_Gene_Name'], $result_array['Alias'], $result_array['Parent_Feature_Name'], $result_array['Secondary_SGDID'], $result_array['Chromosome'], $result_array['Start_Coordinate'], $result_array['Stop_Coordinate'], $result_array['Strand'], $result_array['Genetic_Position'], $result_array['Coordinate_Version']);
                    if (mysqli_stmt_fetch($stmt)) {
                        foreach ($result_array as $key => $value) {
                            $result_array[$key] = addslashes($value);
                        }
                    }
                }
            }
        } elseif ($group == 'edges') {
            $table_map = array("coexp" => "SC_co_expression_network",
                "coloc" => "SC_co_localization_network",
                "pi" => "SC_physical_interaction_network",
                "spd" => "SC_shared_protein_domains_network");
            $genes = array_filter(explode('_', $id), "opp3");
            $edge_type = $genes[2];
            $relation = new GeneRelation();
            switch ($edge_type) {
                case 'pai':
                case 'nai':
                    $strain1_list = $relation->getStrainList($genes[0]);
                    $strain2_list = $relation->getStrainList($genes[1]);
                    if (is_null($strain1_list) || is_null($strain2_list) || count($strain1_list) == 0 || count($strain2_list) == 0)
                        break;
                    foreach ($strain1_list as $strain1) {
                        foreach ($strain2_list as $strain2) {
                            $strain1_name = $relation->getStrainNameByStrainID($strain1);
                            $strain2_name = $relation->getStrainNameByStrainID($strain2);
                            $query = "SELECT i.score, i.p_value FROM SC_gi_interaction i WHERE i.query = " . $strain1 . " AND i.array = " . $strain2 . " AND ((i.score < " . $score_neg . " AND i.p_value <= " . $p_neg . ") OR (i.score > " . $score_pos . " AND i.p_value <= " . $p_pos . "))";
                            $result = mysqli_query($dbc, $query);
                            while ($row = mysqli_fetch_row($result)) {
                                $tmp_array['gene1'] = $genes[0];
                                $tmp_array['gene2'] = $genes[1];
                                $tmp_array['strain1'] = $strain1_name;
                                $tmp_array['strain2'] = $strain2_name;
                                $tmp_array['weight'] = $this->format_p_value($row[0]);
                                $tmp_array['pvalue'] = $this->format_p_value($row[1]);
                                array_push($result_array, $tmp_array);
                            }
                            $query = "SELECT i.score, i.p_value FROM SC_gi_interaction i WHERE i.query = " . $strain2 . " AND i.array = " . $strain1 . " AND ((i.score < " . $score_neg . " AND i.p_value <= " . $p_neg . ") OR (i.score > " . $score_pos . " AND i.p_value <= " . $p_pos . "))";
                            $result = mysqli_query($dbc, $query);
                            while ($row = mysqli_fetch_row($result)) {
                                $tmp_array['gene1'] = $genes[0];
                                $tmp_array['gene2'] = $genes[1];
                                $tmp_array['strain1'] = $strain1_name;
                                $tmp_array['strain2'] = $strain2_name;
                                $tmp_array['weight'] = $this->format_p_value($row[0]);
                                $tmp_array['pvalue'] = $this->format_p_value($row[1]);
                                array_push($result_array, $tmp_array);
                            }
                        }
                    }

                    break;
                case 'c':
                    $strain1_list = $relation->getStrainList($genes[0]);
                    $strain2_list = $relation->getStrainList($genes[1]);
                    if (is_null($strain1_list) || is_null($strain2_list) || count($strain1_list) == 0 || count($strain2_list) == 0)
                        break;
                    foreach ($strain1_list as $strain1) {
                        $query = '';
                        foreach ($strain2_list as $strain2) {
                            if ($rvalue == 'significant') {
                                $query = "SELECT c.score FROM SC_gi_correlation c WHERE c.strain1 = " . $strain1 . " AND c.strain2 = " . $strain2 . " AND (c.score > 0.1 OR c.score < -0.1)";
                            } else {
                                $query = "SELECT c.score FROM SC_gi_correlation c WHERE c.strain1 = " . $strain1 . " AND c.strain2 = " . $strain2;
                            }

                            $result = mysqli_query($dbc, $query);
                            while ($row = mysqli_fetch_row($result)) {
                                $tmp_array['gene1'] = $genes[0];
                                $tmp_array['gene2'] = $genes[1];
                                $tmp_array['weight'] = $this->format_p_value($row[0]);
                                array_push($result_array, $tmp_array);
                            }
                            if ($rvalue == 'significant') {
                                $query = "SELECT c.score FROM SC_gi_correlation c WHERE c.strain1 = " . $strain2 . " AND c.strain2 = " . $strain1 . " AND (c.score > 0.1 OR c.score < -0.1)";
                            } else {
                                $query = "SELECT c.score FROM SC_gi_correlation c WHERE c.strain1 = " . $strain2 . " AND c.strain2 = " . $strain1;
                            }
                            $result = mysqli_query($dbc, $query);
                            while ($row = mysqli_fetch_row($result)) {
                                $tmp_array['gene1'] = $genes[0];
                                $tmp_array['gene2'] = $genes[1];
                                $tmp_array['weight'] = $this->format_p_value($row[0]);
                                array_push($result_array, $tmp_array);
                            }
                        }
                    }
                    break;
                default:
                    $tmp_array = array();
                    $gene1_id = $relation->getGeneIDByGeneName($genes[0]);
                    $gene2_id = $relation->getGeneIDByGeneName($genes[1]);
                    $query = "SELECT adt.score, nw.network_name, nw.pubmed_id FROM " . $table_map[$edge_type] . " adt, SC_networks_source nw WHERE nw.id = adt.networkid AND adt.gene_a = " . $gene1_id . " AND adt.gene_b = " . $gene2_id;
                    $result = mysqli_query($dbc, $query);
                    while ($row = mysqli_fetch_row($result)) {
                        $tmp_array['gene1'] = $genes[0];
                        $tmp_array['gene2'] = $genes[1];
                        $tmp_array['weight'] = $this->format_p_value($row[0]);
                        $tmp_array['network'] = $row[1];
                        $tmp_array['pubmed'] = $row[2];
                        if (!in_array($tmp_array, $result_array))
                        array_push($result_array, $tmp_array);
                    }

                    $query = "SELECT adt.score, nw.network_name, nw.pubmed_id FROM " . $table_map[$edge_type] . " adt, SC_networks_source nw WHERE nw.id = adt.networkid AND adt.gene_a = " . $gene2_id . " AND adt.gene_b = " . $gene1_id;
                    $result = mysqli_query($dbc, $query);
                    while ($row = mysqli_fetch_row($result)) {
                        $tmp_array['gene1'] = $genes[1];
                        $tmp_array['gene2'] = $genes[0];
                        $tmp_array['weight'] = $this->format_p_value($row[0]);
                        $tmp_array['network'] = $row[1];
                        $tmp_array['pubmed'] = $row[2];
                        if (!in_array($tmp_array, $result_array))
                            array_push($result_array, $tmp_array);
                    }
                    break;
            }
        } elseif ($group == 'table') {
            $query = "SELECT g.feature_name, g.description FROM SC_gene g WHERE g.feature_name = ? OR g.standard_gene_name = ?";
            if ($stmt = mysqli_prepare($dbc, $query)) {
                mysqli_stmt_bind_param($stmt, "ss", $id, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $count = mysqli_stmt_num_rows($stmt);
                if ($count > 0) {
                    mysqli_stmt_bind_result($stmt, $result_array['Feature'], $result_array['Description']);
                    if (mysqli_stmt_fetch($stmt)) {
                        foreach ($result_array as $key => $value) {
                            $result_array[$key] = addslashes($value);
                        }
                    }
                }
            }
        }

        return json_encode($result_array);
    }

    function format_p_value($p_value) {
        $p_value = str_replace("E", "e", $p_value);
        $tmp_p_value = explode('e', strval($p_value));
        return number_format($tmp_p_value[0], 3) . (count($tmp_p_value) == 2 ? 'E' . $tmp_p_value[1] : '');
    }

}
