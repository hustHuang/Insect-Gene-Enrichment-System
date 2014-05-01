<?php

/**
 * Methods are used frequently.
 * @author GGCoke
 * 2012-2-18 14:38:29
 */

/**
 * Get an instance of MySQL connection. $global_sga_conn is a global variable.
 * @author GGCoke
 * @global type $global_sga_conn
 */
function require_icg_conn() {
    global $global_sga_conn;
    require_once (ABSPATH . "/util/DB.class.php");

    if (isset($global_sga_conn))
        return;

    $db = new DB(DB_DRIVER, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_DEBUG);
    $global_sga_conn = $db->get_connection();
    
    /* Set the result fetch mode as ASSOC, ie using the name of coloum insdead of number.  */
    $global_sga_conn->SetFetchMode(ADODB_FETCH_ASSOC);
    
    /** Get instance of connection failed. */
    if (is_null($global_sga_conn)) {
        die("Failed getting connection. Please review the configuration.");
    }
}

/**
 * Set timezone of the system. Default is UTC
 * @author GGCoke
 */
function set_timezone() {
    if (defined('TIMEZONE')) {
        date_default_timezone_set(TIMEZONE);
    } else {
        date_default_timezone_set('UTC');
    }
}

/**
 * Get an array of result from ADOResultSet.
 * @param ADOResultSet $rs
 * @return array Return null if the count of the result if zero.
 */
function get_array_from_resultset($rs){
    if (!$rs || $rs->RecordCount() == 0)
        return null;
    $result = array();
    $column_count = $rs->FieldCount();
    $rs->Move(0);
    while ($row = $rs->FetchRow()){
        $array_of_row = array();
        for ($i = 0; $i < $column_count; $i++){
            $column_name = $rs->FetchField($i)->name;
            $array_of_row[$column_name] = $row[$column_name];
        }
        array_push($result, $array_of_row);
    }
    
    return $result;
}

/**
 * Log the event.
 * @param type $filename
 * @param type $content
 */
function write_log($filename, $content) {
    if (!SGA_LOG)
        return;
    if (!file_exists(ABSPATH . '/log/')){
        mkdir(ABSPATH . '/log/', 0755, true);
    }
    
    $file = fopen(ABSPATH . '/log/' . $filename, 'a+');
    $now = date("Y-m-d H:i:s", mktime());
    fwrite($file, $now . "\t" . $content . "\r\n");
    fclose($file);
}

function char_at($string, $index) {
    if ($index < mb_strlen($string)) {
        return mb_substr($string, $index, 1);
    } else {
        return -1;
    }
}
//end of script