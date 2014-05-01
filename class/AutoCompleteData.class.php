<?php

/**
 * Description of BlockData
 *
 * @author ggcoke
 * Jun 13, 2012 2:39:44 PM
 */
class AutoCompleteData {

    function get_autocomplete_data($saved_file, $mode) {
        global $global_sga_conn;
        $key_array = array(
            array('feature_name', 'standard_gene_name'),
            array('Biochemical_Pathway_Name'),
            array('GO_Slim_Mapping_Slim_GO_Slim_Term'),
            array('GO_Protein_Complex_Slim_Ontology_GO_Term'),
            array('enrichment'),
            array('feature_name', 'standard_gene_name')
        );

        $type_array = array(0,1,1,1,2,2); 
       
        $SQL_QUERY_GENE = "SELECT g.feature_name, g.standard_gene_name FROM gene g";
        $SQL_QUERY_PATHWAY = 'SELECT DISTINCT Biochemical_Pathway_Name FROM biochemical_pathway';
        $SQL_QUERY_SLIM_MAPPING = 'SELECT DISTINCT GO_Slim_Mapping_Slim_GO_Slim_Term FROM go_slim_mapping';
        $SQL_QUERY_COMPLEX_SLIM = 'SELECT DISTINCT GO_Protein_Complex_Slim_Ontology_GO_Term FROM go_protein_complex_slim';
        $SQL_QUERY_BLOCK_ENRICHMENT = 'SELECT DISTINCT enrichment FROM block_enrichment';
        $SQL_QUERY_BLOCK_STRAIN = 'SELECT DISTINCT g.feature_name, g.standard_gene_name FROM gene g, block_strain b WHERE g.id = b.gene_id';
        $result_data = 'var names = [';

        $sql_array = array($SQL_QUERY_GENE, $SQL_QUERY_PATHWAY, $SQL_QUERY_SLIM_MAPPING, $SQL_QUERY_COMPLEX_SLIM, $SQL_QUERY_BLOCK_ENRICHMENT, $SQL_QUERY_BLOCK_STRAIN);

        $count = count($key_array);
        for ($i = 0; $i < $count; $i++) {
            $result = get_array_from_resultset($global_sga_conn->Execute($sql_array[$i]));
            if (!is_null($result) && (count($result)) > 0) {
                foreach ($result as $item) {
                    $result_data .= '{k:"' . $item[$key_array[$i][0]] . '",v:' . $type_array[$i] . '},';
                    if (!is_null($item[$key_array[$i][1]]) && $item[$key_array[$i][1]] != '') {
                        $result_data .= '{k:"' . $item[$key_array[$i][1]] . '",v:' . $type_array[$i] . '},';
                    }
                }
            }
        }

        $result_data = rtrim($result_data, ", ");
        $result_data .= ']';
        $file = fopen($saved_file, $mode);
        fwrite($file, $result_data);
        fclose($file);
    }
    /**
     * @author huangkegui
     * @time 2013-8-7
     * @conetent get simple autocomplete data
     */
    
    function get_simple_autocomplete_data($saved_file, $mode) {
        global $global_sga_conn;
        $key_array = array(
            array('feature_name', 'standard_gene_name'),
            //array('Biochemical_Pathway_Name'),
            //array('GO_Slim_Mapping_Slim_GO_Slim_Term'),
            //array('GO_Protein_Complex_Slim_Ontology_GO_Term'),
            //array('enrichment'),
            array('feature_name', 'standard_gene_name')
        );

      //$type_array = array(0,1,1,1,2,2); 
        $type_array = array(0,2);

        $SQL_QUERY_GENE = "SELECT g.feature_name, g.standard_gene_name FROM SC_gene g";
        //$SQL_QUERY_PATHWAY = 'SELECT DISTINCT Biochemical_Pathway_Name FROM biochemical_pathway';
        //$SQL_QUERY_SLIM_MAPPING = 'SELECT DISTINCT GO_Slim_Mapping_Slim_GO_Slim_Term FROM go_slim_mapping';
        //$SQL_QUERY_COMPLEX_SLIM = 'SELECT DISTINCT GO_Protein_Complex_Slim_Ontology_GO_Term FROM go_protein_complex_slim';
        // $SQL_QUERY_BLOCK_ENRICHMENT = 'SELECT DISTINCT enrichment FROM block_enrichment';
        $SQL_QUERY_BLOCK_STRAIN = 'SELECT DISTINCT g.feature_name, g.standard_gene_name FROM SC_gene g, SC_gi_block_strain b WHERE g.id = b.gene_id';
        $result_data = 'var names = [';

        $sql_array = array($SQL_QUERY_GENE, $SQL_QUERY_BLOCK_STRAIN);

        $count = count($key_array);
        for ($i = 0; $i < $count; $i++){
            $result = get_array_from_resultset($global_sga_conn->Execute($sql_array[$i]));
            if (!is_null($result) && (count($result)) > 0) {
                foreach ($result as $item) {
                    $result_data .= '{k:"' . $item[$key_array[$i][0]] . '",v:' . $type_array[$i] . '},';
                    if (!is_null($item[$key_array[$i][1]]) && $item[$key_array[$i][1]] != '') {
                        $result_data .= '{k:"' . $item[$key_array[$i][1]] . '",v:' . $type_array[$i] . '},';
                    }
                }
            }
        }

        $result_data = rtrim($result_data, ", ");
        $result_data .= ']';
        $file = fopen($saved_file, $mode);
        fwrite($file, $result_data);
        fclose($file);
    }
    
     /**
      * @author huangkegui
      * @time   2013-8-7
      * @conetent get simple data without non-exist genes 
     */
    
    function get_simple_data($saved_file, $mode) {
        global $global_sga_conn;
        $key_array = array(
            array('feature_name', 'standard_gene_name'),
            //array('Biochemical_Pathway_Name'),
            //array('GO_Slim_Mapping_Slim_GO_Slim_Term'),
            //array('GO_Protein_Complex_Slim_Ontology_GO_Term'),
            //array('enrichment'),
            array('feature_name', 'standard_gene_name')
        );

      //$type_array = array(0,1,1,1,2,2); 
        $type_array = array(0,2);

        $SQL_QUERY_GENE = "SELECT g.feature_name, g.standard_gene_name FROM SC_gene g ,SC_strain s WHERE g.id = s.gene_id";
        //$SQL_QUERY_PATHWAY = 'SELECT DISTINCT Biochemical_Pathway_Name FROM biochemical_pathway';
        //$SQL_QUERY_SLIM_MAPPING = 'SELECT DISTINCT GO_Slim_Mapping_Slim_GO_Slim_Term FROM go_slim_mapping';
        //$SQL_QUERY_COMPLEX_SLIM = 'SELECT DISTINCT GO_Protein_Complex_Slim_Ontology_GO_Term FROM go_protein_complex_slim';
        // $SQL_QUERY_BLOCK_ENRICHMENT = 'SELECT DISTINCT enrichment FROM block_enrichment';
        $SQL_QUERY_BLOCK_STRAIN = 'SELECT DISTINCT g.feature_name, g.standard_gene_name FROM SC_gene g, SC_gi_block_strain b WHERE g.id = b.gene_id';
        $result_data = 'var names = [';

        $sql_array = array($SQL_QUERY_GENE, $SQL_QUERY_BLOCK_STRAIN);
        $tempArray = array();
        $count = count($key_array);
        for ($i = 0; $i < $count; $i++){
            $result = get_array_from_resultset($global_sga_conn->Execute($sql_array[$i]));
            if (!is_null($result) && (count($result)) > 0) {
                foreach ($result as $item) {
                    if(!in_array($item[$key_array[$i][0]], $tempArray)){
                        array_push($tempArray, $item[$key_array[$i][0]]);
                        $result_data .= '{k:"' . $item[$key_array[$i][0]] . '",v:' . $type_array[$i] . '},';
                    }
                    if (!is_null($item[$key_array[$i][1]]) && $item[$key_array[$i][1]] != '') {
                        if(!in_array($item[$key_array[$i][1]], $tempArray)){
                            array_push($tempArray, $item[$key_array[$i][1]]);
                            $result_data .= '{k:"' . $item[$key_array[$i][1]] . '",v:' . $type_array[$i] . '},';
                        }   
                    }
                }
            }
        }

        $result_data = rtrim($result_data, ", ");
        $result_data .= ']';
        $file = fopen($saved_file, $mode);
        fwrite($file, $result_data);
        fclose($file);
    }

}