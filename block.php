<?php
require_once './common.php';
require_once ABSPATH . '/class/BlockData.class.php';
$query_names = array_key_exists('geneNames', $_REQUEST) ? $_REQUEST['geneNames'] : NULL;
$query_type = array_key_exists('query_type', $_REQUEST) ? $_REQUEST['query_type'] : NULL;
$database = array_key_exists('search_dataset', $_REQUEST) ? $_REQUEST['search_dataset'] : NULL;
if (is_null($query_names) || is_null($query_type)) {
    ?>
    <script type="text/javascript">
        alert("No querykeywords were entered. Please try again.");
        window.location.href='search.php';
    </script>
    <?php
} else {
    $block_service = new BlockData();
    $tree_data_json = $block_service->get_feature_name($query_names);
    $array_block_id = $block_service->get_block_id($query_names);
    $key_array = explode(STRING_SEPARATOR, $query_names);
    $user_query_array = '[{"data": "User Submission","attr":{"id":"query_names", "href":"javascript:void(0)"},"children":[';
    $temp_array = array();
    foreach ($key_array as $key) {
        $key = trim($key);
        if (is_null($key) || $key == '' || $key == ' ') {
            continue;
        }
        if (!in_array($key, $temp_array)) {
            array_push($temp_array, $key);
            $user_query_array.=('{"data":{"title":"' . $key . '","attr":{"id":"' . $key . '","href":"javascript:void(0)", "class":"querynode"}}},');
        }
    }
    $user_query_array = rtrim($user_query_array, ',');
    $user_query_array.='],"state":"open"}]';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xHTML11/DTD/xHTML11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Block Result</title>
        <link rel="stylesheet" type="text/css" href="./inc/css/layout-default-latest.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.all.css" />
        <link rel="stylesheet" type="text/css" href="./inc/js/myTheme/jquery.ui.all.css" />
        <link rel="stylesheet" type="text/css" href="./inc/js/myTheme/jquery.ui.core.css" />
        <link rel="stylesheet" type="text/css" href="./inc/js/myTheme/jquery.ui.tabs.css" />      
        <link rel="stylesheet" href="./inc/css/wsj_interactive.min.css" />
        <link rel="stylesheet" href="./inc/css/style.min.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.mCustomScrollbar.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/block.css" />

    </head>
    <body>
        <div id="tabs-north" class="ui-layout-north no-scrollbar no-padding">
            <div id="head"><h1>SGACellMap</h1></div>
        </div>
        <div id="tabs-west" class="ui-layout-west no-scrollbar no-padding"  style="padding:0px 15px;">
            <ul id="leftnav" class="topnav">
                    <li id="tab-genes-selector"><a href="#">Genes</a></li>
                    <li id="hide_west" style="margin-left:0px;"><a style="cursor:pointer;"><img width="10px" height="10px;" src="./inc/images/left.png"/></a></li>
            </ul>        
            <div id="left_tree_box">
                <div id="tab-panel-west-1">
                    <div id="treeloading"></div>
                    <div class="treeview" id="query_names"></div>
                    <div class="treeview" id="tree_fnames"></div>                         
                </div>
            </div>
        </div>
        <div id="tabs-center" class="ui-layout-center">
            <div class="content">
                <div class="topmenu" id="topmenu">
                    <ul id="topnav" class="topnav">
                        <li id="show_west" class="nav-state-active"><a style="padding:0px;cursor:pointer;"><img style="width:10px;height:10px;" src="./inc/images/right.png"/></a></li>
                        <li class="nav-state-active"><a href="javascript:void(0)">Blocks</a></li>
                        <li><a href="index.php" style="cursor:pointer;"> Home</a></li>
                    </ul>
                </div>
                <div id="loading"></div>
                <div class="block_container" id="block_container">
                    <div class="result_view">
                        <div class="block_detail" id="block_detail"><div id='hoverBox'></div>
                            <!-- % -->
                            <?php
                            if (!is_null($array_block_id) && count($array_block_id) > 0) {
                                foreach ($array_block_id as $block_id) {
                                    if (!$block_service->block_exists($block_id))
                                        continue;
                                    //$img_src = SITEURI . '/data/blockImages/' . $block_id . '.png';
                                    $block_info_basic = $block_service->get_block_info_basic($block_id);
                                    $block_info_enrichment_a = $block_service->get_block_info_enrichment($block_id, 0);
                                    $block_info_enrichment_q = $block_service->get_block_info_enrichment($block_id, 1);
                                    ?>

                                    <div class="block_info" id="<?php echo $block_info_basic['id']; ?>">
                                        <table width="100%">
                                            <tbody width="100%">
                                                <tr width="100%" id="<?php echo 'tr' . $block_id; ?>">
                                                    <td width="40%">
                                                        <div align="center" class="blockdiv" id='<?php echo $block_id; ?>'>


                                                            <!-- start of #container -->

                                                            <div style="display:table-cell;vertical-align:middle;" class= 'tileStage' id='<?php echo 'tileStage' . $block_id; ?>' style="overflow:visible;">
                                                                <div class='monthsCol' id='<?php echo 'monthsCol' . $block_id; ?>'></div>
                                                            </div>

                                                        </div>
                                                    </td>
                                                    <td width="60%" class="info">
                                                        <div id='<?php echo 'netview' . $block_id; ?>' style="width:100%;height:100%;display:none">
                                                            <div class="loading net_loading"></div>
                                                            <div class="netview-content" style="width:100%;margin:1px;">
                                                                <input type='button' id='<?php echo 'bi' . $block_id; ?>' value='BlockInfo' style="float:right;background-color:#3E3E3E;color: #FFF;"></input>
                                                                <div class="tools"><span>Change Network Layout:</span>
                                                                    <select name="layout" id="<?php echo 'layout_' . $block_id; ?>" onchange="layout(this)">
                                                                        <option value="1">ForceDirected</option>
                                                                        <option value="2">Circle</option>
                                                                        <option value="3">Radial</option>
                                                                        <option value="4">Tree</option>
                                                            </select>&nbsp;&nbsp;<span>Select the format to export:</span>
                                                                    <select name="export" id="<?php echo 'export_' . $block_id; ?>">
                                                                        <option value="">xgmml</option>
                                                                        <option value="">png</option>
                                                                        <option value="">sif</option>
                                                                        <option value="">svg</option>
                                                                        <option value="">pdf</option>
                                                                        <option value="">graphml</option>
                                                            </select><input id="<?php echo 'exportBtn'.$block_id; ?>" type="button" value="Export" style="width:50px;background:none;border:none;text-decoration:underline;font-style:italic;font-size:14px;"></input></div>
                                                            </div>                                                           
                                                            <div class="netview-content" id='<?php echo 'netview_box_' . $block_id; ?>' style="float: left;width: 70%;"></div>
                                                            <div class="netview-content" id='<?php echo 'choosebox_' . $block_id; ?>' style="width:28%;float: right; position:relative; ">
                                                                <div class="choosebox" style="position:absolute;bottom:0px;">
                                                                    <div id="chooseTitle"><span>Choose Interaction Type</span></div>
                                                                    <div class="chooseitem">
                                                                        <div class="choosecolor" style="color:green;"><input class="choosetype" type="checkbox" name="reltype" value ="pai" checked disabled><span>Positive Interaction</span></div>
                                                                    </div>
                                                                    <div class="chooseitem">
                                                                        <div class="choosecolor" style="color:red;"><input class="choosetype" type="checkbox" name="reltype" value ="nai" checked disabled><span>Negative Interaction</span></div> 
                                                                    </div>
                                                                    <div class="chooseitem">
                                                                        <div class="choosecolor" style="color:#FBD10A;"><input class="choosetype" type="checkbox" name="reltype" value ="coexp" checked onclick="showEdge(this)"><span>Co-expression</span></div>
                                                                    </div>
                                                                    <div class="chooseitem">
                                                                        <div class="choosecolor" style="color:#6261fc;"><input class="choosetype" type="checkbox" name="reltype" value ="coloc" checked onclick="showEdge(this)"><span>Co-localization</span></div>
                                                                    </div>
                                                                    <div class="chooseitem">
                                                                        <div class="choosecolor" style="color:#9EB5E6;"><input class="choosetype" type="checkbox" name="reltype" value ="pi" checked onclick="showEdge(this)"><span>Physical interactions</span></div>
                                                                    </div>
                                                                    <div class="chooseitem">
                                                                        <div class="choosecolor" style="color:#00CCFF;"><input class="choosetype" type="checkbox" name="reltype" value ="spd" checked onclick="showEdge(this)"><span>Shared protein domains</span></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="<?php echo 'info_box' . $block_id; ?>">
                                                            <input type='button' id='<?php echo 'nv' . $block_id; ?>' value='NetworkView' style="float:right;background-color:#3E3E3E;color: #FFF;"></input>
                                                            S.No. <b><?php echo $block_info_basic['id']; ?></b><br /> Clique index = <?php echo $block_info_basic['aq']; ?><br /> Interaction density = <?php echo $block_info_basic['interaction_density']; ?>                                                
                                                            <table>
                                                                <tbody>
                                                                    <tr></tr>
                                                                    <tr></tr>
                                                                    <tr></tr>
                                                                    <tr>
                                                                        <td width="50%"><b>Array genes</b></td>
                                                                        <td width="50%"><b>Query genes</b></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td> Global profile corr = <?php echo $block_info_basic['corr_coef_a']; ?></td>
                                                                        <td> Global profile corr = <?php echo $block_info_basic['corr_coef_q']; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Process Enrichments</b></td>
                                                                        <td><b>Process Enrichments</b></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <?php
                                                                            if (!is_null($block_info_basic['process_enrichment_a'])) {
                                                                                $array_pe_a = explode('|', $block_info_basic['process_enrichment_a']);
                                                                                foreach ($array_pe_a as $pe_a) {
                                                                                    $pe_a = trim($pe_a);
                                                                                    if (strlen($pe_a) == 0)
                                                                                        continue;
                                                                                    ?>
                                                                                    - <?php echo $pe_a; ?><br />
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php
                                                                            if (!is_null($block_info_basic['process_enrichment_q'])) {
                                                                                $array_pe_q = explode('|', $block_info_basic['process_enrichment_q']);
                                                                                foreach ($array_pe_q as $pe_q) {
                                                                                    $pe_q = trim($pe_q);
                                                                                    if (strlen($pe_q) == 0)
                                                                                        continue;
                                                                                    ?>
                                                                                    - <?php echo $pe_q; ?><br />
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Enrichments</b></td>
                                                                        <td><b>Enrichments</b></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><?php
                                                                    if (!is_null($block_info_enrichment_a) && count($block_info_enrichment_a) > 0) {
                                                                        foreach ($block_info_enrichment_a as $enrichment_a) {
                                                                                    ?>
                                                                                    - <?php echo $enrichment_a['enrichment']; ?> <br/>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?> - </td>
                                                                        <td><?php
                                                                    if (!is_null($block_info_enrichment_q) && count($block_info_enrichment_q) > 0) {
                                                                        foreach ($block_info_enrichment_q as $enrichment_q) {
                                                                                    ?>
                                                                                    - <?php echo $enrichment_q['enrichment']; ?> <br/>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?> - </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>OMIM disease</b></td>
                                                                        <td><b>OMIM disease</b></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <?php
                                                                            if (!is_null($block_info_basic['disease_genes_a'])) {
                                                                                $array_dg_a = explode('|', $block_info_basic['disease_genes_a']);
                                                                                foreach ($array_dg_a as $dg_a) {
                                                                                    $dg_a = trim($dg_a);
                                                                                    if (strlen($dg_a) == 0)
                                                                                        continue;
                                                                                    ?>
                                                                                    - <?php echo $dg_a; ?><br />
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php
                                                                            if (!is_null($block_info_basic['disease_genes_q'])) {
                                                                                $array_dg_q = explode('|', $block_info_basic['disease_genes_q']);
                                                                                foreach ($array_dg_q as $dg_q) {
                                                                                    $dg_q = trim($dg_q);
                                                                                    if (strlen($dg_q) == 0)
                                                                                        continue;
                                                                                    ?>
                                                                                    - <?php echo $dg_q; ?><br />
                                                                                    <?php
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>                                                            
                                                        </div>   
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <hr class="blank" id="<?php echo hr . $block_info_basic['id']; ?>"></hr>
                                    <div class="loadings" id="<?php echo loading . $block_info_basic['id']; ?>"></div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <div class="quick_launch"></div>
                    </div>
                </div>
            </div>
        </div>
        <div  id="tabs-east" class="ui-layout-east ui-widget-content no-padding no-scrollbar">
                  
        </div>
        <div id="ui-copyright" class="ui-layout-south ui-widget-content add-padding">

        </div>
        <script type="text/javascript" src="./inc/js/lib/jquery-1.7.2.min.js"></script> 
        <script type="text/javascript" src="./inc/js/lib/jquery-ui-1.8.17.custom.min.js"></script> 
        <script type="text/javascript" src="./inc/js/lib/jquery.layout-latest.js"></script>
        <script type="text/javascript" src="./inc/js/lib/jquery.layout.callbacks.min-latest.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.cookie.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.hotkeys.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.jstree.js"></script>
        <script type="text/javascript" src="inc/js/lib/json2.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/AC_OETags.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/cytoscapeweb.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/lhgdialog.js"></script>   
        <script type="text/javascript" src="inc/js/lib/jquery.mousewheel.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.mCustomScrollbar.js"></script>
        <script type="text/javascript" src="inc/js/block.js"></script>
        <script>!window.jQuery && document.write(unescape('%3Cscript src="./inc/js/lib/jquery-1.7.2.min.js"%3E%3C/script%3E'));</script>
        <script src="./inc/js/lib/raphael-min.min.js"></script>
        <!--<script type="text/javascript">
            $(document).ready(function () {
                /*pageLayout = $("body").layout({
                    west__size:             .20
                    ,south__resizable:      false
                    ,south__closable:       false
                    ,west__onresize:        $.layout.callbacks.resizePaneAccordions
                    ,west__onclose_end:     function() {}
                    ,west__onresize:        function() {}
                });
                
                pageLayout.panes.west.tabs({
                    show: $.layout.callbacks.resizePaneAccordions
                });
                $("#accordion-west").accordion({ fillSpace: true });
                
                $("#tree_fnames").jstree({
                    "themes" : {
                        "theme" : "default",
                        "dots" : false,
                        "icons" : false
                    },
                    "json_data" : {
                        "data": <?php echo $tree_data_json; ?>,
                        "progressive_render" : true
                    },
                    "plugins" : [ "themes", "json_data"]
                });
                
                $("#query_names").jstree({
                    "themes" : {
                        "theme" : "default",
                        "dots" : false,
                        "icons" : false
                    },
                    "json_data" : {
                        "data": <?php echo $user_query_array; ?>,
                        "progressive_render" : true
                    },
                    "plugins" : [ "themes", "json_data"]
                });*/
               
            });
        </script>-->

        <script type="text/javascript">
            if(!window.console)
                window.console={log:function(){}};
            UNEMPLOYMENT=window.UNEMPLOYMENT||{};
            UNEMPLOYMENT.$document=$(document);
            UNEMPLOYMENT.colors=["#FF0000","#EA0000","#CF0000","#B20000","#7C0000","#570000","#2F0000","#020000","#003000"];
            var cw_options = {
                swfPath: "inc/swf/CytoscapeWeb",
                flashInstallerPath: "inc/swf/playerProductInstall",
                flashAlternateContent: '<div class="ui-state-error ui-corner-all"><p>This content requires the Adobe Flash Player.</p><p><a href="http://get.adobe.com/flashplayer/"><img width="160" height="41" border="0" alt="Get Adobe Flash Player" src="http://www.adobe.com/macromedia/style_guide/images/160x41_Get_Flash_Player.jpg"></a></p></div>'
            };
            var vis=[];
            var f=[];
            var _lastFilter;
            var edge_checked = {};
            var nodeColorMapper = {
                attrName: "ngc",                            // nodeGroupCode
                entries: [
                    {
                        attrValue: "q",
                        value: "#FF9086"
                    },      // Query nodes

                    {
                        attrValue: "r",
                        value: "#ffffff"
                    }       // Result nodes
                ]
            };

            //Mapping network groups to edge colors:
            var edgeColorMapper = {
                attrName: "egc",                                // edgeGroupCode
                entries: [
                    {
                        attrValue: "c",
                        value: "#c3844c"
                    },          //Correlation

                    {
                        attrValue: "ai",
                        value: "#2fb56d"
                    },         //Interaction

                    {
                        attrValue: "pai", 
                        value: "green"
                    },	    //Positive Interaction

                    {
                        attrValue: "nai", 
                        value: "red"
                    },	    //Negative Interaction

                    {
                        attrValue: "coexp",
                        value: "#FBD10A"
                    },      //Co-expression

                    {
                        attrValue: "coloc",
                        value: "#6261fc"
                    },      //Co-localization

                    {
                        attrValue: "pi",
                        value: "#9EB5E6"
                    },         //Physical interactions

                    {
                        attrValue: "spd",
                        value: "#00CCFF"
                    },        //Shared protein domains
                ]
            };

            var cw_style = {
                nodes : {
                    shape: "ELIPSE",
                    color: {
                        defaultValue: "#999999",
                        discreteMapper: nodeColorMapper
                    },
                    opacity: 1,
                    size : {
                        defaultValue: 24, 
                        continuousMapper : {
                            attrName: "count", 
                            minValue: 24, 
                            maxValue: 40
                        }
                    },
                    borderColor: "#808080",
                    borderWidth: 1,
                    label: {
                        passthroughMapper: {
                            attrName: "id"
                        }
                    },
                    labelFontWeight: "bold",
                    labelGlowColor: "#ffffff",
                    labelGlowOpacity: 1,
                    labelGlowBlur: 3,
                    labelGlowStrength: 20,
                    labelHorizontalAnchor: "center",
                    labelVerticalAnchor: "bottom",
                    selectionBorderColor: "#000000",
                    selectionBorderWidth: 2,
                    selectionGlowColor: "#ffff33",
                    selectionGlowOpacity: 0.6,
                    hoverBorderColor: "#000000",
                    hoverBorderWidth: 2,
                    hoverGlowColor: "#aae6ff",
                    hoverGlowOpacity: 0.8
                },
                edges : {
                    color: {
                        defaultValue: "#999999",
                        discreteMapper: edgeColorMapper
                    },
                    width : {
                        continuousMapper : {
                            attrName : "distance", 
                            minValue : 1, 
                            maxValue: 4
                        }
                    }
                }
            };
            pageLayout = $("body").layout({
                 west__size:                .16
                ,east__size:                5
                ,north__size:               75
                ,south__size:               13
                ,slidable:                  false
                ,resizerDblClickToggle:     false 
                ,togglerLength_open:        0
                ,togglerLength_closed:      0
                ,spacing_open:              12  //width of the resizer bar
                ,spacing_closed:            12
                ,west__resizable:          true
                ,east__resizable:          false
                ,south__resizable:         false
                ,north__resizable:         false
            });        
             
            $('#treeloading').show();
            $('#loading').show();
            $(document).ready(function(){
                $('#left_tree_box').height($('#tabs-west').height()-$('#leftnav').height());
                $("#query_names").jstree({
                    "themes" : {
                        "theme" : "default",
                        "dots" : false,
                        "icons" : false
                    },
                    "json_data" : {
                        "data": <?php echo $user_query_array; ?>,
                        "progressive_render" : true
                    },
                    "plugins" : [ "themes", "json_data"]
                });
                $("#tree_fnames").jstree({
                    "themes" : {
                        "theme" : "default",
                        "dots" : false,
                        "icons" : false
                    },
                    "json_data" : {
                        "data": <?php echo $tree_data_json; ?>,
                        "progressive_render" : true
                    },
                    "plugins" : [ "themes", "json_data"]
                });

                $('#loading').hide();        
                $('#block_container').show();   
                //var divcount=$('.blockdiv').length;
                for(var i=0;i<10;i++)
                { 
                    var id=$('.blockdiv:eq('+i+')').attr('id'); 
                    $('#'+id).show();
                    $('#hr'+id).show();
                    getData(id);
                }
                //$('#loading').hide();
                $('#treeloading').hide();
                $('#left_tree_box').mCustomScrollbar({advanced:{updateOnContentResize: true}});
                //$('#block_container').show();
                var nScrollHight = 0;
                var nScrollTop = 0;
                var nDivHight = $('#tabs-center').height();
                $('#tabs-center').scroll(function(){
                    nScrollHight = $(this)[0].scrollHeight;
                    nScrollTop = $(this)[0].scrollTop;
                    if(nScrollTop + nDivHight >= nScrollHight){
                        //var lid = $('.blockdiv:eq('+(i-1)+')').attr('id');
                        var lid = $('.blockdiv:visible:last').attr('id');
                        var j = 10+i;
                        $('#loading'+lid).show();
                        $('#block_container .quick_launch .quick_launch_node').remove();
                        $('.monthLabel').css({
                            'background-color':'#FFF',
                            'font-weight':'normal',
                            'font-size':'10px'
                        });
                        $('.yearCol').css({
                            'background-color':'#FFF',
                            'font-weight':'normal',
                            'font-size':'10px'
                        });
                        for(;i<j;i++){
                            var id=$('.blockdiv:eq('+i+')').attr('id');
                            if ($('#block_container .result_view div#' + id+':visible').length>0) continue;
                            if(id==null) break;
                            $('#'+id).show();
                            $('#hr'+id).show();
                            getData(id);    
                        }
                        $('#loading'+lid).hide();
                        resetStyle(); 
                    }
                });
            });
            function resetStyle(){
                $('.genenode').filter('.selected').each(function(i,e){
                    var genename=$(e).attr('id');
        
                    var randomColor=$(e).css('background-color');
       
                    $.ajax({
                        url: './ajax/ajax_get_block.php',
                        type: "POST",
                        data:{
                            genename:genename
                        },
                        cache:false,
                        complete: function(data) {
                            var ids = eval('(' + data.responseText + ')');
                            var count = ids.length;
                            for (var i = 0; i < count; i++){
                                highlightline(ids[i],genename,true, randomColor);
                            }
                        }
                    });
                });
            } 
            function getData(id){
                $.ajax({
                    type: 'POST',
                    url: './ajax/ajax_block_data.php',
                    dataType: "JSON",
                    data: {
                        id: id
                    },
                    async: false,
                    success: function(a){
                        headDek(a,id);drawMonths(a,id);
                        toolTip(id);			
                    }          
                });
            }
            function headDek(a,id){
                $.each(a,function(b,c){
                    if(b==0){
                        $.each(c,function(g, f){
                            if(g!="year"){
                                $("#monthsCol"+id).append("<p class='monthLabel'>"+g+ "</p>");
                            }
                        })
                    }
                })
            }
            function drawMonths(a,id){
                $.each(a,function(b,c){
                    var e=0,g=0,d=c.year;
                    $("#tileStage"+id).append("<div class='yearCol' style='overflow:visible;'><div id='text"+d+id+"' style='overflow:visible;' class='year'></div>");
                    $.each(c,function(g, f){
                        if(g!="year"){
                            e+=1;
                            if(f=='nan') g=0;
                            else if(parseFloat(f)>0.1) g=0.1;
                            else if(parseFloat(f)<-0.7) g=-0.7;
                            else g=f;
                            $("#tileStage"+id+" .yearCol:last").append("<div class='month' id='"+e+"_"+d+"' percentage='"+f+"' style = 'background-color:"+UNEMPLOYMENT.colors[Math.floor(10*parseFloat(g)+7)]+ "'></div>");
                        }
                    });
                    Raphael("text"+d+id).text(10,15,d).transform("r-60");
                });
            }           
            function toolTip(id){
                $("#tileStage"+id+" .month").hover(function(){
                    var a=$(this).attr("percentage"),b=a;
                    if(a!="null"){
                        $(this).after($("#hoverBox")); 
                        $("#hoverBox").show();
                        $(this).parent().find("text").css({ "font-weight":"bold",fill:"#333"});$(this).addClass("monthActive");
                        $("#hoverBox").html(b);
                        $("#monthsCol"+id+" .monthLabel:eq("+($(this).index()-1)+")").css({"font-weight":"bold",color: "#333"});
                    }
                },function(){
                    $("#hoverBox").hide();
                    $(this).parent().find("text").css({"font-weight":"normal",fill:"#999"});
                    $(this).removeClass("monthActive");
                    $("#monthsCol"+id+" .monthLabel:eq("+ ($(this).index()-1)+")").css({"font-weight":"normal",color:"#999"});
                });
                $("#nv"+id).live('click', function(e){
                    if(vis[id]!=null){
                        $("#info_box"+id).hide();
                        $("#netview"+id).show();
                        return;
                    }
                    var len=$("#monthsCol"+id+" .monthLabel").length;
                    var query_names='';var a,b,c,d;
                    for(var i=0;i<len;i++){
                        a=$("#monthsCol"+id+" .monthLabel").eq(i).text();
                        b=a.split('_')[1];
                        query_names += b+' ';
                    }
                    var le=$("#tileStage"+id+" .yearCol").length;
                    for(var j=0;j<le;j++){
                        c=$("#tileStage"+id+" .yearCol").eq(j).text();
                        d=c.split('_')[1];
                        query_names += d+' ';
                    }
                    f[id]=$("#tr"+id).height();
                    $(this).parent('div').parent('td').find('.net_loading').css({height:f[id]});
                    $("#info_box"+id).hide();
                    $("#netview"+id).show();
                    $(this).parent('div').parent('td').find('.net_loading').show();
                    $(this).parent('div').parent('td').find('.netview-content').hide();
                    $.ajax({
                        type: 'POST',
                        url: 'ajax_block_search.php',
                        dataType: "JSON",
                        data: {
                            "query_names":query_names,
                            "database":<?php echo $database; ?>
                        },
                        async: false,
                        success: function(data){
                            $('.net_loading').hide();
                            $('.netview-content').show(); 
                            //$(".block_info").not('#'+id).css({display:"none"});
                            //$(".hr").css({display:"none"});
                            //$(".quick_launch").css({display:"none"});                      
                            //$("#netview_box_"+id).css("height",f[id]-190);                        
                            //$("#tr"+id).css("height",800);
                            $("#netview_box_"+id).css({"height":f[id]+3});
                            $("#choosebox_"+id).css({"height":f[id]-17});                          
                            makeCytoscapeBlockMap(id,eval('(' + data.cw_node_data + ')'), eval('(' + data.cw_edge_data + ')'));
                            //$("#bi"+id).css({display:"block"});                           
                           
                        }
                    });
                });  
                $("#bi"+id).live('click', function(e){
                    //$(".block_info").not('#'+id).css({display:"block"});
                    //$(".hr").css({display:"block"});
                    //$(".quick_launch").css({display:"block"});
                    $("#netview"+id).css({display:"none"});
                    $("#info_box"+id).css({display:"block"});
                    //$("#tr"+id).css("height",f[id]);
               
                });
            
                $("#choosebox_"+id+" .choosecolor span").live('mouseover',function(){
                    $(this).parent().css('background-color', '#F6F6F6');
                    //var index= $(this).parent().parent().index();
                    var type=$.trim($(this).parent().find('input').val());
                    var edges=vis[id].edges();
                    var nodes=vis[id].nodes();
                    var bypass={
                        nodes:{},
                        edges:{}
                    };        
                    var props ={
                        opacity : 1
                    };
                    var _props={
                        opacity: 0.08
                    };
                    var nodesArray=[];
                    var edgesArray=[];
                    $.each(nodes,function(i,e){
                        var n=e.data.id;
                        bypass["nodes"][n]=_props;         
                    });
                    $.each(edges,function(i,e){
                        var c=e.data.id;
                        bypass["edges"][c]=_props;    
                        if($.trim(e.data.egc)==type){
                            var t=e.data.target;
                            var s=e.data.source;             
                            nodesArray.push(t);
                            nodesArray.push(s);
                            edgesArray.push(c);              
                        }          
                    });
                    vis[id].visualStyleBypass(bypass);
                    $.each(nodesArray,function(i,e){
                        bypass["nodes"][e]=props;           
                    });
                    $.each(edgesArray,function(i,e){
                        bypass["edges"][e]=props;           
                    });
                    vis[id].visualStyleBypass(bypass); 
       
                });
    
                $("#choosebox_"+id+" .choosecolor span").live('mouseout',function(){
                    $(this).parent().css('background-color', '#FFF');
                    var edges=vis[id].edges();
                    var nodes=vis[id].nodes();
                    var props ={
                        opacity : 1
                    };
                    var bypass={
                        nodes:{},
                        edges:{}
                    };
                    $.each(nodes,function(i,e){
                        var n=e.data.id;
                        bypass["nodes"][n]=props;         
                    });  
                    $.each(edges,function(i,e){
                        var c=e.data.id;
                        bypass["edges"][c]=props;         
                    });        
                    vis[id].visualStyleBypass(bypass);   
                });
                
                $('#exportBtn'+id).live('click',function(){
                    var visModel=vis[id];
                    var selectedValue = $('#export_'+id).get(0).selectedIndex;
                    var format=$('#export_'+id).get(0).options[selectedValue].text;
                    visModel.exportNetwork(format, 'export.php?type='+format);   
                });
            }
            function showEdge(eve){
                var id=$(eve).parent().parent().parent().parent().attr('id');
                var vid=id.split('_')[1];
                var total_type = 5;
                $('#'+id+' input').each(function() {
                    var type = $(this).attr('value');
                    var checked = true;
                    if ($(this).attr("checked") === undefined){
                        total_type--;
                        checked = false;
                    }
                    edge_checked[type] = checked;
                });
    
                if (total_type == 5){
                    vis[vid].removeFilter("edges", true);
                    _lastFilter = null;
                }else {
                    _lastFilter = function(edge) {
                        return edge_checked[edge.data.egc];
                    };
                    vis[vid].filter("edges", _lastFilter, true);
                }
            }
            function layout(layout){
                var id=$(layout).attr('id');
                var visModel=vis[id.split('_')[1]];
                var selectedValue = layout.selectedIndex;
                var layoutType = layout.options[selectedValue].text;
                var options={};
                if(layoutType=='ForceDirected'){
                    options={
                        weightAttr : "distance"
                    }
                }else if(layoutType=='Radial'){
                    options={
                        radius:150,
                        angleWidth:720
                    } 
                }
                var vis_network = visModel.networkModel();
                var vis_visualStyle = visModel.visualStyle();
                visModel.draw({
                    network: vis_network,
                    visualStyle : vis_visualStyle,
                    layout : {
                        name : layoutType,
                        options :options
                    }
                });
            }
            
            function makeCytoscapeBlockMap(id ,nodes_data ,edges_data){
                var network_json = {
                    dataSchema : 
                        {
                        nodes : [{
                                name : "count", 
                                type : "number"
                            },{
                                name: "ngc",
                                type: "string"
                            }],
                        edges : [{
                                name : "distance", 
                                type : "number"
                            },{
                                name: "pvalue",
                                type: "number"
                            },{
                                name: "egc",
                                type: "string"
                            }]
                    },
                    data : 
                        {
                        nodes : nodes_data,
                        edges : edges_data
                    }
                };
                vis[id] = new org.cytoscapeweb.Visualization('netview_box_'+id, cw_options);
                vis[id].ready(function(){
                    if (!vis[id].hasListener('click', 'nodes')){
                        vis[id].addListener('click', 'nodes', function(event){
                            handle_click(event,id);
                        });
                    }
                    if (!vis[id].hasListener('click', 'edges')){
                        vis[id].addListener('click', 'edges', function(event){
                            handle_click(event,id);
                        });
                    }
                   
                    if (_lastFilter != null){
                        vis[id].filter("edges", _lastFilter, true);
                    } else {
                        edge_checked['pai'] = true;
                        edge_checked['nai'] = true;
                        edge_checked['coexp'] = true;
                        edge_checked['coloc'] = true;
                        edge_checked['pi'] = true;
                        edge_checked['spd'] = true;
                    }
                     
                });
                vis[id].draw({
                    network: network_json,
                    visualStyle : cw_style,
                    panZoomControlVisible: true,
                    edgesMerged: false,
                    nodeLabelsVisible: true,
                    edgeLabelsVisible: false,
                    nodeTooltipsEnabled: false,
                    edgeTooltipsEnabled: false,
                    layout : {
                        name : "ForceDirected",
                        options : {
                            weightAttr : "distance"
                        }
                    }
                });
            }
            function handle_click(event,id) {
                var viewType = event.target.data.id.split('_')[2];
                var group = event.group;
                var target = event.target;
                var tdid = target.data.id;
    
                // Click event
                if (event.type == 'click'){
                    if (group != 'nodes' && group != 'edges'){
                        return;
                    } else {
                        $.ajax({
                            type: 'POST',
                            url: 'ajax_get_info.php',
                            dataType: 'JSON',
                            data: {
                                viewType: viewType
                                , 
                                group: group
                                , 
                                id: tdid
                                , 
                                score_neg: -0.08
                                , 
                                score_pos: 0.08
                                , 
                                p_neg: 0.05
                                , 
                                p_pos: 0.05
                                , 
                                rvalue: 'significant'
                                ,
                                database:<?php echo $database; ?>           
                            },
                            async: true,
                            success: function(data){
                                if ($('.clicked_target').length != 0){
                                    $('.clicked_target').remove();
                                }
                                var divHtml = '<div class="clicked_target" id=' + tdid + ' style="position: relative;z-index: 12; top:' + (target.y - 600) +'px; left:'+ target.x + 'px; width: 2px;height: 2px;"></div>';
                                $('#netview_box_'+id).append(divHtml);
                                showTargetInfo(event.mouseX, event.mouseY, tdid, group, viewType, data, id);
                            }
                        });
                    }
                }
            }
            function showTargetInfo(x, y, tdid, group, type, data, id){
                var showId = tdid.split('_')[0] + '-' + tdid.split('_')[1];
                if(group=='nodes') showId=tdid.split('_')[0];
                var left = $('#netview_box_'+id).offset().left + x + (x > 300 ? -310 : 10);
                var height = $('#netview_box_'+id).offset().top + y;
                var top = height + 260 > $(window).height() ? height - 160 : height + 40;
                var htmlInfo = makeTargetInfoHtml(type, group, data);
                $('.clicked_target').dialog({
                    id: tdid,
                    title: showId,
                    width: 400,
                    height: 220,
                    left: left,
                    top: top,
                    cancelBtn: false,
                    iconTitle:false,
                    rang: true,
                    content:htmlInfo
                });
                $('.clicked_target').trigger("click");
            }
            function makeTargetInfoHtml(type, group, data){
                var html = '';
                if (group == 'nodes'){
                    html += '<span class="node_name">Primary SGDID: </span><span class="node_value">'+ data.Primary_SGDID + '</span></br>' + 
                        '<span class="node_name">Feature Name: </span><span class="node_value"><a target="_blank" href="http://www.yeastgenome.org/cgi-bin/locus.fpl?locus=' + data.Feature_Name + '">' +data.Feature_Name + '</a></span></br>' + 
                        (data.Standard_Gene_Name == '' ? '' : '<span class="node_name">Standard Gene Name: </span><span class="node_value">'+data.Standard_Gene_Name + '</span></br>') +  
                        (data.Alias == '' ? '' : '<span class="node_name">Alias: </span><span class="node_value">'+data.Alias + '</span></br>') + 
                        (data.Parent_Feature_Name == '' ? '' : '<span class="node_name">Parent Feature Name: </span><span class="node_value">'+data.Parent_Feature_Name + '</span></br>') + 
                        (data.Secondary_SGDID == '' ? '' : '<span class="node_name">Secondary SGDID: </span><span class="node_value">'+data.Secondary_SGDID + '</span></br>') + 
                        (data.Chromosome == '' ? '' : '<span class="node_name">Chromosome: </span><span class="node_value">'+data.Chromosome + '</span></br>') + 
                        (data.Start_Coordinate == '' ? '' : '<span class="node_name">Start Coordinate: </span><span class="node_value">'+data.Start_Coordinate + '</span></br>') + 
                        (data.Stop_Coordinate == '' ? '' : '<span class="node_name">Stop Coordinate: </span><span class="node_value">'+data.Stop_Coordinate + '</span>');
        
                } else if (group == 'edges') {
                    if (type == 'c'){
                        html += '<ul class="network_list tooltip"><li class="network_group"><div class="label"><div class="per_cent_text"><span>Correlation Score</span></div><div class="network_name">Gene 1 | Gene 2</div></div>';
                        $.each(eval(data),function(i,e){
                            html += '<ul style="display: block;"><li class="network"><div class="label"><div class="per_cent_text"><span>' + e.weight + '</span></div><div class="network_name">' + e.gene1 + ' | ' + e.gene2 + '</div></div></li></ul>';
                        });
                    } else if (type == 'pai' || type == 'nai'){
                        html += '<ul class="network_list tooltip"><li class="network_group"><div class="label"><div class="per_cent_text"><span>SGA Score | P-Value</span></div><div class="network_name">Query | Array</div></div>';
                        $.each(eval(data),function(i,e){
                            html += '<ul style="display: block;"><li class="network"><div class="label"><div class="per_cent_text"><span>' + e.weight + ' | ' + e.pvalue + '</span></div><div class="network_name">' + e.gene1 + ' | ' + e.gene2 + '</div></div></li></ul>';
                        });
                        html += '</li></ul>';
                    } else {
                        html += '<ul class="network_list tooltip"><li class="network_group"><div class="label"><div class="per_cent_text"><span>Network Name | Weight</span></div><div class="network_name">Gene 1 | Gene 2</div></div>';
                        $.each(eval(data),function(i,e){
                            html += '<ul style="display: block;"><li class="network"><div class="label"><div class="per_cent_text"><span><a target="_blank" href="http://www.ncbi.nlm.nih.gov/pubmed?term=' + e.pubmed + '">' + e.network + '</a> | ' + e.weight + '</span></div><div class="network_name">' + e.gene1 + ' | ' + e.gene2 + '</div></div></li></ul>';
                        });
                        html += '</li></ul>';
                    }
                } else if (group == 'table'){
                    html += '<span class="node_name">Gene Description: </span><br /><span class="node_value">'+ data.Description + '</span><br /><br />' + 
                        '<a href="http://db.yeastgenome.org/cgi-bin/search/quickSearch?query=' + data.Feature +  '" target="_blank">More...</a>';
                } else {}
                return html;
            }
        </script>
    </body>
</html>