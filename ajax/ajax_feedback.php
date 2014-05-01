<?php
set_time_limit(0);
require_once '../common.php';
require_once ABSPATH . '/class/Feedback.class.php';
$type = $_POST['feedback_type'];
$feedback = new Feedback();
if ($type == 'set') {
    $comment = $_POST['feedback_comment'];
    $author = $_POST['feedback_name'];
    $feedback->set_feedback_params($comment, $author);
    $feedback->set_feedback();
    echo true;
}
elseif ($type == 'get') {
    $result = array();
    $result['feedbacks'] = $feedback->get_feedback();
    echo json_encode($result);
} elseif ($type == 'update'){
    $id = $_POST['id'];
    $feedback->set_as_solved($id);
    echo true;
}
