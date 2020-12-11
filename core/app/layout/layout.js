window.addEvent('load', function() {

    // set some properties used by all nave objects
    window.layout_properties = {
        'opened_layer': false,
        'mdl_preview_toggles': Array(),
        'navs_obj': Array()
    }

    // foreach block create nave objects
    $$('body > div[id^=block_]').each(function(b) {

        b.getChildren('div[id^=nav_]').each(function(n) {
            layout_properties.navs_obj[n.id] = new Nave(n, b);    
        });
    });

})

var Nave = new Class({

    initialize: function(el, b) {

        // define block id and nav element
        this.block = b;
        this.nav = el;

        // nav width type (%|px)and value
        this.width_um = this.getUm();
        this.width_value = this.nav.style.width=='' ? 100 : this.nav.style.width.toInt();


        // define all nave controllers
        this.fine_less_width_ctrl = this.nav.getChildren('div[class=navCtrl]')[0]
                         .getChildren('div[class=right]')[0]
                         .getChildren('div[class=fineLessWidthCtrl]')[0];

        this.fine_more_width_ctrl = this.nav.getChildren('div[class=navCtrl]')[0]
                         .getChildren('div[class=right]')[0]
                         .getChildren('div[class=fineMoreWidthCtrl]')[0];

        this.width_ctrl = this.nav.getChildren('div[class=navSizeCtrl]')[0]
                         .getChildren('div[class=widthCtrl]')[0];

        this.dispose_ctrl = this.nav.getChildren('div[class=navCtrl]')[0]
                         .getChildren('div[class=right]')[0]
                         .getChildren('div[class=disposeCtrl]')[0];

        this.float_ctrl = this.nav.getChildren('div[class=navCtrl]')[0]
                         .getChildren('div[class=right]')[0]
                         .getChildren('div[class=floatCtrl]')[0];

        // set nave width
        this.width_display = this.nav.getChildren('div[class=navCtrl]')[0]
                        .getChildren('div[class=left]')[1]
                        .getChildren('span')[0];                 

        this.width_display.set('text', this.width_value+this.width_um);

        // define the container of the sortables elements (mdlContainers)
        this.sortables_container = this.nav.getChildren('div[id=sortables_'+this.nav.id+']')[0];

        // get the modules containers
        this.updateMdlContainers();

        // set the internal counter over modules in order to not duplicate div ids
        this.iter_modules = this.mdl_containers.length-1;

        // init nave events
        this.initNaveEvents();

        // set dinamic mdlContainers events
        this.updateMdlEvents();

        // set as sortables the modules containers
        this.initSortables();

    },
    getUm: function() {
        return /px$/.test(this.nav.style.width) ? 'px' : '%';

    },
    updateMdlContainers: function() {
        this.mdl_containers = this.sortables_container.getChildren('div[id^=mdlContainer_'+this.nav.id+']');              
    },
    initNaveEvents: function() {

        // fine width regulation controllers
        this.fine_less_width_ctrl.addEvent('click', this.clickFineWidthCtrl.bind(this, -1));    
        this.fine_more_width_ctrl.addEvent('click', this.clickFineWidthCtrl.bind(this, 1));    

        // mousemove width regulation
        this.width_ctrl.addEvent('mousedown', this.mousedownWidthCtrl.bind(this));

        // nave dispose controller
        this.dispose_ctrl.addEvent('click', this.clickDisposeCtrl.bind(this));    

        // nave float property control
        this.float_ctrl.addEvent('click', this.clickFloatCtrl.bind(this));    

    },
    clickFineWidthCtrl: function(dx) {
        var nav_width = this.nav.getStyle('width').toInt() + dx;
        this.updateWidth(nav_width);
    },
    mousedownWidthCtrl: function(evt) {
        this.init_move_x = evt.page.x;
        this.init_move_width = this.nav.getStyle('width').toInt();    
        document.addEvent('mousemove', this.mousemoveWidthCtrl.bind(this));
        document.addEvent('mouseup', this.mouseupWidthCtrl.bind(this));
        // cancel out any text selections 
        document.body.focus();
               // prevent text selection in IE 
        document.onselectstart = function () { return false; }; 
        // prevent IE from trying to drag an image 
        this.nav.ondragstart = function() { return false; }; 
        // prevent text selection (except IE) 
        return false; 

    },
    mousemoveWidthCtrl: function(evt) {

        if(!this.prev_evt_x) this.prev_evt_x = this.init_move_x;

        if(this.width_um == 'px')
            var new_width = this.init_move_width + (this.nav.getStyle('float')=='right' ? this.init_move_x-evt.page.x : evt.page.x-this.init_move_x);
        else
            var new_width = this.init_move_width + Math.round(100*(this.nav.getStyle('float')=='right' ? this.init_move_x-evt.page.x : evt.page.x-this.init_move_x)/document.body.getCoordinates().width);

        this.updateWidth(new_width);

    },
    mouseupWidthCtrl: function(evt) {
        document.removeEvents('mousemove');
    },
    updateWidth: function(width) {
        width = width < 0 
            ? 0 
            : (this.block.style.width
                ? (width > this.block.style.width.toInt() 
                    ? this.block.style.width.toInt() 
                    : width)
                : (width > 100 
                    ? 100 
                    : width));
        this.nav.setStyle('width', width+this.width_um);
        this.width_display.set('text', width+this.width_um);
    },
    clickDisposeCtrl: function() {
        layout_properties.opened_layer ? '' : this.nav.dispose();          
    },
    clickFloatCtrl: function(evt) {

        if(layout_properties.opened_layer) return false;
        layout_properties.opened_layer = true;

        this.float_layer = new Element('div', {'class':'floatLayer'});
        this.float_layer.setStyles({'width': this.nav.getStyle('width'), 'opacity':0});

        var navid_part = this.nav.id.substr(0, this.nav.id.lastIndexOf('_'));

        var more_cols = $$('div[id^='+navid_part+'_]').length > 1 ? true : false;

        var div_title = new Element('div', {'html': 'float'});

        var p = new Element('p');
        var button_left = new Element('button', {
                'text': 'left', 'style':'margin-right:5px', 
                'class':this.nav.getStyle('float')=='left' ? 'selected':''});
        var button_center = new Element('button', {
                'text': 'center', 'style':'margin-right:5px', 
                'class':this.nav.getStyle('margin')=='auto' ? 'selected':''});
        var button_right = new Element('button', {
                'text': 'right', 'class':this.nav.getStyle('float')=='right' ? 'selected':''});

        var buttons = more_cols ? [button_left, button_right] : [button_left, button_center, button_right];

        var self = this;
        $$(buttons).addEvent('click', function() {
            self.updateFloat($(this))    
        });

        p.adopt(buttons);

        this.float_layer.adopt(div_title, p);

        this.float_layer.inject(this.nav, 'top');

        layer_effect = new Fx.Tween(this.float_layer, {'duration':500}).start('opacity', '0.9');
        document.addEvent('mousedown', checkCloseLayer.bind(this.float_layer));
    },
    updateFloat: function(btn) {
        if(btn.get('text')=='center') this.nav.setStyles({'float':'none', 'margin':'auto'});
        else this.nav.setStyles({'float': btn.get('text'), 'margin': '0'});

        layer_effect = new Fx.Tween(this.float_layer, {'duration':500});
        layer_effect.start('opacity', '0').chain(function() {this.float_layer.dispose();}.bind(this));

        layout_properties.opened_layer = false;
    },
    updateMdlEvents: function() {
        this.mdl_containers.each(function(el) {
            this.mdlContainerEvents(el);    
        }.bind(this));
    },
    mdlContainerEvents: function(mdl_container) {
        var refillable = $(mdl_container.id.replace("mdlContainer", "refillable"));
        var fill = $(refillable.id.replace("refillable", "fill"));
        refillable.removeEvents('click');
        refillable.addEvent('click', this.clickFill.bind(this, refillable));

        if(refillable.getProperty('class') == 'refillableFilled') {
            mdl_container.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=disposeMdl]')[0].removeEvents('click');
            //mdl_container.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=toggleMdl]')[0].removeEvents('click');
            mdl_container.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=disposeMdl]')[0].addEvent('click', function(event) {
                mdl_container.dispose();
                event.stopPropagation();    
            })
            //mdl_container.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=toggleMdl]')[0].addEvent('click', function(event) {
            //    event.stopPropagation();    
            //    this.mdlPreviewToggle(fill.id);
            //}.bind(this));

        }

    },
    clickFill: function(refillable) {

        if(window.myWin && window.myWin.showing) return false;
        refillable.addClass("selectedMdlContainer");

        var url = 'layout/modulesList/?nav_id='+this.nav.id+'&refillable_id='+refillable.id+'&fill_id='+refillable.id.replace("refillable", "fill");

        window.myWin = new gino.layerWindow({
                'title':$('selMdlTitle').value, 'url':url, 'bodyId':'selMdl', 
                'width':800, 'height':500, 'destroyOnClose':true, 
                'closeCallback':this.unselectMdlContainer, 'closeCallbackParam':refillable});

    var viewport = gino.getViewport();
        window.myWin.display(this.nav, {'left':viewport.cX-350, 'top':viewport.cY-250});

    },
    unselectMdlContainer: function(refillable) {
        refillable.removeClass('selectedMdlContainer');
    },
    mdlPreviewToggle: function(fill_id) {
        pdisplay = ($(fill_id).getStyle('display')=='none')?"block":"none";
        $(fill_id).setStyle('display', pdisplay);
        return false;
    },
    initSortables: function() {
        this.sortable_inst = new Sortables('#'+this.sortables_container.id, {
                        constrain: true,
                        handle: 'div[class^=sortMdl]',
                        clone: true 
                    }
        );
    }

})

function checkCloseLayer(evt) {
    if(evt.page.x<this.getCoordinates().left || evt.page.x>this.getCoordinates().right 
       || evt.page.y<this.getCoordinates().top || evt.page.y>this.getCoordinates().bottom) {
        this.dispose();
        layout_properties.opened_layer = false;
        document.removeEvents('mousedown');
    }
}

function closeAll(nav_id, refillable_id, mdl_title, mdl_code) {

    var refillable = $(refillable_id);
    var action = refillable.get('text') == '' ? "new" : "modify"; 
    var mdl_container = $(refillable.id.replace("refillable", "mdlContainer"));
    var nav_obj = window.layout_properties.navs_obj[nav_id];
    var fill = $(refillable.id.replace("refillable", "fill"));

    // increment internal counter
    if(action=='new') nav_obj.iter_modules++;

    // new elements ids
    var new_refillable_id = refillable_id.replace(/[0-9]*$/, nav_obj.iterModules);
    var new_fill_id = new_refillable_id.replace("refillable", "fill");
    var new_mdl_container_id = new_refillable_id.replace("refillable", "mdlContainer");

    // dispose window
    window.myWin.closeWindow();

    // html to insert in refillable div
    html = "<input type=\"hidden\" name=\"navElement\" value=\""+mdl_code+"\" /><div>"+mdl_title+"</div>";
    refillable.set('html', html);
    refillable.setProperty('class', 'refillableFilled');
    if(action=='new') mdl_container.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=sortMdlDisabled]')[0].setProperties({'class': 'sortMdl', 'title':'ordina'});
    if(action=='new') {
        //mdl_container.getChildren('div[class=mdlContainerCtrl]')[0]
        //        .getChildren('div[class=toggleMdlDisabled]')[0]
        //        .setProperties({'class': 'toggleMdl', 'title': 'vedi/nascondi contenuti'});
        mdl_container.getChildren('div[class=mdlContainerCtrl]')[0]
                .getChildren('div[class=disposeMdlDisabled]')[0]
                .setProperties({'class': 'disposeMdl', 'title': 'elimina modulo'});
    }
    // creating new refillable and fill elements
    if(action=='new') {

        mdlc = new Element('div', {'id':new_mdl_container_id});
        mdlc.inject(nav_obj.sortables_container, 'bottom');

        mdlc_html = "<div class=\"mdlContainerCtrl\">";
        mdlc_html += "<div class=\"disposeMdlDisabled\"></div>";
        mdlc_html += "<div class=\"sortMdlDisabled\"></div>";
        //mdlc_html += "<div class=\"toggleMdlDisabled\"></div>";
        mdlc_html += "<div class=\"null\"></div>";
        mdlc_html += "</div>";

        mdlc.set('html', mdlc_html);
        ref = new Element('div', {'id':new_refillable_id, 'class':'refillable'});
        ref.inject(mdlc, 'bottom');
        // creating new mdl preview container
        f = new Element('div', {'id':new_fill_id, 'style':'display:none;'});
        f.inject(mdlc, 'bottom');

        nav_obj.updateMdlContainers();
        nav_obj.sortable_inst.addItems(mdlc);

    }
    nav_obj.updateMdlEvents();

    window.layout_properties.openedLayer = false;
}

function saveTemplate() {

    var text = '';
    var blocks = $$('body > div[id^=block_]');

    for(var i=1; i<blocks.length+1; i++) {

        var block = blocks[i-1];

        var prev_nave_row = null;
        text += '<div id="block_'+i+'" style="'+block.get('style')+'">\r\n';

        var naves = $$('#'+block.id+' div[id^=nav_]');
        naves.each(function(nave) {
            var matches = /nav_[0-9]+_[0-9]+_/.exec(nave.id);
            var nave_row = matches[0];
            var mcodes = Array();
            var mdls = nave.getChildren('div')[1].getChildren('div');
            for(var i=0; i<mdls.length; i++) {
                var mdl = mdls[i];
                var mdlcode = typeof mdl.getChildren('div')[1].getChildren('input[type=hidden]')[0] != 'undefined'
                    ? mdl.getChildren('div')[1].getChildren('input[type=hidden]')[0].value
                    : null;
                mcodes[i] = mdlcode;
            }
            if((nave_row != prev_nave_row) && prev_nave_row) 
                text += "<div class=\"null\"></div>\r\n";

            text += "<div id=\""+nave.id+"\"";
            if(nave.style.float || nave.style.width) {
                text += " style=\"";
                if(nave.style.cssFloat) text += "float:"+nave.style.cssFloat+";";
                if(nave.style.width) text += "width:"+nave.style.width+";";
                if(nave.style.margin) text += "margin:"+nave.style.margin+";";
                text += "\"";
            }
            text += ">\r\n";

            for(var i=0; i<mcodes.length; i++)
                if(mcodes[i]) text += mcodes[i]+'\r\n';

            text += "</div>\r\n";
            prev_nave_row = nave_row;
        })

        text += "<div class=\"null\"></div>\r\n";
        text += "</div>\r\n";
    }

    text += "<div class=\"null\"></div>\r\n";

    $('tplform_text').value = text;
    $('tplform').submit();

}
