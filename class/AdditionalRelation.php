<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdditionalRelation
 *
 * @author GGCoke
 */
class AdditionalRelation {

    //put your code here
    private static $FILE_FIRST_LINE = 1;
    private static $COMMIT_MAX_COUNT = 5000;
    private static $MAP_TYPE_TABLE_MAP = array("Co-expression" => "co_expression",
        "Co-localization" => "co_localization",
        "Physical_interactions" => "physical_interaction",
        "Shared_protein" => "shared_protein_domains");
    private static $MAP_TYPE_EXP_MAP = array("Co-expression" => 'Co-expression/',
        "Co-localization" => 'Co-localization/',
        "Physical_interactions" => 'Physical_interactions/',
        "Shared_protein" => 'Shared_protein/');

    function get_networks_data() {
        $filename = "../data/Saccharomyces_cerevisiae/networks.txt";
        if (!$fh = fopen($filename, "r")) {
            echo "Cannot open file " . $filename . "<br />";
            return;
        }
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }
        $current_line = 0;
        $query = "INSERT INTO networks(File_Name, Network_Group_Name, Network_Name, Source, Pubmed_ID) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($dbc, $query)) {
            mysqli_bind_param($stmt, "ssssi", $file_name, $group_name, $network_name, $source, $pubmed_id);
            while (!feof($fh)) {
                $current_line++;
                if ($current_line == self::$FILE_FIRST_LINE) {
                    continue;
                }
                $content = explode("\t", fgets($fh));
                if (count($content) != 5)
                    continue;
                $file_name = $content[0];
                $group_name = $content[1];
                $network_name = $content[2];
                $source = $content[3];
                $pubmed_id = $content[4];
                mysqli_stmt_execute($stmt);
            }
        }
    }

    function get_co_expression_data($type) {
        echo "=======================================" . $type . "====================================================<br />";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }
        $gene_id_name_map = $this->get_gene_id_map();
        $network_id_name_map = $this->get_network_map();

        // $query = "INSERT INTO co_expression(Gene_A_ID, Gene_B_ID, Score, Network_ID, Is_Chosen) VALUES (?, ?, ?, ?, 0)";
        $exp = ('/^' . self::$MAP_TYPE_EXP_MAP[$type]);
        $table_name = self::$MAP_TYPE_TABLE_MAP[$type];

        $dir = '../data/Saccharomyces_cerevisiae/';
        $files = scandir($dir);
        // print_r($files);
        foreach ($files as $filename) {
            if ($filename == '.' || $filename == '..')
                continue;
            // if (preg_match('/^Co-expression/', $filename)) {
            if (preg_match($exp, $filename)) {
                $current_line = 0;
                if (!$fh = fopen(('../data/Saccharomyces_cerevisiae/' . $filename), "r")) {
                    echo "Cannot open file " . $filename . "<br />";
                    continue;
                }
                if (!array_key_exists($filename, $network_id_name_map))
                    continue;
                $network_id = $network_id_name_map[$filename];
                echo "Filename: " . $filename . " and ID: " . $network_id . "<br />";
                // continue;
                $count = 0;
                $query = "INSERT INTO " . $table_name . "(Gene_A_ID, Gene_B_ID, Score, Network_ID, Is_Chosen) VALUES ";
                while (!feof($fh)) {
                    $current_line++;
                    if ($current_line == self::$FILE_FIRST_LINE) {
                        continue;
                    }
                    $content = explode("\t", fgets($fh));
                    if (count($content) != 3)
                        continue;
                    if (!array_key_exists($content[0], $gene_id_name_map) || !array_key_exists($content[1], $gene_id_name_map))
                        continue;
                    $gene1_id = $gene_id_name_map[$content[0]];
                    $gene2_id = $gene_id_name_map[$content[1]];
                    $score = trim($content[2]);
                    $is_chosen = ($network_id > 1 && $network_id < 12) ? 1 : 0;
                    $query .= '(' . $gene1_id . ',' . $gene2_id . ',' . $score . ',' . $network_id . ','. $is_chosen . '),';
                    $count++;
                    if (self::$COMMIT_MAX_COUNT <= $count) {
                        $query = trim($query, ",");
                        mysqli_query($dbc, $query);
                        echo "Insert " . $count . " records. <br/>";
                        $count = 0;
                        $query = "INSERT INTO " . $table_name . "(Gene_A_ID, Gene_B_ID, Score, Network_ID, Is_Chosen) VALUES ";
                    }
                }

                if ($count != 0) {
                    $query = trim($query, ",");
                    mysqli_query($dbc, $query);
                    echo "Insert " . $count . " records. <br/>";
                    $count = 0;
                    $query = "";
                }
            }
        }
        echo "=======================================" . $type . "====================================================<br />";
    }

    function get_gene_id_map() {
        $gene_id_name_map = array();
        $query = "SELECT g.idGENE, g.Feature_Name FROM gene g";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }

        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            if (!array_key_exists($row[1], $gene_id_name_map)) {
                $gene_id_name_map[$row[1]] = $row[0];
            }
        }

        return $gene_id_name_map;
    }

    function get_network_map() {
        $network_array = array();
        $query = "SELECT n.id, n.File_Name FROM networks n";
        $dbc = DBCxn::get_conn();
        if (is_null($dbc)) {
            echo "Cannot connect to MySQL server.<br />";
            return;
        }

        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            if (!array_key_exists($row[1], $network_array)) {
                $network_array[$row[1]] = $row[0];
            }
        }
        return $network_array;
    }

}
