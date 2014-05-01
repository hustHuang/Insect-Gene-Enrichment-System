<?php

/**
 * Description of StrainDataFetcher
 *
 * @author GGCoke
 * 2012-5-25 16:00:23
 */
class StrainDataFetcher {
    //put your code here
    private $INSERT_STRAIN_DATA = 'INSERT INTO strain (gene_id, name) VALUES ';
    private $GET_MAP_GENE_ID = 'SELECT id, feature_name FROM SC_gene';
    private $INSERT_STRAIN_FITNESS = 'update SC_strain_fitness set fitness_score = 1,deviation = 2 where a=1 and b =2';
    
    
    
    public $ARRAY_GENE_ID = array();
    public $ARRAY_STRAIN_EXISTS = array();
    public $ARRAY_STRAIN_TYPE = array('sn', 'damp', 'tsq', 'dma', 'tsa');
    
    function __construct() {
        global $global_sga_conn;
        $result = get_array_from_resultset($global_sga_conn->Execute($this->GET_MAP_GENE_ID));
        if (is_null($result)){
            echo "Cannot get the mapping of gene name and id. <br/>";
            exit(0);
        }
        foreach ($result as $value){
            $this->ARRAY_GENE_ID[$value['feature_name']] = $value['id'];
        }
    }
    
    function add_new_strain(){
        if (!file_exists(FILE_INTERACTION_NAME)){
            echo "File of Interaction does't not exist. Please check the setting of FILE_INTERACTION_NAME that specified in cfg.php<br/>";
            exit(0);
        }
        
        global $global_sga_conn;
        $count = 0;
        $sql = $this->INSERT_STRAIN_DATA;
        $file = fopen(FILE_INTERACTION_NAME, 'r');
        if ($file === FALSE){
            echo "Open Interaction file error, please try again.<br/>";
            exit(0);
        }
        
        while(!feof($file)){
            $line = fgets($file);
            $contents = explode("\t", $line);
            $strain1 = $contents[0];
            $strain2 = $contents[1];
            
            if (!in_array($strain1, $this->ARRAY_STRAIN_EXISTS)){
                array_push($this->ARRAY_STRAIN_EXISTS, $strain1);
                $tmp = explode('_', $strain1);
                if (count($tmp) == 2){
                    $gene = $tmp[0];
                    $type = $tmp[1];
                    
                    foreach($this->ARRAY_STRAIN_TYPE as $defined){
                        if (strncmp($type, $defined, strlen($defined)) == 0){
                            $sql .= '(' . $this->ARRAY_GENE_ID[$gene] . ',"' . $type . '"),';
                            $count++;
                            break;
                        }
                    }
                }
            }
            
            if (!in_array($strain2, $this->ARRAY_STRAIN_EXISTS)){
                array_push($this->ARRAY_STRAIN_EXISTS, $strain2);
                $tmp = explode('_', $strain2);
                if (count($tmp) == 2){
                    $gene = $tmp[0];
                    $type = $tmp[1];
                    
                    foreach($this->ARRAY_STRAIN_TYPE as $defined){
                        if (strncmp($type, $defined, strlen($defined)) == 0){
                            $sql .= '(' . $this->ARRAY_GENE_ID[$gene] . ',"' . $type . '"),';
                            $count++;
                            break;
                        }
                    }
                }
            }
            
            if ($count >= 1000){
                $sql = substr($sql, 0, (strlen($slq) - 1));
                
                $global_sga_conn->Execute($sql);
                $count = 0;
                $sql = $this->INSERT_STRAIN_DATA;
            }
        }
        fclose($file);
        
        if ($sql != $this->INSERT_STRAIN_DATA) {
            $sql = substr($sql, 0, (strlen($slq) - 1));
            $global_sga_conn->Execute($sql);
            $count = 0;
            $sql = $this->INSERT_STRAIN_DATA;
        }
    }
    
    function insertFitness($FILES,$temprature){
        if (!file_exists($FILES)){
            echo "File ".$FILES." does't not exist.<br/>";
            exit(0);
        }
        global $global_sga_conn;
        $count = 0;
        $sql = $this->INSERT_STRAIN_FITNESS;
        $file = fopen($FILES, 'r');
        if ($file === FALSE){
            echo "Open file error, please try again.<br/>";
            exit(0);
        }
        while(!feof($file)){
            $line = fgets($file);
            $contents = explode("\t", $line);

            $genes = explode("_", $contents[0]);
            $gene = $genes[0];
            $strain = $genes[1];
            
            $score = $contents[1];
            $deviation = $contents[2];
            $gene_id = $this->ARRAY_GENE_ID[$gene];

            $sql1 = "SELECT id from SC_strain where gene_id=".$gene_id." AND name='".$strain."'";       
            $strain_id = $global_sga_conn->GetOne($sql1);
           
            $sql2 = "INSERT INTO SC_strain_smf SET strain_id=".$strain_id.",fitness_score = ".$score.",deviation = ".$deviation.",temperature=".$temprature;
            if(!is_null($strain_id)){
	          
                 $global_sga_conn->Execute($sql2);
            }
          }
    }
}


