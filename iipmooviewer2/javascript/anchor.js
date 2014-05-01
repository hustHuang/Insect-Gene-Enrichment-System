function newAnchor(x, y){
    var html = '<div class="anchor_point" id="anchor_' + x + '_' + y + '"></div>';
    $('.canvas').append(html);
    $('.canvas #anchor_' + x + '_' + y).css({
        left: x,
        top: y
    });
}

function createAnchor(){
    
}
