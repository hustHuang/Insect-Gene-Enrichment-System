//Mathieu Jacomy @ Sciences Po Médialab & WebAtlas
//(requires sigma.js to be loaded)
sigma.publicPrototype.parseGml = function($, sigInst, gexfPath, vizdata, callback) {
    // Load XML file:
    var defaultColor='#00EC00';//set the node color
    var sigmaInstance = sigInst;
    //var annotations = vizdata[vizdata.loaded_annot];
    var start = new Date().getTime();
    var nodeAttrs = [
           {name: 'id', attr: 'id'}, 
           {name: 'label', attr: 'label'},
           {name: 'x', attr: 'x'},
           {name: 'y', attr: 'y'},
       ];
    var edgeAttrs = [
             {name: 'id', attr: 'id'}, 
             {name: 'source', attr: 'source'},
             {name: 'target', attr: 'target'},
             {name: 'weight', attr: 'value'},
         ];
    
    nodeAttrs.forEach(function(att){
        att.regex = new RegExp(att.attr + "\\s+(.+)\\n");
    });
    edgeAttrs.forEach(function(att){
        att.regex = new RegExp(att.attr + "\\s+(.+)\\n");
    });
    
    /* Fetch all node info */
    $.get(gexfPath, function(data) {
        var dataIdx = 0, parCount, dataStr, m, node, edge;
        var nodeIdMap = {};  
        /*
         * Node loop
         */
        while ((dataIdx = data.indexOf("node", dataIdx)) > 0) {
            parIdx = data.indexOf("[", dataIdx) + 1;
            parCount = 1;
            while (parCount > 0) {
                switch (data.charAt(parIdx)) {
                case "[": parCount++; break;
                case "]": parCount--; break;
                }
                parIdx++;
            }
            
            dataStr = data.slice(dataIdx, parIdx);
            dataIdx++;
            
            node = {};
            
            nodeAttrs.forEach(function(att) {
                m = att.regex.exec(dataStr);
                if (m) {
                    if (!m[1].match(/^".+"$/)) {
                        m[1] = parseFloat(m[1]);
                    } else{
                        m[1] = m[1].replace(/"/g, "");
                    }
                    node[att.name] = m[1];
                }
            });
            
            nodeIdMap[node.id] = node.id;
            
            strain = vizdata['strains'][vizdata['index'][node.id]];
            if (strain == undefined) {
                strain = vizdata['strains'][vizdata['index'][node.label]];
                if (strain == undefined) {
                    console.log("Strain not found:", node.id);
                    strain = {};
//                    continue;
                } else {
                    /* The label is the correct id */
                    nodeIdMap[node.id] = node.label;
                    node.id = node.label;
                }
            }
           /* 
            annot = annotations.map[strain.id];
            if (annot != undefined) {
                color = annotations.colorPalette[annotations.terms[annot[0]].idx];
            } else {
                color = annotations.defaultColor;
            }
            */
            color = defaultColor;
           
            node.label = strain.alel || strain.name || strain.orf || node.label;
            node.size = 2;
            node.color = color;
            
            sigmaInstance.addNode(node.id, node);
        }
        
        /*
         * Edge loop
         */
        dataIdx = 0;
        while ((dataIdx = data.indexOf("edge", dataIdx)) > 0) {
            parIdx = data.indexOf("[", dataIdx) + 1;
            parCount = 1;
            while (parCount > 0) {
                switch (data.charAt(parIdx)) {
                case "[": parCount++; break;
                case "]": parCount--; break;
                }
                parIdx++;
            }
            
            dataStr = data.slice(dataIdx, parIdx);
            dataIdx++;
            
            edge = {};
            
            edgeAttrs.forEach(function(att) {
                m = att.regex.exec(dataStr);
                if (m) {
                    if (!m[1].match(/^".+"$/)) {
                        m[1] = parseFloat(m[1]);
                    } else{
                        m[1] = m[1].replace(/"/g, "");
                    }
                    edge[att.name] = m[1];
                }
            });
            
            edge.size = edge.weight;
            /* Just in case */
            edge.source = nodeIdMap[edge.source];
            edge.target = nodeIdMap[edge.target];
            
            try {
                sigmaInstance.addEdge(edge.id, edge.source, edge.target, edge);
            } catch (err) { console.log('edge error'); /* Meh... just discard an edge without a node */}
        }
        
        var end = new Date().getTime();
        var time = end - start;
        //console.log('Execution time: ' + time);
    }).always(function() { callback(); }).fail(function(e) { 
        console.log('failed', e);
    });
};
