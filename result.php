<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xHTML11/DTD/xHTML11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php
    $query_names = $_REQUEST['geneNames'];
    $epsilon_neg = $_REQUEST['e_neg'];
    $pvalue_neg = $_REQUEST['p_neg'];
    $epsilon_pos = $_REQUEST['e_pos'];
    $pvalue_pos = $_REQUEST['p_pos'];
    $rvalue = $_REQUEST['rvalue'];
    $query_type = 'with';

    $tishi = array(
         'Search for interactions between given genes and their first neighbors'
        , 'Search for genetic interactions strictly within given genes'
        , 'Search genetic interactions in biclustering background'
        , 'Cluster genetic interaction profiles'
        , 'Enrichment test for genes'
    );

    if (in_array($query_names, $tishi)) {
        $query_names = '';
        header('Location:index.php');
        exit();
    } else {
        $query_names .= ' ';
        $query_names = str_replace(","," ",$query_names);
    }
    $query_names = trim($query_names) ;
    if(is_null($_FILES["file"]) || $_FILES["file"]["error"] == 4){
        if($query_names == ''){
            echo "<script>alert('You have not uploaded any file !');window.location.href = 'index.php';</script>";
        }
        $query_names .= ' ';
    }else if($_FILES["file"]["error"] > 0 ){
        //echo "Error: " . $_FILES["file"]["error"] . "<br />";
        echo "<script>alert('"."Error: " . $_FILES["file"]["error"]."! please try again.');window.location.href = 'index.php';</script>";
    }else if ($_FILES["file"]["type"] != 'text/plain') {
        echo "<script>alert('upload error ! file type must be txt .'); window.location.href = 'index.php';</script>";
    } else {
        if (!is_null($_FILES["file"])) {
            $file = fopen($_FILES['file']['tmp_name'], 'r');
            while (!feof($file)) {
                $tmp_str = trim(fgets($file));
                if (strlen($tmp_str) == 0 || is_null($tmp_str))
                    continue;
                $query_names .= ($tmp_str . ' ');
            }
        }
    }
    
    ?>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Search Result</title>
        <link rel="stylesheet" type="text/css" href="./inc/css/layout-default-latest.css" />
        <style type="text/css">
            /* neutralize pane formatting BEFORE loading UI Theme */
            .ui-layout-pane ,
            .ui-layout-content {
                background:	none;
                border:		0;
                padding:	0;
                overflow:       hidden;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.ui.all.css" />
        <link rel="stylesheet" type="text/css" href="./inc/js/myTheme/jquery.ui.all.css" />
        <link rel="stylesheet" type="text/css" href="./inc/js/myTheme/jquery.ui.core.css" />
        <link rel="stylesheet" type="text/css" href="./inc/js/myTheme/jquery.ui.tabs.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.autocomplete.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.mCustomScrollbar.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/docs.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.linkselect.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/jquery.linkselect.style.select.css" />
        <link rel="stylesheet" type="text/css" href="./inc/css/sgastyle.css" />
        <style type="text/css">
             p		        { margin:		1em 0; }
            /* use !important to override UI theme styles */
            .grey	        { background:	#999 !important; }
            .outline		{ /*border:		1px dashed #F00 !important;*/ }
            .add-padding	{ padding:		10px !important; }
            .no-padding       { padding:		0 !important; }
            .add-scrollbar    { overflow:		auto; }
            .no-scrollbar	{ overflow:		hidden; }
            .allow-overflow	{ overflow:		visible; }
            .full-height	{ height:		100%; }
             button            { cursor:		pointer; }
            /* color for autocomplete textbox */
            .ac_results li    { color: #000;}
        </style>
        <script type="text/javascript" src="./inc/js/lib/jquery-1.7.2.min.js"></script> 
        <script type="text/javascript" src="./inc/js/lib/jquery-ui-1.8.17.custom.min.js"></script> 
        <script type="text/javascript" src="./inc/js/lib/jquery.layout-latest.js"></script>
        <script type="text/javascript" src="./inc/js/lib/jquery.layout.callbacks.min-latest.js"></script>

        <script type="text/javascript" src="inc/js/lib/jquery.cookie.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.hotkeys.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.jstree.js"></script>

        <script type="text/javascript" src="inc/js/lib/jquery.autocomplete.js"></script>
        <script type="text/javascript" src="inc/js/localdata.js"></script>
        <script type="text/javascript" src="inc/js/inputvalue.js"></script>

        <script type="text/javascript" src="inc/js/lib/json2.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/AC_OETags.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/cytoscapeweb.min.js"></script>

        <script type="text/javascript" src="inc/js/lib/jquery.ui.core.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.ui.widget.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.ui.tabs.js"></script>      
        <script type="text/javascript" src="inc/js/lib/jquery.mousewheel.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.mCustomScrollbar.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.linkselect.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/lhgdialog.js"></script>
        <script type="text/javascript" src="inc/js/sga.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                //initialize layout
                $('#sidebar1').css({'overflow': 'auto'});  
                $('#result_layout').height($(window).height() - $('#head').height());
                if($('#result_layout').height() < 750){
                    $('#result_layout').height(750);
                }
                $('#result_container').height($('#result_layout').height() - $('#topmenu').height()-15);
                //$('#result_layout').hide();
                result_layout = $('#result_layout').layout({
                    west__size:                .18
                    //,east__size:		.22
                    ,east__size:		355
                    ,south__size:               8
                    ,east__maxSize:             550
                    ,east__minSize:             355
                    ,east__initClosed:          true
                    ,west__resizable:           false
                    ,south__resizable:          false
                    ,slidable:                  false
                    ,resizerDblClickToggle:     false
                    ,togglerLength_open:        0
                    ,togglerLength_closed:      0
                    ,spacing_open:              12  //width of the resizer bar
                    ,spacing_closed:            12
                    ,east__onresize:          function(){reSetQuick_launch();}
                    ,east__onclose:           function(){reSetQuick_launch();}
                    ,east__onopen:            function(){reSetQuick_launch();}
                    ,west__onresize:          function(){reSetQuick_launch();}
                    ,west__onclose:           function(){reSetQuick_launch();}
                    ,west__onopen:            function(){reSetQuick_launch();}
                });
                
                $('#sidebar1').height($('#result_layout').height() - $('#header_west').height()-45);
                $('#feedback').height($('#inner-tabs-center').height() - $('#topmenu').height()-15);
                $('#content_right').height($('#inner-tabs-east').height() - $('#east-tab-selecter').height()-15);
                
                $('.search_area').css('margin-right', result_layout.state.east.size-$('.search_btn').width()-12);
                $('#it').width($('#head').width() - $('#head h1').width() - $('.search_btn').width() - parseInt($('.search_area').css('margin-right'))-125);
                if($('#it').width()>700){$('#it').width(700);}
               
                //var _inner_labels='YMD8 YDJ1 YET1 SVL3';
                //$("#it").inputLabel(_inner_labels, {color:'0x333'});
                
                $('#it').autocomplete(names, {
                    matchContains: true,
                    multiple: true,
                    multipleSeparator: ",",
                    max: 100,
                    formatItem: function(row, i, max) {
                        return row.k;
                    },
                    formatMatch: function(row, i, max) {
                        return row.k + " " + row.v;
                    },
                    formatResult: function(row) {
                        return row.k ;
                    }
                });
                
                var query_names = "<?php echo $query_names ?>";
                var epsilon_neg = '<?php echo $epsilon_neg ?>';
                var pvalue_neg = parseFloat('<?php echo $pvalue_neg ?>');
                var epsilon_pos = '<?php echo $epsilon_pos ?>';
                var pvalue_pos = '<?php echo $pvalue_pos ?>';
                var rvalue = '<?php echo $rvalue ?>';
                var query_type = '<?php echo $query_type ?>';
                var init_view = 'tv';
                var init_type = 'n'; 
                var vs = '';
                var key = [],num = [];
                $('ul#topnav li#hm_view').attr('d',makeGeneStandardNames(query_names));
                
                //Default to show init_view and init_type
                /*
                $('ul#topnav li#table_view').addClass('nav-state-active');
                showResultView(query_names, epsilon_neg, pvalue_neg, epsilon_pos, pvalue_pos, rvalue, init_view, init_type, query_type);
                 */
               
                $('ul#topnav li#fm_view').addClass('nav-state-active');
                setTimeout(function(){  
                    $('#fm_view').trigger('click');   
                },50);
               
                
                //navigation to feedback
                $('ul#topnav li#tab_feedback').live('click', function(e){
                    closeAllDialog();
                    $('#topmenu .nav-state-active').removeClass('nav-state-active');
                    $('ul#topnav li#tab_feedback').addClass('nav-state-active');
                    result_layout.close('east');
                    $('#topnav').find('li').eq(0).css('margin-right',result_layout.state.east.size);
                    $("#tab-annotations-selector a").trigger("click");
                    $('#result_container').hide();
                    $('#feedback').show();
                    showFeedback(); 
                });  
                
                //navigation events
                $('ul#topnav li a.view').live('click', function(e){
                    closeAllDialog();
                    var target = $(e.target).closest('a.view');
                    var view = target.attr('id').split("_")[0];    // tv(Table View) or nv(Network View) or heatmap_view;
                    var type = target.attr('id').split("_")[1];    // Type of result.
                    $('#topmenu .nav-state-active').removeClass('nav-state-active');
                    if (view == 'tv'){
                        $('ul#topnav li#table_view').addClass('nav-state-active');
                    } else if (view == 'nv'){
                        $('ul#topnav li#nw_view').addClass('nav-state-active');
                    }else if (view == 'hm' || view == 'fm'){
                        result_layout.close('east');
                        return;
                    }
                    result_layout.open('east');
                    setTimeout(function(){
                        showResultView(query_names, epsilon_neg, pvalue_neg, epsilon_pos, pvalue_pos, rvalue, view, type, query_type);
                    },500);
                    
                });
                
                //event to reset network view node size
                $('.reSetNum').live('click',function(){
                    var view = 'nv';
                    var type = $(this).attr('id').split('_')[1];
                    var Num = parseInt($('#num_' + type).text());
                    reLoadNetworkView(query_names, epsilon_neg, pvalue_neg, epsilon_pos, pvalue_pos, rvalue, view, type, query_type,Num);
                });
                
                //change type event in MolecularInteraction
                $('#result_tv_m select').live('change',function(){
                    //var Index=$("#result_tv_m select").get(0).selectedIndex;                  
                    var interactionType=$('#result_tv_m select').find('option:selected').text();
                    $('#table_box .table_detail').hide();
                    if($('#table_detail__' + interactionType).length>0){
                        $('#table_detail__' + interactionType).show();
                        if($('#table_detail__' + interactionType).attr('last')==1){
                            $('#lastpage').show(); 
                        }else{
                            $('#lastpage').hide();  
                        }
                        if($('#table_detail__' + interactionType).attr('next')==1){
                            $('#nextpage').show(); 
                        }else{
                            $('#nextpage').hide();  
                        }
                        $('#pageIndex').text($('#table_detail__' + interactionType).attr('index'));
                        return;
                    }
                    //console.log(interactionType);
                    getMolecularInteractionResultData(query_names, rvalue , interactionType, query_type, 0 , 0);
                });
                
                //show heatmap
                $('#hm_view .view').live('click',function(){
                    closeAllDialog();
                    $('#topmenu .nav-state-active').removeClass('nav-state-active');
                    $('ul#topnav li#hm_view').addClass('nav-state-active'); 
                    
                    result_layout.close('east');
                    
                    var viewtype = $(this).attr('id').split('_')[1];
                    
                    //$('#tab_feedback').css('margin-right',result_layout.state.east.size); 
                    $('#topnav').find('li').eq(0).css('margin-right',result_layout.state.east.size);
                  
                    $('#result_container').find('.active').hide();
                    $('#result_container').show();
                    $('#result_container .loading').show();
                    $('#result_container').find('.active').removeClass('active');
                    $('#tab-network-selector').hide();
                    $('#tab-annotation-selector').trigger('click');
                    //$('#result_heatmap').remove();
                    if ($('#result_heatmap_' + viewtype).length != 0){
                        $('#result_container .loading').hide();
                        $('#result_heatmap_' + viewtype).addClass('active').show();
                    }else{
                        $('#result_heatmap_' + viewtype).remove();
                        $('#result_container .loading').hide();
                        setTimeout(function(){
                            var resultView = '<div class="result_view result_heatmap active" id="result_heatmap_'+viewtype+'" style="height:100%;width:100%;background-color: #3C735E;">'+ 
                                '<iframe id="hm_iframe_' + viewtype + '" class="hm_iframe" name="hm_iframe_'+viewtype+'" src="heatmap_iipmooviewer.php?type='+viewtype+'" frameborder="0" height=100% width=100%></iframe>'+
                                '</div>';
                            $('#result_container').append(resultView);
                            //$('#result_heatmap_'+viewtype).attr('type',viewtype);
                            $('#result_heatmap_'+viewtype).height($('#inner-tabs-center').height()-$('topmenu').height()-25);
                            _rows = getText('data/'+viewtype + '_rows.txt');
                            _cols = getText('data/'+viewtype + '_cols.txt');   
                        },500);   
                    }         
                    if($('#tree_tv_n').length>0&&$('#tree_tv_p').length>0){
                        $('#sidebar1').find('.active').removeClass('active');  
                        $('#tree_tv_n,#tree_tv_p').addClass('active');
                        return
                    }
                    setTimeout(function(){
                        showTreeView(query_names, epsilon_neg, pvalue_neg, epsilon_pos, pvalue_pos, rvalue, 'all');       
                    },120); 
                });
                
                //show functional map
                $('#fm_view').live('click',function(){
//                    if($('.ui_border').length > 0 ){
//                        $('.ui_border').parent('div').hide();
//                    }
                    closeAllDialog();
                    result_layout.close('east');
                    //$('#tab_feedback').css('margin-right',result_layout.state.east.size);
                    $('#topnav').find('li').eq(0).css('margin-right',result_layout.state.east.size);
                    $('#topmenu .nav-state-active').removeClass('nav-state-active');
                    $('ul#topnav li#fm_view').addClass('nav-state-active'); 
                    $('#result_container').find('.active').hide();
                    $('#result_container').show();
                    
                    $('#feedback').hide();
                    $('#result_container').find('.active').removeClass('active');
                   
                    if ($('#result_cytoscape').length != 0){
                        $('#result_cytoscape').addClass('active').show();
                    }else{
                        $('#result_container #loading').show();
                        $('#result_cytoscape').remove();  
                        var resultView = '<div class="result_view active" id="result_cytoscape" style="height:100%;width:100%;background-color: #3C735E;">'+
                            '<iframe id="fm_iframe" name="fm_iframe" src="booneGraph/demo.html" frameborder="0" height=100% width=100%></iframe>'+
                            '</div>';
                        $('#result_container').append(resultView);
                        $('#result_container #loading').hide();
                        $('#result_cytoscape').height($('#inner-tabs-center').height() - $('topmenu').height() - 25);   
                    }    
                    setTimeout(function(){
                        showTreeView(query_names, epsilon_neg, pvalue_neg, epsilon_pos, pvalue_pos, rvalue ,'all');
                    },300);
                });
                
                //show block view
                $('#block_view').live('click',function(){
                    closeAllDialog();  
                    result_layout.close('east');
                    //$('#tab_feedback').css('margin-right',result_layout.state.east.size);
                    $('#topnav').find('li').eq(0).css('margin-right',result_layout.state.east.size);
                    $('#topmenu .nav-state-active').removeClass('nav-state-active');
                    $('ul#topnav li#block_view').addClass('nav-state-active'); 
                    $('#result_container').find('.active').hide();
                    $('#result_container').show();
                    $('#result_container').find('.active').removeClass('active');
                    
                    if ($('#result_block').length != 0){
                        $('#result_block').addClass('active').show();
                    }else{
                        $('#result_block').remove();      
                        var resultView = '<div class="result_view active" id="result_block" style="height:100%;width:100%;background-color: #3C735E;">'+
                            '<iframe id="block_iframe" name="block_iframe" src="block_view.php?geneNames='+query_names+'" frameborder="0" height=100% width=100%></iframe>'+
                            '</div>';
                        setTimeout(function(){
                            $('#result_container').append(resultView);
                            $('#result_block').height($('#inner-tabs-center').height()-$('topmenu').height()-25);
                        },500);
                    }   
                    $('#sidebar1').find('.result_tree_view').removeClass('active');
                    if($('#tree_block').length>0){
                        $('#tree_block').addClass('active');
                    }else{
                        setTimeout(function(){
                            $('#sidebar1 #loading').show();
                            $.ajax({
                                type: 'POST',
                                url:'ajax_get_blocktree.php',
                                dataType:"JSON",
                                data:{
                                    query_names: query_names
                                    ,rvalue: rvalue
                                },
                                async: false,
                                success: function(data){ 
                                    $('#sidebar1 #loading').hide();
                                    var treeId = 'tree_block';
                                    var queryresult = '<div class="treeview active result_tree_view" id="' + treeId +  '">';
                                    $('#sidebar1 .mCSB_container').append(queryresult);
                                    loadTreeView(treeId, eval('(' +data+ ')'));          
                                }
                            });
                        },150);
                    }
                });
                
                //download button click event
                $('.dwn').live('click',function(){
                    var export_name = $(this).parent().attr('name');
                    var type=export_name.split('_')[3];
                    $(this).parent().find('input[name="query_names"]').attr('value',query_names);                   
                    $(this).parent().find('input[name="type"]').attr('value',type);          
                    $(this).parent().find('input[name="query_type"]').attr('value',query_type);
                    $(this).parent().find('input[name="epsilon_neg"]').attr('value',epsilon_neg);
                    $(this).parent().find('input[name="epsilon_pos"]').attr('value',epsilon_pos);
                    $(this).parent().find('input[name="pvalue_neg"]').attr('value',pvalue_neg);
                    $(this).parent().find('input[name="pvalue_pos"]').attr('value',pvalue_pos);
                    $(this).parent().find('input[name="rvalue"]').attr('value',rvalue);
                    //$(this).parent().find('input[name="database"]').attr('value',database);
                    if(type=='m'){
                        var interactionType=$('#result_tv_m select').find('option:selected').text();
                        $(this).parent().find('input[name="interactionType"]').attr('value',interactionType);  
                    }                          
                    var download_form=document.forms[export_name];
                    download_form.submit();                    
                });  
                
                $('.annotabs .setp :checkbox').live('click',function(){
                    var i = $(this).attr('id').split('_')[1];
                    if($(this).attr("checked")=="checked"){
                        $('#alert').text('');
                        $('.annotabs #setp_'+i).slideDown('slow');
                        $('.annotabs #setp_'+i+' input').focus().select();   
                    }else{
                        Check_pvalue(parseInt(i));
                        $('.annotabs #setp_'+i).slideUp("slow");
                    }         
                });

                $('.setpremeter input').blur(function(){
                    var i=$(this).next('label').attr('id').split('_')[1];
                    Check_pvalue(parseInt(i));
                });
                $('.reset_p').live('click',function(){
                    $(this).parent().parent().find('.annotation').hide();
                    $(this).parent().parent().find('.annotation_premeters').slideDown();
                    $(this).parent().hide();
                });
                $('.toggle_premeters').live('click',function(){
                    $(this).next('.annotation_premeters').slideToggle();
                    if($(this).hasClass('open')){
                        $(this).removeClass('open').addClass('closed');
                        $(this).parent().find('.annotation_results').show();
                    }else{
                        $(this).removeClass('closed').addClass('open');
                        $(this).parent().find('.annotation_results').hide();
                    }               
                });  
                
                $('.an_submit').live('click',function(){
                    var type=$(this).attr('id').split('_')[2];
                    var id='#tabs-'+type;
                    var analysisType = $('.set_range input[name="interactionType_'+type+'"]:checked').val();
                    //alert(analysisType);
                    $(id).find('.toggle_premeters').trigger('click');
                    $(id).find('.annotation_premeters').hide();
                    $('#alert').text('');

                    //load the left tree result
                    $('#sidebar1 #loading').show();
                    $.ajax({
                        type: 'POST',
                        url:'ajax_get_tree.php',
                        dataType:"JSON",
                        data:{
                            query_names: query_names
                            ,epsilon_neg: epsilon_neg
                            ,pvalue_neg: pvalue_neg
                            ,epsilon_pos: epsilon_pos
                            ,pvalue_pos: pvalue_pos
                            ,rvalue: rvalue
                            ,analysis_type: analysisType
                        },
                        async: false,
                        success: function(data){
                            $('#sidebar1').find('.result_tree_view').removeClass('active');
                            if(analysisType=='all'){
                                //load the left tree
                                $('#sidebar1 #loading').hide();
                                if($('#tree_tv_p').length>0){
                                    $('#tree_tv_p').addClass('active');
                                }else{
                                    var treeId_p = 'tree_tv_p';
                                    var queryresult = '<div class="treeview active result_tree_view" id="' + treeId_p +  '">';
                                    $('#sidebar1 .mCSB_container').append(queryresult);
                                    loadTreeView(treeId_p, eval('(' + data.tree_result_data_p+ ')'));
                                }
                                if($('#tree_tv_n').length>0){
                                    $('#tree_tv_n').addClass('active');
                                }else{
                                    var treeId_n = 'tree_tv_n';
                                    var queryresult = '<div class="treeview active result_tree_view" id="' + treeId_n +  '">';
                                    $('#sidebar1 .mCSB_container').append(queryresult);
                                    loadTreeView(treeId_n, eval('(' + data.tree_result_data_n+ ')'));
                                }                                
                                // var treeId_n = 'tree_tv_n';
                                // var queryresult = '<div class="treeview active" id="' + treeId_p +  '"></div>'+'<div class="treeview active" id="' + treeId_n +  '"></div>';
                                // $('#sidebar1').append(queryresult);
                                // $('#sidebar1 #loading').hide();
                                // loadTreeView(treeId_p, eval('(' + data.tree_result_data_p+ ')'));
                                // loadTreeView(treeId_n, eval('(' + data.tree_result_data_n+ ')'));
                                //fetch the genes
                                var a_n=eval(data.tree_query_data_n);
                                var b_n=eval(a_n[0].children);
                                var c_n=eval(b_n[0].data);
                                var query_n=c_n.title; //get query gene_n
                                var genes=query_n;            
                                // var a_p=eval(data.tree_query_data_p);
                                // var b_p=eval(a_p[0].children);
                                // var c_p=eval(b_p[0].data);
                                // var query_p=c_p.title;//get query gene_p
                                // genes+=query_p;
                                var result_n=eval(data.tree_result_data_n);
                                var results_n=eval(result_n[0].children);
                                $.each(eval(results_n), function(i,e){
                                    var d=eval(e.data);
                                    //alert(d.title);
                                    genes+=','+d.title;      //get result genes_n     
                                });
                                //alert(genes);
                                var result_p=eval(data.tree_result_data_p);
                                var results_p=eval(result_p[0].children);
                                //alert(result_p);
                                $.each(eval(results_p), function(i,e){
                                    var d=eval(e.data);
                                    //alert(d.title);
                                    genes+=','+d.title;      //get result genes_p     
                                });
                                //alert(genes);
                            }else{
                                //load the tree 
                                $('#sidebar1 #loading').hide();
                                var treeId = 'tree_tv_'+analysisType;
                                if($('#'+treeId).length>0){
                                    $('#'+treeId).addClass('active');
                                }else{
                                    var queryresult = '<div class="treeview active result_tree_view" id="' + treeId + '"></div>';
                                    $('#sidebar1 .mCSB_container').append(queryresult);
                                    loadTreeView(treeId, eval('(' + data.tree_result_data+ ')'));   
                                }      
                                //fetch the genes
                                var a=eval(data.tree_query_data);
                                var b=eval(a[0].children);
                                var c=eval(b[0].data);
                                var query=c.title;
                                var genes=query;             //get query gene                        
                                var result=eval(data.tree_result_data);
                                var results=eval(result[0].children);
                                $.each(eval(results), function(i,e){
                                    var d=eval(e.data);
                                    //alert(d.title);
                                    genes+=','+d.title;      //get result genes     
                                });
                            }
                            $(id).find('.loading').show();
                            //alert(genes);
                            if(type=='g'){          //for gene ontology
                                $('#test_type').empty();
                                for(var i=1;i<=3;i++){
                                    //var t=$('#test_0'+i).val();  //test type BP,VV,MF
                                    if($('#test_0'+i).attr('checked')== 'checked'){
                                        switch (i){
                                            case 1 :{
                                                    var  v='BP';
                                                    var  options='<option value="BP">Biological Process</option>';  
                                                    break;
                                                }
                                            case 2 :{
                                                    var  v='CC';
                                                    var options='<option value="CC">Cellular Compont</option>';
                                                    break;
                                                }
                                            case 3 :{
                                                    var  v='MF';
                                                    var options='<option value="MF">Molecluar Function</option>';
                                                    break;
                                                }
                                        }
                                        $('#test_type').append(options);
                                        $('#testType').show();
                                        var pval=$('#setp_0'+i+' input').val(); //get p-value
                                        $.ajax({
                                            type: 'POST',
                                            url:'ajax_analysis.php',
                                            dataType:"JSON",
                                            data:{
                                                g:genes
                                                ,c:pval
                                                ,type:type
                                                ,t:v
                                            },
                                            async: false,
                                            success: function(data){
                                                if(!data){
                                                    data='';
                                                }
                                                //alert(data);
                                                key[v]=data;                                           
                                            },
                                            error: function(XMLHttpRequest, textStatus, errorThrown){
                                                alert(XMLHttpRequest + ' | '+ textStatus + ' | ' + errorThrown);  
                                            }          
                                        });  
                                    }
                                    vs+=v+'_';//store the option value
                                }
                                var initial_v=vs.split('_')[0];
                                //var initial_v="BP";
                                //alert(initial_v);
                                key[initial_v]='123456';
                                showTableResult('an_result_'+type,key[initial_v]);    
                            }else{
                                var pvalue = $('#setp_ input').val(); //p-value
                                //alert(pvalue);
                                $.ajax({
                                    type: 'POST',
                                    url:'ajax_analysis.php',
                                    dataType:"JSON",
                                    data:{
                                        g:genes
                                        //,t:t
                                        ,c:pvalue
                                        ,type:type
                                    },
                                    async: false,
                                    success: function(data){
                                        if(!data){
                                            data='';
                                        }
                                        //alert(data);
                                        num[type]=data;                                           
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown){
                                        alert(XMLHttpRequest + ' | '+ textStatus + ' | ' + errorThrown);  
                                    }          
                                });
                                num[type]='123456';
                                showTableResult('an_result_'+type,num[type]); 
                            } 
                        }
                    });                 
                    //showTableResult('result_an_'+type,key);
                });
                                       
                $('#test_type').live('change',function(){
                    var t=$('#test_type').val();
                    $('#an_result_g').find('.test_table').hide();
                    if($('#test_'+t).length>0){
                        $('#test_'+t).show();
                        return;
                    }
                    $('#result_an_g').find('.loading').hide(); 
                    key[t]='123456';
                    showTableResult('an_result_g',key[t]);
                }); 
                
                $('span.tr a').live('click',function(){
                    vis.exportNetwork('xgmml', 'export.php?type=xgmml');
                });
                
                $('#lastpage').live('click',function(){
                    var type=$('#result_tv_m select').find('option:selected').text();
                    var index=$('#table_detail__'+type).attr('index');
                    showNextPage(query_names,rvalue , type ,query_type, parseInt(index)-1 , 0 );
                });
     
                $('#nextpage').live('click',function(){
                    var type=$('#result_tv_m select').find('option:selected').text();
                    var index=$('#table_detail__'+type).attr('index');
                    showNextPage(query_names,rvalue ,type ,query_type, parseInt(index)+1 , 1);
                });
            });
            $('#hide_west').live('click',function(){
                result_layout.close('west');
                $('.left_btn').show();
                if($('#result_container div.active').find('#block_iframe').length != 0){
                    var block=window.frames['block_iframe'].document.getElementById('tabs-center');
                    $(block).find('.quick_launch').css({
                        'left': ($(window).width()-50-12-7-10) + 'px'
                    });                     
                }
            });
            $('#hide_east').live('click',function(){
                result_layout.close('east');
                //if(result_layout.state.east.size>450){
                //    $('#fm_view').css('margin-right',350);
                //}else{
                $('#topnav').find('li').eq(0).css('margin-right',result_layout.state.east.size-$('.right_btn').width());
                //}
                $('.right_btn').show();
            });
            $('.left_btn').live('click',function(){ 
                result_layout.open('west');
                $(this).hide();
                if($('#result_container div.active').find('#block_iframe').length != 0){
                    var block=window.frames['block_iframe'].document.getElementById('tabs-center');
                    $(block).find('.quick_launch').css({
                        'left': ($(window).width()-$('#inner-tabs-west').width()-50-12-7-10) + 'px'
                    });
                }
            });
            $('.right_btn').live('click',function(){
                result_layout.open('east');
                //$('#fm_view').css('margin-right',0);
                $('#topnav').find('li').eq(0).css('margin-right',0);
                $(this).hide();
            });
        </script> 

    </head> 
    <body> 
        <div class="content">
            <div id="head">
                <h1><a href="index.php">SGACellMap</a></h1>                  
                <div class="search_area">
                    <form id="re_search" method="POST" action="result.php">
                        <input class="search_text" id="it" type="text"  name="geneNames" value=""/>
                        <input type="hidden" name="e_neg" value="<?php echo $epsilon_neg ?>" />
                        <input type="hidden" name="e_pos" value="<?php echo $epsilon_pos ?>" />
                        <input type="hidden" name="p_neg" value="<?php echo $pvalue_neg ?>" />
                        <input type="hidden" name="p_pos" value="<?php echo $pvalue_pos ?>" />
                        <input type="hidden" name="rvalue" value="<?php echo $rvalue ?>"  />
                        <input class="search_btn" type="submit" value="GO" />
                    </form>
                </div>
            </div>
            <div id="result_layout">
                <div id="inner-tabs-west" class="ui-layout-west no-padding no-scrollbar">
                    <div class="ui-tabs-panel outline" style="height:auto;padding:0px 25px 25px 25px;overflow: auto;">
                        <div id="header_west"><p id="title_west"><b>Genes</b>&nbsp;&nbsp;&nbsp;&nbsp;<a id="hide_west"><img width ="12px" height="12px" src="./inc/images/left.png"/></a></p></div>
                        <div class="sidebar1" id="sidebar1"><div class="loading" id="loading" style="display:none;"></div></div>
                    </div>
                </div>
                <div id="inner-tabs-center" class="ui-layout-center no-padding no-scrollbar">
                    <div class="topmenu" id="topmenu">
                        <ul id="topnav">
                            <a class="right_btn" ><img style="width:12px;height:12px;margin:9px;" src="./inc/images/left.png"/></a>
                            <li id="tab_feedback"  onmousemove="change_style(this.id)" style="margin-right:0px;"><a href="javascript:void(0)">Feedback</a></li>
                            <li id="block_view"  onmousemove="change_style(this.id)" ><a href="javascript:void(0)">In Block</a></li>
                            <li id="hm_view"  onmousemove="change_style(this.id)" >
                                <a href="javascript:void(0)">In Heatmap</a>
                                <span>
                                    <a class="view" id="hm_s" href="javascript:void(0)">Science+</a>|<a class="view" id="hm_t" href="javascript:void(0)">Ts_merge</a>
                                </span>
                            </li>
                            <li id="nw_view" onmousemove="change_style(this.id)">
                                <a href="javascript:void(0)">In Network</a>
                                <span>
                                    <a class="view" id="nv_i" href="javascript:void(0)">Interaction</a>|<a class="view" id="nv_c" href="javascript:void(0)">Correlation</a>
                                </span>
                            </li>                            
                            <li id="table_view" onmousemove="change_style(this.id)">
                                <a href="javascript:void(0)" >In Table</a>
                                <span>
                                    <a class="view" id="tv_n" href="javascript:void(0)">Negative interaction</a>|<a class="view" id="tv_p" href="javascript:void(0)">Positive interaction</a>|<a class="view" id="tv_c" href="javascript:void(0)">Correlation</a>|<a class="view" id="tv_m" href="javascript:void(0)">Molecular interaction</a>
                                </span>
                            </li>
                            <li id="fm_view" style="width: 165px;"  onmousemove="change_style(this.id)"><a href="javascript:void(0)">In Functional Map</a></li>
                            <a class="left_btn"><img style="width:12px;height:12px;margin:9px;" src="./inc/images/right.png"/></a>
                        </ul>
                    </div>
                    <div class="result_container" id="result_container" style="display: none;">
                        <div class="loading" id="loading" style="display:none;"></div>
                    </div>
                    <div class="feedback" id="feedback" style="height:94%;width:100%;display:none;">
                        <strong class="feedback_title">Feedback: </strong><br />
                        <textarea class="feedback_comment" id="feedback_comment"></textarea><br />
                        <strong class="feedback_title">Name: </strong>
                        <input class="feedback_name" id="feedback_name" type="text"></input>
                        <strong class="feedback_priority"> Priority: </strong>
                        <select id="priority">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                        <input class="feedback_submit" id="feedback_submit" type="button" value="Submit"></input><br />
                        <table class="table_detail" id="feedback_table" border="0" bgcolor="white" cellSpacing="0" cellpadding="5" width="90%" style="table-layout:fixed;">
                            <thead>
                                <tr><th width="40%" align="center">Comment</th><th width="16%" align="left">Name</th><th width="18%" align="left">Time</th><th width="8%" align="center">Priority</th><th width="15%">Is Solved</th></tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>          
                    </div>
                </div>
                <div id="inner-tabs-east" class="ui-layout-east no-padding" style="padding:0px 15px 0px 0px;">
                    <ul id="east-tab-selecter">
                        <li id="hide_east" class="active" style="margin-left:0px"><a><img width="12px" height="12px;" src="./inc/images/right.png"/></a></li>
                        <li id="tab-network-selector" style="display:none;padding-right: 5px;margin-right: 16px;">Networks&nbsp;&nbsp;</li>
                        <li id="tab-annotation-selector" style="padding-right:8px;">Annotations</li>
                    </ul>
                    <div id="content_right">
                        <div id="tab-panel-east-1" style="height:auto;margin: 0px 8px;background-color: #3C735E;">
                            <div class="ui-tabs-panel outline" style="margin:0px;padding:5px;padding-top: 0px;">

                            </div>
                        </div>
                        <div id="tab-panel-east-2" style="width:auto;height:auto;padding:0px;margin:0px 8px 5px 8px;background-color:#1A313F;">
                            <div class="ui-tabs-panel outline" style="width:auto;height: auto;padding:0px 5px 5px 5px;">
                                <div id="annotation-tabs">
                                    <ul id="anno_nav">
                                        <li class="selected"><a t="#tabs-g">GO</a></li>
                                        <li><a t="#tabs-k">KEGG</a></li>
                                        <li><a t="#tabs-d">DO</a></li>
                                        <li><a t="#tabs-p">PFAM</a></li>
                                    </ul>
                                    <div id="tabs-g" class="annotabs  selected" style="display: block;">
                                        <div class="annotation_type"><p>Gene Ontology</p></div>
                                        <div class="toggle_premeters open"><p>set the premeters</p></div>
                                        <div class="annotation_premeters">
                                            <div class="set_range" id="set_range_g" style="display:block;">Enrichment test on:<br/><input type="radio" name="interactionType_g" value="n" checked>Genes in negative interactions</input><br/><input type="radio" name="interactionType_g" value="p">Genes in positive interactions</input><br/><input type="radio" name="interactionType_g" value="all">Genes in all interactions</input></div>
                                            <div class="setp">
                                                <input id="test_01" type="checkbox" checked value="BP">Test on Biological Process</input></br>
                                                <div class="setpremeter" id="setp_01" style="display:block;">
                                                    <p>with p-value<=<input type="text" value="0.005"></input><label id="lab_01"></label></p>
                                                    <p>Test&nbsp;<select id="s_01"><option value="over">Over</option><option value="under">Under</option></select>&nbsp;&nbsp;Represented GO Terms</p>
                                                </div>
                                                <input id="test_02" type="checkbox"  value="CC">Test on Cellular Compont</input></br>
                                                <div class="setpremeter" id="setp_02">
                                                    <p>with p-value<=<input type="text" value="0.005"></input><label id="lab_02"></label></p>
                                                    <p>Test&nbsp;<select id="s_02"><option value="over">Over</option><option value="under">Under</option></select>&nbsp;&nbsp;Represented GO Terms</p>
                                                </div>
                                                <input  id="test_03" type="checkbox" value="MF">Test on Molecluar Function</input></br>
                                                <div class="setpremeter" id="setp_03">
                                                    <p>with p-value<=<input type="text" value="0.005"></input><label id="lab_03"></label></p>
                                                    <p>Test&nbsp;<select id="s_03"><option value="over">Over</option><option value="under">Under</option></select>&nbsp;&nbsp;Represented GO Terms</p>
                                                </div>
                                            </div>
                                            <input class="an_submit" id="an_submit_g" type="button" value="TEST"></input><label id="alert"></label>
                                        </div>
                                        <div class="annotation_results" id="an_result_g">
                                            <p id="testType">Test on &nbsp;<select id="test_type" name="test_type"></select></p>
                                            <div class="loading" id="loading" style="display: none;"></div>
                                        </div>
                                    </div>
                                    <div id="tabs-k" class="annotabs">

                                        <div class="annotation_type"><p>KEGG Pathway</p></div>
                                        <div class="toggle_premeters open"><p>set the premeters</p></div>
                                        <div class="annotation_premeters">
                                            <div class="set_range" id="set_range_k" style="display:block;">Enrichment test on:<br/><input type="radio" name="interactionType_k" value="n" checked>Genes in negative interactions</input><br/><input type="radio" name="interactionType_k" value="p">Genes in positive interactions</input><br/><input type="radio" name="interactionType_k" value="all">Genes in all interactions</input></div>
                                            <div class="setp">
                                                <div class="setpremeter" id="setp_" style="display:block;">
                                                    <p>with p-value<=<input type="text" value="0.005"></input><label id="lab_"></label></p></div>
                                            </div>
                                            <input class="an_submit" id="an_submit_k" type="button" value="TEST"></input><label id="alert"></label>
                                        </div>
                                        <div class="annotation_results" id="an_result_k">
                                            <div class="loading" id="loading" style="display: none;"></div>
                                        </div>
                                    </div>
                                    <div id="tabs-d" class="annotabs">

                                        <div class="annotation_type"><p>Disease Ontology</p></div>
                                        <div class="toggle_premeters open"><p>set the premeters</p></div>
                                        <div class="annotation_premeters">
                                            <div class="set_range" id="set_range_d" style="display:block;">Enrichment test on:<br/><input type="radio" name="interactionType_d" value="n" checked>Genes in negative interactions</input><br/><input type="radio" name="interactionType_d" value="p">Genes in positive interactions</input><br/><input type="radio" name="interactionType_d" value="all">Genes in all interactions</input></div>
                                            <div class="setp">
                                                <div class="setpremeter" id="setp_" style="display:block;">
                                                    <p>with p-value<=<input type="text" value="0.005"></input><label id="lab_"></label></p></div>
                                            </div>
                                            <input class="an_submit" id="an_submit_d" type="button" value="TEST"></input><label id="alert"></label>
                                        </div>
                                        <div class="annotation_results" id="an_result_d">
                                            <div class="loading" id="loading" style="display: none;"></div>
                                        </div>
                                    </div>
                                    <div id="tabs-p" class="annotabs">

                                        <div class="annotation_type"><p>PFAM</p></div>
                                        <div class="toggle_premeters open"><p>set the premeters</p></div>
                                        <div class="annotation_premeters">
                                            <div class="set_range" id="set_range_p" style="display:block;">Enrichment test on:<br/><input type="radio" name="interactionType_p" value="n" checked>Genes in negative interactions</input><br/><input type="radio" name="interactionType_p" value="p">Genes in positive interactions</input><br/><input type="radio" name="interactionType_p" value="all">Genes in all interactions</input></div>
                                            <div class="setp">
                                                <div class="setpremeter" id="setp_" style="display:block;">
                                                    <p>with p-value<=<input type="text" value="0.005"></input><label id="lab_"></label></p></div>
                                            </div>
                                            <input class="an_submit" id="an_submit_p" type="button" value="TEST"></input><label id="alert"></label>
                                        </div>
                                        <div class="annotation_results" id="an_result_p">
                                            <div class="loading" id="loading" style="display: none;"></div>
                                        </div>
                                    </div>  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="inner-tabs-south" class="ui-layout-south no-padding" style="padding:0px 15px 0px 0px;"></div>
            </div>
        </div>
    </body> 
</html>
