<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Correlation
 *
 * @author GGCoke
 */
class Correlation {
    //put your code here
    private $gene1_id;
    private $gene1_sdg_id;
    private $gene1_feature_name;
    private $gene1_standard_gene_name;
    private $gene1_alias;
    private $gene1_description;
    
    private $gene2_id;
    private $gene2_sdg_id;
    private $gene2_feature_name;
    private $gene2_standard_gene_name;
    private $gene2_alias;
    private $gene2_description;
    
    private $strain1_name;
    private $strain2_name;
    
    private $score;
    
    function __construct() {}
    
    function __set($name, $value) {
        $this->$name = $value;
    }
    
    function __get($name) {
        return $this->$name;
    }
}

?>
