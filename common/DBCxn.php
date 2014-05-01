<?php

/**
 * Description of DBCxn
 *
 * @author GGCoke
 */
class DBCxn {
  //private static $DB_HOST = '115.156.216.110';
    private static $DB_HOST = '115.156.216.95';
   // private static $DB_USER = 'lzf';
   // private static $DB_PASS = '123456';
   //private static $DB_NAME = 'sga';

    private static $DB_USER = 'root';
    private static $DB_PASS = 'elwg324';
    private static $DB_NAME = 'sga';
    private static $db;

    final private function __construct() {
        
    }

    final private function __clone() {
        
    }

    public static function get_conn() {
        if (is_null(self::$db)) {
            self::$db = mysqli_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME) or die('Error: Can not connect to MySQL server.');
        }
        return self::$db;
    }

    public static function close_conn($dbc) {
        if (!is_null($dbc)) {
            mysqli_close($dbc);
        }
    }
}
?>
