<?php

/**
 * @author GGCoke
 * 2012-9-26 20:24:10
 */

require_once '../common.php';
require_once ABSPATH . '/class/BlockData.class.php';
$gene = key_exists('genename', $_REQUEST) ? $_REQUEST['genename'] : NULL;
$result = array();
$block_data_service = new BlockData();
if (!is_null($gene)){
    $result = $block_data_service->get_block_id($gene);
}

echo json_encode($result);
