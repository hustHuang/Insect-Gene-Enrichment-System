<?php
require_once './common.php';
require_once ABSPATH . '/class/BlockData.class.php';
$query_names = $_REQUEST['geneNames'];
$query_type = 'with';
//$database = '2';
if (is_null($query_names) || is_null($query_type)) {
    ?>
    <script type="text/javascript">
        alert("No querykeywords were entered. Please try again.");
        window.location.href='index.php';
    </script>
    <?php
} else {
    $block_service = new BlockData();
    $array_block_id = $block_service->get_block_id($query_names);
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
        <div id="tabs-center" class="ui-layout-center">
            <div id="loading"></div>
            <div class="content">    
                <div class="block_container" id="block_container">
                    <div class="result_view">
                        <div class="block_detail" id="block_detail"><div id='hoverBox'></div>
                            <!-- % -->
                            <?php
                            if (!is_null($array_block_id) && count($array_block_id) > 0) {
                                foreach ($array_block_id as $block_id) {
                                    if (!$block_service->block_exists($block_id))
                                        continue;
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
                                                                <input type='button' id='<?php echo 'bi' . $block_id; ?>' value='BlockInfo' style="float:right;background-color:#3E3E3E;color: #FFF;border: none;"></input>
                                                                <div class="tools"><span>Change Network Layout:</span>
                                                                    <select name="layout" id="<?php echo 'layout_' . $block_id; ?>" onchange="layout(this)">
                                                                        <option value="1">ForceDirected</option>
                                                                        <option value="2">Circle</option>
                                                                        <!--<option value="3">Radial</option>-->
                                                                        <option value="4">Tree</option>
                                                                    </select>&nbsp;&nbsp;<span>Select the format to export:</span>
                                                                    <select name="export" id="<?php echo 'export_' . $block_id; ?>">
                                                                        <option value="">xgmml</option>
                                                                        <option value="">png</option>
                                                                        <option value="">sif</option>
                                                                        <option value="">svg</option>
                                                                        <option value="">pdf</option>
                                                                        <option value="">graphml</option>
                                                                    </select><input id="<?php echo 'exportBtn' . $block_id; ?>" type="button" value="Export" style="width:50px;background:none;border:none;text-decoration:underline;font-style:italic;font-size:14px;"></input></div>
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
                                                            <input type='button' id='<?php echo 'nv' . $block_id; ?>' value='NetworkView' style="float:right;background-color:#3E3E3E;color: #FFF;border: none;"></input>
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
                    </div>
                </div>
            </div>
            <div class="quick_launch"></div>
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
        <script src="./inc/js/lib/raphael-min.min.js"></script>
        <script type="text/javascript" src="inc/js/block.js"></script>
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
            var vis = [];
            var NUM = 5;  //num of blocks to show first
            var f = [];
            var _lastFilter;
            var edge_checked = {};
            var nodeColorMapper = {
                attrName: "ngc", 			// nodeGroupCode
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
                attrName: "egc",                           // edgeGroupCode
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
                slidable:                  false
                ,resizerDblClickToggle:     false 
                ,togglerLength_open:        0
                ,togglerLength_closed:      0
                ,spacing_open:              0  //width of the resizer bar
                ,spacing_closed:            0              
            });        

            $('#block_container').hide();
            $('#loading').show();
            $(document).ready(function(){
                $('#tabs-center').mCustomScrollbar({
                    advanced:{updateOnContentResize: true,autoScrollOnFocus: false}
                    ,callbacks:{
                         onScroll: function(){resetQuicklunch();}
                        ,whileScrolling:function(){ resetQuicklunch();}
                        ,onTotalScroll: function(){
                            var lid = $('.blockdiv:eq('+(i-1-1)+')').attr('id');//  显示当前页面最后一个div
                            var j = NUM+i;
                            $('#loading'+lid).show();
                            $('#tabs-center .quick_launch .quick_launch_node').remove();
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
                                if(id==null) break;
                                if ($('#block_container .result_view div#' + id+':visible').length>0) continue;
                                $('#'+id).show();
                                $('#hr'+id).show();
                                getData(id);
                            }
                            $('#loading'+lid).hide();
                            resetStyle(); 
                        }
                    }
                });
                $('#block_container').show();
                var i = 0;
                for(; i < NUM ; i++)
                { 
                    var id = $('.blockdiv:eq('+ i +')').attr('id'); 
                    $('#' + id).show();
                    $('#hr' + id).show();
                    getData(id);
                }
                $('#loading').hide();  
                //$('#block_container').show();
                if($('#block_container').find('.block_info').length==0){
                    $('.quick_launch').remove();
                    $('#block_detail').append('<div id="noBlock">No search results!</div>');
                }
                $('#block_container').bind('click',function(){
                    var cid = $('#block_container').attr('data');
                    if(cid != 0 && cid != null && cid != undefined){//only when id is a non-zero value can it works
                        $('#' + cid).show();
                        $('#hr' + cid).show();
                        getData(cid);
                        $('#block_container').attr('data',0);//reset the data to 0
                    }
                });
                
                $('.monthLabel,.year').live('click',function(e){
                    var strain ,name ;
                    if($(this).attr("class") == "monthLabel"){
                        strain = $(this).attr('id').split('_')[0];
                        name = $(this).attr('id').split('_')[1];
                    }else{
                        strain = $(this).attr('id').split('_')[1];
                        name = $(this).attr('id').split('_')[2];
                    }  
                    var mouseX = e.pageX ;
                    var mouseY = e.pageY ;  
                    var viewType = 'block';
                    var group = 'nodes';
                    $.ajax({
                        type: 'POST',
                        url: './ajax_get_info.php',
                        dataType: 'JSON',
                        data: {
                            viewType: viewType ,
                            group: group ,
                            id: name
                        },
                        async: true,
                        success: function(data){
                            data.strain = strain;
                            if ($('.clicked_target').length != 0){
                                $('.clicked_target').remove();
                            }
                            var divHtml = '<div class="clicked_target" style="z-index: 12; width: 2px;height: 2px;"></div>';
                            $("#block_container").append(divHtml);
                            
                            var htmlInfo = makeTargetInfoHtml(viewType,group,data);
                            var title = "Description of " + name;
                            var diaId = "dia-" + name;
                            var container = parent.document.getElementById('result_container');
                            var width = 400 , height = 180 ; 
                            var left = mouseX + $(container).offset().left;
                            var top = mouseY + $(container).offset().top;
                            if(left + width > $(container).width() + $(container).offset().left){
                                left = $(container).width() + $(container).offset().left - width - 25;
                            }
                            if(top + height > $(container).height() + $(container).offset().top){
                                top = $(container).height() + $(container).offset().top - height - 25;
                            }
                            $('.clicked_target').dialog({
                                id: diaId,
                                title: title,
                                width: width,
                                height: height,
                                left: left,
                                top: top,
                                content: htmlInfo
                            });
                            $('.clicked_target').trigger("click");
                        }
                    });           
                });

                $('.monthLabel').live({
                    mouseover:function(){
                        $(this).css({"font-weight":"bold",color: "#333"});
                    },
                    mouseout:function(){
                        $(this).css({"font-weight":"normal",color:"#999"});
                    }
                });

                $('.year').live({
                    mouseover:function(){
                        $(this).find("text").css({ "font-weight":"bold",fill:"#333"});
                    },
                    mouseout:function(){
                        $(this).find("text").css({"font-weight":"normal",fill:"#999"});
                    }
                });

    
            });
             
            function resetQuicklunch(){
                $('.quick_launch').css({
                    'margin-top':-$('.mCSB_container').offset().top
                });
            }
            
            //reset the position of the quicklunch node
            function resetStyle(){
                var tree_block=parent.document.getElementById('tree_block');
                $(tree_block).find('.treenode').filter('.selected').each(function(i,e){
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
                        },
                        error:function(){
                            alert('error!');
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
                                $("#monthsCol"+id).append("<p class='monthLabel' id='"+ g +"'>" + g.split('_')[1] + "</p>");
                            }
                        });
                    }
                })
            }
            
            function drawMonths(a,id){
                $.each(a,function(b,c){
                    var e = 0 , g = 0 , d = c.year;
                    var gene = d.split('_')[1];
                    var strain = d.split('_')[0];
                    $("#tileStage"+id).append("<div class='yearCol' style='overflow:visible;'><div id='text_"+ d + "_" + id + "' strain='"+ strain + "' style='overflow:visible;' class='year'></div>");
                    $.each(c,function(g, f){
                        if(g!="year"){
                            e+=1;
                            if(f=='nan') g=0;
                            else if(parseFloat(f) > 0.1) g = 0.1;
                            else if(parseFloat(f) < -0.7) g = -0.7;
                            else g = f;
                            $("#tileStage"+id+" .yearCol:last").append("<div class='month' id='"+e+"_"+d+"' percentage='"+f+"' style = 'background-color:"+UNEMPLOYMENT.colors[Math.floor(10*parseFloat(g)+7)]+ "'></div>");
                        }
                    });
                    Raphael("text_"+ d + "_" + id).text( 5 , 40 , gene).transform("r-60");
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
                    var query_names = '';var a,b,c,d;
                    for(var i=0;i< len;i++){
                        a = $("#monthsCol"+id+" .monthLabel").eq(i).text();
                        //b=a.split('_')[1];
                        query_names += a + ' ';
                    }
                    var le = $("#tileStage"+id+" .yearCol").length;
                    for(var j = 0;j < le;j++){
                        c = $("#tileStage"+ id + " .yearCol").eq(j).find('text').text();
                        //d=c.split('_')[1];
                        query_names += c + ' ';
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
                            "query_names":query_names
                        },
                        async: false,
                        success: function(data){
                            $('.net_loading').hide();
                            $('.netview-content').show(); 
                            $("#netview_box_"+id).css({"height":f[id]+3});
                            $("#choosebox_"+id).css({"height":f[id]-17});                          
                            makeCytoscapeBlockMap(id,eval('(' + data.cw_node_data + ')'), eval('(' + data.cw_edge_data + ')'));
                        }
                    });
                });  
                $("#bi"+id).live('click', function(e){
                    $("#netview"+id).css({display:"none"});
                    $("#info_box"+id).css({display:"block"});           
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
        </script>
    </body>
</html>