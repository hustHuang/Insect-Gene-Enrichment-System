<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Interaction
 *
 * @author GGCoke
 */
class MiRelation {
    //put your code here
    //public $gene1_id;
    public $gene1_Symbol;
    //public $gene1_Chromosome;
    //public $gene1_MapLocation;
    //public $gene1_synomyms;
    //public $gene1_description;
    
    //public $gene2_id;
    public $gene2_Symbol;
    //public $gene2_Chromosome;
    //public $gene2_MapLocation;
    //public $gene2_synomyms;
    //public $gene2_description;
    
    
   // public $strain1_name;
   // public $strain2_name;
    public $network;
    public $pubmedid;
    public $weight;
    public $type;
    //public $p_value;
    
    function __construct() {}
    
    function __set($name, $value) {
        $this->$name = $value;
    }
    
    function __get($name) {
        return $this->$name;
    }
}

?>
