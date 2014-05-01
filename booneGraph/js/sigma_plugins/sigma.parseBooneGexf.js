//Mathieu Jacomy @ Sciences Po Médialab & WebAtlas
//(requires sigma.js to be loaded)
sigma.publicPrototype.parseBooneGexf = function($, sigInst, gexfPath, vizdata, callback) {
    // Load XML file:
    var sigmaInstance = sigInst;
    var start = new Date().getTime();
    
    $.get(gexfPath, function(gexf) {
        var i, j, k;

        // Parse Attributes
        // This is confusing, so I'll comment heavily
        var nodesAttributes = [];   // The list of attributes of the nodes of the graph that we build in json
        var edgesAttributes = [];   // The list of attributes of the edges of the graph that we build in json
        var attributesNodes = gexf.getElementsByTagName('attributes');  // In the gexf (that is an xml), the list of xml nodes 'attributes' (note the plural 's')

        for(i = 0; i<attributesNodes.length; i++){
            var attributesNode = attributesNodes[i];  // attributesNode is each xml node 'attributes' (plural)
            if(attributesNode.getAttribute('class') == 'node'){
                var attributeNodes = attributesNode.getElementsByTagName('attribute');  // The list of xml nodes 'attribute' (no 's')
                for(j = 0; j<attributeNodes.length; j++){
                    var attributeNode = attributeNodes[j];  // Each xml node 'attribute'

                    var id = attributeNode.getAttribute('id'),
                    title = attributeNode.getAttribute('title'),
                    type = attributeNode.getAttribute('type');

                    var attribute = {id:id, title:title, type:type};
                    nodesAttributes.push(attribute);

                }
            } else if(attributesNode.getAttribute('class') == 'edge'){
                var attributeNodes = attributesNode.getElementsByTagName('attribute');  // The list of xml nodes 'attribute' (no 's')
                for(j = 0; j<attributeNodes.length; j++){
                    var attributeNode = attributeNodes[j];  // Each xml node 'attribute'

                    var id = attributeNode.getAttribute('id'),
                    title = attributeNode.getAttribute('title'),
                    type = attributeNode.getAttribute('type');

                    var attribute = {id:id, title:title, type:type};
                    edgesAttributes.push(attribute);
                }
            }
        }

        var annotations = vizdata[vizdata.loaded_annot];

        var nodes = []; // The nodes of the graph
        var nodesNodes = gexf.getElementsByTagName('nodes') // The list of xml nodes 'nodes' (plural)

        for(i=0; i<nodesNodes.length; i++){
            var nodesNode = nodesNodes[i];  // Each xml node 'nodes' (plural)
            var nodeNodes = nodesNode.getElementsByTagName('node'); // The list of xml nodes 'node' (no 's')

            for(j=0; j<nodeNodes.length; j++){
                var nodeNode = nodeNodes[j];  // Each xml node 'node' (no 's')

//                window.NODE = nodeNode;

                var id = parseInt(nodeNode.getAttribute('id'));
                var label = nodeNode.getAttribute('label') || id;

                //viz
                var size = 10;
                var x = 100 - 200*Math.random();
                var y = 100 - 200*Math.random();
                var color;

                var sizeNodes = nodeNode.getElementsByTagName('size');
                sizeNodes = sizeNodes.length ?
                        sizeNodes :
                            nodeNode.getElementsByTagName('viz:size');
                if(sizeNodes.length>0){
                    sizeNode = sizeNodes[0];
//                          size = parseFloat(sizeNode.getAttribute('value'));
                }

                var positionNodes = nodeNode.getElementsByTagName('position');
                positionNodes = positionNodes.length ?
                        positionNodes :
                            nodeNode.getElementsByTagName('viz:position');
                if(positionNodes.length>0){
                    var positionNode = positionNodes[0];
                    x = parseFloat(positionNode.getAttribute('x'));
                    y = parseFloat(positionNode.getAttribute('y'));
                }

                var colorNodes = nodeNode.getElementsByTagName('color');
                colorNodes = colorNodes.length ?
                        colorNodes :
                            nodeNode.getElementsByTagName('viz:color');
                if(colorNodes.length>0){
                    colorNode = colorNodes[0];
                    color = '#'+sigma.tools.rgbToHex(parseFloat(colorNode.getAttribute('r')),
                            parseFloat(colorNode.getAttribute('g')),
                            parseFloat(colorNode.getAttribute('b')));
                }

                var strain = vizdata['strains'][vizdata['index'][id]];
                if (strain == undefined) {
                    console.log(vizdata['index'][id]);
                    strain = {};
//                    continue;
                }

                var annot = annotations.map[strain.id];
                if (annot != undefined) {
                    color = annotations.colorPalette[annotations.terms[annot[0]].idx];
                } else {
                    color = annotations.defaultColor;
                }

                // Create Node
                var node = {
                        label: strain.alel || strain.name || strain.orf, 
                        size: 2, 
                        x:x, 
                        y:y, 
                        attributes:[], 
                        color:color
                };  // The graph node

                // Attribute values
                var attvalueNodes = nodeNode.getElementsByTagName('attvalue');
                for(k=0; k<attvalueNodes.length; k++){
                    var attvalueNode = attvalueNodes[k];
                    var attr = attvalueNode.getAttribute('for');
                    var val = attvalueNode.getAttribute('value');
                    node.attributes.push({attr:attr, val:val});
                }

                sigmaInstance.addNode(id,node);
            }
        }

        var edges = [];
        var edgesNodes = gexf.getElementsByTagName('edges');
        for(i=0; i<edgesNodes.length; i++){
            var edgesNode = edgesNodes[i];
            var edgeNodes = edgesNode.getElementsByTagName('edge');
            for(j=0; j<edgeNodes.length; j++){
                var edgeNode = edgeNodes[j];
                var id = edgeNode.getAttribute('id');
                var source = edgeNode.getAttribute('source');
                var target = edgeNode.getAttribute('target');
                var label = edgeNode.getAttribute('label');
                var edge = {
                        id:         id,
                        sourceID:   source,
                        targetID:   target,
                        label:      label,
                        attributes: []
                };

                var weight = edgeNode.getAttribute('weight');
                if(weight!=undefined){
                    edge['weight'] = weight;
                    edge['size'] = weight;
                }

                var attvalueNodes = edgeNode.getElementsByTagName('attvalue');
                for(k=0; k<attvalueNodes.length; k++){
                    var attvalueNode = attvalueNodes[k];
                    var attr = attvalueNode.getAttribute('for');
                    var val = attvalueNode.getAttribute('value');
                    edge.attributes.push({attr:attr, val:val});
                }

                sigmaInstance.addEdge(id,source,target,edge);
            }
        }
        
        var end = new Date().getTime();
        var time = end - start;
        console.log('Execution time: ' + time);
    }).always(function() { callback(); });
};
