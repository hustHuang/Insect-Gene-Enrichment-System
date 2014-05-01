<?php

require 'DBCxn.php';
require '../Stopwatch.php';

$SQL_TEMP_QUERY = '(SELECT g1.Feature_Name, g1.Standard_Gene_Name, g1.Alias, g1.Description, g2.Feature_Name, g2.Standard_Gene_Name, g2.Alias, g2.Description, s1.Strain_Name, s2.Strain_Name, s1.Marker_Type, i.Interaction_Score, i.P_Value FROM gene g1 LEFT JOIN (gene g2, strain s1, strain s2, interaction i) ON s1.GENE_idGENE = g1.idGENE AND i.Query_Strain = s1.idSTRAIN AND s2.idSTRAIN = i.Array_Strain AND g2.idGENE = s2.GENE_idGENE WHERE g1.Feature_Name = ? AND i.Interaction_Score < ? AND i.P_Value < ?) UNION ALL (SELECT g1.Feature_Name, g1.Standard_Gene_Name, g1.Alias, g1.Description, g2.Feature_Name, g2.Standard_Gene_Name, g2.Alias, g2.Description, s1.Strain_Name, s2.Strain_Name, s1.Marker_Type, i.Interaction_Score, i.P_Value FROM gene g1 LEFT JOIN (gene g2, strain s1, strain s2, interaction i) ON s1.GENE_idGENE = g1.idGENE AND i.Query_Strain = s1.idSTRAIN AND s2.idSTRAIN = i.Array_Strain AND g2.idGENE = s2.GENE_idGENE WHERE g2.Feature_Name = ? AND i.Interaction_Score < ? AND i.P_Value < ?) ORDER BY Interaction_Score ASC;';
$db = DBCxn::get_conn();
$s = new Stopwatch();

$stmt = $db->prepare($SQL_TEMP_QUERY);
$s->start('Search_1');
$param1 = array('YNL051W', '-0.08', '0.05', 'YNL051W', '-0.08', '0.05');
$stmt->execute($param1);
while ($row = $stmt->fetch()) {
    echo $row[0] . "<br/>\n";
}
$s->stop('Search_1');
$time1 = $s->getDuration('Search_1');
echo 'Time 1 = ' . $time1 . "<br/>\n";

$s->start('Search_2');
$param1 = array('YNL041C', -0.08, 0.05, 'YNL041C', -0.08, 0.05);
$stmt->execute($param1);
while ($row = $stmt->fetch()) {
    echo $row[0] . "<br/>\n";
}
$s->stop('Search_2');
$time1 = $s->getDuration('Search_2');
echo 'Time 2 = ' . $time1 . "<br/>\n";

$s->start('Search_3');
$param1 = array('YGL005C', -0.08, 0.05, 'YGL005C', -0.08, 0.05);
$stmt->execute($param1);
while ($row = $stmt->fetch()) {
    echo $row[0] . "<br/>\n";
}
$s->stop('Search_3');
$time1 = $s->getDuration('Search_3');
echo 'Time 3 = ' . $time1 . "<br/>\n";
?>
