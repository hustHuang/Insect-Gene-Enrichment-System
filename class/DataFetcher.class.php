<?php


/**
 * @author GGCoke
 * 2012-5-30 18:59:41
 */


class DataFetcher {
    private $GET_CONDITION_ID = 'SELECT id FROM tcondition2 WHERE temperature = ? AND type = ?';
    private $GET_MAP_GENE_ID = 'SELECT id, feature_name FROM gene2';
    private $GET_MAP_STRAIN_ID = 'SELECT id, gene_id, name FROM strain2';
    private $GET_MAP_CONDITION_ID = 'SELECT id, temperature, type FROM tcondition2';
    
    public $QUERY_STRAIN_TYPE = array('sn', 'damp', 'tsq');
    public $ARRAY_STRAIN_TYPE = array('dma', 'tsa');
    public $ARRAY_GENE_ID = array();
    public $ARRAY_STRAIN_ID = array();
    public $ARRAY_CONDITION_ID = array();
    
    function __construct() {
        global $global_sga_conn;
        $result = get_array_from_resultset($global_sga_conn->Execute($this->GET_MAP_GENE_ID));
        if (is_null($result)){
            echo "Cannot get the mapping of gene name and id. <br/>";
            exit(0);
        }
        
        // Get map of gene name and id into memory, for saving time.
        foreach ($result as $value){
            $this->ARRAY_GENE_ID[$value['feature_name']] = $value['id'];
        }
        
        write_log(SGA_LOG_FILE, "Count of gene_id_map = " . count($this->ARRAY_GENE_ID));
        
        $result = get_array_from_resultset($global_sga_conn->Execute($this->GET_MAP_STRAIN_ID));
        if (is_null($result)){
            echo "Cannot get the mapping of strain name and id. <br/>";
            exit(0);
        }
        
        // Get map of gene name and id into memory, for saving time.
        foreach ($result as $value){
            $this->ARRAY_STRAIN_ID[$value['gene_id'] . '_' . $value['name']] = $value['id'];
        }
        write_log(SGA_LOG_FILE, "Count of strain_id_map = " . count($this->ARRAY_STRAIN_ID));
        
        $result = get_array_from_resultset($global_sga_conn->Execute($this->GET_MAP_CONDITION_ID));
        if (is_null($result)){
            echo "Cannot get the mapping of condition name and id. <br/>";
            exit(0);
        }
        foreach ($result as $value){
            $this->ARRAY_CONDITION_ID[$value['type'] . $value['temperature']] = $value['id'];
        }
        write_log(SGA_LOG_FILE, "Count of condition_id_map = " . count($this->ARRAY_CONDITION_ID));
    }
    
    /**
     * 检查strain的类型是否是规定的5中类型之一
     * @param type $strain1 name of strain 1
     * @param type $strain2 name of strain 2
     * @return Boolean true if belongs to $ARRAY_STRAIN_TYPE or false if not
     */
    function valid_interaction($strain1, $strain2) {
        $tmp1 = explode('_', $strain1);
        $tmp2 = explode('_', $strain2);
        $flag = FALSE;
        if ((count($tmp1) == 2) && (count($tmp2) == 2)) {
            $type1 = trim($tmp1[1]);
            $type2 = trim($tmp2[1]);
            foreach ($this->QUERY_STRAIN_TYPE as $defined) {
                if (strncmp($type1, $defined, strlen($defined)) == 0){
                    $flag = TRUE;
                    break;
                }
            }
            
            if (!$flag){
                write_log(SGA_LOG_FILE, "This line is not corracte. Query is " . $strain1 . " and Array is " . $strain2);
                return FALSE;
            }
            foreach ($this->ARRAY_STRAIN_TYPE as $defined) {
                if (strncmp($type2, $defined, strlen($defined)) == 0){
                    return TRUE;
                }
            }
        }
        write_log(SGA_LOG_FILE, "This line is not corracte. Query is " . $strain1 . " and Array is " . $strain2);
        return FALSE;
    }
    
    /**
     * Get id of gene from strain in interaction.
     * @param type $strain
     * @return int id of gene in strain, return 0 if no such gene.
     */
    function get_gene_id($gene){
        if (array_key_exists($gene, $this->ARRAY_GENE_ID)){
            return $this->ARRAY_GENE_ID[$gene];
        }
        
        return 0;
    }
    
    /**
     * Get id of strain of strain in interaction
     * @global type $global_sga_conn
     * @param type $gene_id
     * @param type $type
     * @return int id of strain, return 0 if no such strain
     */
    function get_strain_id($gene_id, $type){
        if (array_key_exists($gene_id . '_' . $type, $this->ARRAY_STRAIN_ID)){
            return $this->ARRAY_STRAIN_ID[$gene_id . '_' . $type];
        }
        
        return 0;
    }
    
    /**
     * Get id of condition from file name
     * @global type $global_sga_conn
     * @param type $filename
     * @return int id of condition
     */
    function get_condition_id($filename){
        global $global_sga_conn;
        $names = explode('_', $filename);
        if (array_key_exists($names[0], $this->ARRAY_CONDITION_ID)){
            return $this->ARRAY_CONDITION_ID[$names[0]];
        }
        return 0;
    }
}

?>
