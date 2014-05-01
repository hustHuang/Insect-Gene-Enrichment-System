<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <link type="text/css" rel="stylesheet"  href="inc/css/index_style.css" />
        <link type="text/css" rel="stylesheet" href="inc/css/jquery.autocomplete.css" />
        <script type="text/javascript" src="inc/js/lib/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="inc/js/lib/jquery.autocomplete.js"></script>
        <script type="text/javascript" src="inc/js/localdata.js"></script>
        <script type="text/javascript" src="inc/js/inputvalue.js"></script>
        <script type="text/javascript" src="inc/js/index.js"></script>
        <script type="text/javascript">
            var _input_labels='YMD8 YDJ1 YET1 SVL3';
          //var _input_labels = 'Enrichment test for genes';
        </script>
        <!--[if IE]>
            <style type="text/css">
                .file{right:60px;}
            </style>
        <![endif]-->
        <title>SGA Search</title>
    </head>
    <body>    
        <div class="main">
            <form id="searchForm" name="searchForm" method="POST"  action="result.php" enctype="multipart/form-data">

                <div class="center">
                    <h1 class="h1_text">SGACellMap</h1>
                    <div class="search_area">
                        <input class="search_text" id="it" type="text" name="geneNames" value=""/>
                        <input class="search_btn" type="submit" value="GO" />
                    </div>
                    <div class="set_search">
                        <div class="upload_genelist">
                            <a class="show_upload">Upload Genelist<span class="zk1"> ► </span></a>
                            <div class="show_upload_genelist" style='position:relative'>
                                <!--<form action="" method="post" enctype="multipart/form-data" style='position:relative'>-->
                                <input type='text' name='textfield' id='textfield' class='txt' />  
                                <input type='button' class='btn' value='Select files' />
                                <input type="file" name="file" class="file" id="fileField" onchange="document.getElementById('textfield').value=this.value" />
                                <!-- <input type="button" name="upload_btn" class="btn" value="upload" />
                                                          </form>-->
                                <div class="clear"></div>
                                <p>Text files can be uploaded directly, searched genes should be listed in the file and separated by blank space.</p>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="set_search_parameters">
                            <a class="set__parameters">Set Search Parameters<span class="zk2"> ► </span></a>
                            <div class="show_parameters">
                                <p style="margin-top: 15px;">Negative interactions:SGA score <= </p><input type="text" class="e_neg" name="e_neg" value="-0.08" style="margin-top: 20px;"/>
                                <div class="clear"></div>
                                <p>with p-value <= </p><input type="text" class="p_neg" name="p_neg" value="0.05"/>
                                <div class="clear"></div>
                                <p>Positive interactions:SGA score => </p><input type="text" class="e_pos" name="e_pos" value="0.08"/>
                                <div class="clear"></div>
                                <p>with p-value <= </p><input type="text" class="p_pos" name="p_pos" value="0.05"/>
                                <div class="clear"></div>
                                <p style="text-align: left;padding-left: 15px">Significant genetic interaction profile correlations (|r| > 0.1)</p><input type="radio" name="rvalue" value="significant" checked="true" style="height: 15px;width: 15px;overflow: hidden;margin-left: 5px;margin-top: 10px;"/>
                                <div class="clear"></div>
                                <p style="text-align: left;padding-left: 15px">Not significant genetic interaction profile correlations</p><input type="radio" name="rvalue" value="notsignificant" style="height: 15px;width: 15px;overflow: hidden;margin-left: 5px;margin-top: 10px;" />
                            </div> 
                        </div>   
                    </div>
                </div>     
            </form>
        </div>
        <p style="width: 100%;position: absolute;bottom: 15px;color: #FFF;text-align: center;font-size: 14px;">Copyright &copy;2013 , all rights reserved.</p>
    </body>
</html>
