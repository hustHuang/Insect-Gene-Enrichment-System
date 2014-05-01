<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GeneDataFetcher
 *
 * @author GGCoke
 */
class GeneDataFetcher {
    //put your code here
    private static $DB_HOST = 'localhost';
    private static $DB_USER = 'root';
    private static $DB_PASS = '123';
    private static $DB_NAME = 'sga';
    
    private static $SQL_INSERT_BIOCHEMICAL_PATHWAY = "INSERT INTO Biochemical_Pathway(Biochemical_Pathway_Name, Biochemical_Pathway_Enzyme_Name, Biochemical_Pathway_Num_Of_Reaction, Biochemical_Pathway_Gene_Name, Biochemical_Pathway_Reference) VALUES (?, ?, ?, ?, ?)";
    private static $SQL_INSERT_COMPLEX_SLIM = "INSERT INTO GO_Protein_Complex_Slim(GO_Protein_Complex_Slim_Ontology_GO_Term, GO_Protein_Complex_Slim_Ontology_GO_ID, GO_Protein_Complex_Slim_Ontology_Gene_Name, GO_Protein_Complex_Slim_Ontology_ORF) VALUES (?, ?, ?, ?)";
    private static $SQL_INSERT_SLIM_MAPPING = "INSERT INTO GO_Slim_Mapping(GO_Slim_Mapping_ORF, GO_Slim_Mapping_Gene_Name, GO_Slim_Mapping_SGDID, GO_Slim_Mapping_Aspect, GO_Slim_Mapping_Slim_GO_Slim_Term, GO_Slim_Mapping_GOID) VALUES (?, ?, ?, ?, ?, ?)";
    
    private static $FILE_BIOCHEMICAL_PATHWAY = "../data/biochemical_pathways.tab";
    private static $FILE_GO_SLIM_MAPPING = "../data/go_slim_mapping.tab";
    private static $FILE_GO_COMPLEX_SLIM = "../data/go_protein_complex_slim.tab";
    
    function __construct() {
    }
    
    function fetch_pathway_data() {
        // Get biochemical pathway data from $FILE_BIOCHEMICAL_PATHWAY
        $file = fopen(self::$FILE_BIOCHEMICAL_PATHWAY, "rb");
        if (!$file) {
            echo "Cannot Open file: " . self::$FILE_BIOCHEMICAL_PATHWAY;
            return;
        }
        $dbc = mysqli_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME) or die("Error: Could not connect to MySQL server");
        if ($stmt = mysqli_prepare($dbc, self::$SQL_INSERT_BIOCHEMICAL_PATHWAY)) {
            mysqli_bind_param($stmt, "sssss", $pathway_name, $enzyme_name, $num_of_reaction, $gene_name, $reference);
            while (!feof($file)) {
                $content = explode("\t", fgets($file));
                if (count($content) != 5 || $content[3] == null)
                    continue;
                $pathway_name = $content[0];
                $enzyme_name = $content[1];
                $num_of_reaction = $content[2];
                $gene_name = $content[3];
                $reference = $content[4];
                mysqli_stmt_execute($stmt);
            }
        }

        mysqli_close($dbc);
        fclose($file);
    }
    
    function fetch_slim_mapping_data() {
        // Get biochemical pathway data from $FILE_BIOCHEMICAL_PATHWAY
        $file = fopen(self::$FILE_GO_SLIM_MAPPING, "rb");
        if (!$file) {
            echo "Cannot Open file: " . self::$FILE_GO_SLIM_MAPPING;
            return;
        }
        $dbc = mysqli_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME) or die("Error: Could not connect to MySQL server");
        if ($stmt = mysqli_prepare($dbc, self::$SQL_INSERT_SLIM_MAPPING)) {
            mysqli_bind_param($stmt, "ssssss", $orf, $gene_name, $sgd_id, $aspect, $slim_term, $go_id);
            $i = 0;
            while (!feof($file)) {
                $i++;
                $content = explode("\t", fgets($file));
                if (count($content) != 7 || !strstr($content[6], "ORF"))
                    continue;
                
                $orf = $content[0];
                $gene_name = $content[1] == null ? $content[0] : $content[1];   // If gene has no name, then using its ORF
                $sgd_id = $content[2];
                $aspect = $content[3];
                $slim_term = $content[4];
                $go_id_array = $content[5] == null ? 0 : explode(":", $content[5]);
                $go_id = $content[5] == null ? 0 : $go_id_array[1];
                
                mysqli_stmt_execute($stmt);
            }
        }

        mysqli_close($dbc);
        fclose($file);
    }
    
    function fetch_complex_slim_data() {
        // Get biochemical pathway data from $FILE_BIOCHEMICAL_PATHWAY
        $file = fopen(self::$FILE_GO_COMPLEX_SLIM, "rb");
        if (!$file) {
            echo "Cannot Open file: " . self::$FILE_GO_COMPLEX_SLIM;
            return;
        }
        
        $dbc = mysqli_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME) or die("Error: Could not connect to MySQL server");
        if ($stmt = mysqli_prepare($dbc, self::$SQL_INSERT_COMPLEX_SLIM)) {
            mysqli_bind_param($stmt, "ssss", $go_term, $go_id, $gene_name, $orf);
            $i=0;
            while (!feof($file)) {
                $content = explode("\t", fgets($file));
                if (count($content) != 2)
                    continue;
                $go = explode(":", $content[0], 2);
                $go_detail = explode("/", $go[1]);
                $go_term = trim($go_detail[0]);
                $go_id = trim($go_detail[1]);
                $associations = explode("/|/", trim($content[1], "/\n"));
                foreach ($associations as $key=>$value){
                    $details = explode("/", $value);
                    $type = count($details) == 4 ? $details[3] : $details[2];
                    if (!strstr($type, "ORF"))
                        continue;
                    $gene_name = count($details) == 4 ? $details[0] : null;
                    $orf = count($details) == 4 ? $details[1] : $details[0];
                    mysqli_stmt_execute($stmt);
                }
            }
        }

        mysqli_close($dbc);
        fclose($file);
    }
}
