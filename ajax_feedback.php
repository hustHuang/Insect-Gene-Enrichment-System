<?php
set_time_limit(0);
include 'Feedback.php';
$type = $_POST['feedback_type'];
date_default_timezone_set('PRC');
$feedback = new Feedback();
if ($type == 'set') {
    $comment = $_POST['feedback_comment'];
    $author = $_POST['feedback_name'];
    $time = date('Y-m-j H:i:s');
    $priority = $_POST['priority'];
    $feedback->set_feedback_params($comment, $author,$priority,$time);
    $feedback->set_feedback();
    echo $time;
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

?>
