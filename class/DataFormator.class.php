<?php

/**
 * Description of DataFormator
 *
 * @author GGCoke
 * 2012-7-27 20:02:00
 */
class DataFormator {

    //public $TABLE_FILE = '/R_results/GO_enrichment_test/table_';
    public $TABLE_FILE = '/data/table_';
    public $NETWORK_ANNOT_FILE = '/R_results/GO_enrichment_test/network_annot_';
    public $NETWORK_RELATIONS_FILE = '/R_results/GO_enrichment_test/network_relation_';
    public $DATA_CYTOSCAPEWEB_DATA = array();
    public $DATA_D3JS_DATA = array();
    public $DATA_D3JS_IDS = array();
    public $DATA_NODE_ID_MAP = array();
    public $DATA_TREE_LEAVES_VALUE = array();
    public $DATA_TREE_RELATION = array();
    public $DATA_TREE_RESULT = '';
    public $DATA_NAME_FIRST = TRUE;

    function __construct($timestam) {
        $this->TABLE_FILE = ABSPATH . $this->TABLE_FILE . $timestam . '.txt';
        $this->NETWORK_ANNOT_FILE = ABSPATH . $this->NETWORK_ANNOT_FILE . $timestam . '.txt';
        $this->NETWORK_RELATIONS_FILE = ABSPATH . $this->NETWORK_RELATIONS_FILE . $timestam . '.txt';
    }

    function get_table_data() {
        $file = fopen($this->TABLE_FILE, 'r');
        if (!$file) {
            echo 'Cannot find file ' . $this->TABLE_FILE . '. Please check again.';
            return;
        }

        $data_table_data = array();
        if($flag=="do")
        {
        	$data_table_ids = array('gobpid', 'pvalue', 'genes_in_Category', 'odds_ratio', 'percent_in_the_observed', 'List_percent_in_the_genome', 'fold_of_overrepresents','term');
        
        }else{        
        $data_table_ids = array('gobpid', 'pvalue', 'oddsratio', 'expcount', 'size', 'count', 'term');
        }
        while (!feof($file)) {
            $line = fgets($file);
            if (is_null($line) || strlen($line) == 0)
                continue;
            $items = explode("\t", $line);
            if (is_null($items) || count($items) < 7)
                continue;

            $tmp_array = array();
            foreach ($data_table_ids as $index => $value) {
                if ($value == 'pvalue' || $value == 'oddsratio' || $value == 'expcount') {
                    $tmp_value = rtrim($items[$index], "\r\n");
                    if (strtolower($tmp_value) == 'inf')
                        $tmp_array[$value] = 'Inf';
                    else
                        $tmp_array[$value] = sprintf("%E", $tmp_value);
                } else {
                    $tmp_array[$value] = rtrim($items[$index], "\r\n");
                }
            }

            array_push($data_table_data, $tmp_array);
        }

        fclose($file);
        return json_encode($data_table_data);
    }

    function get_cytoscapeweb_data() {
        $data_cytoscapeweb_data = array();
        $data_cytoscapeweb_node = '[';
        $data_cytoscapeweb_edge = '[';

        // For node of cytoscapeweb
        $file = fopen($this->NETWORK_ANNOT_FILE, 'r');
        if (!$file) {
            echo 'Cannot find file ' . $this->NETWORK_ANNOT_FILE . '. Please check again.';
            return;
        }
        while (!feof($file)) {
            $line = fgets($file);
            if (is_null($line) || strlen($line) == 0)
                continue;
            $items = explode("\t", $line);
            if (is_null($items) || count($items) != 6)
                continue;
            $data_cytoscapeweb_node .= ('{id:"' . $items[0] . '",name:"' . $items[1] . '",desc:"' . $items[2] . '",color:"' . $items[3] . '",fillcolor:"' . $items[4] . '",fontcolor:"' . rtrim($items[5], "\r\n") . '"},');
        }

        $data_cytoscapeweb_node = rtrim($data_cytoscapeweb_node, ',');
        $data_cytoscapeweb_node .= ']';
        fclose($file);

        // For edge of cytoscapeweb
        $file = fopen($this->NETWORK_RELATIONS_FILE, 'r');
        if (!$file) {
            echo 'Cannot find file ' . $this->NETWORK_RELATIONS_FILE . '. Please check again.';
            return;
        }
        while (!feof($file)) {
            $line = fgets($file);
            if (is_null($line) || strlen($line) == 0)
                continue;
            $items = explode("\t", $line);
            if (is_null($items) || count($items) != 3)
                continue;
            $data_cytoscapeweb_edge .= ('{id:"' . $items[0] . '",source:"' . $items[1] . '",target:"' . rtrim($items[2], "\r\n") . '",directed:true},');
        }

        $data_cytoscapeweb_edge = rtrim($data_cytoscapeweb_edge, ',');
        $data_cytoscapeweb_edge .= ']';
        fclose($file);

        $data_cytoscapeweb_data['node'] = $data_cytoscapeweb_node;
        $data_cytoscapeweb_data['edge'] = $data_cytoscapeweb_edge;
        return json_encode($data_cytoscapeweb_data);
    }

    function get_d3js_data() {
        $leaves = array();
        $nodes = array();

        $this->get_node_map();
        $this->get_tree_leaves_value();

        // Get the root of the tree
        $root = $this->get_root_of_tree();
        $this->build_tree($root);
        return $this->DATA_TREE_RESULT;
    }

    function get_other_d3js_data($type) {
        $file = fopen($this->TABLE_FILE, 'r');
        //$file = fopen("F:/webapps/SGA_10/data/table_1354602634.txt", 'r');
        $tree = array();
        $tree["name"] = $type;
        $child = array();
        while (!feof($file)) {
            $line = fgets($file);
            if (is_null($line) || strlen($line) == 0)
                continue;
            $items = explode("\t", $line);
            if (is_null($items) || count($items) < 7)
                continue;
            $tmp_array = array();
            $tmp_array['name'] = $items[0];
            $tmp_array['pvalue'] = $items[1];
            $tmp_array['size'] = $items[4];
            array_push($child, $tmp_array);
        }
        $tree["children"] = $child;
        return(json_encode($tree));
    }

    /**
     * Get root of the tree according the relation of nodes.
     * @return string id of the root node.
     */
    private function get_root_of_tree() {
        if (!file_exists($this->NETWORK_RELATIONS_FILE))
            return NULL;
        $root = '';
        $child = '';
        $first_line = TRUE;
        $relations = array();

        $lines = file($this->NETWORK_RELATIONS_FILE);
        foreach ($lines as $line) {
            if (strlen($line) == 0 || $line == "\r\n")
                continue;
            $items = explode("\t", $line);
            if (is_null($items) || count($items) != 3)
                continue;

            if ($first_line) {
                $root = $child = $items[1];
                $first_line = FALSE;
            }

            $this->DATA_TREE_RELATION[$items[1] . '_' . rtrim($items[2], "\r\n")] = rtrim($items[2], "\r\n");
        }

        $key = array_search($child, $this->DATA_TREE_RELATION);
        while ($key) {
            $items = explode('_', $key);
            $root = $child = $items[0];
            $key = array_search($child, $this->DATA_TREE_RELATION);
        }

        return $root;
    }

    private function build_tree($parent) {
        $name = $this->DATA_NODE_ID_MAP[$parent];
        $this->DATA_TREE_RESULT .= (($this->DATA_NAME_FIRST ? '' : ',') . '{"name":' . '"' . $name . '"');
        if ($this->DATA_NAME_FIRST)
            $this->DATA_NAME_FIRST = FALSE;
        $pattern = "/" . $parent . "_/";
        $result = $this->preg_grep_keys($pattern, $this->DATA_TREE_RELATION);
        if (!is_null($result) && count($result) > 0) {
            $children = array();
            $this->DATA_TREE_RESULT .= ',"children":[';
            $this->DATA_NAME_FIRST = TRUE;
            foreach ($result as $key => $value) {
                $this->build_tree($value);
            }
            $this->DATA_TREE_RESULT .= "]}";
        } else {
            $values = explode('_', $this->DATA_TREE_LEAVES_VALUE[$parent]);
            $this->DATA_TREE_RESULT .= ',"size":' . $values[0] . ',"count":' . $values[1] . '}';
            return;
        }
    }

    private function get_node_map() {
        if (!file_exists($this->NETWORK_ANNOT_FILE))
            return NULL;
        $lines = file($this->NETWORK_ANNOT_FILE);
        foreach ($lines as $line) {
            if (is_null($line) || strlen($line) == 0)
                continue;
            $items = explode("\t", $line);
            if (is_null($items) || count($items) != 6)
                continue;
            $this->DATA_NODE_ID_MAP[$items[0]] = $items[1];
        }
    }

    private function get_tree_leaves_value() {
        if (!file_exists($this->TABLE_FILE))
            return NULL;
        $lines = file($this->TABLE_FILE);
        foreach ($lines as $line) {
            if (is_null($line) || strlen($line) == 0)
                continue;
            $items = explode("\t", $line);
            if (is_null($items) || count($items) != 8)
                continue;
            $node = array_search($items[0], $this->DATA_NODE_ID_MAP);
            if (!$node)
                continue;
            $this->DATA_TREE_LEAVES_VALUE[$node] = (ceil(1 / $items[1])) . '_' . $items[5];
        }

//        var_dump($this->DATA_TREE_LEAVES_VALUE);
    }

    private function preg_grep_keys($pattern, $input, $flags = 0) {
        $keys = preg_grep($pattern, array_keys($input), $flags);
        $vals = array();
        foreach ($keys as $key) {
            $vals[$key] = $input[$key];
        }
        return $vals;
    }

}

