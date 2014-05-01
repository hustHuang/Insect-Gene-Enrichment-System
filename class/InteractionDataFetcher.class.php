<?php

/**
 * Description of DataFatcher
 *
 * @author GGCoke
 * 2012-5-25 15:06:19
 */

require_once ABSPATH . '/class/DataFetcher.class.php';
class InteractionDataFetcher extends DataFetcher {
    private $INSERT_INTERACTION_DATA = 'INSERT INTO interaction2 (query, array, edf, odf, odf_dmf, score, p_value, standard_deviation, tcondition) VALUES ';
    
    function add_new_interation() {
        write_log(SGA_LOG_FILE, "Began to Read File And Store Interaction Datas to MySQL Database");
        if (!file_exists(FILE_INTERACTION_NAME)) {
            write_log(SGA_LOG_FILE, "File of Interaction does't not exist. Please check the setting of FILE_INTERACTION_NAME that specified in cfg.php");
            // echo "File of Interaction does't not exist. Please check the setting of FILE_INTERACTION_NAME that specified in cfg.php<br/>";
            exit(0);
        }

        global $global_sga_conn;
        $count = 0;
        $sql = $this->INSERT_INTERACTION_DATA;
        $file = fopen(FILE_INTERACTION_NAME, 'r');
        if ($file === FALSE) {
            write_log(SGA_LOG_FILE, "Open Interaction file error, please try again");
            // echo "Open Interaction file error, please try again.<br/>";
            exit(0);
        }

        // Get condition id accordding file name
        $condition_id = $this->get_condition_id(basename(FILE_INTERACTION_NAME, '.txt'));
        if ($condition_id == 0){
            // condition id must between 1 and 4
            write_log(SGA_LOG_FILE, "Error getting id of condition. Result id = " . $condition_id);
            // echo "Error getting id of condition. Result id = ' . $condition_id . '<br/>";
            exit(0);
        }
        write_log(SGA_LOG_FILE, "Condition ID = " . $condition_id);
        while (!feof($file)) {
            $line = fgets($file);
            
            /**
             * *************************************************************
             * *   field            example         content                * 
             * *$contents[0]    YAL034W-A_tsq235   Query Orf               *
             * *$contents[1]    YBR030W_dma193     Array Orf               *
             * *$contents[2]    -58.185933         e_score                 *
             * *$contents[3]    24.427553          e_score (std)           *
             * *$contents[4]    2.129027e-02       p-value                 *
             * *$contents[5]    NaN                Query smf               *
             * *$contents[6]    NaN                Query smf (std)         *
             * *$contents[7]    0.966500           Array smf               *
             * *$contents[8]    0.004000           Array smf (std)         *
             * *$contents[9]    0.966500           Expected dmf            *
             * *$contents[10]   0.860665           Observed dmf            *
             * *$contents[11]   0.044432           Observed dmf (std)      *
             * *************************************************************
             * 
             * SGA score = eps = Observed dmf - Expected dmf (ie. score = $contents[10] - $contents[9])
             * 
             * ****************************************************************
             * *    Column in table         Field in file      Num in content *
             * *        query                 Query Orf              0        *
             * *        array                 Array Orf              1        *
             * *        edf                  Expected dmf            9        *
             * *        odf                  Observed dmf            10       *
             * *        odf_dmf              Observed dmf (std)      11       *
             * *        score                    NaN                score     *
             * *        p_value                p-value               4        *
             * *   standard_deviation        e_score(std)            3        *
             * *      condition                  NaN          id of condition *
             * ****************************************************************
             */
            $contents = explode("\t", $line);
            if (count($contents) != 12){
                write_log(SGA_LOG_FILE, "Content Error: Count of contents incorracte. Line is " . $line);
                continue;
            }
            
            if (!$this->valid_interaction($contents[0], $contents[1]))
                continue;
            
            $full_strain_1 = explode('_', $contents[0]);
            $full_strain_2 = explode('_', $contents[1]);
            $gene1 = $this->get_gene_id($full_strain_1[0]);
            $gene2 = $this->get_gene_id($full_strain_2[0]);
            $query = $this->get_strain_id($gene1, $full_strain_1[1]);
            $array = $this->get_strain_id($gene2, $full_strain_2[1]);
            
            $count++;
            $sql .= ('(' . $query . ',' . $array . ',' . $contents[9] . ',' . $contents[10] . ',' . $contents[11] . ',' . ($contents[10] - $contents[9]) . ',' . $contents[4] . ',' . $contents[3] . ',' . $condition_id . '),');
            
            if ($count >= 1000) {
                write_log(SGA_LOG_FILE, "Insert new datas to database. Count = " . $count);
                $sql = substr($sql, 0, strlen($sql) - 1);
                $global_sga_conn->Execute($sql);
                $count = 0;
                $sql = $this->INSERT_INTERACTION_DATA;
            }
        }
        if (is_resource($file)){
            write_log(SGA_LOG_FILE, "Close File Successfully");
            fclose($file);
        }
        if ($sql != $this->INSERT_INTERACTION_DATA){
            write_log(SGA_LOG_FILE, "Insert new datas to database. Count = " . $count);
            $sql = substr($sql, 0, strlen($sql) - 1);
            $global_sga_conn->Execute($sql);
            $count = 0;
            $sql = $this->INSERT_INTERACTION_DATA;
        }
        
        write_log(SGA_LOG_FILE, "Fetcher data of " . FILE_INTERACTION_NAME . " Successfully");
    }
}
