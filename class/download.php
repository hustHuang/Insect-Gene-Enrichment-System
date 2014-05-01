<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class download {

    function getdowndata($file, $data, $type) {
        $str1 = "Query gene" . "\t" . "Array gene" . "\t" . "SGA score" . "\t" . "p-value" . "\r\n";
        $str2 = "Gene1" . "\t" . "Gene2" . "\t" . "Correlation" . "\r\n";
        $str3 = "Gene1_Name" . "\t" . "Gene2_Name" . "\t" . "Network" . "\t" . "Type" . "\t" . "Weight" . "\r\n";
        if ($type == 'c') {
            $str = $str2;
        } else if ($type == 'm') {
            $str = $str3;
        } else {
            $str = $str1;
        }
        @$fp = fopen($file, 'ab');
        flock($fp, LOCK_EX);
        if (!$fp) {
            echo "error";
            exit;
        }
        fwrite($fp, $str, strlen($str));
        fwrite($fp, $data, strlen($data));
        flock($fp, LOCK_UN);
        $str = file_get_contents($file);
        $str = str_replace(",\"||\",", "\r\n", $str);
        $str = str_replace("||", "\r\n", $str);
        $str = str_replace("\"", "", $str);
        $str = str_replace(",", "\t", $str);
        $str = str_replace("[", "", $str);
        $str = str_replace("]", "\r\n", $str);
        $str = stripcslashes($str);
        file_put_contents($file, $str);
        fclose($fp);
    }

}

?>
