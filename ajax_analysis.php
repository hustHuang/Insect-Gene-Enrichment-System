<?php

set_time_limit(0);
require_once './common.php';
require_once ABSPATH . '/class/DataFormator.class.php';
$genes = $_REQUEST['g'];
$type = $_REQUEST['t'];
$cutoff = $_REQUEST['c'];
$representation = $_REQUEST['type'];
$flag = $_REQUEST['flag'];
//$genes = 'YET1,CIK1,SEC22,CDC1,RPN5,CDS1,BRN1,ESA1,SSS1,SEC66,MSL5,CMD1,CHO2,DED1,RPT2,APP1,OLA1,MLP1,PTI1,DID4,SAC3,YOR200W,WHI5,HSP10,DED81,TAF5,TFC3,NIP100,SSH1,MTC5,ECM13,PRP39,YTA12,MMS21,ASK1,CHZ1,LEE1,RTC3,YKR018C,RAD14,HOC1,SYS1,AGP1,CDC12,YBL055C,ACF2,SLX9,RRP1,GAL4,RCN2,VPS36,KNS1,RRT5,PAM17,MLP2,FKS1,PRP21,AIM33,PMA2,FIP1,ECM1,MOG1,NSE4,UBP3,RPT6,DIG1,CSM1,COR1,COX17,ICE2,AUR1,MAK11,YPT6,TOR2,IPL1,SWD3,UMP1,CAP2,DPH2,CDC48,YPR098C,MED4,CAF4,OAR1,POP4,RPT1,PHB2,PRP9,SEC20,BCK2,LCB1,RPT4,PTC1,RIM21,PMR1,LDB19,THP2,TOF1,MDM31,YBR255C-A,YDL176W,AKR2,RGP1,MED6,ALO1,VPS63,CAJ1,FCJ1,PIH1,GWT1,YCS4,YCG1,LRS4,SCS7,ARO1,JNM1,SCO1';
//$type = 'BP';
//$cutoff = 0.005;
//$representation = 'over';
//$flag = 'd';
//GO,PFAM,KEGG
if ($flag != "d") {
    global $global_sga_conn;
    $sql = 'SELECT g.Feature_Name FROM gene g WHERE g.Feature_Name = ? OR g.Standard_Gene_Name = ?';
    $items = explode(',', $genes);
    $feature_names = '';
    foreach ($items as $gene) {
        $feature_name = $global_sga_conn->GetOne($sql, array($gene, $gene));
        $feature_names .= ($feature_name . ',');
    }
    $feature_names = rtrim($feature_names, ',');
//echo $feature_names;
    if ($flag == "g") {
        exec("/heap/opt/bin/Rscript ./R/analysis.R $feature_names $type $cutoff $representation", $timestamp);
    } else if ($flag == "k") {
        exec("/heap/opt/bin/Rscript ./R/analysis_KEGG.R $feature_names $type $cutoff $representation", $timestamp);
    } else {
        exec("/heap/opt/bin/Rscript ./R/analysis_PFAM.R $feature_names $type $cutoff $representation", $timestamp);
    }
} else {
    //DO
    global $global_sga_conn;
    $sql = 'SELECT g.Feature_Name FROM gene g WHERE g.Feature_Name = ? OR g.Standard_Gene_Name = ?';
    $query = 'SELECT h.GeneID FROM `human2yeast` h where h.GeneName=?';
    $items = explode(',', $genes);
    $feature_names = '';
    $geneid = '';
    $geneid_arr = array();
    $geneidlist = "";
    foreach ($items as $gene) {
        $feature_name = $global_sga_conn->GetOne($sql, array($gene, $gene));
        $geneid = $global_sga_conn->GetOne($query, array($feature_name));
        if ($geneid !== NULL) {
            $geneids = explode("|", $geneid);
            foreach ($geneids as $id) {
                if (!in_array($id, $geneid_arr)) {
                    //echo "here";
                    array_push($geneid_arr, $id);
                    $feature_names.=($id . ',');
                }
            }
        }
    }
    $feature_names = rtrim($feature_names, ',');
//echo $feature_names;
//exec("/heap/opt/bin/Rscript ./R/analysis_DO.R $feature_names $type $cutoff $representation", $timestamp);
}
exec("Rscript /var/www/SGA/R/analysis_KEGG.R $feature_names $type $cutoff $representation", $timestamp);
echo $timestamp[1];
