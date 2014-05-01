//Mathieu Jacomy @ Sciences Po Médialab & WebAtlas
//(requires sigma.js to be loaded)
sigma.publicPrototype.parseJson = function($, sigInst, gexfPath, vizdata, callback) {
    // Load XML file:
    var sigmaInstance = sigInst;
    var annotations = vizdata[vizdata.loaded_annot];
    var start = new Date().getTime();

    /* Fetch all node info */
    $.getJSON(gexfPath, function(data) {
        var strain, annot, color;
        
        data.nodes.forEach(function (node) {
            strain = vizdata['strains'][vizdata['index'][node.id]];
            
            if (strain == undefined) {
                console.log("Strain not found:", node.id);
                strain = {};
//                return;
            }
            
            annot = annotations.map[strain.id];
            if (annot != undefined) {
                color = annotations.colorPalette[annotations.terms[annot[0]].idx];
            } else {
                color = annotations.defaultColor;
            }
            
            node.label = strain.alel || strain.name || strain.orf;
            node.size = 2;
            node.color = color;
            
            sigmaInstance.addNode(node.id, node);
        });
        
        data.edges.forEach(function (edge) {
            edge.size = edge.weight; // Sets the thickness of the edge
            sigmaInstance.addEdge(edge.id, edge.source, edge.target, edge);
            //sigmaInstance.addEdge(edge.root_index, edge.source, edge.target, edge);
        });
        
        var end = new Date().getTime();
        var time = end - start;
        console.log('Execution time: ' + time);
    }).always(function() { callback(); }).fail(function(e) { 
        console.log('failed', e);
    });
};
