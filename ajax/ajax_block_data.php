<?php
require_once '../common.php';
require_once ABSPATH . '/class/BlockData.class.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$id = $_POST['id'];
//$id = 6;
$blockdata = new BlockData();
$result = $blockdata->get_data_by_blockid($id);
echo json_encode($result);
?>
