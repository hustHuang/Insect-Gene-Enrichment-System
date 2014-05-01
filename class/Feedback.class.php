<?php

/**
 * Description of Feedback
 *
 * @author GGCoke
 */
class Feedback {
    private static $INSERT_FEEDBACK = "INSERT INTO feedback(comment, author, is_solved) VALUES(?, ?, 0)";
    private static $QUERY_FEEDBACK = "SELECT f.id, f.comment, f.author, f.is_solved FROM feedback f ORDER BY f.id DESC";
    private static $SET_FEEDBACK_READ = 'UPDATE feedback f SET f.is_solved = 1 WHERE f.id = ?';
    private $comment = '';
    private $author = '';

    function __construct() {
        
    }

    function set_feedback_params($comment, $author) {
        $this->comment = $comment;
        $this->author = $author;
    }

    function set_feedback() {
        global $global_sga_conn;
        $global_sga_conn->Execute(self::$INSERT_FEEDBACK, array($this->comment, $this->author));
        return true;
    }

    function get_feedback() {
        global $global_sga_conn;
        $items = "[";

        $result = get_array_from_resultset($global_sga_conn->Execute(self::$QUERY_FEEDBACK));
        if (!is_null($result) && (count($result) != 0)) {
            foreach ($result as $row) {
                $items .= '{"id":"' . $row['id'] . '","comment":"' . (addslashes($row['comment'])) . '","author":"' . (addslashes($row['author'])) . '","solved":' . $row['is_solved'] . '},';
            }
        }
        $items .= ']';
        return $items;
    }

    function set_as_solved($id) {
        global $global_sga_conn;
        $global_sga_conn->Execute(self::$SET_FEEDBACK_READ, array($id));
        return true;
    }
}
