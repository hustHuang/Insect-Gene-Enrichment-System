(function($) {
    $.extend($.fn, {
        /**
         * Starting point, example:
         * $('#myelement').jBooneGraph({foo: bar});
         */
        booneGraph : function(o) {
            /* Default options */
            var DEFAULTS = {
                defaultNodeColor: '#00EC00',
                maxSliderVal: 1000,
                runningLayout: null,
                layouts: [],
                annotations: [],
                layoutAlgo: ['fa2', 'fr', 'fl']
            };

            /* Runtime options */
            var opts = $.extend({}, DEFAULTS, o);
            
            var rootElement = $(this)[0];
            
            /* Common vars */
            var sigInst = null;
            var vizdata = {};
            var mouseX, mouseY;
            var logScale = Math.log(2) / opts.maxSliderVal;
            
            function updateColorInputs() {
                $.fn.spectrum.processNativeColorInputs();
                $('.sp-dd').remove();
            }
            
            function iterVisibleNodes(func, ids) {
                sigInst._core.graph.nodes.filter(function(node) {
                    return !node.hidden;
                }).forEach(func, ids);
            }

            function iterVisibleEdges(func, ids) {
                sigInst._core.graph.edges.filter(function(edge) {
                    return !edge.hidden;
                }).forEach(func, ids);
            }

            function logslider(position) {
                return Math.exp(logScale * position) - 1;
            }

            function getStrain(id) {
                return vizdata.strains[vizdata.index[id]];
            }

            function getNode(id) {
                return sigInst._core.graph.nodesIndex[id];
            }

            function setNodeColor(node, color) {
                if (color == undefined) {
                    var annot = vizdata[vizdata.loaded_annot].map[node.id];
                    if (annot != undefined) {
                        color = vizdata[vizdata.loaded_annot].colorPalette[vizdata[vizdata.loaded_annot].terms[annot[0]].idx];
                    } else {
                        color = vizdata[vizdata.loaded_annot].defaultColor;
                    }
                }

                node.color = color;
            }

            function updateMousePosition(event) {
                mouseX = event.pageX;
                mouseY = event.pageY;
            }

            var selected = [];
            var selectedConnectedNode = {};
            function selectNode(id) {
                var idx = $.inArray(id, selected);

                if (idx < 0) {
                    var strain = getStrain(id);
                    var txt = strain.alel || strain.name || strain.orf;

                    selected.push(id);
                    $("#menu-isolate-nodes").before(
                        $('<li class="selected-node-row">').attr('id', "selected-node-" + id)
                        .append(
                            $('<a href="#" class="selected-node-item">').text(txt)
                            .attr('ref', id)));

                    setNodeColor(getNode(id), "#FF0000")
                    sigInst.draw();
                } else {
                    selected.splice(idx, 1);
                    $("#selected-node-" + id).remove();
                    //setNodeColor(getNode(id));
                    setNodeColor(getNode(id),"#00EC00");
                    sigInst.draw();
                }

                // update menu
                $("#selection-menu-container .menu").menu("refresh");
                $("#menu_clear_selection").toggleClass('ui-state-disabled', !selected.length)
                $("#selection-menu-container").toggle(!!selected.length);
            }

            function clearSelection() {
                selected.length = 0;
                $(".selected-node-row").remove();
                $("#selection-menu-container .menu").menu("refresh");
                $("#menu_clear_selection").addClass('ui-state-disabled');
                $("#selection-menu-container").hide();
            }

            function loadLayout(e) {
                var ele = $(e.target);
                var url = ele.attr('rel');
                
                sigInst.emptyGraph();
                $(".avail-on-loaded-layout").addClass("ui-state-disabled");
                
                var layoutCallback = function () {
                    cutoffApply();
                    $('.load-layout').removeClass('ui-selected');
                    ele.addClass('ui-selected');
                    clearSelection();
                    $(".avail-on-loaded-layout").removeClass("ui-state-disabled");
                }
                
                var layout = opts.layouts.filter(function(layout){
                    return layout.url == url
                })[0];
                var parser = sigInst.parseBooneGexf;
                
                if (isFunction(layout.parser)) {
                    parser = layout.parser;
                } else if (isString(layout.parser)) {
                    switch (layout.parser.toLowerCase()) {
                        case 'gexf':
                            parser = sigInst.parseBooneGexf;
                            break;
                        case 'json':
                            parser = sigInst.parseJson;
                            break;
                        case 'gml':
                            parser = sigInst.parseGml;
                            break;
                    }
                }
                
                parser($, sigInst, url, vizdata, layoutCallback);
            }

            function loadAnnotation(e) {
                var id;
                if (typeof e == "string") {
                    id = e;
                } else {
                    id = $(e.target).attr('rel');
                }

                var key = id;
                vizdata.loaded_annot = key;

                if (vizdata[key] == undefined) {
                    if (id == 'white') {
                        vizdata[key] = {
                            map : {},
                            defaultColor : opts.defaultNodeColor,
                            terms: []
                        }
                    } else {
                        $.ajax({
                            url : key,
                            dataType : 'json',
                            async : false,
                            success : function(data) {
                                vizdata[key] = data;
                                if (vizdata['defaultColor'] == undefined) {
                                    vizdata.defaultColor = opts.defaultNodeColor;
                                }

                                var i = 0, n;
                                for (n in vizdata[key].terms) {
                                    vizdata[key].terms[n] = {
                                        idx : i,
                                        name : vizdata[key].terms[n]
                                    }
                                    i++;
                                }

                                vizdata[key].colorPalette = get_color_palette(i);
                            }
                        });
                    }
                }

                sigInst.iterNodes(function(n) {
                    var strain = vizdata.strains[vizdata.index[n.id]];
                    var annot = vizdata[key].map[n.id];
                    if (annot != undefined) {
                        n.color = vizdata[key].colorPalette[vizdata[key].terms[annot[0]].idx];
                    } else {
                        n.color = vizdata[key].defaultColor;
                    }
                }).draw();

                $('.load-annotation').removeClass('ui-selected');
                $('.load-annotation[rel="' + id + '"]').addClass('ui-selected');
                
                updateLegend();
            }
            
            function updateLegend() {
                $('#legend').empty();
                
                var termid, term, annot = vizdata[vizdata.loaded_annot];
                for (termid in annot.terms) {
                    term = annot.terms[termid];
                    
                    $('#legend').append($('<li class="dialog-field">').append(
                        $('<input type="color">').attr({
                            value: annot.colorPalette[term.idx],
                            id: "a" + termid
                        })).append(
                        $('<label>').attr('for', "a" + termid).text(term.name)));
                }
                
                updateColorInputs();
                
                $('#legend input[type=color]').change(function() {
                    var term = annot.terms[$(this).attr('id').replace(/^a/, '')];
                    annot.colorPalette[term.idx] = $(this).val();
                    loadAnnotation(vizdata.loaded_annot);
                });
            }

            function isolateNodes() {
                selectedConnectedNode = {};

                selected.forEach(function(nodeid) {
                    selectedConnectedNode[nodeid] = true;
                    sigInst.iterEdges(function(edge) {
                        if (edge.source.id == nodeid)
                            selectedConnectedNode[edge.target.id] = true;
                        if (edge.target.id == nodeid)
                            selectedConnectedNode[edge.source.id] = true;
                    });
                });

                sigInst.iterNodes(function(node) {
                    if (selectedConnectedNode[node.id] == undefined) {
                        node.hidden = true;
                    }
                });

                sigInst.draw();

            // $("#menu_unhide_nodes").removeClass('ui-state-disabled');
            // clearSelection();
            }

            function onNodesContext(targets) {
                //var node = getNode(targets.content);
                //var name = node.label;
                $("#contextmenu-container").show().delay(2000).hide(200);
                $("#contextmenu-container").css({
                    left : mouseX,
                    top : mouseY
                });   
            }

            function makeTargetInfoHtml(data){
                var html;
                if(data == null || data == undefined || data == '' ){
                    html = '<span>No information ablout it .</span>';    
                }else{
                    html = '<span class="node_name">Primary SGDID: </span><span class="node_value">'+ data.Primary_SGDID + '</span></br>' + 
                    '<span class="node_name">Feature Name: </span><span class="node_value"><a target="_blank" href="http://www.yeastgenome.org/cgi-bin/locus.fpl?locus=' + data.Feature_Name + '">' +data.Feature_Name + '</a></span></br>' + 
                    (data.Standard_Gene_Name == '' ? '' : '<span class="node_name">Standard Gene Name: </span><span class="node_value">'+data.Standard_Gene_Name + '</span></br>') +  
                    (data.Alias == '' ? '' : '<span class="node_name">Alias: </span><span class="node_value">'+data.Alias + '</span></br>') + 
                    (data.Parent_Feature_Name == '' ? '' : '<span class="node_name">Parent Feature Name: </span><span class="node_value">'+data.Parent_Feature_Name + '</span></br>') + 
                    (data.Secondary_SGDID == '' ? '' : '<span class="node_name">Secondary SGDID: </span><span class="node_value">'+data.Secondary_SGDID + '</span></br>') + 
                    (data.Chromosome == '' ? '' : '<span class="node_name">Chromosome: </span><span class="node_value">'+data.Chromosome + '</span></br>') + 
                    (data.Start_Coordinate == '' ? '' : '<span class="node_name">Start Coordinate: </span><span class="node_value">'+data.Start_Coordinate + '</span></br>') + 
                    (data.Stop_Coordinate == '' ? '' : '<span class="node_name">Stop Coordinate: </span><span class="node_value">'+data.Stop_Coordinate + '</span>'); 
                }
                return html;
            }

            /**
             * Select nodes to isolate
             */
            function onNodesCtrlClick(targets) {
                targets.content.forEach(selectNode);
                
                //var node = getNode(targets.content);
                //var name = node.label;       
            }

            function onNodesClick(targets) {
                var node = getNode(targets.content);
                var name = node.label;
                name = name.split('_')[0];
                var viewType = 'functional map';
                var group = 'nodes' ;
                $.ajax({
                    type: 'POST',
                    url: '../ajax_get_info.php',
                    dataType: 'JSON',
                    data: {
                        viewType: viewType ,
                        group: group ,
                        id: name
                    },
                    async: true,
                    success: function(data){ 
                        if ($('.clicked_target').length != 0){
                            $('.clicked_target').remove();
                        }
                        var divHtml = '<div class="clicked_target" style="z-index: 12; width: 2px;height: 2px;"></div>';
                        $("#network-container").append(divHtml);
                        
                        var htmlInfo = makeTargetInfoHtml(data);
                        var title = "Description of " + name;
                        var diaId = "dia-" + name;
                        
                        var container = parent.document.getElementById('result_container');
                        var width = 400 ,height = 180 ; 
                        var left = mouseX + $(container).offset().left;
                        var top = mouseY + $(container).offset().top;
                        if(left + width  >  $(container).width() + $(container).offset().left){
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
    
                    /*
                        $("#contextmenu-container").show().delay(2000).hide(200);
                        $("#contextmenu-container").css({
                            left : mouseX,
                            top : mouseY
                        });
                     */
                    }
                });  
                
            }

            function toggleLayout(e) {
                var ele = $(e.target);
                var layout_id = ele.attr('id');
                var start = true;

                $('.layout').removeClass('ui-selected');

                if (opts.runningLayout != null) {
                    if (opts.runningLayout == layout_id) {
                        start = false;
                    }

                    switch (opts.runningLayout) {
                        case 'layout_fa2':
                            sigInst.stopForceAtlas2();
                            break;
                        case 'layout_fr':
                            sigInst.stopFruchtermanReingold();
                            break;
                        case 'layout_fl':
                            sigInst.stopForceLayout();
                    }

                    opts.runningLayout = null;
                }

                if (start) {
                    switch (layout_id) {
                        case 'layout_fa2':
                            sigInst.startForceAtlas2();
                            break;
                        case 'layout_fr':
                            sigInst.startFruchtermanReingold();
                            break;
                        case 'layout_fl':
                            sigInst.startForceLayout();
                    }

                    opts.runningLayout = layout_id;
                    ele.addClass('ui-selected');
                }
            }

            function initMenus() {
                /* Init all menus on the page */
                $('.menu').menu();
                $('.menu-container').mouseleave(function() {
                    $('.menu').menu("collapseAll", null, true);
                });

                /*
                 * Init all draggable elements and contain them
                 * on the graph canvas (menus)
                 */
                $(".draggable").draggable({
                    containment : '#network-container',
                    handle : ".drag-handle"
                });

                /* Assign action to each menu item */
                $('.load-layout').click(loadLayout);
                $('.load-annotation').click(loadAnnotation);
                $(".layout").click(toggleLayout);
                $('#fullscreen').fullscreen($("#network-container"), function() {
                    $('#fullscreen').toggleClass('ui-selected', isFullscreen());
                });
                /* User added search actions */
                $('#sBtn').click(function(){
                    if($("#s").val()=="" || $("#s").val()==" "){
                        return;
                    }
                    for (var k in extractSearchedStrains($("#s"))) {
                        selectNode(k);
                    }
                //isolateNodes();
                });
                
                /* Reset the booneGraph view */
                $('#clBtn').live('click',function(){
                     $('#s').val('');
                     $('#load-layout-s2010').trigger('click');   
                 });
                
//               $('#clBtn').live('click',function(){       
//                    $.each(selected,function(i,e){
//                        var idx = $.inArray(e, selected);
//                        selected.splice(idx, 1);
//                        $("#selected-node-" + e).remove();
//                        setNodeColor(getNode(e),'#E8E8E8'); 
//                    });
//                    clearSelection();
//                });
                
                
                /* Selection menu items */
                $("#menu-isolate-nodes").click(isolateNodes);

                /* Context menu */
               
                /*
                 * Prevent context menu, we want our own
                 * rightclick functionality
                 */
                $("#network-container").contextmenu(function() {
                    return false;
                });
                
                // sigh... disable context menu on context menu
                // b/c its not in the other container
                $("#contextmenu-container").contextmenu(function() {
                    return false;
                });
                // Nice effects, stop any animations on enter,
                // hide on leave, hide if not entered (code in
                // callback above)
                $("#contextmenu-container").mouseleave(function() {
                    $(this).delay(500).hide(200);
                }).mouseenter(function() {
                    $(this).stop(true);
                });
            }
            
            function extractSearchedStrains(ele) {
                var matchedNodes = {}, matchedLines = [];
                var i, o, n, a;
                var lines = ele.val().toLowerCase().split(/[\s,]+/);
                
                lines = lines.map(function(item) {
                    return item.trim();
                });
                vizdata.strains.forEach(function(strain) {
                    o = strain.orf.toLowerCase();
                    n = strain.name && strain.name.toLowerCase();
                    a = strain.alel && strain.alel.toLowerCase();
                    /*NOT lines.length-1 */
                    for (i = 0; i < lines.length; i++) {
                        if ((lines[i] == o || lines[i] == n || lines[i] == a)
                            && getNode(strain.id) != undefined) {
                            matchedLines.push(i);
                            matchedNodes[strain.id] = true;
                        }
                    }
                });

                $.unique(matchedLines).sort().reverse().forEach(function(idx) {
                    lines.splice(idx, 1);
                });
                
                /* added */
                //don't reset the value of input
                //ele.val(lines.join('\n'));
                
                return matchedNodes;
            }
            
            function initSearchBoxes() {
                $('#menu-search-node').click(function() {
                    if ($(this).parent().hasClass('ui-state-disabled'))
                        return false;

                    $("#dialog-searchstrain").dialog({
                        resizable : false,
                        modal : true,
                        buttons : {
                            Search: function() {
                                var k;
                                for (k in extractSearchedStrains($("#strain-search"))) {
                                    selectNode(k);
                                }
                            },
                            Done: function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });

                $('#menu-color-node').click(function() {
                    if ($(this).parent().hasClass('ui-state-disabled'))
                        return false;

                    $("#dialog-colorstrain").dialog({
                        resizable : false,
                        modal : true,
                        buttons : {
                            Search: function() {
                                var color = $("#dialog-colorstrain").find('input[type=color]').val();
                                
                                var k;
                                for (k in extractSearchedStrains($("#strain-color"))) {
                                    getNode(k).color = color;
                                }
                                
                                sigInst.draw();
                            },
                            Done: function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });

                $('#menu-legend').click(function() {
                    if ($(this).parent().hasClass('ui-state-disabled'))
                        return false;

                    $("#dialog-legend").dialog({
                        resizable : true,
                        modal : false,
                        height: 500,
                        buttons : {
                            Close: function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            }

            function cutoffLabelUpdate(event, ui) {
                var val = $("#slider-vertical").slider("value");
                if (!!ui)
                    val = ui.value;
                $("#cutoff-label").html(logslider(val).toFixed(3));
            }

            function cutoffApply() {
                var cutoff = logslider($("#slider-vertical").slider("value"));

                /* Do we have some selected nodes? */
                if (selected.length > 0) {
                    sigInst.iterNodes(function(node) {
                        if (selectedConnectedNode[node.id] != undefined) {
                            node.visibleDegree = node.degree;
                        }
                    });

                    sigInst.iterEdges(function(edge) {
                        /* Not connecting visible nodes */
                        if (!selectedConnectedNode[edge.source.id]
                            || !selectedConnectedNode[edge.target.id]) {
                            edge.source.visibleDegree--;
                            edge.target.visibleDegree--;
                            return;
                        }

                        if (edge.weight < cutoff
                            || ($.inArray(edge.source.id, selected) < 0 && $.inArray(
                                edge.target.id, selected) < 0)) {
                            edge.source.visibleDegree--;
                            edge.target.visibleDegree--;
                            edge.hidden = true;
                        } else {
                            edge.hidden = false;
                        }
                    });

                    sigInst.iterNodes(function(node) {
                        if (selectedConnectedNode[node.id] != undefined) {
                            node.hidden = !node.visibleDegree;
                        }
                    });
                } else {
                    sigInst.iterNodes(function(node) {
                        node.visibleDegree = node.degree;
                    });

                    sigInst.iterEdges(function(edge) {
                        if (edge.weight < cutoff) {
                            edge.source.visibleDegree--;
                            edge.target.visibleDegree--;
                            edge.hidden = true;
                        } else {
                            edge.hidden = false;
                        }
                    });

                    sigInst.iterNodes(function(node) {
                        node.hidden = !node.visibleDegree;
                    });
                }

                sigInst.draw();
            }
            
            function initElements() {
                $(rootElement).append(
                    $('<div id="navigation">').append(
                        $('<div id="slider-vertical">')).append(
                        $('<div id="cutoff-label" class="ui-widget ui-corner-all ui-widget-content">'))
                    ).append(
                    $('<div id="menu-container" class="draggable selectable menu-container">').append(
                        $('<ul class="menu">').append(
                            $('<li class="menu-title drag-handle"><span>Menu</span></li>')).append(
                            // Other menu items here
                            $('<li><a href="#" id="fullscreen">Fullscreen</a></li>')).append(
                            $('<li><a href="#" id="menu-legend">Legend</a></li>')).append(
                            $('<li>')).append( // separator line
                            $('<li class="ui-state-disabled avail-on-loaded-layout"><a href="#" id="menu-search-node">Search nodes</a></li>')).append(
                            $('<li class="ui-state-disabled avail-on-loaded-layout"><a href="#" id="menu-color-node">Color nodes</a></li>')))
                    ).append(
                    $('<div id="selection-menu-container" class="draggable menu-container" style="display: none;">').append(
                        $('<ul class="menu">').append(
                            $('<li class="menu-title drag-handle"><span>Selected nodes</span></li>')).append(
                            $('<li><a href="#" id="menu-isolate-nodes">Isolate selected nodes</a></li>')))
                    ).append(
                    $('<div id="graph-loading">')
                    );
                
                if (opts.layouts.length > 0) {
                    $('#fullscreen').parent().before($('<li><a href="#" id="menu-avail-layout">Available layouts</a><ul id="avail-layout-list"></ul></li>'));
                    opts.layouts.forEach(function(layout) {
                        $('#avail-layout-list').append(
                            $('<li>').append(
                                $('<a href="#" class="load-layout">'
                                    ).attr({
                                    rel: layout.url, 
                                    id: "load-layout-" + layout.id
                                }
                                ).html(layout.name)));
                    });
                }
                
                if (opts.annotations.length > 0) {
                    $('#fullscreen').parent().before($('<li><a href="#" id="menu-annotations">Annotations</a><ul id="annot-list"></ul></li>'));
                    $('#annot-list').append('<li><a href="#" rel="white" id="load-annotation-white" class="load-annotation">None</a></li>');
                    opts.annotations.forEach(function(annot) {
                        $('#annot-list').append(
                            $('<li>').append(
                                $('<a href="#" class="load-annotation">'
                                    ).attr({
                                    rel: annot.url, 
                                    id: "load-annotation-" + annot.id
                                }
                                ).html(annot.name)));
                    });
                }
                
                if (opts.layoutAlgo.length > 0) {
                    $('#fullscreen').parent().before($('<li><a href="#" id="menu-run-layout">Layout graph</a><ul id="layout-list"></ul></li>'));
                    opts.layoutAlgo.forEach(function(layout) {
                        switch (layout) {
                            case 'fa2':
                                $('#layout-list').append($('<li><a href="#" id="layout_fa2" class="layout">ForceAtlas2</a></li>'));
                                break;
                            case 'fr':
                                $('#layout-list').append($('<li><a href="#" id="layout_fr" class="layout">Fruchterman-Reingold</a></li>'));
                                break;
                            case 'fl':
                                $('#layout-list').append($('<li><a href="#" id="layout_fl" class="layout">Simple force layout</a></li>'));
                                break;
                        }
                    });
                }
                
                $('body').append(
                    $('<div id="dialog-searchstrain" title="Search strains in this network" style="display: none;" class="ui-dialog">').append(
                        $('<p>').append(
                            $('<textarea id="strain-search" class="strain-search-textbox" placeholder="Start typing gene names or ORFs">')))
                    ).append(
                    $('<div id="dialog-colorstrain" title="Color strains in this network" style="display: none;" class="ui-dialog">').append(
                        $('<p>').append(
                            $('<div class="dialog-field"><input type="color" name="node_color" value="#3355cc" /></div>')).append(
                            $('<div class="dialog-field"><textarea id="strain-color" class="strain-search-textbox" placeholder="Select color above and start typing gene names or ORFs"/></div>')))
                    ).append(
                    $('<div id="dialog-legend" title="Annotation legend" style="display: none;" class="ui-dialog">').append(
                        $('<p>').append(
                            $('<ul id="legend"></ul>')))
                    ).append(
                    $('<div id="contextmenu-container" style="display: none;">').append(
                        $('<ul id="contextmenu" class="menu">').append(
                            $('<li><a href="#">Not implemented yet</a></li>')))
                    );
                
                updateColorInputs();
            }
            
            function init() {
                initElements();
                
                sigInst = sigma.init(rootElement).drawingProperties({
                    edgeColor : 'white',
                    defaultLabelColor : 'white',
                    nodeColor : opts.defaultNodeColor
                }).graphProperties({
                    // minNodeSize: 2,
                    // maxNodeSize: 2,
                    minEdgeSize : 0,
                    maxEdgeSize : 1.5,
                    nodesPowRatio : 1,
                    edgesPowRatio : .5,
                    safe : false
                }).mouseProperties({
                    maxRatio : 64
                }
                // }).hoverHighlight(_
                ).bind('rightclicknodes', onNodesContext).bind('ctrlclicknodes', onNodesCtrlClick).bind(
                    'downnodes', onNodesClick);
                    //'dblclicknodes', onNodesClick);
                
                /* Loading spinner each time we hit the server */
                $("body").on({
                    ajaxStart: function() {
                        $(rootElement).append('<div id="modal-overlay" class="ui-widget-overlay ui-front"></div>');
                    },
                    ajaxStop: function() {
                        $("#modal-overlay").remove()
                    }
                });

                /* Fetch all node info */
                $.getJSON(opts.nodesUrl, function(data) {
                    vizdata['strains'] = data.nodes;
                    vizdata['annotations'] = data.annotations;
                    vizdata['index'] = {};

                    for (i in data.nodes) {
                        vizdata.index[data.nodes[i].id] = i;
                    }

                    // Load plot graph in Michael Jackson mode by
                    // default
                    loadAnnotation('white');
                });

                initMenus();
                initSearchBoxes();

                $(document).mousemove(updateMousePosition);

                $("#slider-vertical").slider({
                    orientation : "vertical",
                    min : 0,
                    step : 1,
                    value : 263,
                    max : opts.maxSliderVal,
                    range : "max",
                    slide : cutoffLabelUpdate,
                    change : cutoffApply
                });
                cutoffLabelUpdate();
                /*
                //added by huang , highlight queried gene user input
                var genes = $("#s").val();
                if( $.trim(genes) == ""){
                    return;
                }
                setTimeout(function(){
                    for (var k in extractSearchedStrains($("#s"))) {
                        selectNode(k);
                    //getNode(k).color = '#FF0000';
                    }
                //isolateNodes();
                },1000);
               */
            }
            
            /* Starting point */
            init();
            //added by huang , highlight queried gene user input
            var genes = $("#s").val();
            if( $.trim(genes) == ""){
                return;
            }
            setTimeout(function(){
                for (var k in extractSearchedStrains($("#s"))) {
                    selectNode(k);
                    //getNode(k).color = '#FF0000';
                }
            //isolateNodes();
            },2000);
        }
    });
})(jQuery);