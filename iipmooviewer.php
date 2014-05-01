<?php
//require_once './common.php';
$query_names = array_key_exists('geneNames', $_REQUEST) ? $_REQUEST['geneNames'] : NULL;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Cluster interaction profiles of genes</title>
        <meta charset="utf-8" />
        <meta name="author" content="Ruven Pillay &lt;ruven@users.sourceforge.netm&gt;"/>
        <meta name="keywords" content="IIPImage HTML5 Ajax IIP Zooming Streaming High Resolution Mootools"/>
        <meta name="description" content="IIPImage: High Resolution Remote Image Streaming Viewer"/>
        <meta name="copyright" content="&copy; 2003-2011 Ruven Pillay"/>
        <link rel="stylesheet" type="text/css" media="all" href="iipmooviewer2/css/iip.css" />
        <link rel="stylesheet" type="text/css" href="iipmooviewer2/css/imgareaselect-default.css" />
        <!--[if lt IE 9]>
            <link rel="stylesheet" type="text/css" media="all" href="iipmooviewer2/css/ie.css" />
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="./inc/css/layout-default-latest.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.all.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.tabs.css" />
        <style type="text/css">
            body{height:100%;height:100%;margin:0px;font-family: 'Segoe UI Semilight', 'Open Sans', Verdana, Arial, Helvetica, sans-serif;}
            #view_container{width:100%;height:auto;background-color:#CCC;padding: 0px;margin-top: 0px;}
            #view{width:auto;height:auto;margin:0px;padding-top: 0px;margin-top:60px;margin-left: 0px;background-color:#CCC;}
            #viewer{width:100%;height:100%;margin:0px;padding-top:0px;margin-top:0px;margin-left: 0px;background-color:#CCC;}
            #navbar{width:auto;height:auto;margin-top:0px;padding:0px; border: 1px solid #CCC;overflow:auto;} 
            #navbar table{width:100%;margin:0px auto;font-size: 12px;}
            #navbar table th{height:30px;line-height:30px;font-weight: bold;text-align: left;background-color: #1A313F;color: #FFF;font-style: italic;}
            #navbar table td{height:30px;line-height:30px;border-top:1px solid #FFF;text-align: left;}
            #navbar .item{font-size: 12px;padding:0px; background-color:#EEE;width:100%;cursor: default;}
            #navbar .activeItem{background-color: #C6C6C6;}
            .navTag{width:2px;height:2px;display:block;background-color:#F3F3FA; position: absolute;z-index:2;}
            .activeNavTag{background-color:yellow ;}
            .tag{width: 24px;height:38px;color: #FFF;z-index: 2;font-size:10px;line-height: 24px;;position:absolute;left: 100px;top:100px;text-align:center;background:url('iipmooviewer2/images/47.ico') no-repeat;}
            .activeTag{font-weight: bold;color:red;z-index: 5;background:url('iipmooviewer2/images/48.ico') no-repeat center bottom;}      
            .single_line{background-color: #FFFCEC;position: absolute;z-index: 2;left: 0px;top: 0px}
            #imagick{width: 100%;clear: both;margin-top: 25px;margin-left:0px;border: 0px solid #026890;z-index:5;}
            #navBack{background-color: #3E3E3E;width:100%;margin: 0px;height: 45px;font-weight: 400;display: none;}
            #back_home{width: auto;height: auto;float:left;border: none;margin-right: 0px;border-right: 1px solid #000;}
            #matrix {display:none;width:auto;height:auto;margin-left:0px;border:none}
            #matrix #getHeatmap{width:auto;height:auto;margin-left:0px;border:none}
            #img{width:100%;height:auto;overflow: scroll;position: relative;border-left: 1px solid #EEE;}
            #img img{width: 100%;height: 100%;}
            .imgLoading{width: 300px;height:300px;margin: 0px auto;background:url('./inc/img/loader.gif') no-repeat center;display:none;z-index: 5 }
            #hide_line{position: absolute;top: 0px;left:0px;height: 3px;background-color: yellow;}
            .imgareaselect-outer{background-color:#ffffcc;padding: 0px;margin:0px;z-index: 3;}
            .imgareaselect-outer p{margin-top: 5px;}           
            #l{float: left;width: auto;height:auto;padding: 0px;margin: 0px;}
            #sr{float:left; width: 150px;height:auto;margin-top:0px; padding: 0px;overflow:hidden;border:0px solid #EEE;}
            #r{width: 150px;height:auto;margin-top:0px; overflow:hidden;}
            #s{width: 150px;height:150px;border:0px solid #EEE;border-left: 0px;border-top:0px;font-size: 12px;padding: 0px;margin: 0px;}
            #s p{margin:auto;padding: 8px;}
            #r p{height: 16px;line-height: 16px;margin: 0px;margin-left: 15px;padding: 0px;font-size: 12px;cursor:default;}
            #t{width: 100%; height:150px;overflow:hidden;margin:0px;border: 0px solid #EEE;border-right:none;}
            #t .col{height:150px;width: 16px;float:left;text-align:left;font-size: 12px;writing-mode: tb-lr;margin: 0px;cursor: default;padding-top: 15px;}
            #img #line_x{position: absolute;width:475px;height: 2px;background-color: #444;z-index: 5;left: 0px;}
            #img #line_y{position: absolute;width: 2px;height: 625px;background-color: #444;z-index: 5;top:0px;}
            #img .stag{position: absolute;width: 12px;height: 12px;border: 2px solid #FFFFFF;z-index:4;}
            .toolbar{width: 75px;margin:0px auto;padding:2px;height:72px;text-align: center;border: 0px solid #999;}
            #getImagick,#reCluster,#cancelBtn{width:75px;height:24px;background-color: #6D848C;color:#FFF;}
            #reCluster,#getImagick{border-bottom: none}
            .credit{display: none;}
            .s_loading{width: 100%;height: 100%;margin: 0px auto;background:url('./inc/img/loader.gif') no-repeat center;display:none}
            #east-content,#east-content-2{z-index:5;display: none;}
            /*area left after select*/
            .selected_area{position: absolute; overflow: hidden; z-index: 0; display: block;border: 1px solid #FFF;}
            /* resizer-bars */
            .ui-layout-resizer{ background:#DDD;border:1px solid #BBB;border-width:0;}
        </style>
        <link rel="stylesheet" type="text/css" href="./inc/css/heatmap.css" />
    </head>
    <body>
        <div id="navBack">
            <input id="back_home" type="button" value="Back To Homepage"/>
        </div>
        <div id="view_container">
            <div id="navbar" class="ui-layout-west no-padding no-scrollbar">
            </div>
            <div id="view" class="ui-layout-center no-padding no-scrollbar">
                <div id="viewer"></div>
            </div>
            <div id="imagick" class="ui-layout-east no-padding no-scrollbar" style="z-index:15;">
                <div class="imgLoading" style="clear: both;display:none"></div>
                <div id="east-content">
                    <div id="l">                 
                        <div id="t"></div>
                        <div id="img">
                            <img src="" alt="Imagick"/>
                        </div>
                    </div>
                    <div id="sr">
                        <div id="s"><div class="s_loading"></div></div>
                        <div id="r"></div>                     
                    </div>
                </div>
                <div id="east-content-2"></div>
            </div>
        </div>

        <script type="text/javascript" src="./inc/js/lib/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/underscore-min.js"></script>
        <script type="text/javascript">
            var $j = jQuery.noConflict();
            function preview(img, selection) {
                if (!selection.width || !selection.height)
                    return;
                alert("Location: (" + selection.x1 + "," + selection.y1 + "),(" + selection.x2 + "," + selection.y1 + "),(" + selection.x1 + "," + selection.y2 + "),(" + selection.x2 + "," + selection.y2 + ")");  
            }
        </script>
        <script type="text/javascript" src="./inc/js/lib/jquery-ui-1.8.17.custom.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/jquery.layout-latest.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/jquery.imgareaselect.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/mootools-core-1.3.2-full-nocompat.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/mootools-more-1.3.2.1.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/protocols.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/raphael.js"></script>
        <script type="text/javascript" src="iipmooviewer2/javascript/iipmooviewer-2.0.js"></script>
        <!--<script type="text/javascript" src="./inc/js/heatmap.js"></script>-->

        <!--[if lt IE 7]>
          <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE7.js">IE7_PNG_SUFFIX = ".png";</script>
        <![endif]-->

        <script type="text/javascript">
            var _posArray;  //记录位置的数组
            var _selectedPosArray;//包含在截取区域内部的位置数组
            var isMouseDown;//标记鼠标是否按下
            var step=16;    //每个单元的size
            var _rows,_cols;//存放两个横列的基因数目
            //var interval=0;
            //var excutime=0;
            var _top=0,_left=0;
            var instance_1,instance_2;
            var Layout;
            $j(function(){
                $j('#view_container').height($j(window).height()-12);
                Layout=$j('#view_container').layout({
                     west__size:       .15
                    ,west__resizable:  false
                    ,west__onclose:function(){resizeViewer();}
                    ,east__size:      675
                  //,east__maxSize:   675
                    ,east__minSize :  400
                    ,east__resizable:  true
                    ,east__initClosed: true
                    ,east__onresize:function(){sizeImagick();sizeImageArea();}
                    ,east__onclose: function(){reSizeImagick();}
                    ,east__onopen:function(){sizeImagick();}
                });
                $j('#back_home').button({});
                function targetclick(e){
                    var xx, yy;
                    xx = e.target.offsetLeft + e.event.offsetX;
                    yy = e.target.offsetTop + e.event.offsetY;
                    //alert("XX= " + xx + " and YY = " + yy);
                }
                // The iipsrv server path (/fcgi-bin/iipsrv.fcgi by default)

                var server = '/fcgi-bin/IIPImageServer.fcgi';
                //var server = 'http://115.156.216.80/fcgi-bin/iipsrv.fcgi';

                // The *full* image path on the server. This path does *not* need to be in the web
                // server root directory. On Windows, use Unix style forward slash paths without
                // the "c:" prefix
                var images = 'D:/WebRoot/SGA_17/iipmooviewer2/fg30_2.tif';
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
                    viewport:{resolution:5, x:0.15, y:0.15, rotation:0},
                    showNavWindow: true,
                    showNavButtons: true,
                    winResize: false,
                    protocol: 'iip',
                    targetclick: targetclick
                });
                //alert(iipmooviewer.createNavigationWindow); 
                var genes="<?php echo $query_names ?>";        
                if(genes=='Cluster genetic interaction profiles'){
                    alert("No query keywords were entered. Please try again.");
                    window.location.href='index.php'; 
                }else{  
                    var navHtml='';
                    var posInfor=getCoordinate(genes);
                    var geneArr=$j.trim(genes).split(' ');
                    if(geneArr.length==1){
                        var x=posInfor.x;
                        var y=posInfor.y;
                        if(x>0){
                            var lineItemX='<div id="x_line" class="single_line" style="background-color:yellow;width:1px;left:0px;"> </div>';
                            $j('#viewer').append(lineItemX);   
                        }
                        if(y>0){
                            var lineItemY='<div id="y_line" class="single_line" style="background-color:yellow;height:1px;top:0px;"> </div>';
                            $j('#viewer').append(lineItemY); 
                        }
                        navHtml+='<p class="item" style="height:50px;line-height:24px;margin:0px;padding-top:8px;">There is only one input.<br/><b>Query:</b>'+posInfor.query.split('_')[0]+'</p>';
                    }else{
                        navHtml+='<table cellpadding="0" cellspacing="0"><tr><th></th><th>Query</th><th>Array</th><th>Score</th></tr>';
                        $j.each(posInfor,function(i,e){
                            //var item='<p class="item" id="item_'+parseInt(i+1)+'">'+' <input type="checkbox" name="item" value="'+parseInt(i+1)+'"><b> ID : </b>item_'+parseInt(i+1)+'<br/><b> Query </b>:'+e.query+' <b> Array:</b>'+e.array+'<br/><b> X: </b>'+e.x+' <b> Y:</b>'+e.y+'</p>';
                            var item='<tr class="item" id="item_'+parseInt(i+1)+'">'+'<td><input type="checkbox" name="item" value="'+parseInt(i+1)+'"/></td><td>'+e.query.split('_')[0]+'</td><td>'+e.array.split('_')[0]+'</td><td>'+getScore(e.query,e.array)+'</td></tr>';
                            navHtml+=item;
                            var tag='<div id="tag_'+parseInt(i+1)+'" class="tag">'+'<span>'+parseInt(i+1)+'</span></div>';       
                            $j('#viewer').append(tag);                       
                        });
                        navHtml+='</table>';
                    }
                    $j('#navbar').append(navHtml);
                    //alert($j('#view_container').height());
                    //$j('#navbar').css('height', $j('#view_container').height());
                    //$j('#navbar').css('height', 0.75*parseInt(window.screen.height));
                }
                
                $j('#back_home').live('click',function(){
                    window.location.href='./index.php';             
                });
                
                $j('#navbar .item').live('click',function(e){
                    var id=$j(this).attr('id').split('_')[1];
                    if(!$j(this).hasClass('activeItem')){
                        //$j('#navbar .item').removeClass('activeItem');
                        $j(this).addClass('activeItem');
                        //$j('.tag').removeClass('activeTag');
                        $j(this).find('input').attr('checked', true);
                        $j('#tag_'+id).addClass('activeTag');
                        $j('#navTag_'+id).addClass('activeNavTag');
                    }else{
                        $j(this).removeClass('activeItem');
                        $j(this).find('input').attr('checked', false);
                        $j('#tag_'+id).removeClass('activeTag');
                        $j('#navTag_'+id).removeClass('activeNavTag');
                    }
                });
              
                $j('.tag').live('click',function(){
                    var id=$j(this).attr('id').split('_')[1];
                    //$j('.tag').removeClass('activeTag');
                    if(!$j(this).hasClass('activeTag')){
                        $j(this).addClass('activeTag'); 
                        $j('#item_'+id).addClass('activeItem');
                        $j('#item_'+id+' input').attr('checked',true);
                        //$j('.item').removeClass('activeItem');                  
                        var height=$j('#navbar').height();
                        var l=$j('#navbar .item').length;
                        var h=parseInt($j('.item').css('height'))+parseInt($j('.item').css('margin-top'));
                        if(l*h>height){                  //点击tag的时候调整滚动条的高度
                            var index=parseInt(id);
                            var scrollTop=$j('#navbar').scrollTop();
                            //var currentHeight=$j('#navbar').height()+scrollTop;
                            if(index*h!=(scrollTop-6*h)){
                                $j('#navbar').scrollTop((index-6)*h);
                            }
                        }
                    }else{
                        $j(this).removeClass('activeTag'); 
                        $j('#item_'+id).removeClass('activeItem');    
                        $j('#item_'+id+' input').attr('checked',false);
                    }
                });
                
                $j('#getImagick').live('click',function(){    
                    var timeout;
                    if(Layout.state.east.isClosed){
                        timeout=250; 
                    }else{
                        timeout=0;
                    }
                    $j('#east-content,#east-content-2').hide();
                    $j('.s_loading').hide();
                    $j('.imgLoading').show();
                    Layout.show("east");
                    Layout.open("east");
                    Layout.sizePane('east',675);
                    $j('#imagick').css('z-index',15);
                    $j('#imagick').show();
                    $j('#east-content-2,#t,#r').empty();
                    $j('.imgareaselect-selection').parent().hide();             
                    $j('.imgareaselect-outer,.imgareaselect-selection').hide();
                    $j('#s p').remove();
                    //Layout.close("west");
                    $j('#viewer').width($j(window).width()-$j('#imagick').width()-$j('#navbar').width());             
                    var pos=$j('.canvas').attr('d');//取出截取位置的坐标
                    var x=parseInt(pos.split('_')[0]);
                    var y=parseInt(pos.split('_')[1]);
                    var w=parseInt(pos.split('_')[2]);
                    var h=parseInt(pos.split('_')[3]);
                    //初始化外围区域的大小
                    $j('#l').width(480);
                    $j('#img').height(640);
                    $j('#r').height(640);
                    //标示截取区域
                    $j('.selected_area').remove();
                    var div='<div class="selected_area"></div>';
                    $j('#viewer').append(div);            
                    var d1=parseFloat($j('.imgareaselect-selection').parent().width()/parseInt($j('.canvas').css('width')));    //储存宽度比例
                    var d2=parseFloat($j('.imgareaselect-selection').parent().height()/parseInt($j('.canvas').css('height')));  //储存高度比例
                    var left_gap=parseInt($j('.imgareaselect-selection').parent().css('left'))-parseInt($j('#navbar').css('width'))-8-parseInt($j('.canvas').css('left'));
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
                        left:parseInt($j('.imgareaselect-selection').parent().css('left'))-parseInt($j('#navbar').css('width'))-8,
                        top:parseInt($j('.imgareaselect-selection').parent().css('top'))
                    });
                    $j('#viewer .selected_area').show();
                    //alert(x+'_'+y+'_'+w+'_'+h);
                    setTimeout(function(){//to make the loading image show,so there is time out
                        $j.ajax({
                            type:'POST',
                            url:'imagick.ajax.php',
                            dataType: 'json',
                            async: false,
                            data:{ x:x, y:y,w:w,h:h},
                            success:function(data){
                                $j('#img .target_line').remove(); 
                                $j('#imagick').show();
                                $j('#east-content').show();
                                $j('.imgLoading').hide();
                                var src='iipmooviewer2/new_'+data+'.png';
                                $j('#img img').attr('src',src);
                                getGeneItems(x,y,w,h);                      //产生横纵基因信息         
                                $j('#img img').css('width',8*w);            //图片区域设置为实际大小的8倍
                                $j('#img img').css('height',8*h);
                                if(8*w< $j('#l').width()){
                                    $j('#l').width(8*w+25);
                                }
                                if(8*h<$j('#img').height()){
                                    $j('#img,#r').height(8*h+25);
                                }
                                if($j(window).height()-$j('#t').height()<8*h){
                                    $j('#img,#r').height($j(window).height()-$j('#t').height()-25);
                                }
                                $j('#east-content').width($j('#l').width()+$j('#sr').width()+5);
                                if($j('#east-content').width()<Layout.state.east.size+30){
                                    Layout.sizePane('east',$j('#east-content').width()+30);
                                }
                                //var step=16;                                //每个单元的size     
                                var row=parseInt($j('#r').height()/step);     //每次显示的行列数
                                var col=parseInt($j('#t').width()/step);  
                                var scrollWidth=parseInt($j('#img').get(0).offsetWidth-$j('#img').get(0).clientWidth);  //滚动条的宽度
                                var extra_n=parseInt(scrollWidth/step);
                                $j('#r p').show();
                                $j('#t .col').show();
                                $j('#r p:gt('+parseInt(row-1-extra_n)+')').hide();          //初始显示的行列数，除去滚动条对应的那一行
                                $j('#t .col:gt('+parseInt(col-1-extra_n)+')').hide();
                                makeSelectedTags();
                                $j('#matrix').show();
                            }
                        });
                    },timeout);
                });
                
                $j('#reCluster').live('click',function(){
                    var timeout;
                    if(Layout.state.east.isClosed){
                        timeout=250; 
                    }else{
                        timeout=0;
                    }
                    var row1=parseInt($j('#matrixBtn #r1').val());
                    var row2=parseInt($j('#matrixBtn #r2').val());
                    var col1=parseInt($j('#matrixBtn #c1').val());
                    var col2=parseInt($j('#matrixBtn #c2').val());
                    $j('.imgareaselect-selection').parent().hide();                    
                    $j('.imgareaselect-outer,.imgareaselect-selection').hide();
                    $j('#east-content,#east-content-2').hide();
                    $j('.s_loading').hide();
                    $j('.imgLoading').show();
                    Layout.show("east"); 
                    Layout.open("east");
                    Layout.sizePane('east',875);
                    $j('#east-content-2').width(800);
                    //标示截取区域
                    $j('.selected_area').remove();
                    var div='<div class="selected_area"></div>';
                    $j('#viewer').append(div);
                    var d1=parseFloat($j('.imgareaselect-selection').parent().width()/parseInt($j('.canvas').css('width')));    //储存宽度比例
                    var d2=parseFloat($j('.imgareaselect-selection').parent().height()/parseInt($j('.canvas').css('height')));  //储存高度比例
                    var left_gap=parseInt($j('.imgareaselect-selection').parent().css('left'))-parseInt($j('#navbar').css('width'))-8-parseInt($j('.canvas').css('left'));
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
                        left:parseInt($j('.imgareaselect-selection').parent().css('left'))-parseInt($j('#navbar').css('width'))-8,
                        top:parseInt($j('.imgareaselect-selection').parent().css('top'))
                    });
                    $j('#viewer .selected_area').show();
                    //加载重新生成的图
                    setTimeout(function(){//to make the loading image show,so there is time out
                        $j.ajax({
                            type: 'GET',
                            dataType: "html",
                            url: './heatmap/testdesc.R',
                            data: {
                                r1: row1,
                                r2: row2,
                                c1: col1,
                                c2: col2
                            },
                            async: false,
                            success: function(data){
                                //alert(data);
                                $j('#east-content-2,#t,#r').empty(); 
                                $j('.imgLoading').hide();
                                $j('#imagick').show();     
                                $j('#east-content-2').append(data);
                                $j.getScript("./inc/js/heatmap.js");
                                $j('#east-content-2').show();
                                //$j('#east-content-2').width($j('#ll').width()+$j('#heatmap').width()+$j('#r_').width()+5);
                            },
                            error:function(){}
                        });
                    },timeout);
                
                    if($j('#east-content-2').width()<Layout.state.east.size+30){
                        setTimeout(function(){
                            Layout.sizePane('east',$j('#east-content-2').width()+30);
                        },50);      
                    }
                
                
                });
                
                
                $j('#img').scroll(function(){
                    //$j('.target_line').remove();
                    //interval=interval+1;
                    //$j('#pointer_1').text(interval); 
                    //$j('#hide_line').remove();
                    //$j('#img').append('<div id="hide_line"></div>');
                    //$j('#hide_line').css('width',$j('#img img').css('width'));
                    var top=this.scrollTop;
                    var left=this.scrollLeft; //获取滚动条移动的距离  
                    var _this=this;
                    clearTimeout(instance_1);          //清除上次的生成
                    instance_1=setTimeout(function(){  //两次触发时间间隔超过一定值才执行
                        //excutime+=1;
                        //$j('#pointer_2').text(excutime); 
                        var row=parseInt($j('#r').height()/step); //每次显示的行列数
                        var col=parseInt($j('#t').width()/step); 
                        //$j('#r p:gt('+parseInt(row-1)+')').hide();
                        //$j('#t span:gt('+parseInt(col-1)+')').hide();
                        var scrollWidth=parseInt(_this.offsetWidth)-parseInt(_this.clientWidth);//滚动条的宽度
                        var extra_n=parseInt(scrollWidth/step);
                        var rows_n=Math.round(top/step);
                        var cols_n=Math.round(left/step);                        
                        $j('#r p').show();              //设置显示与隐藏的行与列
                        $j('#r p:lt('+rows_n+')').hide();
                        $j('#r p:gt('+parseInt(row+rows_n-1-extra_n)+')').hide();
                        $j('#t .col').show();           
                        $j('#t .col:lt('+cols_n+')').hide();
                        $j('#t .col:gt('+parseInt(col+cols_n-1-extra_n)+')').hide();
                        if(top!=rows_n*step){           //调整滚动条位置，为整数个小区域
                            $j(_this).scrollTop(rows_n*step);
                        }
                        if(left!=cols_n*step){
                            $j(_this).scrollLeft(cols_n*step);
                        }
                    },50);
                });
                
                
                $j('#img').live('click',function(e){                   
                    var _this=this;
                    clearTimeout(instance_2);          //清除上次的生成
                    var pointer_X=e.clientX||(e.pageX+(document.documentElement.scrollLeft||document.body.scrollLeft));
                    var pointer_Y=e.clientY||(e.pageY+(document.documentElement.scrollTop||document.body.scrollTop));
                    //alert('e.clientX : '+pointer_X +'  e.clientY : '+ pointer_Y);         //鼠标位置       
                    var x=pointer_X-$j(_this).offset().left+_this.scrollLeft;
                    var y=pointer_Y-$j(_this).offset().top+_this.scrollTop;                 //相对左上角的偏移
                    var scrollWidth=parseInt(_this.offsetWidth)-parseInt(_this.clientWidth);//滚动条的宽度
                    var w=$j('#img').width();
                    var h=$j('#img').height();      //显示框的高度
                    if((_this.scrollLeft+w-x<=scrollWidth)||(_this.scrollTop+h-y<=scrollWidth)){
                        //alert('No action!!');           //鼠标移至滚动条上面时无动作
                        return;
                    }
                    var index_x=Math.floor(x/step);
                    var index_y=Math.floor(y/step);
                    var target_x=16*parseInt(x/16)+7;
                    var target_y=16*parseInt(y/16)+7;
                    clearStyle();
                    $j(_this).append('<div id="line_x" class="target_line"></div>');
                    $j(_this).append('<div id="line_y" class="target_line"></div>');
                    $j('#line_x').width($j('#img img').width());
                    $j('#line_x').css('top',target_y);
                    $j('#line_x').css('left',0);
                    //$j('#line_x').css('left',this.scrollLeft);
                    $j('#line_y').height($j('#img img').height());
                    $j('#line_y').css('left',target_x);
                    $j('#line_y').css('top',0);
                    //$j('#line_y').css('top',this.scrollTop);                        
                    if(!+[1,]){//IE browser
                        $j("#t .col:eq("+index_x+")").css({
                            "font-weight":"bold",
                            color:"#6495ED"      
                        }); 
                    }else{   //Other browsers
                        $j("#t .col:eq("+index_x+")").find("text").css({
                            "font-weight":"bold",
                            fill:"#6495ED"
                        });                           
                    }
                    $j("#r p:eq("+index_y+")").css({
                        "font-weight":"bold",
                        color: "#6495ED"
                    });
                    instance_2=setTimeout(function(){  //两次触发时间间隔超过一定值才执行  
                        if(!+[1,]){//IE browser
                            var array=$j("#t .col:eq("+index_x+")").text(); 
                        }else{
                            var array=$j("#t .col:eq("+index_x+")").find("text").text();
                        }
                        var query=$j("#r p:eq("+index_y+")").text();
                        //alert(query+'_'+array);
                        $j('#s p').remove();
                        $j('.s_loading').show();
                        var htm='<p><b>query : </b>'+query.split('_')[0]+'<br/><b>array : </b>'+array.split('_')[0]+'<br/><b>score : </b>'+getScore(query,array)+'</p>';
                        $j('#s').append(htm); 
                        $j('.s_loading').hide();
                    },1000);//延迟1000ms执行
                });
                
                 $j('.selected_area').live('mouseover',function(){
                       $j('.selected_area').hide();
                 });
                 
                 $j('.selected_area').live('mouseout',function(){
                     setTimeout(function(){ $j('.selected_area').show();},1500);
                 });
                 
                $j('#r p').live('click',function(){//  点击右边的条目，变色，加线
                    var i=$j(this).index();
                    var top=$j('#img').get(0).scrollTop;
                    //var left=$j('#img').get(0).scrollLeft;
                    var index=i-parseInt(top/step);
                    clearStyle();
                    $j("#r p:eq("+i+")").css({
                        "font-weight":"bold",
                        color: "#6495ED"
                    }); 
                    $j('#img').append('<div id="line_x" class="target_line"></div>');
                    $j('#line_x').css('top',top+16*index+7);
                    $j('#line_x').css('left',0);
                    $j('#line_x').width($j('#img img').width());
                });
                
                $j('#t .col').live('click',function(){                 
                    var i=$j(this).index();
                    //var top=$j('#img').get(0).scrollTop;
                    var left=$j('#img').get(0).scrollLeft;                     
                    var index=i-parseInt(left/step);
                    clearStyle();
                    if(!+[1,]){//IE browser
                        $j("#t .col:eq("+i+")").css({
                            "font-weight":"bold",
                            color:"#6495ED"      
                        });                     
                    }else{  //Other browsers
                        $j("#t .col:eq("+i+")").find("text").css({
                            "font-weight":"bold",
                            fill:"#6495ED"
                        });       
                    }
           
                    $j('#img').append('<div id="line_y" class="target_line"></div>');
                    $j('#line_y').height($j('#img img').height());
                    $j('#line_y').css('top',0);
                    $j('#line_y').css('left',left+16*index+7);                            
                });
                
                //$j('#back_home').click(function(){
                window.onclose=function(){
                    //$j('.target_line').remove();
                    //$j('#imagick').hide();
                    //$j('#view_container,#getImagick').show();                  
                    var src=$j('#img img').attr('src');
                    $j.ajax({           //clear the temp image
                        type: 'POST',
                        url:'services/imageClear.php',
                        async: false, 
                        data:{src:src},
                        success:function(d){
                            //alert(d);     
                        }
                    });                  
                }
                
                $j('#imagick').live('click',function(){
                    //clearStyle();
                    //alert('here!');
                });
                
                $j('#cancelBtn').live('click',function(){
                    //IAS.cancelSelection();
                    //$j.imgAreaSelect.cancelSelection();
                });

                $j('#r').mouseup(function(){
                    var text=$j.trim(getSelectedText());
                    if(text!=''&&text){
                        //alert(text);
                        var genes=[];
                        var lists=text.split('\n');
                        $j.each(lists,function(i,e){
                            var term=$j.trim(e);
                            if(term&&term!=''){
                                var item=$j.trim(e.split('_')[0]);
                                genes.push(item);
                                //alert(e);
                            }
                        });
                        
                        var pos=[];
                        $j.each(genes,function(i,e){
                            var tp=getPos(e,_rows);
                            if(tp!=-1){
                                pos.push(tp);  
                            }
                        });
                        
                        //alert('start : '+pos[0]+' --  end : '+pos[pos.length-1]);
                        //alert(genes.length);
                    }    
                });       
            });
            //获取选中的文本
            function getSelectedText() {
                if (window.getSelection) {
                    return window.getSelection().toString();
                }else if (document.selection) {
                    return document.selection.createRange().text;
                }
                return '';
            }
            //清除横列的样式
            function clearStyle(){
                $j('.target_line').remove();
                $j("#t .col").find("text").css({
                    "font-weight":"normal",
                    fill:"#333"
                }); 
                $j("#t .col").css({
                    "font-weight":"normal",
                    color:"#333"
                });
                $j("#r p").css({
                    "font-weight":"normal",
                    color: "#333"
                });     
            }

            function getCoordinate(genes){ //获取输入基因的对应坐标信息，返回值
                var posArr=[];
                _rows=$j.trim(getText('data/rows.txt')).split('\n');
                _cols=$j.trim(getText('data/cols.txt')).split('\n');
                var geneArr=$j.trim(genes).split(' ');
                geneArr=_.uniq(geneArr);    // 获取不相同的输入基因
                //alert(geneArr);
                var geneArrLength=geneArr.length;
                if(geneArrLength==1){       //只输入了一个基因的情况
                    var gene=geneArr[0];
                    var y=getPos(gene,_rows);
                    var x=getPos(gene,_cols);
                    var pos={query:gene,x:x,y:y};
                    _posArray=pos;
                    return pos;
                }
                for(var i=0;i<geneArrLength;i++){
                    var  array=geneArr[i];  //横列的为array
                    var  x=getPos(array,_cols);
                    var arrayName=_cols[x];
                    var  candidate=_.without(geneArr,array);
                    for(var j=0;j<candidate.length;j++){
                        var y=getPos(candidate[j],_rows);//竖列的为query
                        var queryName=_rows[y];
                        var pos={query:queryName,array:arrayName,x:x,y:y};
                        posArr.push(pos);
                    }
                }
                _.each(posArr,function(e){                    
                    //alert(" x: "+ e.x+ ' y: '+e.y);
                    if(e.x==-1||e.y==-1){
                        posArr=_.without(posArr,e);
                    }
                });
                //              _.each(posArr,function(e){                    
                //                    alert(" x: "+ e.x+ ' y: '+e.y+'--'+e.query+'--'+e.array);
                //              });
                _posArray=posArr;//alert(posArr);
                return posArr;
            }
              
            function getPos(gene,arrs){//获取某个基因在横列中的位置
                var name=getStandardName(gene);
                for(var i=0;i<arrs.length;i++){
                    var arr=$j.trim(arrs[i]).split('_')[0];
                    if(name==arr){
                        return i;
                        break;
                    }      
                }
                return -1;
            }
            
            function getGeneItems(x,y,w,h){//获取截取区域对应的横纵列的基因并生成HTML
                $j('#t').empty();
                $j('#r').empty();
                //var initialWidth=7817;
                var initialHeight=7425;     //原图片的大小
                var rows_num=3652;
                //var cols_num=3841;        //基因的横列数目
                //var _rows=$j.trim(getText('data/rows.txt')).split('\n');
                //var _cols=$j.trim(getText('data/cols.txt')).split('\n');
                var colStartIndex=Math.round(x/2)-2;
                var colLength=Math.round(w/2);
                var rowStartIndex=Math.round((y-(initialHeight-2*rows_num))/2)-2;
                var rowLength=Math.round(h/2);
                $j('#r_1').val(rowStartIndex+1);
                $j('#r_2').val(rowStartIndex+rowLength);
                $j('#c_1').val(colStartIndex+1);
                $j('#c_2').val(colStartIndex+colLength);		
                var rowHtml='';
                for(var i=colStartIndex;i<colStartIndex+colLength;i++){
                    var d=$j.trim(_cols[i]);
                    var colHtml;
                    if(!+[1,]){//IE浏览器
                        colHtml='<div class="col" id="text_'+d+'"  style="overflow:visible;">'+d+'</div>';
                        $j('#t').append(colHtml);
                    }else{    //非IE浏览器
                        colHtml='<div class="col" id="text_'+d+'"  style="overflow:visible;"></div>';  
                        $j('#t').append(colHtml);
                        Raphael("text_"+d).text(0,50,d).transform("r-60");
                    }                   
                }
                for(var j=rowStartIndex;j<rowStartIndex+rowLength;j++){
                    rowHtml+='<p>'+$j.trim(_rows[j])+'</p>';
                }                       
                $j('#r').append(rowHtml);
            }
            
            function makeSelectedTags(){  //place selected tags to new map
                $j('.stag').remove();
                //var num=0;
                $j.each(_selectedPosArray,function(i,e){
                    var selectedTag='<div id="stag_'+parseInt(i+1)+'" class="stag"></div>';                  
                    $j('#img').append(selectedTag);
                    var left=Math.round(8*e.x/16)+2;
                    left=left*16;
                    var top=Math.round(8*e.y/16)+2;
                    top=top*16;
                    $j('#stag_'+parseInt(i+1)).css('top',top);
                    $j('#stag_'+parseInt(i+1)).css('left',left); 
                    //num=i+1;
                });    
                //alert('Selected '+ num +' tags!');
            }
            
            function getText(url){      //获取txt文本      
                var txt;
                $j.ajax({
                    type: 'POST',
                    url:url,
                    dataType: "text",
                    async: false,   
                    success:function(d){
                        txt=d;
                    }
                });
                return txt;
            }
            
            function getStandardName(id){
                var name='';
                $j.ajax({
                    type: 'POST',
                    url:'ajax_get_standardName.php',
                    dataType:"JSON",
                    data:{
                        id:id
                    },
                    async: false,
                    success: function(data){
                        name+=data;
                    }
                });
                return name;   
            }
            
            function getScore(query,array){
                
                var score; 
                $j.ajax({
                    type: 'POST',
                    url:'ajax_get_score.php',
                    dataType:"JSON",
                    data:{
                        query:query,
                        array:array
                    },
                    async: false,
                    success: function(data){
                        if(data!=null&&data!=''&&data!=-2){
                            score=data;
                        }else{
                            score='0'; 
                        }
                    }
                }); 
                if(score.length>10){
                    score=score.substring(0, 10);
                }
                return score;
            }
            function sizeImagick(){
                $j('#viewer').width($j(window).width()-$j('#imagick').width()-$j('#navbar').width()-35);
                
            }
            function reSizeImagick(){
                $j('#viewer').width($j(window).width()-$j('#navbar').width()-15);
            }
            function sizeImageArea(){
                if($j('#east-content').css('display')!='none'){
                    if($j('#img img').width()<=$j('#l').width()-15){
                        return;
                    }
                    var currentWidth=Layout.state.east.size-50-$j('#sr').width();
                    currentWidth=(currentWidth/16)*16;
                    $j('#l').width(currentWidth);
                    $j('#east-content').width(Layout.state.east.size-30);
                    $j('#img').trigger('scroll');   
                }else if($j('#east-content-2').css('display')!='none'){
                    if($j('#heatmap img').width()<=$j('#l_').width()-15){
                        return;
                    }
                    var currentWidth=Layout.state.east.size-50-$j('#left').width()-$j('#sr_').width();
                    currentWidth=(currentWidth/16)*16;
                    $j('#l_').width(currentWidth);
                    $j('#east-content-2').width(Layout.state.east.size-30);
                    $j('#main_container').width(Layout.state.east.size-30);
                    $j('#heatmap').trigger('scroll');
                }
            }
            function resizeViewer(){
                if($('#viewer').width()<$('#view').width()-25){
                    $('#viewer').width($('#view').width());
                }  
            }
        </script>
    </body>
</html>
