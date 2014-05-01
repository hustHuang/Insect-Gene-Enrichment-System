<?php

$filename = '../' . $_POST['src'];
if (is_file($filename)) {
    if (unlink($filename)) {
        echo 'success';
    } else {
        return;
    }
}
?>
