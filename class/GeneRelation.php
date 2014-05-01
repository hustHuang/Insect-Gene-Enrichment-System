<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchAction2
 *
 * @author GGCoke
 */
class GeneRelation {
    static $MARKER_TYPE_QUERY = 0;
    static $MARKER_TYPE_ARRAY = 1;
    //put your code here

    /**
     *
     * @param string $gene_name Name of a gene
     * @return int The ID of gene in the database.
     */
    function getGeneIDByGeneName($gene_name) {
        $query = "SELECT g.idGENE FROM gene g WHERE g.Feature_Name = '" . $gene_name . "' OR g.Standard_Gene_Name = '" . $gene_name . "'";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return -1;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return -1;
        if ($row = mysqli_fetch_row($result)) {
            return $row[0];
        }
        return -1;
    }

    function getGeneNameByStrainId($id){
        $query = "SELECT g.Feature_Name, g.Standard_Gene_Name FROM gene g, strain s WHERE g.idGENE = s.GENE_idGENE AND s.idSTRAIN = " . $id;
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        
        if ($row = mysqli_fetch_row($result)) {
            return ($row[1] == null || $rowp[1] == '' ? $row[0] : $row[1]);
        }
        
        return null;
    }
    
    function getGeneDataByGeneName($gene_name) {
        // $query = "SELECT g.idGENE, g.Primary_SGDID, g.Feature_Name, g.Standard_Gene_Name, g.Alias, g.Description FROM gene g WHERE g.Feature_Name = '" . $gene_name . "' OR g.Standard_Gene_Name = '" . $gene_name . "'";
        $query = "SELECT g.idGENE, g.Primary_SGDID, g.Feature_Name, g.Standard_Gene_Name, g.Alias FROM gene g WHERE g.Feature_Name = '" . $gene_name . "' OR g.Standard_Gene_Name = '" . $gene_name . "'";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        
        if ($row = mysqli_fetch_row($result)) {
            $gene_data = new GeneData();
            $gene_data->gene_id = addslashes($row[0]);
            $gene_data->sgd_id = addslashes($row[1]);
            $gene_data->feature_name = addslashes($row[2]);
            $gene_data->standard_gene_name = addslashes($row[3]);
            $gene_data->alias = addslashes($row[4]);
            //$gene_data->description = addslashes($row[5]);
            return $gene_data;
        }
        return null;
    }

    function getStrainList($gene_name){
        $gene_id = $this->getGeneIDByGeneName($gene_name);
        if ($gene_id == -1)
            return null;
        $strain_array = array();
        $query = "SELECT s.idSTRAIN FROM strain s WHERE s.GENE_idGENE = " . $gene_id;
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        while ($row = mysqli_fetch_row($result)) {
            array_push($strain_array, $row[0]);
        }
        return $strain_array;
    }
    
    /**
     *
     * @param type $gene_name
     * @return type 
     */
    function getStrainID($gene_name, $mark_type) {
        $gene_id = $this->getGeneIDByGeneName($gene_name);
        if ($gene_id == -1)
            return -1;
        $query = "SELECT s.idSTRAIN FROM strain s WHERE s.GENE_idGENE = " . $gene_id . " AND s.Marker_Type = " . $mark_type."";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return -1;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return -1;
        if ($row = mysqli_fetch_row($result)) {
            return $row[0];
        }
        return -1;
    }

    
    function getStrainNameByStrainID($strain_id) {
        $query = "SELECT s.Strain_Name FROM strain s WHERE s.idSTRAIN = " . $strain_id;
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        if ($row = mysqli_fetch_row($result)) {
            return $row[0];
        }
        return null;
    }

    /**
     * 获取给定基因和其他所有基因之间的interaciton反应信息，包括gene信息，starin信息和interaciton信息
     * 思路是先根据gene名字获取strain的ID，然后查询query strain 或 array strain为此ID的interaction记录
     * 然后获取两个strain的ID, 最后和用两个gene查询相同，不过要判断是query还是array
     * @param type $gene    
     * @param type $type    
     * @param type $score   
     * @param type $p_value 
     */
    function getWithInteractionData($gene, $type, $score, $p_value) {
        $interaction_array = array();
        $strain_query = $this->getStrainID($gene, self::$MARKER_TYPE_QUERY);
        $strain_array = $this->getStrainID($gene, self::$MARKER_TYPE_ARRAY);
        if ($strain_query == -1 && $strain_array == -1) return $interaction_array;
        $query = "SELECT i.Query_Strain, i.Array_Strain, i.Interaction_Score, i.P_Value FROM interaction i WHERE i.Interaction_Score " . ($type == 'n' ?  " < " :  " > ") . $score ." AND i.P_Value < " . $p_value ." AND  (i.Query_Strain = " . $strain_query . " OR i.Array_Strain = " . $strain_array . ") ORDER BY i.Interaction_Score " . ($type == 'n' ?  "ASC" :  "DESC");
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        
        $gene1_data = null;
        $gene2_data = null;
        $strain1_name = "";
        $strain2_name = "";
        while ($row = mysqli_fetch_row($result)){
            if ($strain_query == $row[0]){
                $gene1_data = $this->getGeneDataByGeneName($gene);
                if (is_null($gene1_data)) continue;
                $gene2_name = $this->getGeneNameByStrainId($row[1]);
                if (is_null($gene2_name)) continue;
                $gene2_data = $this->getGeneDataByGeneName($gene2_name);
                if (is_null($gene2_data)) continue;
                $strain1_name = $this->getStrainNameByStrainID($row[0]);
                $strain2_name = $this->getStrainNameByStrainID($row[1]);
                $interaction = $this->getInteraction($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2], $row[3]);
                array_push($interaction_array, $interaction);
            } elseif ($strain_array == $row[1]) {
                $gene1_name = $this->getGeneNameByStrainId($row[0]);
                if (is_null($gene1_name)) continue;
                $gene1_data = $this->getGeneDataByGeneName($gene1_name);
                if (is_null($gene1_data)) continue;
                $gene2_data = $this->getGeneDataByGeneName($gene);
                if (is_null($gene2_data)) continue;
                
                $strain1_name = $this->getStrainNameByStrainID($row[0]);
                $strain2_name = $this->getStrainNameByStrainID($row[1]);
                $interaction = $this->getInteraction($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2], $row[3]);
                array_push($interaction_array, $interaction);
            } else {
                continue;
            }
        }
        
        return $interaction_array;
    }

    /**
     * 获取两个基因之间的interaciton反应数据，包括gene的信息，starin的信息和interaction的信息
     * @param type $gene1    The name of Gene 1
     * @param type $gene2    The name of Gene 1
     * @param type $type     Positive or Negative
     * @param type $score    Interaction score between the two genes
     * @param type $p_value  P value of the two genes
     * @return type 
     */
    function getWithInInteractionData($gene1, $gene2, $type, $score, $p_value) {
        
        $strain_id_gene1 = $this->getStrainID($gene1, self::$MARKER_TYPE_QUERY);
        $strain_id_gene2 = $this->getStrainID($gene2, self::$MARKER_TYPE_ARRAY);
        if ($strain_id_gene1 == -1 && $strain_id_gene2 == -1) {
            return null;
        }
        
        $gene1_data = $this->getGeneDataByGeneName($gene1);
        $gene2_data = $this->getGeneDataByGeneName($gene2);
        if ($gene1_data == null || $gene2_data == null) {
            return null;
        }
        
        $strain1_name = $this->getStrainNameByStrainID($strain_id_gene1);
        $strain2_name = $this->getStrainNameByStrainID($strain_id_gene2);
        
        $query = "SELECT i.Interaction_Score, i.P_Value FROM interaction i WHERE i.Interaction_Score " . ($type == 'n' ?  " < " :  " > ") . $score ." AND i.P_Value < " . $p_value ." AND i.Query_Strain = " . $strain_id_gene1 . " AND i.Array_Strain = " .$strain_id_gene2;
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        if ($row = mysqli_fetch_row($result)) {
            return $this->getInteraction($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[0], $row[1]);
        }
        
        return null;
    }
    
    
    function getWithCorrelationData($gene, $type){
        $correlation_array = array();
        $strain_1 = $this->getStrainID($gene, self::$MARKER_TYPE_QUERY);
        $strain_2 = $this->getStrainID($gene, self::$MARKER_TYPE_ARRAY);
        if ($strain_query == -1 && $strain_array == -1) return $correlation_array;
        $query = "SELECT c.Strain1, c.Strain2, c.Correlation_Score FROM correlation c WHERE" . ($type == 'significant' ?  " (c.Correlation_Score > 0.1 OR c.Correlation_Score < -0.1) AND " :  "")  ." (c.Strain1 = " . $strain_1 . " OR c.Strain2 = " .$strain_2 . ") ORDER BY c.Correlation_Score DESC";
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        
        $correlation_array = array();
        $gene1_data = null;
        $gene2_data = null;
        $strain1_name = "";
        $strain2_name = "";
        while ($row = mysqli_fetch_row($result)){
            if ($strain_1 == $row[0]){
                $gene1_data = $this->getGeneDataByGeneName($gene);
                if (is_null($gene1_data)) continue;
                $gene2_name = $this->getGeneNameByStrainId($row[1]);
                if (is_null($gene2_name)) continue;
                $gene2_data = $this->getGeneDataByGeneName($gene2_name);
                if (is_null($gene2_data)) continue;
                
                $strain1_name = $this->getStrainNameByStrainID($row[0]);
                $strain2_name = $this->getStrainNameByStrainID($row[1]);
                $interaction = $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2]);
                array_push($correlation_array, $interaction);
            } elseif ($strain_2 == $row[1]) {
                $gene1_name = $this->getGeneNameByStrainId($row[0]);
                if (is_null($gene1_name)) continue;
                $gene1_data = $this->getGeneDataByGeneName($gene1_name);
                if (is_null($gene1_data)) continue;
                $gene2_data = $this->getGeneDataByGeneName($gene);
                if (is_null($gene2_data)) continue;
                
                $strain1_name = $this->getStrainNameByStrainID($row[0]);
                $strain2_name = $this->getStrainNameByStrainID($row[1]);
                $interaction = $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2]);
                array_push($correlation_array, $interaction);
            } else {
                continue;
            }
        }
        
        return $correlation_array;
    }
    
    function getWithInCorrelationData($gene1, $gene2, $type){
        if ($strain_query == -1 && $strain_array == -1) return null;
        $strain_id_gene1 = $this->getStrainID($gene1, self::$MARKER_TYPE_QUERY);
        $strain_id_gene2 = $this->getStrainID($gene2, self::$MARKER_TYPE_ARRAY);
        if ($strain_id_gene1 == -1 || $strain_id_gene2 == -1) {
            return null;
        }
        
        $gene1_data = $this->getGeneDataByGeneName($gene1);
        $gene2_data = $this->getGeneDataByGeneName($gene2);
        if ($gene1_data == null || $gene2_data == null) {
            return null;
        }
        
        $strain1_name = $this->getStrainNameByStrainID($strain_id_gene1);
        $strain2_name = $this->getStrainNameByStrainID($strain_id_gene2);
        
        $query = "SELECT c.Correlation_Score FROM correlation c WHERE" . ($type == 'significant' ?  " (c.Correlation_Score > 0.1 OR c.Correlation_Score < -0.1) AND " :  "")  ." c.Strain1 = " . $strain_id_gene1 . " AND c.Strain2 = " .$strain_id_gene2 . " ORDER BY c.Correlation_Score DESC";
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        if ($row = mysqli_fetch_row($result)) {
            return $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[0]);
        }
        
        return null;
    }
    
    function getAdditionalRelation($gene1_id, $gene2_id, $gene1_feature_name, $gene2_feature_name, $type){
        $table_name = "";
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
        $query = "SELECT adt.Score, adt.Network_ID FROM " . $table_name . " adt WHERE adt.Is_Chosen = 1 AND adt.Gene_A_ID = ". $gene1_id . " AND adt.Gene_B_ID = " .$gene2_id;
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        if ($row = mysqli_fetch_row($result)) {
            return $this->getAddtionalRelation($gene1_id, $gene2_id, $gene1_feature_name, $gene2_feature_name, $row[0], $row[1]);
        }
        
        return null;
    }
    
    function getInteraction($gene1_data, $gene2_data, $strain1_name, $strain2_name, $score, $p_value){
        $interaction_data = new Relation();
        $interaction_data->gene1_id = $gene1_data->gene_id;
        $interaction_data->gene1_sdg_id = $gene1_data->sgd_id;
        $interaction_data->gene1_feature_name = $gene1_data->feature_name;
        $interaction_data->gene1_standard_gene_name = $gene1_data->standard_gene_name;
        $interaction_data->gene1_alias = $gene1_data->alias;
        //$interaction_data->gene1_description = $gene1_data->description;

        $interaction_data->gene2_id = $gene2_data->gene_id;
        $interaction_data->gene2_sdg_id = $gene2_data->sgd_id;
        $interaction_data->gene2_feature_name = $gene2_data->feature_name;
        $interaction_data->gene2_standard_gene_name = $gene2_data->standard_gene_name;
        $interaction_data->gene2_alias = $gene2_data->alias;
        //$interaction_data->gene2_description = $gene2_data->description;

        $interaction_data->strain1_name = $strain1_name;
        $interaction_data->strain2_name = $strain2_name;

        $interaction_data->score = number_format($score, 3);
        $p_value = str_replace("E", "e",$p_value);
        $tmp_p_value = explode('e', strval($p_value));
        $interaction_data->p_value = number_format($tmp_p_value[0], 3) . (count($tmp_p_value) == 2 ? 'E' . $tmp_p_value[1] : '');

        return $interaction_data;
    }
    
    function getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $score){
        $correlation_data = new Relation();
        $correlation_data->gene1_id = $gene1_data->gene_id;
        $correlation_data->gene1_sdg_id = $gene1_data->sgd_id;
        $correlation_data->gene1_feature_name = $gene1_data->feature_name;
        $correlation_data->gene1_standard_gene_name = $gene1_data->standard_gene_name;
        $correlation_data->gene1_alias = $gene1_data->alias;
        //$correlation_data->gene1_description = $gene1_data->description;

        $correlation_data->gene2_id = $gene2_data->gene_id;
        $correlation_data->gene2_sdg_id = $gene2_data->sgd_id;
        $correlation_data->gene2_feature_name = $gene2_data->feature_name;
        $correlation_data->gene2_standard_gene_name = $gene2_data->standard_gene_name;
        $correlation_data->gene2_alias = $gene2_data->alias;
        //$correlation_data->gene2_description = $gene2_data->description;

        $correlation_data->strain1_name = $strain1_name;
        $correlation_data->strain2_name = $strain2_name;

        $correlation_data->score = number_format($score, 3);

        return $correlation_data;
    }
    
    function getAddtionalRelation($gene1_id, $gene2_id, $gene1_feature_name, $gene2_feature_name, $score, $network_id){
        $additional_date = new Relation();
        $additional_date->gene1_id = $gene1_id;
        $additional_date->gene1_feature_name = $gene1_feature_name;
        $additional_date->gene1_standard_gene_name = '';
                
        $additional_date->gene2_id = $gene2_id;
        $additional_date->gene2_feature_name = $gene2_feature_name;
        $additional_date->gene1_standard_gene_name = '';
        
        $additional_date->score = $score;
        $additional_date->p_value = ($network_id / 100);
        return $additional_date;
    }
    
    
}
