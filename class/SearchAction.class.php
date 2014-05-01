<?php

/**
 * @author GGCoke
 * 2012-5-31 15:18:07
 */
class SearchActionClass {

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
    private $cw_node_data = '[';
    private $cw_edge_data = '[';
    private $result_count = 0;
    private $gene_name_id_map;
    private $gene_name_name_map;

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

    /**
     * Clear user's queries, including remove epactal blanks, repetitive queries and so on.
     * @param type $query_names
     * @return Array result of cleaning, return empty array if query is null.
     */
    function get_query_names_as_array($query_names) {
        $result = array();
        if (is_null($query_names))
            return $result;

        // STRING_SEPARATOR
        $items = explode(STRING_SEPARATOR, $query_names);
        foreach ($items as $item) {
            if ((!($item == ' ')) && (!($item == '')) && (!in_array($item, $result))) {
                array_push($result, $item);
            }
        }

        return $result;
    }

    /**
     * Get feature name of gene from feature name, standard gene name or other keywords.
     * @param type $array_query_names
     * @return array 
     */
    function get_gene_name_from_keywords($array_query_names, $query_type) {
        $array_result = array();
        if (is_null($array_query_names) || count($array_query_names) == 0) return $array_result;
        
        global $global_sga_conn;
        if ($query_type == 'with'){
            $sql = 'SELECT feature_name FROM gene WHERE feature_name = ? OR standard_gene_name = ?';
            foreach ($array_query_names as $gene)
            $result = $global_sga_conn->GetOne($sql, array($gene, $gene));
            if (!is_null($result) && $result != ''){
                array_push($array_result, $result);
            }
        }
        return $array_result;
    }

    function execute_search($num) {
        $clean_query_names = $this->get_query_names_as_array($this->query_names);
        $feature_names = $this->get_gene_name_from_keywords($clean_query_names, $this->query_type);
        // Tree view query
        $tmp_string = $this->tree_user_data;
        foreach ($feature_names as $name) {
            $this->tree_user_data .= ('{"data":{"title":"' . $name . '","attr":{"id":"' . $name . '","href":"javascript:void(0)", "class":"treenode"}}},');
        }
        $this->tree_user_data = ($this->tree_user_data == $tmp_string) ? $this->tree_user_data : substr($this->tree_user_data, 0, strlen($this->tree_user_data) - 1);
        $this->tree_user_data .= '],"state":"open"}]';

        $score = 0;
        $p_value = 0;
        $query_result_array = array();

        switch ($this->view) {
            case 'tv': {
                    if ($this->type == 'n') {
                        $score = $this->epsilon_neg;
                        $p_value = $this->pvalue_neg;
                    } elseif ($this->type == 'p') {
                        $score = $this->epsilon_pos;
                        $p_value = $this->pvalue_pos;
                    }

                    if ($this->query_type == 'with') {
                        foreach ($feature_names as $gene) {
                            if ($gene == ' ' || is_null($gene) || strlen($gene) == 0) continue;
                            $tmp_result_array = array();
                            if ($this->type == 'n' || $this->type == 'p') {
                                $tmp_result_array = $this->get_interaction_result_with($gene, $this->type, $score, $p_value);
                            } else {
                                $tmp_result_array = $this->get_interaction_result_within($gene, $this->rvalue);
                            }
                            if (count($tmp_result_array) > 0) {
                                $query_result_array = array_merge($query_result_array, $tmp_result_array);
                            }
                            unset($tmp_result_array);
                        }
                    } elseif ($this->query_type == 'within') {
                        foreach ($feature_names as $gene1) {
                            if ($gene1 == ' ' || is_null(1) || strlen($gene1) == 0) continue;
                            foreach ($feature_names as $gene2) {
                                if ($gene2 == ' ' || is_null($gene2) || strlen($gene2) == 0) continue;
                                if ($gene1 == $gene2) continue;

                                $tmp_result_array = array();
                                if ($this->type == 'n' || $this->type == 'p') {
                                    $tmp_result_array = $this->get_interaction_result_within($gene1, $gene2, $this->type, $score, $p_value);
                                } else {
                                    $tmp_result_array = $this->get_correlation_result_within($gene1, $gene2, $this->rvalue);
                                }
                                if (count($tmp_result_array) > 0) {
                                    $query_result_array = array_merge($query_result_array, $tmp_result_array);
                                }
                                unset($tmp_result_array);
                            }
                        }
                    } else { }
                    $this->result_count = count($query_result_array);
                    $this->get_table_result_from_array($query_result_array, $this->type, $feature_names);
                    break;
                }

            case 'nv': {
                if ($this->query_type == 'with') {
                        foreach ($feature_names as $gene) {
                            if ($gene == ' ' || is_null($gene) || strlen($gene) == 0) continue;

                            if ($this->type == 'i') {
                                $tmp_result_array1 = $this->get_interaction_result_with($gene, 'n', $this->epsilon_neg, $this->pvalue_neg);
                                $tmp_result_array2 = $this->get_interaction_result_with($gene, 'p', $this->epsilon_pos, $this->pvalue_pos);
                                $query_result_array = array_merge($query_result_array, $tmp_result_array1, $tmp_result_array2);

                                unset($tmp_result_array1);
                                unset($tmp_result_array2);
                            } else {
                                $tmp_result_array = $this->get_correlation_result_with($gene, $this->rvalue);
                                $query_result_array = array_merge($query_result_array, $tmp_result_array);
                                unset($tmp_result_array);
                            }
                        } 
                        $this->get_network_result_from_array($query_result_array, $this->type, $feature_names,$num);
                    } elseif ($this->query_type == 'within') {
                        foreach ($feature_names as $gene1) {
                            if ($gene1 == ' ' || is_null(1) || strlen($gene1) == 0) continue;
                            foreach ($feature_names as $gene2) {
                                if ($gene2 == ' ' || is_null($gene2) || strlen($gene2) == 0) continue;
                                if ($gene1 == $gene2) continue;

                                $rel1 = null;
                                $rel2 = null;

                                if ($this->type == 'i') {
                                    $rel1 = $this->get_interaction_result_within($gene1, $gene2, 'n', $this->epsilon_neg, $this->pvalue_neg);
                                    $rel2 = $this->get_interaction_result_within($gene1, $gene2, 'p', $this->epsilon_pos, $this->pvalue_pos);
                                } else {
                                    $rel1 = $this->get_correlation_result_within($gene1, $gene2, $this->rvalue);
                                }

                                if ($rel1 != null)
                                    array_push($query_result_array, $rel1);
                                if ($rel2 != null)
                                    array_push($query_result_array, $rel2);
                            }
                        }
                        
                        $this->get_network_result_from_array($query_result_array, $this->type, $feature_names,$num);
                    }
                    break;
                }
        }
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
     * Get interations where query or array related to the specified gene name, satisfing with the value of score and p-value.
     * @global type $global_sga_conn
     * @param String $gene Name of gene
     * @param String $type interaction type, n for negative and p for positive interactions
     * @param Double $score interaction score
     * @param Double $p_value p-value of interaction
     * @return array Array of interactions, every one of result array is an array of interction values.
     */
    function get_interaction_result_with($gene, $type, $score, $p_value) {

        // 排序函数
        if(!function_exists('my_sort_with')){
            function my_sort_with($a, $b) {
                if ($a['score'] == $b['score'])
                    return 0;
                if ($a['score'] < 0 && $b['score'] < 0) {
                    return ($a['score'] < $b['score']) ? -1 : 1;
                } else {
                    return ($a['score'] > $b['score']) ? -1 : 1;
                }
            }
        }

        write_log(SGA_LOG_FILE, 'In Function: get_interaction_result_with');
        $array_result = array();
        // 获取gene对应的strain id
        $strain_ids = $this->get_strain_id_by_gene_name($gene);
        if (is_null($strain_ids) || (count($strain_ids) == 0)) {
            write_log(SGA_LOG_FILE, 'Get strain id empty.');
            return $array_result;
        }

        global $global_sga_conn;
        $array_tmp_result = array();
        $query_query = 'SELECT g1.id AS g1id, g2.id AS g2id, s1.name AS s1n, s2.name AS s2n, i.score, i.p_value, i.tcondition FROM gene g1, gene g2, strain s1, strain s2, interaction i WHERE s1.id = i.query AND s2.id = i.array AND g1.id = s1.gene_id AND g2.id = s2.gene_id AND i.query = ? AND i.score' . ($type == 'n' ? ' <= ' : ' >= ') . ' ? AND i.p_value < ?';
        $query_array = 'SELECT g1.id AS g1id, g2.id AS g2id, s1.name AS s1n, s2.name AS s2n, i.score, i.p_value, i.tcondition FROM gene g1, gene g2, strain s1, strain s2, interaction i WHERE s1.id = i.query AND s2.id = i.array AND g1.id = s1.gene_id AND g2.id = s2.gene_id AND i.array = ? AND i.score' . ($type == 'n' ? ' <= ' : ' >= ') . ' ? AND i.p_value < ?';
        // 查找interaction是否有记录存在，不存在则不执行查询语句
        foreach ($strain_ids as $item) {
            $strain_id = $item['id'];
            $count_query = $this->get_interaction_count_query($strain_id);
            if ($count_query > 0) {
                $array_tmp_result = get_array_from_resultset($global_sga_conn->Execute($query_query, array($strain_id, $score, $p_value)));

                if (!is_null($array_tmp_result) && (count($array_tmp_result) > 0)) {
                    write_log(SGA_LOG_FILE, 'Result of query is ' . count($array_tmp_result));
                    $array_result = array_merge($array_result, $array_tmp_result);
                }
            }

            $count_array = $this->get_interaction_count_array($strain_id);
            if ($count_array > 0) {
                $array_tmp_result = get_array_from_resultset($global_sga_conn->Execute($query_array, array($strain_id, $score, $p_value)));

                if (!is_null($array_tmp_result) && (count($array_tmp_result) > 0)) {
                    write_log(SGA_LOG_FILE, 'Result of array is ' . count($array_tmp_result));
                    $array_result = array_merge($array_result, $array_tmp_result);
                }
            }
        }

        write_log(SGA_LOG_FILE, 'Exiting Function: get_interaction_result_with.');
        uasort($array_result, "my_sort_with");
        return $array_result;
    }

    /**
     * Get interactin between query and array
     * @global type $global_sga_conn
     * @param String $query name of query gene
     * @param String $array name of array gene
     * @param String $type interaction type, n for negative and p for positive interactions
     * @param Double $score interaction score
     * @param Double $p_value p-value of interaction
     * @return array Array of interactions, every one of result array is an array of interction values.
     */
    function get_interaction_result_within($query, $array, $type, $score, $p_value) {

        // 排序函数
        if (!function_exists('my_sort_within')){
            function my_sort_within($a, $b) {
                if ($a['score'] == $b['score'])
                    return 0;
                if ($a['score'] < 0 && $b['score'] < 0) {
                    return ($a['score'] < $b['score']) ? -1 : 1;
                } else {
                    return ($a['score'] > $b['score']) ? -1 : 1;
                }
            }
        }

        write_log(SGA_LOG_FILE, 'In Function: get_interaction_result_within.');
        global $global_sga_conn;
        $array_result = array();
        $sql_query = 'SELECT g1.id AS g1id, g2.id AS g2id, s1.name AS s1n, s2.name AS s2n, i.score, i.p_value, i.tcondition FROM gene g1, gene g2, strain s1, strain s2, interaction i WHERE s1.id = i.query AND s2.id = i.array AND g1.id = s1.gene_id AND g2.id = s2.gene_id AND i.query = ? AND i.array = ? AND i.score' . ($type == 'n' ? ' <= ' : ' >= ') . ' ? AND i.p_value < ?';
        $strain_ids_1 = $this->get_strain_id_by_gene_name($query);
        if (is_null($strain_ids_1) || (count($strain_ids_1) == 0)) {
            write_log(SGA_LOG_FILE, 'Get strain id of query is empty.');
            return $array_result;
        }

        $strain_ids_2 = $this->get_strain_id_by_gene_name($array);
        if (is_null($strain_ids_2) || (count($strain_ids_2) == 0)) {
            write_log(SGA_LOG_FILE, 'Get strain id of array is empty.');
            return $array_result;
        }

        foreach ($strain_ids_1 as $item1) {
            foreach ($strain_ids_2 as $item2) {
                $array_tmp_result = get_array_from_resultset($global_sga_conn->Execute($sql_query, array($item1['id'], $item2['id'], $score, $p_value)));
                if (!is_null($array_tmp_result) && (count($array_tmp_result) > 0)) {
                    write_log(SGA_LOG_FILE, 'Result of query is ' . count($array_tmp_result));
                    array_push($array_result, $array_tmp_result);
                }

                $array_tmp_result = get_array_from_resultset($global_sga_conn->Execute($sql_query, array($item2['id'], $item1['id'], $score, $p_value)));
                if (!is_null($array_tmp_result) && (count($array_tmp_result) > 0)) {
                    write_log(SGA_LOG_FILE, 'Result of array is ' . count($array_tmp_result));
                    array_push($array_result, $array_tmp_result);
                }
            }
        }
        write_log(SGA_LOG_FILE, 'Exiting Function: get_interaction_result_within.');

        // 对结果按照score绝对值从大到小排序
        uasort($array_result, "my_sort_within");
        return $array_result;
    }

    function get_correlation_result_with($gene, $r_value) {
        
    }

    function get_correlation_result_within($query, $array, $r_value) {
        
    }

    function get_gene_id_from_name($feature_name){
        global $global_sga_conn;
        $query_sql = 'SELECT id FROM gene WHERE feature_name = ?';
        return $global_sga_conn->GetOne($query_sql, array($feature_name));
    }
    
    function get_gene_names_info($gene_id) {
        global $global_sga_conn;
        $query_sql = 'SELECT feature_name, standard_gene_name, alias, description FROM gene WHERE id = ?';
        $result = get_array_from_resultset($global_sga_conn->Execute($query_sql, array($gene_id)));
        if (!is_null($result) && (count($result) > 0))
            return $result[0];
        else
            return NULL;
    }

    function get_table_result_from_array($query_result_array, $type, $feature_names) {
        $array_result = array();
        $query_nodes = array();
        $array_gene_infos = array();        // 存储gene的信息，防止同一个gene多次查询数据库

        foreach ($query_result_array as $relation) {
            $tmp_array = array();
            $g1id = $relation['g1id'];
            $g2id = $relation['g2id'];
            if (!array_key_exists($g1id, $array_gene_infos)) {
                $gene_info_1 = $this->get_gene_names_info($g1id);
                if (is_null($gene_info_1))
                    continue;
                $array_gene_infos[$g1id] = $gene_info_1;
            }

            if (!array_key_exists($g2id, $array_gene_infos)) {
                $gene_info_2 = $this->get_gene_names_info($g2id);
                if (is_null($gene_info_2))
                    continue;
                $array_gene_infos[$g2id] = $gene_info_2;
            }

            $tmp_array['g1Feature'] = $array_gene_infos[$g1id]['feature_name'];
            $tmp_array['g1Name'] = $array_gene_infos[$g1id]['standard_gene_name'];
            $tmp_array['g1Alias'] = $array_gene_infos[$g1id]['alias'];
            $tmp_array['g1Desc'] = $array_gene_infos[$g1id]['description'];
            $tmp_array['g2Feature'] = $array_gene_infos[$g2id]['feature_name'];
            $tmp_array['g2Name'] = $array_gene_infos[$g2id]['standard_gene_name'];
            $tmp_array['g2Alias'] = $array_gene_infos[$g2id]['alias'];
            $tmp_array['g2Desc'] = $array_gene_infos[$g2id]['description'];
            $tmp_array['s1Strain'] = $array_gene_infos[$g1id]['feature_name'] . '_' . $relation['s1n'];
            $tmp_array['s2Strain'] = $array_gene_infos[$g2id]['feature_name'] . '_' . $relation['s2n'];
            $tmp_array['score'] = number_format($relation['score'], 3);
            $tmp_array['condition'] = $relation['tcondition'];
            if ($type == 'n' || $type == 'p') {
                $tmp_array['pValue'] = sprintf("%E", $relation['p_value']);
            }

            array_push($array_result, $tmp_array);

            // 统计出现次数最多的gene，为左边的treeview显示做准备
            $node1 = $array_gene_infos[$g1id]['feature_name'];
            $node2 = $array_gene_infos[$g2id]['feature_name'];
            if ($node1 == $node2) continue;

            // 只统计搜索结果，不计用户输入的gene
            if (!in_array($node1, $feature_names)) {
                if (key_exists($node1, $query_nodes)) {
                    $query_nodes[$node1]++;
                } else {
                    $query_nodes[$node1] = 1;
                }
            }
            if (!in_array($node2, $feature_names)) {
                if (key_exists($node2, $query_nodes)) {
                    $query_nodes[$node2]++;
                } else {
                    $query_nodes[$node2] = 1;
                }
            }

            unset($gene_info_1);
            unset($gene_info_2);
            unset($tmp_array);
        }

        $this->table_list_data = json_encode($array_result);
        unset($array_result);       // 释放内存

        arsort($query_nodes);
        foreach ($query_nodes as $key => $value) {
            $this->tree_result_data .= ('{"data":{"title":"' . $key . '","attr":{"id":"' . $key . '","href":"javascript:void(0)", "class":"treenode"}}},');
        }

        if (count($query_nodes) != 0) {
            $this->tree_result_data = substr($this->tree_result_data, 0, strlen($this->tree_result_data) - 1);
        }
        $this->tree_result_data .= '],"state":"open"}]';
    }

    function get_network_result_from_array($array_result, $type, $feature_names , $num_limit) {       
        $query_relation_nodes = array();
        $result_relation_nodes = array();
        $all_relation_edge_array = array();
        $array_gene_infos = array();        // 存储gene的信息，防止同一个gene多次查询数据库
        
        foreach($array_result as $relation){
            $g1id = $relation['g1id'];
            $g2id = $relation['g2id'];
            if (!array_key_exists($g1id, $array_gene_infos)) {
                $gene_info_1 = $this->get_gene_names_info($g1id);
                if (is_null($gene_info_1))
                    continue;
                $array_gene_infos[$g1id] = $gene_info_1;
                unset($gene_info_1);
            }

            if (!array_key_exists($g2id, $array_gene_infos)) {
                $gene_info_2 = $this->get_gene_names_info($g2id);
                if (is_null($gene_info_2))
                    continue;
                $array_gene_infos[$g2id] = $gene_info_2;
                unset($gene_info_2);
            }
            
            $feature_name1 = $array_gene_infos[$g1id]['feature_name'];
            $feature_name2 = $array_gene_infos[$g2id]['feature_name'];
            $score = '';
            
            if ($type == 'i'){
                $score = number_format($relation['score'], 3) . '_' . sprintf("%E", $relation['p_value']);
            } else {
                $score = number_format($relation['score'], 3) . '_0';
            }
            
            $node1 = $feature_name1;
            $node2 = $feature_name2;
            if ($node1 == $node2) continue;
            
            $id = $node1 . '_' . $node2;
            $all_relation_edge_array[$id] = $score;
            
            if (in_array($feature_name1, $feature_names)) {
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

            if (in_array($feature_name2, $feature_names)) {
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
        
        foreach ($feature_names as $feature_name) {
            if (!key_exists($feature_name, $query_relation_nodes)) {
                $cutoff_node_array[$feature_name] = 1;
                $this->cw_node_data .= ('{id:"' . $feature_name . '", count:1,ngc:"q"},');
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
            $edge_type = $this->type == 'i' ? ($values[0] > 0 ? "pai" : "nai") : "c";
            if ((array_key_exists($node_ids[0], $cutoff_node_array)) && (array_key_exists($node_ids[1], $cutoff_node_array))) {
                $this->cw_edge_data .= ('{id:"' . ($key . "_" . $edge_type) . '", distance:' . $values[0] . ',pvalue:' . $values[1] . ',egc:"' . $edge_type . '",target:"' . $node_ids[1] . '",source:"' . $node_ids[0] . '"},');
            }
        }

        if ($this->query_type == 'with') {
            $this->get_child_relation($result_node_array, "ai");
        }

        if ($this->type == 'i') {
            $this->get_child_relation($cutoff_node_array, "coexp");
            $this->get_child_relation($cutoff_node_array, "coloc");
            $this->get_child_relation($cutoff_node_array, "pi");
            $this->get_child_relation($cutoff_node_array, "spd");
        }

        if (char_at($this->cw_node_data, strlen($this->cw_node_data))) {
            $this->cw_node_data = substr($this->cw_node_data, 0, strlen($this->cw_node_data) - 1);
            $this->cw_edge_data = substr($this->cw_edge_data, 0, strlen($this->cw_edge_data) - 1);
        }
        if (strrpos($this->tree_result_data, '[') != (strlen($this->tree_result_data) - 1)){
            $this->tree_result_data = substr($this->tree_result_data, 0, strlen($this->tree_result_data) - 1);
        }        
        $this->cw_node_data .= ']';
        $this->cw_edge_data .= ']';
        $this->tree_result_data .= '],"state":"open"}]';
    }

    /**
     * Get strain id from gene name
     * @global type $global_sga_conn
     * @param type $gene
     * @return array Array of strain ids, return NULL if no such strain.
     */
    function get_strain_id_by_gene_name($gene) {
        global $global_sga_conn;
        $sql = 'SELECT s.id FROM gene g, strain s WHERE s.gene_id = g.id AND g.feature_name = ?';
        return get_array_from_resultset($global_sga_conn->Execute($sql, array($gene)));
    }

    /**
     * Get count of interactions where query equals the specified strain.
     * @global type $global_sga_conn
     * @param type $strain_id
     * @return int count of interactions, return 0 if no such interaction.
     */
    function get_interaction_count_query($strain_id) {
        global $global_sga_conn;
        $prepare_query = 'SELECT COUNT(id) FROM interaction WHERE query = ?';
        return $global_sga_conn->GetOne($prepare_query, array($strain_id));
    }

    /**
     * Get count of interactions where array equals the specified strain.
     * @global type $global_sga_conn
     * @param type $strain_id
     * @return int count of interactions, return 0 if no such interaction.
     */
    function get_interaction_count_array($strain_id) {
        global $global_sga_conn;
        $prepare_array = 'SELECT COUNT(id) FROM interaction WHERE array = ?';
        return $global_sga_conn->GetOne($prepare_array, array($strain_id));
    }
    
    function get_child_relation($result_node_array, $type) {
        $gene_infos_array = array();
        $query_result_array = array();
        foreach ($result_node_array as $gene1 => $value1) {
            foreach ($result_node_array as $gene2 => $value2) {
                if ($gene1 == $gene2 || $gene1 == '' || $gene1 == ' ' || $gene2 == '' || $gene2 == ' ') {
                    continue;
                }
                $rel = null;
                $rel2 = null;
                if ($type == 'ai') {
                    if ($this->type == 'i') {
                        $rel = $this->get_interaction_result_within($gene1, $gene2, 'p', $this->epsilon_pos, $this->pvalue_pos);
                        $rel2 = $this->get_interaction_result_within($gene1, $gene2, 'n', $this->epsilon_neg, $this->pvalue_neg);
                    } elseif ($this->type == 'c') {
                        $rel = $this->get_correlation_result_within($gene1, $gene2, $this->rvalue);
                    }
                } else {
                    $gene1_id = $this->get_gene_id_from_name($gene1);
                    $gene2_id = $this->get_gene_id_from_name($gene2);
                    $rel = $this->get_additional_relation($gene1_id, $gene2_id, $gene1, $gene2, $type);
                }

                if (!is_null($rel) && (count($rel) > 0)) {
                    $tmp = $rel[0];
                    if (!is_null($tmp) && (count($tmp) > 0)){
                        array_push($query_result_array, $tmp[0]);
                    }
                }
                if (!is_null($rel2) && (count($rel2) > 0)) {
                    $tmp = $rel2[0];
                    if (!is_null($tmp) && (count($tmp) > 0)){
                        array_push($query_result_array, $tmp[0]);
                    }
                }
            }
        }

        $edges_exist = array();
        foreach ($query_result_array as $value) {
            $g1id = $value['g1id'];
            $g2id = $value['g2id'];
            
            if (!array_key_exists($g1id, $gene_infos_array)){
                $g1info = $this->get_gene_names_info($g1id);
                if (is_null($g1info) || count($g1info) == 0){
                    continue;
                }
                
                $gene_infos_array[$g1id] = $g1info;
                unset($g1info);
            }
            
            if (!array_key_exists($g2id, $gene_infos_array)){
                $g2info = $this->get_gene_names_info($g1id);
                if (is_null($g2info) || count($g2info) == 0){
                    continue;
                }
                
                $gene_infos_array[$g2id] = $g2info;
                unset($g2info);
            }
            
            if (!array_key_exists('feature_name', $gene_infos_array[$g1id]) || !array_key_exists('feature_name', $gene_infos_array[$g2id])) continue;
            
            $node1 = $gene_infos_array[$g1id]['feature_name'];
            $node2 = $gene_infos_array[$g2id]['feature_name'];
            if ($node1 == $node2) continue;
            
            $key = $node1 . '_' . $node2;
            $tmp_key = $node2 . '_' . $node1;
            if (in_array($key, $edges_exist) || in_array($tmp_key, $edges_exist)) {
                continue;
            }
            array_push($edges_exist, $key);
            if ($this->type == 'i') {
                $i_type = $type == 'ai' ? ($value['score'] > 0 ? 'pai' : 'nai') : $type;
                $this->cw_edge_data .= ('{id:"' . ($key . "_" . $i_type) . '", distance:' . $value['score'] . ',pvalue:' . $value['p_value'] . ',egc:"' . $i_type . '",target:"' . $node2 . '",source:"' . $node1 . '"},');
            } elseif ($this->type == 'c') {
                $this->cw_edge_data .= ('{id:"' . ($key . "_c") . '", distance:' . $value['score'] . ',pvalue: 0,egc:"c",target:"' . $node2 . '",source:"' . $node1 . '"},');
            }
        }
        
        unset($gene_infos_array);
    }
    
    function get_additional_relation($gene1_id, $gene2_id, $gene1_feature_name, $gene2_feature_name, $type){
        $table_name = '';
        switch ($type){
            case 'coexp':
                $table_name = 'co_expression';
                break;
            case 'coloc':
                $table_name = 'co_localization';
                break;
            case 'pi':
                $table_name = 'physical_interaction';
                break;
            case 'spd':
                $table_name = 'shared_protein_domains';
                break;
            default:
                break;
        }
        
        global $global_sga_conn;
        $query = 'SELECT adt.Score, adt.Network_ID FROM ' . $table_name . ' adt WHERE adt.Is_Chosen = 1 AND adt.Gene_A_ID = '. $gene1_id . ' AND adt.Gene_B_ID = ' .$gene2_id;
        $result = get_array_from_resultset($global_sga_conn->Execute($query));
        if (!is_null($result) && (count($result) > 0)){
            $relation = $result[0];
            $additional_data = array();
            $additional_data['g1id'] = $gene1_id;
            $additional_data['g1Feature'] = $gene1_feature_name;
            $additional_data['g2id'] = $gene2_id;
            $additional_data['g2Feature'] = $gene2_feature_name;
            $additional_data['score'] = number_format($relation['Score'], 3);
            $additional_data['pValue'] = $relation['Network_ID'] / 100;
            
            return $additional_data;
        }
        return NULL;
    }
    
    /**
     * Get info of the clicked target, node or edge.
     * @param type $type    Interaction or Correlation
     * @param type $group   Nodes or Edges.
     * @param type $id      Id of the clicked target.
     * @return type JSON data of the clicked target.
     */
    function get_click_target_info($type, $group, $id, $score_neg = -0.08, $score_pos = 0.08, $p_neg = 0.05, $p_pos = 0.05, $rvalue = 'significant'){
        function opp($var){
            return $var != ' ' && $var != '';
        }
        global $global_sga_conn;
        $result_array = array();
        $col = array();
        $query = "";
        if ($group == 'nodes') {
            $query = 'SELECT primary_sgdid, feature_name, standard_gene_name, alias, parent_feature_name, secondary_sgdid, chromosome, start_coordinate, stop_coordinate, strand, genetic_position, coordinate_version FROM gene WHERE feature_name = ? OR standard_gene_name = ?';
            $result = get_array_from_resultset($global_sga_conn->Execute($query, array($id, $id)));
            if (!is_null($result) && (count($result) > 0)){
                $result_array = $result[0];
            }
        } elseif ($group == 'edges') {
            $table_map = array("coexp" => "co_expression",
                "coloc" => "co_localization",
                "pi" => "physical_interaction",
                "spd" => "shared_protein_domains");
            $genes = array_filter(explode('_', $id), "opp");
            $edge_type = $genes[2];
            switch ($edge_type) {
                case 'pai':
                case 'nai':
                    $query = 'SELECT s1.name AS s1n, s2.name AS s2n, i.score, i.p_value, i.tcondition FROM interaction i, strain s1, strain s2, gene g1, gene g2 WHERE i.array = s1.id AND i.query = s2.id AND s1.gene_id = g1.id AND s2.gene_id = g2.id AND ((i.score > ? AND i.p_value < ?) OR(i.score < ? AND i.p_value < ?)) AND g1.feature_name = ? AND g2.feature_name = ? UNION ALL SELECT s1.name AS s1n, s2.name AS s2n, i.score, i.p_value, i.tcondition FROM interaction i, strain s1, strain s2, gene g1, gene g2 WHERE i.query = s1.id AND i.array = s2.id AND s1.gene_id = g1.id AND s2.gene_id = g2.id AND ((i.score > ? AND i.p_value < ?) OR(i.score < ? AND i.p_value < ?)) AND g1.feature_name = ? AND g2.feature_name = ?';
                    $result = get_array_from_resultset($global_sga_conn->Execute($query, array($score_neg, $p_pos, $score_neg, $p_neg, $genes[0], $genes[1], $score_neg, $p_pos, $score_neg, $p_neg, $genes[0], $genes[1])));
                    if (!is_null($result) && (count($result) > 0)){
                        foreach($result as $item){
                            $tmp_array['gene1'] = $genes[0];
                            $tmp_array['gene2'] = $genes[1];
                            $tmp_array['weight'] = number_format($item['score'], 3);
                            $tmp_array['pvalue'] = sprintf("%E", $item['p_value']);
                            $tmp_array['s1name'] = $item['s1n'];
                            $tmp_array['s2name'] = $item['s2n'];
                            $tmp_array['condition'] = $item['tcondition'];
                            
                            array_push($result_array, $tmp_array);
                            unset($tmp_array);
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
                            if ($rvalue == 'significant'){
                                $query = "SELECT c.Correlation_Score FROM correlation c WHERE c.Strain1 = " . $strain1 . " AND c.Strain2 = " . $strain2 . " AND (c.Correlation_Score > 0.1 OR c.Correlation_Score < -0.1)";
                            } else {
                                $query = "SELECT c.Correlation_Score FROM correlation c WHERE c.Strain1 = " . $strain1 . " AND c.Strain2 = " . $strain2;
                            }
                            
                            $result = mysqli_query($dbc, $query);
                            while ($row = mysqli_fetch_row($result)) {
                                $tmp_array['gene1'] = $genes[0];
                                $tmp_array['gene2'] = $genes[1];
                                $tmp_array['weight'] = $this->format_p_value($row[0]);
                                array_push($result_array, $tmp_array);
                            }
                            if ($rvalue == 'significant'){
                                $query = "SELECT c.Correlation_Score FROM correlation c WHERE c.Strain1 = " . $strain2 . " AND c.Strain2 = " . $strain1 . " AND (c.Correlation_Score > 0.1 OR c.Correlation_Score < -0.1)";
                            } else {
                                $query = "SELECT c.Correlation_Score FROM correlation c WHERE c.Strain1 = " . $strain2 . " AND c.Strain2 = " . $strain1;
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
                    $query = "SELECT adt.Score, nw.Network_Name, nw.Pubmed_ID FROM " .$table_map[$edge_type] . " adt, networks nw WHERE nw.id = adt.Network_ID AND adt.Is_Chosen = 1 AND adt.Gene_A_ID = " . $gene1_id . " AND adt.Gene_B_ID = " . $gene2_id;
                    $result = mysqli_query($dbc, $query);
                    while ($row = mysqli_fetch_row($result)) {
                        $tmp_array['gene1'] = $genes[0];
                        $tmp_array['gene2'] = $genes[1];
                        $tmp_array['weight'] = $this->format_p_value($row[0]);
                        $tmp_array['network'] = $row[1];
                        $tmp_array['pubmed'] = $row[2];
                        array_push($result_array, $tmp_array);
                    }
                    
                    $query = "SELECT adt.Score, nw.Network_Name, nw.Pubmed_ID FROM " .$table_map[$edge_type] . " adt, networks nw WHERE nw.id = adt.Network_ID AND adt.Is_Chosen = 1 AND adt.Gene_A_ID = " . $gene2_id . " AND adt.Gene_B_ID = " . $gene1_id;
                    $result = mysqli_query($dbc, $query);
                    while ($row = mysqli_fetch_row($result)) {
                        $tmp_array['gene1'] = $genes[1];
                        $tmp_array['gene2'] = $genes[0];
                        $tmp_array['weight'] = $this->format_p_value($row[0]);
                        $tmp_array['network'] = $row[1];
                        $tmp_array['pubmed'] = $row[2];
                        array_push($result_array, $tmp_array);
                    }
                    break;
            }
        } elseif ($group == 'table'){
            $query = 'SELECT feature_name, description FROM gene WHERE feature_name = ? OR standard_gene_name = ?';
            $result = get_array_from_resultset($global_sga_conn->Execute($query, array($id, $id)));
            if (!is_null($result) && (count($result) > 0)){
                $result_array = $result[0];
            }
        }
        
        return json_encode($result_array);
    }
}
