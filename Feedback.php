<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Feedback
 *
 * @author GGCoke
 */

class Feedback {
    private static $DB_HOST = '115.156.216.108';
    private static $DB_USER = 'lzf';
    private static $DB_PASS = '123456';
    private static $DB_NAME = 'sga';
    private static $INSERT_FEEDBACK = "INSERT INTO feedback_tmp(comment,author,is_solved,time,priority) VALUES(?,?,0,?,?)";
    private static $QUERY_FEEDBACK = "SELECT f.id,f.comment,f.author,f.is_solved,f.time,f.priority FROM feedback_tmp f ORDER BY f.id DESC";
    private $comment;
    private $author;
    private $time;
    private $priority;
    
    function __construct() {
    }
    
    function set_feedback_params($comment, $author,$priority,$time) {
        $this->comment = $comment;
        $this->author = $author;
        $this->time = $time;
        $this->priority = $priority;
    }
    
    function set_feedback(){
        $dbc = mysqli_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME) or die("Error: Could not connect to MySQL server");
        if ($stmt = mysqli_prepare($dbc, self::$INSERT_FEEDBACK)) {
           mysqli_bind_param($stmt, "sssd", $this->comment, $this->author,$this->time,$this->priority);
           mysqli_stmt_execute($stmt);
        }
        mysqli_close($dbc);
    }
    
    function get_feedback(){
        $items = "[";
        $dbc = mysqli_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME) or die("Error: Could not connect to MySQL server");
        $result = mysqli_query($dbc, self::$QUERY_FEEDBACK);
        while ($row = mysqli_fetch_array($result)) {
            $items .= '{"id":"' . $row[0] .'","comment":"'  .(addslashes($row[1])) . '","author":"' .(addslashes($row[2])) .'","time":"' . (addslashes($row[4])) .'","priority":"' . $row[5] .'","solved":' .$row[3] .'},';
        }
        $items .= ']';
        mysqli_close($dbc);
        return $items;
    }
    
    function set_as_solved($id){
        $update = "UPDATE feedback_tmp f SET f.is_solved = 1 WHERE f.id = " . $id;
        $dbc = mysqli_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME) or die("Error: Could not connect to MySQL server");
        mysqli_query($dbc, $update);
        mysqli_close($dbc);
    }
}

?>
