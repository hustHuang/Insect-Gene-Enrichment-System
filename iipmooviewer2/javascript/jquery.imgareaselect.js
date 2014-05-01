/*
 * imgAreaSelect jQuery plugin
 * version 0.9.8
 *
 * Copyright (c) 2008-2011 Michal Wojciechowski (odyniec.net)
 *
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://odyniec.net/projects/imgareaselect/
 *
 */

(function($j) {

var abs = Math.abs,
    max = Math.max,
    min = Math.min,
    round = Math.round;

function div() {
    return $j('<div/>');
}

$j.imgAreaSelect = function (img, options) {
    var

        $jimg = $j(img),

        imgLoaded,

        $jbox = div(),
        $jarea = div(),
        $jborder = div().add(div()).add(div()).add(div()),
        $jouter = div().add(div()).add(div()).add(div()),
        $jhandles = $j([]),

        $jareaOpera,

        left, top,

        imgOfs = { left: 0, top: 0 },

        imgWidth, imgHeight,

        $jparent,

        parOfs = { left: 0, top: 0 },

        zIndex = 0,

        position = 'absolute',

        startX, startY,

        scaleX, scaleY,

        resize,

        minWidth, minHeight, maxWidth, maxHeight,

        aspectRatio,

        shown,

        x1, y1, x2, y2,

        selection = { x1: 0, y1: 0, x2: 0, y2: 0, width: 0, height: 0 },

        docElem = document.documentElement,

        $jp, d, i, o, w, h, adjusted;

    function viewX(x) {
        return x + imgOfs.left - parOfs.left;
    }

    function viewY(y) {
        return y + imgOfs.top - parOfs.top;
    }

    function selX(x) {
        return x - imgOfs.left + parOfs.left;
    }

    function selY(y) {
        return y - imgOfs.top + parOfs.top;
    }

    function evX(event) {
        return event.pageX - parOfs.left;
    }

    function evY(event) {
        return event.pageY - parOfs.top;
    }

    function getSelection(noScale) {
        var sx = noScale || scaleX, sy = noScale || scaleY;

        return { x1: round(selection.x1 * sx),
            y1: round(selection.y1 * sy),
            x2: round(selection.x2 * sx),
            y2: round(selection.y2 * sy),
            width: round(selection.x2 * sx) - round(selection.x1 * sx),
            height: round(selection.y2 * sy) - round(selection.y1 * sy) };
    }

    function setSelection(x1, y1, x2, y2, noScale) {
        var sx = noScale || scaleX, sy = noScale || scaleY;

        selection = {
            x1: round(x1 / sx || 0),
            y1: round(y1 / sy || 0),
            x2: round(x2 / sx || 0),
            y2: round(y2 / sy || 0)
        };

        selection.width = selection.x2 - selection.x1;
        selection.height = selection.y2 - selection.y1;
    }

    function adjust(){
        if (!$jimg.width())
            return;

        imgOfs = { left: round($jimg.offset().left), top: round($jimg.offset().top) };

        imgWidth = $jimg.innerWidth();
        imgHeight = $jimg.innerHeight();

        imgOfs.top += ($jimg.outerHeight() - imgHeight) >> 1;
        imgOfs.left += ($jimg.outerWidth() - imgWidth) >> 1;

        minWidth = round(options.minWidth / scaleX) || 0;
        minHeight = round(options.minHeight / scaleY) || 0;
        maxWidth = round(min(options.maxWidth / scaleX || 1<<24, imgWidth));
        maxHeight = round(min(options.maxHeight / scaleY || 1<<24, imgHeight));

        if ($j().jquery == '1.3.2' && position == 'fixed' &&
            !docElem['getBoundingClientRect'])
        {
            imgOfs.top += max(document.body.scrollTop, docElem.scrollTop);
            imgOfs.left += max(document.body.scrollLeft, docElem.scrollLeft);
        }

        parOfs = /absolute|relative/.test($jparent.css('position')) ?
            { left: round($jparent.offset().left) - $jparent.scrollLeft(),
                top: round($jparent.offset().top) - $jparent.scrollTop() } :
            position == 'fixed' ?
                { left: $j(document).scrollLeft(), top: $j(document).scrollTop() } :
                { left: 0, top: 0 };

        left = viewX(0);
        top = viewY(0);

        if (selection.x2 > imgWidth || selection.y2 > imgHeight)
            doResize();
    }

    function update(resetKeyPress) {
        if (!shown) return;

        $jbox.css({ left: viewX(selection.x1), top: viewY(selection.y1) })
            .add($jarea).width(w = selection.width).height(h = selection.height);

        $jarea.add($jborder).add($jhandles).css({ left: 0, top: 0 });

        $jborder
            .width(max(w - $jborder.outerWidth() + $jborder.innerWidth(), 0))
            .height(max(h - $jborder.outerHeight() + $jborder.innerHeight(), 0));

        $j($jouter[0]).css({ left: left, top: top,
            width: selection.x1, height: imgHeight });
        $j($jouter[1]).css({ left: left + selection.x1, top: top,
            width: w, height: selection.y1 });
        $j($jouter[2]).css({ left: left + selection.x2, top: top,
            width: imgWidth - selection.x2, height: imgHeight });
        $j($jouter[3]).css({ left: left + selection.x1, top: top + selection.y2,
            width: w, height: imgHeight - selection.y2 });

        w -= $jhandles.outerWidth();
        h -= $jhandles.outerHeight();

        switch ($jhandles.length) {
        case 8:
            $j($jhandles[4]).css({ left: w >> 1 });
            $j($jhandles[5]).css({ left: w, top: h >> 1 });
            $j($jhandles[6]).css({ left: w >> 1, top: h });
            $j($jhandles[7]).css({ top: h >> 1 });
        case 4:
            $jhandles.slice(1,3).css({ left: w });
            $jhandles.slice(2,4).css({ top: h });
        }

        if (resetKeyPress !== false) {
            if ($j.imgAreaSelect.keyPress != docKeyPress)
                $j(document).unbind($j.imgAreaSelect.keyPress,
                    $j.imgAreaSelect.onKeyPress);

            if (options.keys)
                $j(document)[$j.imgAreaSelect.keyPress](
                    $j.imgAreaSelect.onKeyPress = docKeyPress);
        }

        if ($j.browser.msie && $jborder.outerWidth() - $jborder.innerWidth() == 2) {
            $jborder.css('margin', 0);
            setTimeout(function () { $jborder.css('margin', 'auto'); }, 0);
        }
    }

    function doUpdate(resetKeyPress) {
        adjust();
        update(resetKeyPress);
        x1 = viewX(selection.x1); y1 = viewY(selection.y1);
        x2 = viewX(selection.x2); y2 = viewY(selection.y2);
    }

    function hide($jelem, fn) {
        options.fadeSpeed ? $jelem.fadeOut(options.fadeSpeed, fn) : $jelem.hide();

    }

    function areaMouseMove(event) {//选择区域鼠标移动
        var x = selX(evX(event)) - selection.x1,
            y = selY(evY(event)) - selection.y1;

        if (!adjusted) {
            adjust();
            adjusted = true;

            $jbox.one('mouseout', function () { adjusted = false; });
        }

        resize = '';

        if (options.resizable) {
            if (y <= options.resizeMargin)
                resize = 'n';
            else if (y >= selection.height - options.resizeMargin)
                resize = 's';
            if (x <= options.resizeMargin)
                resize += 'w';
            else if (x >= selection.width - options.resizeMargin)
                resize += 'e';
        }

        $jbox.css('cursor', resize ? resize + '-resize' :
            options.movable ? 'move' : '');
        if ($jareaOpera)
            $jareaOpera.toggle();
    }

    function docMouseUp(event) {
        $j('body').css('cursor', '');
        if (options.autoHide || selection.width * selection.height == 0)
            hide($jbox.add($jouter), function () { $j(this).hide(); });

        $j(document).unbind('mousemove', selectingMouseMove);
        $jbox.mousemove(areaMouseMove);

        options.onSelectEnd(img, getSelection());
    }

    function areaMouseDown(event) {
        if (event.which != 1) return false;

        adjust();

        if (resize) {
            $j('body').css('cursor', resize + '-resize');

            x1 = viewX(selection[/w/.test(resize) ? 'x2' : 'x1']);
            y1 = viewY(selection[/n/.test(resize) ? 'y2' : 'y1']);

            $j(document).mousemove(selectingMouseMove)
                .one('mouseup', docMouseUp);
            $jbox.unbind('mousemove', areaMouseMove);
        }
        else if (options.movable) {
            startX = left + selection.x1 - evX(event);
            startY = top + selection.y1 - evY(event);

            $jbox.unbind('mousemove', areaMouseMove);

            $j(document).mousemove(movingMouseMove)
                .one('mouseup', function () {
                    options.onSelectEnd(img, getSelection());

                    $j(document).unbind('mousemove', movingMouseMove);
                    $jbox.mousemove(areaMouseMove);
                });
        }
        else
            $jimg.mousedown(event);

        return false;
    }

    function fixAspectRatio(xFirst) {
        if (aspectRatio)
            if (xFirst) {
                x2 = max(left, min(left + imgWidth,
                    x1 + abs(y2 - y1) * aspectRatio * (x2 > x1 || -1)));

                y2 = round(max(top, min(top + imgHeight,
                    y1 + abs(x2 - x1) / aspectRatio * (y2 > y1 || -1))));
                x2 = round(x2);
            }
            else {
                y2 = max(top, min(top + imgHeight,
                    y1 + abs(x2 - x1) / aspectRatio * (y2 > y1 || -1)));
                x2 = round(max(left, min(left + imgWidth,
                    x1 + abs(y2 - y1) * aspectRatio * (x2 > x1 || -1))));
                y2 = round(y2);
            }
    }

    function doResize() {
        x1 = min(x1, left + imgWidth);
        y1 = min(y1, top + imgHeight);

        if (abs(x2 - x1) < minWidth) {
            x2 = x1 - minWidth * (x2 < x1 || -1);

            if (x2 < left)
                x1 = left + minWidth;
            else if (x2 > left + imgWidth)
                x1 = left + imgWidth - minWidth;
        }

        if (abs(y2 - y1) < minHeight) {
            y2 = y1 - minHeight * (y2 < y1 || -1);

            if (y2 < top)
                y1 = top + minHeight;
            else if (y2 > top + imgHeight)
                y1 = top + imgHeight - minHeight;
        }

        x2 = max(left, min(x2, left + imgWidth));
        y2 = max(top, min(y2, top + imgHeight));

        fixAspectRatio(abs(x2 - x1) < abs(y2 - y1) * aspectRatio);

        if (abs(x2 - x1) > maxWidth) {
            x2 = x1 - maxWidth * (x2 < x1 || -1);
            fixAspectRatio();
        }

        if (abs(y2 - y1) > maxHeight) {
            y2 = y1 - maxHeight * (y2 < y1 || -1);
            fixAspectRatio(true);
        }

        selection = { x1: selX(min(x1, x2)), x2: selX(max(x1, x2)),
            y1: selY(min(y1, y2)), y2: selY(max(y1, y2)),
            width: abs(x2 - x1), height: abs(y2 - y1) };

        update();

        options.onSelectChange(img, getSelection());
        
    }

    function selectingMouseMove(event) {
        x2 = /w|e|^$/.test(resize) || aspectRatio ? evX(event) : viewX(selection.x2);
        y2 = /n|s|^$/.test(resize) || aspectRatio ? evY(event) : viewY(selection.y2);

        doResize();

        return false;

    }

    function doMove(newX1, newY1) {
        x2 = (x1 = newX1) + selection.width;
        y2 = (y1 = newY1) + selection.height;

        $j.extend(selection, { x1: selX(x1), y1: selY(y1), x2: selX(x2),
            y2: selY(y2) });

        update();

        options.onSelectChange(img, getSelection());
    }

    function movingMouseMove(event) {
        x1 = max(left, min(startX + evX(event), left + imgWidth - selection.width));
        y1 = max(top, min(startY + evY(event), top + imgHeight - selection.height));

        doMove(x1, y1);

        event.preventDefault();

        return false;
    }

    function startSelection() {
        $j(document).unbind('mousemove', startSelection);
        adjust();

        x2 = x1;
        y2 = y1;

        doResize();

        resize = '';

        if (!$jouter.is(':visible'))
            $jbox.add($jouter).hide().fadeIn(options.fadeSpeed||0);

        shown = true;

        $j(document).unbind('mouseup', cancelSelection)
            .mousemove(selectingMouseMove).one('mouseup', docMouseUp);
        $jbox.unbind('mousemove', areaMouseMove);

        options.onSelectStart(img, getSelection());
    }

    function cancelSelection() {
        $j(document).unbind('mousemove', startSelection)
            .unbind('mouseup', cancelSelection);
        hide($jbox.add($jouter));

        setSelection(selX(x1), selY(y1), selX(x1), selY(y1));

        if (!this instanceof $j.imgAreaSelect) {
            options.onSelectChange(img, getSelection());
            options.onSelectEnd(img, getSelection());
        }
    }

    function imgMouseDown(event) {
        if (event.which !=3|| $jouter.is(':animated')) return false;

        adjust();
        startX = x1 = evX(event);
        startY = y1 = evY(event);

        $j(document).bind("contextmenu", function(e){
            if (!selection.width || !selection.height)
              return;
          //alert("Location: (" + selection.x1 + "," + selection.y1 + "),(" + selection.x2 + "," + selection.y1 + "),(" + selection.x1 + "," + selection.y2 + "),(" + selection.x2 + "," + selection.y2 + ")");
          return false;
        }).mousemove(startSelection).mouseup(cancelSelection);

        return false;
    }

    function windowResize() {
        doUpdate(false);
    }

    function imgLoad() {
        imgLoaded = true;

        setOptions(options = $j.extend({
            classPrefix: 'imgareaselect',
            movable: true,
            parent: 'body',
            resizable: true,
            resizeMargin: 10,
            onInit: function () {},
            onSelectStart: function () {},
            onSelectChange: function () {},
            onSelectEnd: function () {}
        }, options));

        $jbox.add($jouter).css({ visibility: '' });

        if (options.show) {
            shown = true;
            adjust();
            update();
            $jbox.add($jouter).hide().fadeIn(options.fadeSpeed||0);
        }

        setTimeout(function () { options.onInit(img, getSelection()); }, 0);
    }

    var docKeyPress = function(event) {
        var k = options.keys, d, t, key = event.keyCode;

        d = !isNaN(k.alt) && (event.altKey || event.originalEvent.altKey) ? k.alt :
            !isNaN(k.ctrl) && event.ctrlKey ? k.ctrl :
            !isNaN(k.shift) && event.shiftKey ? k.shift :
            !isNaN(k.arrows) ? k.arrows : 10;

        if (k.arrows == 'resize' || (k.shift == 'resize' && event.shiftKey) ||
            (k.ctrl == 'resize' && event.ctrlKey) ||
            (k.alt == 'resize' && (event.altKey || event.originalEvent.altKey)))
        {
            switch (key) {
            case 37:
                d = -d;
            case 39:
                t = max(x1, x2);
                x1 = min(x1, x2);
                x2 = max(t + d, x1);
                fixAspectRatio();
                break;
            case 38:
                d = -d;
            case 40:
                t = max(y1, y2);
                y1 = min(y1, y2);
                y2 = max(t + d, y1);
                fixAspectRatio(true);
                break;
            default:
                return;
            }

            doResize();
        }
        else {
            x1 = min(x1, x2);
            y1 = min(y1, y2);

            switch (key) {
            case 37:
                doMove(max(x1 - d, left), y1);
                break;
            case 38:
                doMove(x1, max(y1 - d, top));
                break;
            case 39:
                doMove(x1 + min(d, imgWidth - selX(x2)), y1);
                break;
            case 40:
                doMove(x1, y1 + min(d, imgHeight - selY(y2)));
                break;
            default:
                return;
            }
        }

        return false;
    };

    function styleOptions($jelem, props) {
        for (option in props)
            if (options[option] !== undefined)
                $jelem.css(props[option], options[option]);
    }

    function setOptions(newOptions) {
        if (newOptions.parent)
            ($jparent = $j(newOptions.parent)).append($jbox.add($jouter));

        $j.extend(options, newOptions);

        adjust();

        if (newOptions.handles != null) {
            $jhandles.remove();
            $jhandles = $j([]);

            i = newOptions.handles ? newOptions.handles == 'corners' ? 4 : 8 : 0;

            while (i--)
                $jhandles = $jhandles.add(div());

            $jhandles.addClass(options.classPrefix + '-handle').css({
                position: 'absolute',
                fontSize: 0,
                zIndex: zIndex + 1 || 1
            });

            if (!parseInt($jhandles.css('width')) >= 0)
                $jhandles.width(5).height(5);

            if (o = options.borderWidth)
                $jhandles.css({ borderWidth: o, borderStyle: 'solid' });

            styleOptions($jhandles, { borderColor1: 'border-color',
                borderColor2: 'background-color',
                borderOpacity: 'opacity' });
        }

        scaleX = options.imageWidth / imgWidth || 1;
        scaleY = options.imageHeight / imgHeight || 1;

        if (newOptions.x1 != null) {
            setSelection(newOptions.x1, newOptions.y1, newOptions.x2,
                newOptions.y2);
            newOptions.show = !newOptions.hide;
        }

        if (newOptions.keys)
            options.keys = $j.extend({ shift: 1, ctrl: 'resize' },
                newOptions.keys);

        $jouter.addClass(options.classPrefix + '-outer');
        $jarea.addClass(options.classPrefix + '-selection');
        for (i = 0; i++ < 4;)
            $j($jborder[i-1]).addClass(options.classPrefix + '-border' + i);

        styleOptions($jarea, { selectionColor: 'background-color',
            selectionOpacity: 'opacity' });
        styleOptions($jborder, { borderOpacity: 'opacity',
            borderWidth: 'border-width' });
        styleOptions($jouter, { outerColor: 'background-color',
            outerOpacity: 'opacity' });
        if (o = options.borderColor1)
            $j($jborder[0]).css({ borderStyle: 'solid', borderColor: o });
        if (o = options.borderColor2)
            $j($jborder[1]).css({ borderStyle: 'dashed', borderColor: o });

        $jbox.append($jarea.add($jborder).add($jareaOpera).add($jhandles));

        if ($j.browser.msie) {
            if (o = $jouter.css('filter').match(/opacity=(\d+)/))
                $jouter.css('opacity', o[1]/100);
            if (o = $jborder.css('filter').match(/opacity=(\d+)/))
                $jborder.css('opacity', o[1]/100);
        }

        if (newOptions.hide)
            hide($jbox.add($jouter));
        else if (newOptions.show && imgLoaded) {
            shown = true;
            $jbox.add($jouter).fadeIn(options.fadeSpeed||0);
            doUpdate();
        }

        aspectRatio = (d = (options.aspectRatio || '').split(/:/))[0] / d[1];

        $jimg.add($jouter).unbind('mousedown', imgMouseDown);

        if (options.disable || options.enable === false) {
            $jbox.unbind('mousemove', areaMouseMove).unbind('mousedown', areaMouseDown);
            $j(window).unbind('resize', windowResize);
        }
        else {
            if (options.enable || options.disable === false) {
                if (options.resizable || options.movable)
                    $jbox.mousemove(areaMouseMove).mousedown(areaMouseDown);

                $j(window).resize(windowResize);
            }

            if (!options.persistent)
                $jimg.add($jouter).mousedown(imgMouseDown);
        }

        options.enable = options.disable = undefined;
    }

    this.remove = function () {
        setOptions({ disable: true });
        $jbox.add($jouter).remove();
    };

    this.getOptions = function () { return options; };

    this.setOptions = setOptions;

    this.getSelection = getSelection;

    this.setSelection = setSelection;

    this.cancelSelection = cancelSelection;

    this.update = doUpdate;

    $jp = $jimg;

    while ($jp.length) {
        zIndex = max(zIndex,
            !isNaN($jp.css('z-index')) ? $jp.css('z-index') : zIndex);
        if ($jp.css('position') == 'fixed')
            position = 'fixed';

        $jp = $jp.parent(':not(body)');
    }

    zIndex = options.zIndex || zIndex;

    if ($j.browser.msie)
        $jimg.attr('unselectable', 'on');

    $j.imgAreaSelect.keyPress = $j.browser.msie ||
        $j.browser.safari ? 'keydown' : 'keypress';

    if ($j.browser.opera)
        $jareaOpera = div().css({ width: '100%', height: '100%',
            position: 'absolute', zIndex: zIndex + 2 || 2 });

    $jbox.add($jouter).css({ visibility: 'hidden', position: position,
        overflow: 'hidden', zIndex: zIndex || '0' });
    $jbox.css({ zIndex: zIndex + 2 || 2 });
    $jarea.add($jborder).css({ position: 'absolute', fontSize: 0 });

    img.complete || img.readyState == 'complete' || !$jimg.is('img') ?
        imgLoad() : $jimg.one('load', imgLoad);

   if ($j.browser.msie && $j.browser.version >= 7)
        img.src = img.src;
};

$j.fn.imgAreaSelect = function (options) {
    options = options || {};

    this.each(function () {
        if ($j(this).data('imgAreaSelect')) {
            if (options.remove) {
                $j(this).data('imgAreaSelect').remove();
                $j(this).removeData('imgAreaSelect');
            }
            else
                $j(this).data('imgAreaSelect').setOptions(options);
        }
        else if (!options.remove) {
            if (options.enable === undefined && options.disable === undefined)
                options.enable = true;

            $j(this).data('imgAreaSelect', new $j.imgAreaSelect(this, options));
        }
    });

    if (options.instance)
        return $j(this).data('imgAreaSelect');

    return this;
};

})(jQuery);
