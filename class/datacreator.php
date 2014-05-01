<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
set_time_limit(0);
include '../common/DBCxn.php';
include 'GeneDataFetcher.php';
include 'AdditionalRelation.php';
// $fetcher = new GeneDataFetcher();
// $fetcher->fetch_pathway_data();
// $fetcher->fetch_slim_mapping_data();
// $fetcher->fetch_complex_slim_data();

$additional = new AdditionalRelation();
// $additional->get_networks_data();
$additional->get_co_expression_data("Co-expression");
//    $additional->get_co_expression_data("Co-localization");
// $additional->get_co_expression_data("Physical_interactions");
// $additional->get_co_expression_data("Shared_protein");
