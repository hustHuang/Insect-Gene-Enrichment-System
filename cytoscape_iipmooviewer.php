<?php
//require_once './common.php';
//$query_names = array_key_exists('geneNames', $_REQUEST) ? $_REQUEST['geneNames'] : NULL;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Science_2010_map</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" type="text/css" media="all" href="iipmooviewer2/css/iip.css" />
        <link rel="stylesheet" type="text/css" href="iipmooviewer2/css/imgareaselect-default.css" />
        <!--[if lt IE 9]>
            <link rel="stylesheet" type="text/css" media="all" href="iipmooviewer2/css/ie.css" />
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="./inc/css/layout-default-latest.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.all.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.tabs.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/cytoscype_iipmooviewer.css" />
    </head>
    <body>
        <div id="view_container">
            <div id="view" class="ui-layout-center no-padding no-scrollbar">
                <div id="viewer"></div>
            </div>
            <div id="imagick" class="ui-layout-east no-padding no-scrollbar" style="z-index:15;">
                <div class="imgLoading" style="clear: both;display:none"></div>
                <div id="east-content"></div>
            </div>
        </div>

        <script type="text/javascript" src="./inc/js/lib/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/underscore-min.js"></script>
        <script type="text/javascript">
            var $j = jQuery.noConflict();
        </script>
        <script type="text/javascript" src="./inc/js/lib/jquery-ui-1.8.17.custom.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/jquery.layout-latest.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/jquery.imgareaselect.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/mootools-core-1.3.2-full-nocompat.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/mootools-more-1.3.2.1.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/protocols.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/cytoscape_iipmooviewer.js"></script>
        <script type="text/javascript" rc="./inc/js/lib/json2.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/AC_OETags.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/cytoscapeweb.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/lhgdialog.js"></script>
        <script type="text/javascript" src="./inc/js/Science_2010_map_09.js"></script>
        <script type="text/javascript" src="./inc/js/makeCytoscapeWebView.js"></script>


        <!--[if lt IE 7]>
          <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE7.js">IE7_PNG_SUFFIX = ".png";</script>
        <![endif]-->

        <script type="text/javascript">
            var initialWidth=3844;  //原图片的大小
            var initialHeight=3544;
            var initialX=-2468;
            var initialY=-458;
            var _posArray;  //记录位置的数组
            var _selectedArray=[];//包含在截取区域内部的基因的数组
            var isMouseDown;//标记鼠标是否按下
            var query_names='';
            var _top=0,_left=0;
            var instance_1,instance_2;
            var Layout;
            $j(function(){
                $j('#view_container').height($j(window).height()-12);
                Layout=$j('#view_container').layout({
                    east__size:  675
                    //,east__maxSize:   675
                    ,east__minSize :  400
                    ,east__resizable:  true
                    ,east__initClosed: true
                    ,east__onresize:function(){sizeImagick();} 
                    ,east__onclose: function(){reSizeImagick();}
                    ,east__onopen:function(){sizeImagick();}
                });

                // The iipsrv server path (/fcgi-bin/iipsrv.fcgi by default)
                var server = '/fcgi-bin/IIPImageServer.fcgi';
                //var server = 'http://115.156.216.80/fcgi-bin/iipsrv.fcgi';

                // The *full* image path on the server. This path does *not* need to be in the web
                // server root directory. On Windows, use Unix style forward slash paths without
                // the "c:" prefix
                var images = 'D:/WebRoot/SGA_17/iipmooviewer2/Science_2010_map_09.tif';
                //var images = 'D:/WebRoot/iipmooviewer2/result.tif';
                // Copyright or information message
                var credit = '&copy; copyright or information message';

                // Create our viewer object
                // See documentation for more details of options
                var iipmooviewer = new IIPMooViewer( "viewer", {
                    image: images,
                    server: server,
                    credit: credit, 
                    scale: 20.0,
                    viewport:{resolution:2, x:0.00, y:0.15, rotation:0},
                    showNavWindow: true,
                    showNavButtons: true,
                    winResize: false,
                    protocol: 'iip'
                });
            });
            $j('#getImagick').live('click',function(){    
                var timeout;
                if(Layout.state.east.isClosed){
                    timeout=250; 
                }else{
                    timeout=0;
                }
                $j('#east-content').hide();
                $j('.imgLoading').show();
                Layout.show("east");
                Layout.open("east");
                Layout.sizePane('east',675);
                $j('#imagick').css('z-index',15);
                $j('#imagick').show();
                $j('.imgareaselect-selection').parent().hide();             
                $j('.imgareaselect-outer,.imgareaselect-selection').hide();
                //Layout.close("west");
                $j('#viewer').width($j(window).width()-$j('#imagick').width());             
                var pos=$j('.canvas').attr('d');//取出截取位置的坐标
                var x=parseInt(pos.split('_')[0]);
                var y=parseInt(pos.split('_')[1]);
                var w=parseInt(pos.split('_')[2]);
                var h=parseInt(pos.split('_')[3]);
                   
                //寻找截取区域内的基因
                /*
                var pos;
                var query_names='';
                $j.each(posArray,function(i,e){
                    var dx=e.x-initialX;
                    var dy=e.y-initialY;
                    if(x<dx&&dx<x+w&&y<dy&&dy<y+h){
                        _selectedArray.push(e.name);
                        var genes=e.name.split('_');
                        if(genes.length>1){
                            var gene=genes[0];
                        }else{
                            var gene=e.name;
                        }
                        query_names+=gene+' ';
                    }     
                });
                 */
                
                //标示截取区域
                $j('.selected_area').remove();
                var div='<div class="selected_area"></div>';
                $j('#viewer').append(div);            
                var d1=parseFloat($j('.imgareaselect-selection').parent().width()/parseInt($j('.canvas').css('width')));    //储存宽度比例
                var d2=parseFloat($j('.imgareaselect-selection').parent().height()/parseInt($j('.canvas').css('height')));  //储存高度比例
                var left_gap=parseInt($j('.imgareaselect-selection').parent().css('left'))-8-parseInt($j('.canvas').css('left'));
                var top_gap=parseInt($j('.imgareaselect-selection').parent().css('top'))-parseInt($j('.canvas').css('top'));//获取初始相对位移
                var current_radio=$j('.canvas').attr('r');//记录canvas的初当前缩放比例
                //console.log('l_'+left_gap+' t_'+top_gap);
                $j('.selected_area').attr('wr',d1);
                $j('.selected_area').attr('hr',d2);
                $j('.selected_area').attr('c_r',current_radio);
                $j('.selected_area').attr('left_gap',left_gap);
                $j('.selected_area').attr('top_gap',top_gap);//数据存储
                $j('#viewer .selected_area').css({
                    width:$j('.imgareaselect-selection').parent().width()-2,
                    height:$j('.imgareaselect-selection').parent().height()-2,
                    left:parseInt($j('.imgareaselect-selection').parent().css('left'))-8,
                    top:parseInt($j('.imgareaselect-selection').parent().css('top'))
                });
                $j('#viewer .selected_area').show();
                //alert(x+'_'+y+'_'+w+'_'+h);
                setTimeout(function(){//to make the loading image show,so there is time out
                    $j.ajax({
                        type:'POST',
                        url:'ajax_search.php',
                        dataType: 'JSON',
                        async: false,
                        data: {
                            query_names:query_names,
                            epsilon_neg:-0.08,
                            pvalue_neg:	0.05, 
                            epsilon_pos:0.08, 
                            pvalue_pos:	0.05, 
                            rvalue:'significant',
                            view:'nv', 
                            type:'c',
                            query_type:'within',
                            num:20
                        },
                        async: false,
                        success: function(data){
                            //alert(data.cw_node_data);
                            $j('.networkview').remove();
                            var tools = '<div id="tools" class="networkview">'+
                                '&nbsp;&nbsp;<span>Change Network Layout: </span><select title="Select layout type" name="layout" id="layout" onchange="layout(this)">' + 
                                '<option value="1">ForceDirected</option>' +
                                '<option value="2">Circle</option>' + 
                                '<option value="3">Radial</option>' + 
                                '<option value="4">Tree</option>' + 
                                '</select>' + 
                                '&nbsp;&nbsp;&nbsp;&nbsp;<span>Export in : </span><select title="Select export type" name="export" id="export">' + 
                                '<option value="">xgmml</option>' +
                                '<option value="">png</option>' + 
                                '<option value="">sif</option>' + 
                                '<option value="">svg</option>' +
                                '<option value="">pdf</option>' +
                                '<option value="">graphml</option>' + 
                                '</select>'+
                                '&nbsp;&nbsp;<input class="exportBtn" id="exportBtn" type="button" value="Export"></input>' + 
                                '</div>' ;
                            $j('#east-content').append(tools);
                            var  chooseHtml='<div class="choosebox networkview" id="choosebox" >'+
                                '<div id="chooseTitle" class="chooseTitle"><span>Choose Interaction Type</span><a id="toggle" class="open"></a></div>'+
                                '<div class="chooseitem">'+
                                '<div class="choosecolor" style="color:#c3844c;"><input class="choosetype" type="checkbox" name="reltype" value ="c" checked disabled><span>Correlation</span></div>'+
                                '</div>'+                    
                                '<div class="chooseitem">'+
                                '<div class="choosecolor" style="color:#FBD10A;"><input class="choosetype" type="checkbox" name="reltype" value ="coexp" checked onclick="showEdge(this)"><span>Co-expression</span></div>'+
                                '</div>'+
                                '<div class="chooseitem">'+
                                '<div class="choosecolor" style="color:#6261fc;"><input class="choosetype" type="checkbox" name="reltype" value ="coloc" checked onclick="showEdge(this)"><span>Co-localization</span></div>'+
                                '</div>'+
                                '<div class="chooseitem">'+
                                '<div class="choosecolor" style="color:#9EB5E6;"><input class="choosetype" type="checkbox" name="reltype" value ="pi" checked onclick="showEdge(this)"><span>Physical interactions</span></div>'+
                                '</div>'+
                                '<div class="chooseitem">'+
                                '<div class="choosecolor" style="color:#00CCFF;"><input class="choosetype" type="checkbox" name="reltype" value ="spd" checked onclick="showEdge(this)"><span>Shared protein domains</span></div>'+
                                '</div>'+
                                '</div>';
                            $j('#east-content').append('<div class="networkview" id="nw_view"></div>');
                            makeCytoscapeWebView('nw_view', eval('(' + data.cw_node_data + ')'), eval('(' + data.cw_edge_data + ')'));
                            $j('#nw_view').append(chooseHtml);
                            $j('#imagick').show();
                            $j('#east-content').show();
                            $j('.imgLoading').hide();
                            if($j('#east-content').width()<Layout.state.east.size+30){
                               Layout.sizePane('east',$j('#east-content').width()+30);
                            }
                        }
                    });
                },timeout);
                
                //initial filter
                //_lastFilter = function(edge) {
                //    return edge_checked[edge.data.egc];
                // };
                //vis.filter("edges", _lastFilter, true);
                
                //export network
                $j('#exportBtn').live('click',function(){
                    var index=$j('#export').get(0).selectedIndex;
                    var format=$j('#export').get(0).options[index].text;
                    vis.exportNetwork(format, 'export.php?type='+format);   
                }); 
                
                //toggle the choosebox header
                $j('.choosebox #toggle').live('click',function(){
                    if($j(this).hasClass('open')){
                        $j(this).removeClass('open').addClass('closed');
                        $j('.chooseitem').hide();
                    }else{
                        $j(this).addClass('open').removeClass('closed');
                        $j('.chooseitem').show();
                    }
                });
                
                
                $j('.choosecolor span').live('mouseover',function(){
                    $j(this).parent().css('background-color', '#6D848C');
                    var type=$j.trim($j(this).parent().find('input').val());
                    var edges=vis.edges();
                    var nodes=vis.nodes();
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
                    $j.each(nodes,function(i,e){
                        var n=e.data.id;
                        bypass["nodes"][n]=_props;         
                    });
                    $j.each(edges,function(i,e){
                        var c=e.data.id;
                        bypass["edges"][c]=_props;    
                        if($j.trim(e.data.egc)==type){
                            var t=e.data.target;
                            var s=e.data.source;             
                            nodesArray.push(t);
                            nodesArray.push(s);
                            edgesArray.push(c);              
                        }          
                    });
                    vis.visualStyleBypass(bypass);//set all nodes and edges unvisible
                    $j.each(nodesArray,function(i,e){
                        bypass["nodes"][e]=props;           
                    });
                    $j.each(edgesArray,function(i,e){
                        bypass["edges"][e]=props;           
                    });
                    vis.visualStyleBypass(bypass);//set related nodes and edges visible
       
                });
    
                $j('.choosecolor span').live('mouseout',function(){
                    $j(this).parent().css('background-color', '#1A313F');
                    var edges=vis.edges();
                    var nodes=vis.nodes();
                    var props ={
                        opacity : 1
                    };
                    var bypass={
                        nodes:{},
                        edges:{}
                    };
                    $j.each(nodes,function(i,e){
                        var n=e.data.id;
                        bypass["nodes"][n]=props;         
                    });  
                    $j.each(edges,function(i,e){
                        var c=e.data.id;
                        bypass["edges"][c]=props;         
                    });        
                    vis.visualStyleBypass(bypass);
                });                 
            });
            
            function sizeImagick(){
                $j('#viewer').width($j(window).width()-$j('#imagick').width()-30);       
            }
            function reSizeImagick(){
                $j('#viewer').width($j(window).width()-15);
            }
            
            
        </script>
    </body>
</html>
