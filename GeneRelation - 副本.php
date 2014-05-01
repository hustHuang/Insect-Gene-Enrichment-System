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
        $query = "SELECT g.id FROM SC_gene g WHERE g.feature_name = '" . $gene_name . "' OR g.standard_gene_name = '" . $gene_name . "'";
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

    function getGeneNameByStrainId($id) {
        $query = "SELECT g.feature_name, g.standard_gene_name FROM SC_gene g, SC_strain s WHERE g.id = s.gene_id AND s.id = " . $id;
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        if ($row = mysqli_fetch_row($result)) {
            return ($row[1] == null || $row[1] == '' ? $row[0] : $row[1]);
        }
        return null;
    }

    function getGeneDataByGeneName($gene_name) {
        // $query = "SELECT g.idGENE, g.Primary_SGDID, g.Feature_Name, g.Standard_Gene_Name, g.Alias, g.Description FROM gene g WHERE g.Feature_Name = '" . $gene_name . "' OR g.Standard_Gene_Name = '" . $gene_name . "'";
        $query = "SELECT g.id, g.primary_sgdid, g.feature_name, g.standard_gene_name, g.alias FROM SC_gene g WHERE g.feature_name = '" . $gene_name . "' OR g.standard_gene_name = '" . $gene_name . "'";
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

    function getStrainList($gene_name) {
        $gene_id = $this->getGeneIDByGeneName($gene_name);
        if ($gene_id == -1)
            return null;
        $strain_array = array();
        $query = "SELECT s.id FROM SC_strain s WHERE s.gene_id = " . $gene_id;
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
        $query = "SELECT s.id FROM SC_strain s WHERE s.gene_id = " . $gene_id . " and s.marker_type=$mark_type";
        //$query = "SELECT s.id FROM SC_strain s WHERE s.gene_id = " . $gene_id . "";
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

    function getStrainsID($gene_name, $mark_type) {
        $result = array();
        $gene_id = $this->getGeneIDByGeneName($gene_name);
        if ($gene_id == -1)
            return -1;
        $query = "SELECT s.id FROM SC_strain s WHERE s.gene_id = " . $gene_id . " and s.marker_type=$mark_type ORDER BY s.id ASC";
        //$query = "SELECT s.id FROM SC_strain s WHERE s.gene_id = " . $gene_id . "";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc))
            return -1;
        $temp_result = mysqli_query($dbc, $query);
        if (!$temp_result)
            return -1;
        while ($row = mysqli_fetch_row($temp_result)) {
            array_push($result, $row[0]);
        }
        if (!is_null($result)) {
            return $result;
        }
        return -1;
    }

    function getStrainUniqueID($gene_name) {

        $gene = explode("_", trim($gene_name));
        // echo $gene[0];
        $alled = $gene[1];
        $gene_id = $this->getGeneIDByGeneName($gene[0]);
        if ($gene_id == -1)
            return -1;
        $query = "SELECT s.id FROM SC_strain s WHERE s.gene_id =$gene_id and s.name='" . $alled . "'";
        //echo $query;
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
        $query = "SELECT s.name FROM SC_strain s WHERE s.id = " . $strain_id;
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

    function getStrainScoreByStrainID($strain_id){
        $query = "SELECT s.fitness_score FROM SC_strain_smf s WHERE s.id = " . $strain_id;
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
        $strain_query = $this->getStrainsID($gene, self::$MARKER_TYPE_QUERY);
        $strain_array = $this->getStrainsID($gene, self::$MARKER_TYPE_ARRAY);
        if ($strain_query == -1 && $strain_array == -1)
            return $interaction_array;
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        for ($i = 0; $i < count($strain_query); $i++) {
            $query = "SELECT i.query, i.array, i.score, i.p_value,i.dataset FROM SC_gi_interaction i WHERE i.score " . ($type == 'n' ? " < " : " > ") . $score . " AND i.p_value < " . $p_value . " AND  i.query = " . $strain_query[$i] . "  ORDER BY i.score " . ($type == 'n' ? "ASC" : "DESC");
            // echo $query;
            $result = mysqli_query($dbc, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_row($result)) {
                    $gene1_data = $this->getGeneDataByGeneName($gene);
                    if (is_null($gene1_data))
                        continue;
                    $gene2_name = $this->getGeneNameByStrainId($row[1]);
                    if (is_null($gene2_name))
                        continue;
                    $gene2_data = $this->getGeneDataByGeneName($gene2_name);
                    if (is_null($gene2_data))
                        continue;
                    $strain1_name = $this->getStrainNameByStrainID($row[0]);
                    $strain2_name = $this->getStrainNameByStrainID($row[1]);
                    
                    $strain1_score = $this->getStrainScoreByStrainID($row[0]);
                    $strain2_score = $this->getStrainScoreByStrainID($row[1]);
                    
                    $interaction = $this->getInteraction($gene1_data, $gene2_data, $strain1_name,$strain1_score, $strain2_name,$strain2_score, $row[2], $row[3], $row[4]);
                    array_push($interaction_array, $interaction);
                }

                //return $interaction_array;
                //break;               
            }
        }

        for ($j = 0; $j < count($strain_array); $j++) {
            $query = "SELECT i.query, i.array, i.score, i.p_value,i.dataset FROM SC_gi_interaction i WHERE  i.score " . ($type == 'n' ? " < " : " > ") . $score . " AND i.p_value < " . $p_value . " AND  i.array = " . $strain_array[$j] . " ORDER BY i.score " . ($type == 'n' ? "ASC" : "DESC");
            $result = mysqli_query($dbc, $query);
            //var_dump($result);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_row($result)) {
                    $gene1_name = $this->getGeneNameByStrainId($row[0]);
                    if (is_null($gene1_name))
                        continue;
                    $gene1_data = $this->getGeneDataByGeneName($gene1_name);
                    if (is_null($gene1_data))
                        continue;
                    $gene2_data = $this->getGeneDataByGeneName($gene);
                    if (is_null($gene2_data))
                        continue;
                    $strain1_name = $this->getStrainNameByStrainID($row[0]);
                    $strain2_name = $this->getStrainNameByStrainID($row[1]);
                    
                    $strain1_score = $this->getStrainScoreByStrainID($row[0]);
                    $strain2_score = $this->getStrainScoreByStrainID($row[1]);                    
                    
                    $interaction = $this->getInteraction($gene1_data, $gene2_data, $strain1_name,$strain1_score,$strain2_name,$strain2_score, $row[2], $row[3], $row[4]);
                    array_push($interaction_array, $interaction);
                }
                //return $interaction_array;
                //break;
            }
        }
        /*
          $query = "SELECT i.query, i.array, i.score, i.p_value FROM SC_gi_interaction i WHERE i.tcondition=".$database." AND i.score " . ($type == 'n' ? " < " : " > ") . $score . " AND i.p_value < " . $p_value . " AND  (i.query = " . $strain_query . " OR i.array = " . $strain_array . ") ORDER BY i.score " . ($type == 'n' ? "ASC" : "DESC");
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
          while ($row = mysqli_fetch_row($result)) {
          if ($strain_query == $row[0]) {
          $gene1_data = $this->getGeneDataByGeneName($gene);
          if (is_null($gene1_data))
          continue;
          $gene2_name = $this->getGeneNameByStrainId($row[1]);
          if (is_null($gene2_name))
          continue;
          $gene2_data = $this->getGeneDataByGeneName($gene2_name);
          if (is_null($gene2_data))
          continue;
          $strain1_name = $this->getStrainNameByStrainID($row[0]);
          $strain2_name = $this->getStrainNameByStrainID($row[1]);
          $interaction = $this->getInteraction($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2], $row[3], $row[4]);
          array_push($interaction_array, $interaction);
          } elseif ($strain_array == $row[1]) {
          $gene1_name = $this->getGeneNameByStrainId($row[0]);
          if (is_null($gene1_name))
          continue;
          $gene1_data = $this->getGeneDataByGeneName($gene1_name);
          if (is_null($gene1_data))
          continue;
          $gene2_data = $this->getGeneDataByGeneName($gene);
          if (is_null($gene2_data))
          continue;

          $strain1_name = $this->getStrainNameByStrainID($row[0]);
          $strain2_name = $this->getStrainNameByStrainID($row[1]);
          $interaction = $this->getInteraction($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2], $row[3], $row[4]);
          array_push($interaction_array, $interaction);
          } else {
          continue;
          }
          }
         */
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

        $strain_id_gene1 = $this->getStrainsID($gene1, self::$MARKER_TYPE_QUERY);
        $strain_id_gene2 = $this->getStrainsID($gene2, self::$MARKER_TYPE_ARRAY);
        if ($strain_id_gene1 == -1 && $strain_id_gene2 == -1) {
            return null;
        }

        $gene1_data = $this->getGeneDataByGeneName($gene1);
        $gene2_data = $this->getGeneDataByGeneName($gene2);
        if ($gene1_data == null || $gene2_data == null) {
            return null;
        }
        for ($i = 0; $i < count($strain_id_gene1); $i++) {
            for ($j = 0; $j < count($strain_id_gene2); $j++) {
                $strain1_name = $this->getStrainNameByStrainID($strain_id_gene1);
                $strain2_name = $this->getStrainNameByStrainID($strain_id_gene2);

                $query = "SELECT i.score, i.p_value, i.dataset FROM SC_gi_interaction i WHERE i.score " . ($type == 'n' ? " < " : " > ") . $score . " AND i.p_value < " . $p_value . " AND i.query = " . $strain_id_gene1[$i] . " AND i.array = " . $strain_id_gene2[$j] . "";
                $dbc = DBCxn::get_conn();
                if ($dbc == null)
                    return null;
                $result = mysqli_query($dbc, $query);
                if (!$result)
                    return null;
                if ($row = mysqli_fetch_row($result)) {
                    return $this->getInteraction($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[0], $row[1], $row[2]);
                }
            }
        }

        return null;
    }

    function getWithCorrelationData($gene, $type) {
        $correlation_array = array();
        $strain_1 = $this->getStrainsID($gene, self::$MARKER_TYPE_QUERY);
        $strain_2 = $this->getStrainsID($gene, self::$MARKER_TYPE_ARRAY);
        if ($strain_1 == -1 && $strain_2 == -1)
            return $correlation_array;
        $correlation_array = array();
        $gene1_data = null;
        $gene2_data = null;
        $strain1_name = "";
        $strain2_name = "";
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;

        for ($i = 0; $i < count($strain_1); $i++) {
            $query = "SELECT c.strain1, c.strain2, c.score FROM SC_gi_correlation c WHERE" . ($type == 'significant' ? " (c.score > 0.1 OR c.score < -0.1) AND " : "") . " (c.strain1 = " . $strain_1[$i] . ") ORDER BY c.score DESC";
            $result = mysqli_query($dbc, $query);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_row($result)) {
                    $gene1_data = $this->getGeneDataByGeneName($gene);
                    if (is_null($gene1_data))
                        continue;
                    $gene2_name = $this->getGeneNameByStrainId($row[1]);
                    if (is_null($gene2_name))
                        continue;
                    $gene2_data = $this->getGeneDataByGeneName($gene2_name);
                    if (is_null($gene2_data))
                        continue;
                    $strain1_name = $this->getStrainNameByStrainID($row[0]);
                    $strain2_name = $this->getStrainNameByStrainID($row[1]);
                    $interaction = $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2]);
                    array_push($correlation_array, $interaction);
                }
                return $correlation_array;
                //break;               
            }
        }

        for ($j = 0; $j < count($strain_2); $j++) {
            $query = "SELECT c.strain1, c.strain2, c.score FROM SC_gi_correlation c WHERE" . ($type == 'significant' ? " (c.score > 0.1 OR c.score < -0.1) AND " : "") . " (c.strain2 = " . $strain_2[$j] . ") ORDER BY c.score DESC";
            $result = mysqli_query($dbc, $query);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_row($result)) {
                    $gene1_name = $this->getGeneNameByStrainId($row[0]);
                    if (is_null($gene1_name))
                        continue;
                    $gene1_data = $this->getGeneDataByGeneName($gene1_name);
                    if (is_null($gene1_data))
                        continue;
                    $gene2_data = $this->getGeneDataByGeneName($gene);
                    if (is_null($gene2_data))
                        continue;
                    $strain1_name = $this->getStrainNameByStrainID($row[0]);
                    $strain2_name = $this->getStrainNameByStrainID($row[1]);
                    $interaction = $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2]);
                    array_push($correlation_array, $interaction);
                }
                return $correlation_array;
                //break;
            }
        }
        /* $query = "SELECT c.strain1, c.strain2, c.score FROM SC_gi_correlation c WHERE" . ($type == 'significant' ? " (c.score > 0.1 OR c.score < -0.1) AND " : "") . " (c.strain1 = " . $strain_1 . " OR c.strain2 = " . $strain_2 . ") ORDER BY c.score DESC";
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
          while ($row = mysqli_fetch_row($result)) {
          if ($strain_1 == $row[0]) {
          $gene1_data = $this->getGeneDataByGeneName($gene);
          if (is_null($gene1_data))
          continue;
          $gene2_name = $this->getGeneNameByStrainId($row[1]);
          if (is_null($gene2_name))
          continue;
          $gene2_data = $this->getGeneDataByGeneName($gene2_name);
          if (is_null($gene2_data))
          continue;

          $strain1_name = $this->getStrainNameByStrainID($row[0]);
          $strain2_name = $this->getStrainNameByStrainID($row[1]);
          $interaction = $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2]);
          array_push($correlation_array, $interaction);
          } elseif ($strain_2 == $row[1]) {
          $gene1_name = $this->getGeneNameByStrainId($row[0]);
          if (is_null($gene1_name))
          continue;
          $gene1_data = $this->getGeneDataByGeneName($gene1_name);
          if (is_null($gene1_data))
          continue;
          $gene2_data = $this->getGeneDataByGeneName($gene);
          if (is_null($gene2_data))
          continue;

          $strain1_name = $this->getStrainNameByStrainID($row[0]);
          $strain2_name = $this->getStrainNameByStrainID($row[1]);
          $interaction = $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[2]);
          array_push($correlation_array, $interaction);
          } else {
          continue;
          }
          } */
        return $correlation_array;
    }

    function getWithInCorrelationData($gene1, $gene2, $type) {

        $strain_id_gene1 = $this->getStrainsID($gene1, self::$MARKER_TYPE_QUERY);
        $strain_id_gene2 = $this->getStrainsID($gene2, self::$MARKER_TYPE_ARRAY);
        if ($strain_id_gene1 == -1 || $strain_id_gene2 == -1) {
            return null;
        }

        $gene1_data = $this->getGeneDataByGeneName($gene1);
        $gene2_data = $this->getGeneDataByGeneName($gene2);
        if ($gene1_data == null || $gene2_data == null) {
            return null;
        }
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        for ($i = 0; $i < count($strain_id_gene1); $i++) {
            for ($j = 0; $j < count($strain_id_gene2); $j++) {
                $strain1_name = $this->getStrainNameByStrainID($strain_id_gene1[$i]);
                $strain2_name = $this->getStrainNameByStrainID($strain_id_gene2[$j]);

                $query = "SELECT c.score FROM SC_gi_correlation c WHERE" . ($type == 'significant' ? " (c.score > 0.1 OR c.score < -0.1) AND " : "") . " c.Strain1 = " . $strain_id_gene1[$i] . " AND c.Strain2 = " . $strain_id_gene2[$j] . " ORDER BY c.score DESC";
                $result = mysqli_query($dbc, $query);
                if (!$result)
                    continue;
                if ($row = mysqli_fetch_row($result)) {
                    return $this->getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $row[0]);
                }
            }
        }
        return null;
    }

    function getAdditionalRelation($gene1_id, $gene2_id, $gene1_feature_name, $gene2_feature_name, $type) {
        $table_name = "";
        switch ($type) {
            case 'coexp':
                $table_name = 'SC_co_expression_network';
                break;
            case 'coloc':
                $table_name = 'SC_co_localization_network';
                break;
            case 'pi':
                $table_name = 'SC_physical_interaction_network';
                break;
            case 'spd':
                $table_name = 'SC_shared_protein_domains_network';
                break;
            default:
                break;
        }
        $query = "SELECT adt.score, adt.networkid FROM " . $table_name . " adt WHERE adt.gene_a = " . $gene1_id . " AND adt.gene_b = " . $gene2_id;
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

    function getInteraction($gene1_data, $gene2_data, $strain1_name,$strain1_score, $strain2_name,$strain2_score, $score, $p_value, $dataset) {
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
        $interaction_data->strain1_score = $strain1_score;
        $interaction_data->strain2_score = $strain2_score;
        $interaction_data->dataset = $dataset;

        $interaction_data->score = number_format($score, 3);
        $p_value = str_replace("E", "e", $p_value);
        $tmp_p_value = explode('e', strval($p_value));
        $interaction_data->p_value = number_format($tmp_p_value[0], 3) . (count($tmp_p_value) == 2 ? 'E' . $tmp_p_value[1] : '');

        return $interaction_data;
    }

    function getCorrelation($gene1_data, $gene2_data, $strain1_name, $strain2_name, $score) {
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

    function getAddtionalRelation($gene1_id, $gene2_id, $gene1_feature_name, $gene2_feature_name, $score, $network_id) {
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

    function getIntercorrforEdgeInfo($gene1, $gene2, $type) {
        $intercorr_array = array();
        $unique_array = array();
        $intercorr1 = $this->getGeneIDBYGeneName($gene1);

        $intercorr2 = $this->getGeneIDBYGeneName($gene2);

        if ($intercorr1 == -1 || $intercorr2 == -1)
            return;
        $type1 = 'SC_' . $type . '_network';
        $query = "SELECT adt.gene_a,adt.gene_b,adt.score,nw.network_name,nw.pubmed_id from " . $type1 . " adt,SC_networks_source nw WHERE ((adt.gene_a=$intercorr1 and adt.gene_b=$intercorr2) or (adt.gene_a=$intercorr2 and adt.gene_b=$intercorr1))and nw.id=adt.networkid ";

        $dbc = DBCxn::get_conn();
        if ($dbc == null) {
            return null;
        }
        $result = mysqli_query($dbc, $query);

        if (!$result) {
            
        }

        while ($row = mysqli_fetch_row($result)) {
            $interaction = $this->getIntercorrforJudge($gene1, $gene2, $type, $row[3], $row[4]);
            $inte2 = $this->getIntercorrEdgeInfo($gene1, $gene2, $row[2], $type, $row[3], $row[4]);
            if (!in_array($interaction, $unique_array)) {
                array_push($intercorr_array, $inte2);
                array_push($unique_array, $interaction);
            }
        }
        if ($intercorr_array != null)
            return $intercorr_array;
    }

    function getIntercorrEdgeInfo($gene1, $gene2, $weight, $type, $network, $pubmedid) {

        $intercorr_data = new MiRelation();
        $intercorr_data->gene1_Symbol = $gene1;
        $intercorr_data->gene2_Symbol = $gene2;
        // $intercorr_data->weight = number_format($weight, 5);
        $intercorr_data->weight = $weight;
        $intercorr_data->type = $type;
        $intercorr_data->network = $network;
        $intercorr_data->pubmedid = $pubmedid;
        //echo $intercorr_data->pubmedid;
        return $intercorr_data;
    }

    function getIntercorrforJudge($gene1, $gene2, $type, $db, $pubmedid) {

        $intercorr_data = new MiRelation();
        $intercorr_data->gene1_Symbol = $gene1;
        $intercorr_data->gene2_Symbol = $gene2;
        //$intercorr_data->weight = number_format($weight, 3);
        $intercorr_data->type = $type;
        $intercorr_data->network = $db;
        $intercorr_data->pubmedid = $pubmedid;
        return $intercorr_data;
    }

    function getWithCorrelationGene($gene, $type) {
        $correlation_array = array();
        $strain_1 = $this->getStrainID($gene, self::$MARKER_TYPE_QUERY);
        $strain_2 = $this->getStrainID($gene, self::$MARKER_TYPE_ARRAY);
        if ($strain_1 == -1 && $strain_2 == -1) {
            array_push($correlation_array, $gene);
            return $correlation_array;
        }
        $query = "SELECT c.strain1, c.strain2 FROM SC_gi_correlation c WHERE" . ($type == 'significant' ? " (c.score > 0.1 OR c.score < -0.1) AND " : "") . " (c.strain1 = " . $strain_1 . " OR c.strain2 = " . $strain_2 . ") ORDER BY c.score DESC";
        //echo $query;
        $dbc = DBCxn::get_conn();
        if ($dbc == null)
            return null;
        $result = mysqli_query($dbc, $query);
        if (!$result)
            return null;
        $strain1_name = "";
        $strain2_name = "";
        while ($row = mysqli_fetch_row($result)) {
            if ($strain_1 == $row[0]) {
                $strain2_name = $this->getGeneNameByStrainId($row[1]);
                array_push($correlation_array, $strain2_name);
            } elseif ($strain_2 == $row[1]) {
                $strain1_name = $this->getGeneNameByStrainId($row[0]);
                array_push($correlation_array, $strain1_name);
            } else {
                continue;
            }
        }
        array_push($correlation_array, $gene);
        return $correlation_array;
    }

}

?>
