// Mathieu Jacomy @ Sciences Po Médialab & WebAtlas
// (requires sigma.js to be loaded)
sigma.forcelayout = sigma.forcelayout || {};
sigma.forcelayout.ForceLayout = function(graph) {
    sigma.classes.Cascade.call(this);
    var self = this;
    this.graph = graph;

    var EPSILON = 0.000001;
    var attraction_constant;
    var repulsion_constant;
    var forceConstant;
    var layout_iterations = 0;
    var temperature = 0;

    // performance test
    var mean_time = 0;

    this.p = {
        attraction_multiplier : 5,
        repulsion_multiplier : 0.75,
        max_iterations : 1000,
        width : 1000,
        height : 1000,
        finished : false,
        nodes : this.graph.nodes.filter(function(n) {
            return !n.hidden;
        }),
        edges : this.graph.edges.filter(function(e) {
            return !e.source.hidden && !e.target.hidden;
        })
    };
    
    this.init = function() {
//        var xmin = 0, xmax = 0, ymin = 0, ymax = 0;
//        self.graph.nodes.forEach(function (node) {
//            xmin = Math.min(xmin, node.x);
//            xmax = Math.max(xmax, node.x);
//            ymin = Math.min(ymin, node.y);
//            ymax = Math.max(ymax, node.y);
//        });
//        self.p.width = Math.abs(xmax - xmin);
//        self.p.height = Math.abs(ymax - ymin);
        
        self.p.width = self.p.nodes.length * 15;
        self.p.height = self.p.nodes.length * 15;
        
        self.p.finished = false;
        temperature = self.p.width / 10.0;
        nodes_length = self.p.nodes.length;
        edges_length = self.p.edges.length;
        forceConstant = Math.sqrt(self.p.height * self.p.width / nodes_length);
        attraction_constant = self.p.attraction_multiplier * forceConstant;
        repulsion_constant = self.p.repulsion_multiplier * forceConstant;

        self.graph.nodes.forEach(function(n) {
            n.layout = {
                offset_x : 0,
                offset_y : 0,
                force : 0,
                tmp_pos_x : n.x,
                tmp_pos_y : n.y
            };
        });

        return self;
    }

    this.go = function() {
        while (self.atomicGo()) {
        }
    }

    this.atomicGo = function() {
        var graph = self.graph;
        var p = self.p;
        var nodes = p.nodes;
        var edges = p.edges;

        var start = new Date().getTime();

        var delta_x, delta_y, delta_length, force;

        // calculate repulsion
        for ( var i = 0; i < nodes_length; i++) {
            var node_v = nodes[i];
            node_v.layout = node_v.layout || {};
            if (i == 0) {
                node_v.layout.offset_x = 0;
                node_v.layout.offset_y = 0;
            }

            node_v.layout.force = 0;
            node_v.layout.tmp_pos_x = node_v.layout.tmp_pos_x || node_v.x;
            node_v.layout.tmp_pos_y = node_v.layout.tmp_pos_y || node_v.y;

            for ( var j = i + 1; j < nodes_length; j++) {
                var node_u = nodes[j];
                if (i != j) {
                    node_u.layout = node_u.layout || {};
                    node_u.layout.tmp_pos_x = node_u.layout.tmp_pos_x || node_u.x;
                    node_u.layout.tmp_pos_y = node_u.layout.tmp_pos_y || node_u.y;

                    delta_x = node_v.layout.tmp_pos_x - node_u.layout.tmp_pos_x;
                    delta_y = node_v.layout.tmp_pos_y - node_u.layout.tmp_pos_y;
                    delta_length = Math.max(EPSILON, Math.sqrt((delta_x * delta_x) + (delta_y * delta_y)));
                    force = (repulsion_constant * repulsion_constant) / delta_length;

                    node_v.layout.force += force;
                    node_u.layout.force += force;

                    node_v.layout.offset_x += (delta_x / delta_length) * force;
                    node_v.layout.offset_y += (delta_y / delta_length) * force;

                    if (i == 0) {
                        node_u.layout.offset_x = 0;
                        node_u.layout.offset_y = 0;
                    }
                    node_u.layout.offset_x -= (delta_x / delta_length) * force;
                    node_u.layout.offset_y -= (delta_y / delta_length) * force;

                }
            }
        }

        // calculate attraction
        edges.forEach(function(edge) {
            delta_x = edge.source.layout.tmp_pos_x - edge.target.layout.tmp_pos_x;
            delta_y = edge.source.layout.tmp_pos_y - edge.target.layout.tmp_pos_y;

            delta_length = Math.max(EPSILON, Math.sqrt((delta_x * delta_x) + (delta_y * delta_y)));
            force = ((delta_length * delta_length) / attraction_constant) * (edge.weight * 10);

            edge.source.layout.force -= force;
            edge.target.layout.force += force;

            edge.source.layout.offset_x -= (delta_x / delta_length) * force;
            edge.source.layout.offset_y -= (delta_y / delta_length) * force;

            edge.target.layout.offset_x += (delta_x / delta_length) * force;
            edge.target.layout.offset_y += (delta_y / delta_length) * force;
        });

        // calculate positions
        nodes.forEach(function(node) {
            delta_length = Math.max(EPSILON, Math.sqrt(node.layout.offset_x * node.layout.offset_x
                    + node.layout.offset_y * node.layout.offset_y));

            node.layout.tmp_pos_x += (node.layout.offset_x / delta_length) * Math.min(delta_length, temperature);
            node.layout.tmp_pos_y += (node.layout.offset_y / delta_length) * Math.min(delta_length, temperature);

            node.x -= (node.x - node.layout.tmp_pos_x) / 10;
            node.y -= (node.y - node.layout.tmp_pos_y) / 10;
        });

        temperature *= (1 - (layout_iterations / p.max_iterations));
        layout_iterations++;

        var end = new Date().getTime();
        mean_time += end - start;
    }

    this.end = function() {
        this.graph.nodes.forEach(function(n) {
            n.layout = null;
        });
    }
};

sigma.publicPrototype.startForceLayout = function() {
    this.forcelayout = new sigma.forcelayout.ForceLayout(this._core.graph);
    this.forcelayout.init();

    this.addGenerator('forcelayout', this.forcelayout.atomicGo, function() {
        return true;
    });
};

sigma.publicPrototype.stopForceLayout = function() {
    this.removeGenerator('forcelayout');
};
