<?php

/**
 * Description of BlockData
 *
 * @author ggcoke
 * Jun 13, 2012 2:39:44 PM
 */
class BlockData{
    private $FILE_FIRST_TITLE = 0;
    private $GET_MAP_GENE_FEATURE_ID = 'SELECT id, feature_name FROM SC_gene';
    private $GET_MAP_GENE_STANDARD_ID = 'SELECT id, standard_gene_name FROM SC_gene';
    private $BLOCK_BLOCK_INFO = 'INSERT INTO block_info(dim_a, dim_q, corr_coef_a, corr_coef_q, aq, interaction_density, process_enrichment_a, process_enrichment_q, disease_genes_a, disease_genes_q) VALUES (?,?,?,?,?,?,?,?,?,?)';
    private $BLOCK_ENRICHMENT = 'INSERT INTO block_enrichment(block_id, type, enrichment) VALUES (?,?,?)';
    private $BLOCK_STRAIN = 'INSERT INTO block_strain(gene_id, strain_name, block_id, strain_type) VALUES (?,?,?,?)';
    private $BLOCK_INTERACTION = 'INSERT INTO block_interaction(block_id, strain_a, strain_q, interaction_socre) VALUES (?,?,?,?)';
    private $CHECK_IS_GENE = 'SELECT id FROM SC_gene WHERE feature_name = ? OR standard_gene_name = ?';
    private $GET_BLOCK_ID_FROM_GENE = 'SELECT DISTINCT(block_id) FROM SC_gi_block_strain WHERE gene_id = ? ORDER BY block_id ASC';
    private $GET_BLOCK_ID_FROM_EHRICHMENT = 'SELECT DISTINCT(block_id) FROM SC_gi_block_enrichment WHERE enrichment = ? ORDER BY block_id ASC';
    private $GET_BLOCK_STRAIN_ID = 'SELECT id FROM block_strain WHERE gene_id = ? AND strain_name = ? AND block_id = ? AND strain_type = ?';
    private $BLOCK_EXISTS = 'SELECT COUNT(id) FROM SC_gi_block_info WHERE id = ?';
    private $GET_BLOCK_INFO_BASIC = 'SELECT id, dim_a, dim_q, corr_coef_a, corr_coef_q, aq, interaction_density, process_enrichment_a, process_enrichment_q, disease_genes_a, disease_genes_q FROM SC_gi_block_info WHERE id = ?';
    private $GET_BLOCK_INFO_ENRICHMENT = 'SELECT enrichment FROM SC_gi_block_enrichment WHERE block_id = ? AND type = ?';
    
    private $GET_GENE_ID_FROM_NAME = 'SELECT id FROM SC_gene WHERE feature_name = ? OR standard_gene_name = ?';
    private $GET_RESULT_NAME_FROM_ENRICHMENT = 'SELECT g1.feature_name AS fname1, g1.standard_gene_name AS sname1, g2.feature_name AS fname2, g2.standard_gene_name AS sname2 FROM SC_gi_block_interaction b, SC_gi_block_strain s1, SC_gi_block_strain s2, SC_gene g1, SC_gene g2, SC_gi_block_enrichment be WHERE g1.id = s1.gene_id AND g2.id = s2.gene_id AND s1.id = b.strain_a AND s2.id = b.strain_q AND b.block_id = be.block_id AND be.enrichment = ?';
    private $GET_RESULT_NAME_FROM_BLOCK = 'SELECT g1.feature_name AS fname1, g1.standard_gene_name AS sname1, g2.feature_name AS fname2, g2.standard_gene_name AS sname2 FROM SC_gene g1, SC_gene g2, SC_gi_block_strain bs1, SC_gi_block_strain bs2, SC_gi_block_interaction bi WHERE g1.id = bs1.gene_id AND g2.id = bs2.gene_id AND bs1.id = bi.strain_a AND bs2.id = bi.strain_q AND bi.block_id = ?';
    
    public $ARRAY_GENE_FEATURE_ID = array();
    public $ARRAY_GENE_STANDARD_ID = array();
    
    function __construct() {
      global $global_sga_conn;
      $result = get_array_from_resultset($global_sga_conn->Execute($this->GET_MAP_GENE_FEATURE_ID));
      if (is_null($result)){
          echo "Cannot get the mapping of gene feature name and id. <br/>";
          exit(0);
      }
      
      // Get map of gene name and id into memory, for saving time.
      foreach ($result as $value){
          $this->ARRAY_GENE_FEATURE_ID[$value['feature_name']] = $value['id'];
      }
      
      $result = get_array_from_resultset($global_sga_conn->Execute($this->GET_MAP_GENE_STANDARD_ID));
      if (is_null($result)){
          echo "Cannot get the mapping of gene standard name and id. <br/>";
          exit(0);
      }
      
      // Get map of gene name and id into memory, for saving time.
      foreach ($result as $value){
          $this->ARRAY_GENE_STANDARD_ID[$value['standard_gene_name']] = $value['id'];
      }
    }
    
    function get_block_interaction($files_path){
        if (!file_exists($files_path)){
            echo 'Path ' . $files_path . ' does not exist. Please check it.<br/>';
//            write_log(SGA_LOG_FILE, 'Path ' . $files_path . ' does not exist. Please check it.');
            exit(0);
        }
        
        $dir_handler = @opendir($files_path);
        if ($dir_handler){
            global $global_sga_conn;
            
            while (($file_name = readdir($dir_handler)) !== FALSE){
                if ($file_name == '.' || $file_name == '..' || pathinfo($files_path . '/' . $file_name, PATHINFO_EXTENSION) != 'txt'){
                    continue;
                }
                
                $array_strain_id_arrays = array();
                
                $block_id = pathinfo($files_path . '/' . $file_name, PATHINFO_FILENAME );
                $file = fopen($files_path . '/' . $file_name, 'r');
                if ($file === FALSE){
                    echo 'Open file ' . $files_path . '/' . $file_name . ' failed. Pleas try again.<br/>';
//                    write_log(SGA_LOG_FILE, 'Open file ' . $filename . ' failed. Pleas try again.');
                    exit(0);
                }
                
                echo '==============================================================================================<br/>';
                echo 'File = ' . $files_path . '/' . $file_name . '<br/>';
                $line_number = 0;
                while (!feof($file)){
                    $line = fgets($file);
                    if (is_null($line) || strlen($line) == 0){
                        continue;
                    }
                    
                    if ($line_number == 0){
                        $strain_arrays = explode("\t", $line);
                        var_dump($strain_arrays);
                        foreach ($strain_arrays as $strain_a){
                            $strain_a = trim($strain_a);
                            if (is_null($strain_a) || strlen($strain_a) == 0){
                                continue;
                            }
                            
                            $block_strain_a_id = $this->get_block_strain_id($strain_a, $block_id, 0);
                            
                            if ($block_strain_a_id != -1){
                                array_push($array_strain_id_arrays, $block_strain_a_id);
                            } else {
                                continue;
                            }
                        }
                        
                        var_dump($array_strain_id_arrays);
                        echo '<br/>';
                        
                    } else {
                        $strain_interactions = explode("\t", $line);
                        var_dump($strain_interactions);
                        $strain_q = $strain_interactions[0];
                        $block_strain_q_id = $this->get_block_strain_id($strain_q, $block_id, 1);
                        echo 'ID of strain q = ' . $block_strain_q_id . '<br/>';
                        for ($i = 1; $i < count($strain_interactions); $i++){
                            $tmp_score = trim($strain_interactions[$i]);
                            echo "Interaction score = " . $strain_interactions[$i] . " and block id = " . $block_id . "<br/>";
                            $score = $tmp_score == 'NaN' ? NULL : $tmp_score;
                            $global_sga_conn->Execute($this->BLOCK_INTERACTION, array($block_id, $array_strain_id_arrays[$i - 1], $block_strain_q_id, $score));
                        }
                    }
                    $line_number++;
                }
                
                if (is_resource($file)){
                    fclose($file);
                }
                echo '==============================================================================================<br/>';
            }
        }
    }
    
    function get_data($filename){
        if (!file_exists($filename)){
            echo 'File ' . $filename . ' does not exist. Please check the path of the file.<br/>';
//            write_log(SGA_LOG_FILE, 'File ' . $filename . ' does not exist. Please check the path of the file.');
            exit(0);
        }
        
        $file = fopen($filename, 'r');
        if ($file === FALSE){
            echo 'Open file ' . $filename . ' failed. Pleas try again.<br/>';
//            write_log(SGA_LOG_FILE, 'Open file ' . $filename . ' failed. Pleas try again.');
            exit(0);
        }
        
        global $global_sga_conn;
        $block_id = 0;
        
        while (!feof($file)){
            $line = fgets($file);
            $contents = explode("\t", $line);
            
            if ($block_id == $this->FILE_FIRST_TITLE){
                $block_id++;
                continue;
            }
            if (count($contents) != 25){
                echo 'Content of this line is error. Line number is ' . $block_id . ' and content is ' . $line . '<br/>';
                continue;
            }
            
            // block base info
//            $global_sga_conn->Execute($this->BLOCK_BLOCK_INFO, array($contents[0], $contents[1], $contents[2], $contents[3], $contents[4], $contents[5], $contents[17], $contents[18], $contents[19], $contents[20]));
            
            // A enrichment
            if (!is_null($contents[15]) && $contents[15] != ''){
                $enrichments_a = explode('|', $contents[15]);
                foreach($enrichments_a as $enrichment_a){
                    $enrichment_a = trim($enrichment_a);
                    if (is_null($enrichment_a) || $enrichment_a == '' || strlen($enrichment_a) == 0){
                        continue;
                    }
                    
//                    $global_sga_conn->Execute($this->BLOCK_ENRICHMENT, array($block_id, 0, $enrichment_a));
                }
            }
            
            // Q enrichment
            if (!is_null($contents[16]) && $contents[16] != ''){
                $enrichments_q = explode('|', $contents[16]);
                foreach($enrichments_q as $enrichment_q){
                    $enrichment_q = trim($enrichment_q);
                    if (is_null($enrichment_q) || $enrichment_q == '' || strlen($enrichment_q) == 0){
                        continue;
                    }
                    
//                    $global_sga_conn->Execute($this->BLOCK_ENRICHMENT, array($block_id, 1, $enrichment_q));
                }
            }
            
            // A orf
            if (!is_null($contents[23]) && $contents[23] != ''){
                $orfs_a = explode('|', $contents[23]);
                foreach($orfs_a as $orf_a){
                    $orf_a = trim($orf_a);
                    $names = explode('_', $orf_a);
                    $feature_name = $names[0];
                    $strain_name = $names[1];
                    $gene_id = $this->ARRAY_GENE_FEATURE_ID[$feature_name];
//                    $global_sga_conn->Execute($this->BLOCK_STRAIN, array($gene_id, $strain_name, $block_id, 0));
                }
            }
            
            // Q orf
            if (!is_null($contents[24]) && $contents[24] != ''){
                $orfs_q = explode('|', $contents[24]);
                foreach($orfs_q as $orf_q){
                    $orf_q = trim($orf_q);
                    $names = explode('_', $orf_q);
                    $feature_name = $names[0];
                    $strain_name = $names[1];
                    $gene_id = $this->ARRAY_GENE_FEATURE_ID[$feature_name];
//                    $global_sga_conn->Execute($this->BLOCK_STRAIN, array($gene_id, $strain_name, $block_id, 1));
                }
            }
            
            $block_id++;
        }
        if (is_resource($file)){
            fclose($file);
        }
        echo 'End of file<br/>';
//        write_log(SGA_LOG_FILE, 'End of file');
    }
    
    function get_block_strain_id ($orf, $block_id, $strain_type){
        global $global_sga_conn;
        $tmp_array = explode('_', $orf);   // $tmp_array[0]: strain_name $tmp_array[1]: gene standard name
        if (array_key_exists($tmp_array[1], $this->ARRAY_GENE_STANDARD_ID)){
            return $global_sga_conn->GetOne($this->GET_BLOCK_STRAIN_ID, array($this->ARRAY_GENE_STANDARD_ID[$tmp_array[1]], $tmp_array[0], $block_id, $strain_type));
        } else {
            return -1;
        }
    }
    
    function block_exists($block_id){
        global $global_sga_conn;
        return $global_sga_conn->GetOne($this->BLOCK_EXISTS, array($block_id)) == 0 ? FALSE : TRUE;
    }
    
    function get_block_info_basic($block_id){
        global $global_sga_conn;
        $result = get_array_from_resultset($global_sga_conn->Execute($this->GET_BLOCK_INFO_BASIC, array($block_id)));
        return $result[0];
    }
    
    function get_block_info_enrichment($block_id, $type){
        global $global_sga_conn;
        return get_array_from_resultset($global_sga_conn->Execute($this->GET_BLOCK_INFO_ENRICHMENT, array($block_id, $type)));
    }
    
    function get_block_id($keywords){
        global $global_sga_conn;
        $key_array = explode(STRING_SEPARATOR, $keywords);
        $result_array = array();
        
        foreach($key_array as $key){
            $key = trim($key);
            
            if (is_null($key) || strlen($key) == 0) continue;
            if ($this->is_gene($key)){
                $key = strtoupper($key);
                $gene_id = array_key_exists($key, $this->ARRAY_GENE_FEATURE_ID) ? $this->ARRAY_GENE_FEATURE_ID[$key] : $this->ARRAY_GENE_STANDARD_ID[$key];
                $tmp_result = get_array_from_resultset($global_sga_conn->Execute($this->GET_BLOCK_ID_FROM_GENE, array($gene_id)));
                if (is_null($tmp_result)) continue;
                foreach($tmp_result as $item){
                    if (!in_array($item['block_id'], $result_array)){
                        array_push($result_array, $item['block_id']);
                    }
                }               
            } else {
                $tmp_result = get_array_from_resultset($global_sga_conn->Execute($this->GET_BLOCK_ID_FROM_EHRICHMENT, array($key)));
                if (is_null($tmp_result)) continue;
                foreach($tmp_result as $item){
                    if (!in_array($item['block_id'], $result_array)){
                        array_push($result_array, $item['block_id']);
                    }
                }
            }
        }
        return $result_array;
    }
    
    
    /**
     * Check whether a key word is a feature name of gene or a standard gene name.
     * @global type $global_sga_conn
     * @param type $name
     * @return Boolean Return true if it is a gene, false or not.
     */
    function is_gene($name){
        global $global_sga_conn;
        $result = $global_sga_conn->GetOne($this->CHECK_IS_GENE, array($name, $name));
        return is_null($result) || $result == 0 ? FALSE : TRUE;
    }
    
    function get_array_from_key_array($key_array, $key){
        if (is_null($key_array)) return NULL;
        $result = array();
        foreach($key_array as $item){
            array_push($result, $item[$key]);
        }
        return $result;
    }
    
    /**
     * Get feature names from enrichment.
     * @global type $global_sga_conn
     * @param type $enrichment
     * @return type 
     */
    private function get_feature_name_from_enrichment($enrichment){
        global $global_sga_conn;
        return get_array_from_resultset($global_sga_conn->Execute($this->GET_RESULT_NAME_FROM_ENRICHMENT, array($enrichment)));
    }
    
    /**
     * Get feature name from gene name.
     * @global type $global_sga_conn
     * @param type $gene
     * @return array 
     */
    private function get_feature_name_from_gene($gene){
        global $global_sga_conn;
        $array_feature_name = array();
        $gene_id = $global_sga_conn->GetOne($this->GET_GENE_ID_FROM_NAME, array($gene, $gene));
        $block_ids = get_array_from_resultset($global_sga_conn->Execute($this->GET_BLOCK_ID_FROM_GENE, array($gene_id)));
        if (is_null($block_ids) || count($block_ids) == 0) return NULL;
        foreach ($block_ids as $block_id){
            $tmp_result = get_array_from_resultset($global_sga_conn->Execute($this->GET_RESULT_NAME_FROM_BLOCK, array($block_id['block_id'])));
            if (!is_null($tmp_result) && count($tmp_result) > 0){
                $array_feature_name = array_merge($array_feature_name, $tmp_result);
            }
        }
        
        return $array_feature_name;
    }
    
    /**
     * Get all related gene names.
     * @param type $keywords
     * @return string 
     */
    function get_feature_name($keywords){
        $tree_feature_name = '[{"data": "Search results","attr":{"id":"queryresult", "href":"javascript:void(0)"},"children":[';
        $array_exist_name = array();
        $array_name = explode(STRING_SEPARATOR, $keywords);
        $result_array = array();
        
        if (!is_null($array_name) && count($array_name) > 0){
            foreach ($array_name as $name){
                $name = trim($name);
                if (is_null($name) || strlen($name) == 0) continue;
                $tmp_result = array();
                if ($this->is_gene($name)){
                    $tmp_result = $this->get_feature_name_from_gene($name);
                } else {
                    $tmp_result = $this->get_feature_name_from_enrichment($name);
                }
                
                if (!is_null($tmp_result) && count($tmp_result) > 0){
                    foreach ($tmp_result as $item){
                        $name1 = $item['sname1'] == null || strlen($item['sname1']) == 0 ? $item['fname1'] : $item['sname1'];
                        $name2 = $item['sname2'] == null || strlen($item['sname2']) == 0 ? $item['fname2'] : $item['sname2'];
                        if (!in_array($name1, $array_exist_name)){
                            array_push($array_exist_name, $name1);
                            $tree_feature_name .= ('{"data":{"title":"' . $name1 . '","attr":{"id":"' . $name1 . '","href":"javascript:void(0)", "class":"treenode"}}},');
                        }
                        
                        if (!in_array($name2, $array_exist_name)){
                            array_push($array_exist_name, $name2);
                            $tree_feature_name .= ('{"data":{"title":"' . $name2 . '","attr":{"id":"' . $name2 . '","href":"javascript:void(0)", "class":"treenode"}}},');
                        }
                    }
                }
            }
        }
        
        $tree_feature_name = rtrim($tree_feature_name, ",");
        $tree_feature_name .= '],"state":"open"}]';
        return $tree_feature_name;
    }
    function get_data_by_blockid($id)
    {
        global $global_sga_conn;       
        $query = "select strain_a ,strain_q ,interaction_socre from SC_gi_block_interaction where block_id=";
        $queryt = "select strain_name ,gene_id from SC_gi_block_strain where id=";
        $queryn = "select feature_name ,standard_gene_name from SC_gene where id=";
        $temp = get_array_from_resultset($global_sga_conn->Execute($query.$id));
        $strainq = array();$temp_result = array();$result = array();
        foreach ($temp as $value){
            if (!in_array($value['strain_a'], $strainq))
                {
                 array_push($strainq, $value['strain_a']);
            }
            }
        foreach($strainq as $term){ 
                $tempname=get_array_from_resultset($global_sga_conn->Execute($queryt.$term));
                foreach ($tempname as $tem){
                    $a=$tem['strain_name'];$b=$tem['gene_id'];
                }
                $tempsymbol=get_array_from_resultset($global_sga_conn->Execute($queryn.$b));
                foreach ($tempsymbol as $tem){                 
                   $c=$tem['standard_gene_name'];
                   if($c==NULL || $c=='') {$c=$tem['feature_name'];}
                }
                $temp_result['year']=$a.'_'.$c;              
                foreach ($temp as $value){ 
                if($term==$value['strain_a']){
                    $tempname=get_array_from_resultset($global_sga_conn->Execute($queryt.$value['strain_q']));
                    foreach ($tempname as $tem){
                    $a=$tem['strain_name'];$b=$tem['gene_id'];
                }
                $tempsymbol=get_array_from_resultset($global_sga_conn->Execute($queryn.$b));
                foreach ($tempsymbol as $tem){
                   $c=$tem['standard_gene_name'];
                   if($c==NULL || $c=='') {$c=$tem['feature_name'];}
                }
                if($value['interaction_socre']==null) {$temp_result[$a.'_'.$c]='nan';}
                else {$temp_result[$a.'_'.$c]=$value['interaction_socre'];}
                }    
                }
                array_push($result, $temp_result);
        }       
        return $result;
    }
}
