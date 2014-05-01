<?php
//require_once './common.php';
//$query_names = array_key_exists('type', $_REQUEST) ? $_REQUEST['type'] : NULL;
$type=$_GET["type"];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Cluster interaction profiles of genes</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" type="text/css" media="all" href="./iipmooviewer2/css/iip.css" />
        <link rel="stylesheet" type="text/css" href="./iipmooviewer2/css/imgareaselect-default.css" />
        <!--[if lt IE 9]>
            <link rel="stylesheet" type="text/css" media="all" href="iipmooviewer2/css/ie.css" />
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="./inc/css/layout-default-latest.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.all.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.tabs.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/heatmap_iipmooviewer.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/heatmap.css" />
    </head>
    <body>
        <div id="view_container">
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
            var datatype='<?php echo $type;?>';
            /*
            var ds={
                 s:{w:7940,h:4813,row_num:2346,col_num:3906}
                ,t:{w:2128,h:4985,row_num:2432,col_num:972}
            };
            */
            /* var ds = {
                 s:{w:7812,h:4692,row_num:2346,col_num:3906}
                ,t:{w:1944,h:4864,row_num:2432,col_num:972}
            };*/
            var ds = {
                 s:{w:7812,h:4716,row_num:2358,col_num:3906}
                ,t:{w:1944,h:4894,row_num:2447,col_num:972}
            };
            
        </script>
        <script type="text/javascript" src="./inc/js/lib/jquery-ui-1.8.17.custom.min.js"></script>
        <script type="text/javascript" src="./inc/js/lib/jquery.layout-latest.js"></script>
        <script type="text/javascript" src="./iipmooviewer2/javascript/jquery.imgareaselect.js"></script>
        <script type="text/javascript" src="./iipmooviewer2/javascript/mootools-core-1.3.2-full-nocompat.js"></script>
        <script type="text/javascript" src="./iipmooviewer2/javascript/mootools-more-1.3.2.1.js"></script>
        <script type="text/javascript" src="./iipmooviewer2/javascript/protocols.js"></script>
        <script type="text/javascript" src="./iipmooviewer2/javascript/raphael.js"></script>
        <script type="text/javascript" src="./inc/js/lib/lhgdialog.js"></script>
        <script type="text/javascript" src="./iipmooviewer2/javascript/heatmap_iipmooviewer.js"></script>
        <!--<script type="text/javascript" src="./inc/js/heatmap.js"></script>-->

        <!--[if lt IE 7]>
          <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE7.js">IE7_PNG_SUFFIX = ".png";</script>
        <![endif]-->

        <script type="text/javascript">
            var _posArray;  //记录位置的数组
            var _selectedPosArray;//包含在截取区域内部的位置数组
            var isMouseDown;//标记鼠标是否按下
            var step=16;    //每个单元的size
            //存放两个横列的基因
            var _rows=$j.trim(getText('data/'+datatype+'_rows.txt')).split('\n');
            var _cols=$j.trim(getText('data/'+datatype+'_cols.txt')).split('\n');
            //var interval=0;
            //var excutime=0;
            var _top = 0,_left = 0;
            var instance_1,instance_2;
            var Layout;
            $j(function(){
                $j('#view_container').height($j(window).height()-12);
                Layout=$j('#view_container').layout({
                     east__size:      675
                    ,east__minSize :  400
                    ,east__resizable:  true
                    ,east__initClosed: true
                    ,east__onresize:function(){sizeImagick();sizeImageArea();}
                    ,east__onclose: function(){reSizeImagick();}
                    ,east__onopen:function(){sizeImagick();}
                });
                
                // The iipsrv server path (/fcgi-bin/iipsrv.fcgi by default)

                var server = '/fcgi-bin/IIPImageServer.fcgi';
                //var server = 'http://115.156.216.80/fcgi-bin/iipsrv.fcgi';

                // The *full* image path on the server. This path does *not* need to be in the web
                // server root directory. On Windows, use Unix style forward slash paths without
                // the "c:" prefix
                if(datatype == 's'){
                    //var imagename='science';
                    //var imagename = 'sc_';
                      var imagename = 'sc';
                }else{
                    //var imagename='ts_merge';
                    //var imagename = 'ts_';
                      var imagename = 'ts';
                }
                var images = 'D:/WebRoot/SGA_22/iipmooviewer2/'+imagename+'.tif';
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
                    viewport:{resolution:4, x:0.15, y:0.15, rotation:0},
                    showNavWindow: true,
                    showNavButtons: true,
                    winResize: false,
                    protocol: 'iip'
                });
                
                var d=parent.document.getElementById('hm_view').getAttribute('d'); //get the input genes
                var query_names=$j.trim(d).split(' ');
                //var t_rows=getText('./data/'+datatype+'_rows.txt');
                //var t_cols=getText('./data/'+datatype+'_cols.txt');
                //var rows=t_rows.split('\n');
                //var cols=t_cols.split('\n');
                $j.each(query_names,function(i,e){
                    var geneName=getStandardName(e);
                    var x=getPos(geneName,_cols);  
                    if(x>=0){ 
                        var lineItemX='<div id="x_line_'+x+'" class="single_line x_line" style="width:1px;top:0px;"></div>';
                        $j('#viewer').append(lineItemX);//add vertical line 
                    }
                    var y=getPos(geneName,_rows);
                    if(y>=0){
                        var lineItemY='<div id="y_line_'+y+'" class="single_line y_line" style="height:1px;left:0px;"></div>';
                        $j('#viewer').append(lineItemY); //add horizonal line
                    }
                });
                
                $j('.tag').live('click',function(){
                    //alert($j(this).attr('pos'));
                    var x=$j(this).attr('pos').split('_')[0];
                    var y=$j(this).attr('pos').split('_')[1];
                    //var query=cols[x].split('_')[0];
                    //var array=rows[y].split('_')[0];
                    var array=_cols[x];
                    var query=_rows[y];
                    //alert(query+'_'+array);
                    var top=$j(this).offset().top;
                    var left=$j(this).offset().left;
                    //var score=getScore(query,array);
                    $j('.tag_dialog').remove();
                    $j('#viewer').append('<div class="tag_dialog"></div>');
                    //var infor='<p><b>query :</b> '+query.split('_')[0]+'<br/> <b>array :</b> '+array.split('_')[0]+'<br /> <b>score :</b> '+score+'</p>';
                    var infor='<p><b>query :</b> '+query.split('_')[0]+'<br/> <b>array :</b> '+array.split('_')[0]+'<br /></p>';
                    $j('.tag_dialog').dialog({
                        id: 'id',
                        title: 'Interaction Point',
                        width: 150,
                        height: 100,
                        left: left+240,
                        top:top+50,
                        cancelBtn: false,
                        iconTitle:false,
                        rang: true,
                        content:infor
                    });
                    $j('.tag_dialog').trigger("click");
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
                    $j('#viewer').width($j(window).width()-$j('#imagick').width());          
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
                    var left_gap=parseInt($j('.imgareaselect-selection').parent().css('left')) - parseInt($j('.canvas').css('left'));
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
                            url:'imagick.ajax.php',
                            dataType: 'json',
                            async: false,
                            data:{ x:x,y:y,w:w,h:h,image:imagename},
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
                                if(8*w < $j('#l').width()){
                                    $j('#l').width(8*w+25);
                                }
                                if(8*h < $j('#img').height()){
                                    $j('#img,#r').height(8*h+25);
                                }
                                if($j(window).height()-$j('#t').height() < 8*h){
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
                                //makeSelectedTags();
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
                    var left_gap=parseInt($j('.imgareaselect-selection').parent().css('left')) - parseInt($j('.canvas').css('left'));
                    var top_gap=parseInt($j('.imgareaselect-selection').parent().css('top')) - parseInt($j('.canvas').css('top'));//获取初始相对位移
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
                                c2: col2,
                                name:datatype
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
                    
                    // remove score
                    /*
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
                    */
                    
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

            function getPos(gene,arrs){//获取某个基因在横列中的位置
                for(var i=0;i<arrs.length;i++){
                    var arr=$j.trim(arrs[i]).split('_')[0];
                    if(gene==arr){
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
                //var initialHeight=ds[datatype].h;     //原图片的大小
                //var rows_num=ds[datatype].row_num;
                //var cols_num=3841;        //基因的横列数目
                //var _rows=$j.trim(getText('data/rows.txt')).split('\n');
                //var _cols=$j.trim(getText('data/cols.txt')).split('\n');
                var colStartIndex =  x/2 ;
                var colLength =  w/2;
                var rowStartIndex = y/2;
                var rowLength = h/2 ;
               // $j('#r_1').val(rowStartIndex+1);
               // $j('#r_2').val(rowStartIndex+rowLength);
               // $j('#c_1').val(colStartIndex+1);
               // $j('#c_2').val(colStartIndex+colLength);		
                var rowHtml = '';
                for(var i = colStartIndex;i < colStartIndex + colLength;i++){
                    var d=$j.trim(_cols[i]);
                    var colHtml;
                    if(!+[1,]){//IE浏览器
                        colHtml='<div class="col" id="text_' + d + '"  style="overflow:visible;">' + d + '</div>';
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
             
            function getFearureName(id){
                var name='';
                $j.ajax({
                    type: 'POST',
                    url:'ajax_get_featureName.php',
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
           
           function getText(url){      //  get txt use ajax
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
            
            
            function getPos(gene,arrs){		// get position of gene in txt files
                for(var i=0;i<arrs.length;i++){
                    var arr=$j.trim(arrs[i]).split('_')[0];
                    if(gene==arr){
                        return i;
                        break;
                    }      
                }
                return -1;
            }             
            
            function getScore(query,array){
                var score; 
                $j.ajax({
                    type: 'POST',
                    url:'./ajax_get_score.php',
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
