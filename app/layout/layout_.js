window.addEvent('domready', function() {

	window.blocks = new Array();
	window.blocksWidths = new Array(); //TODO remove it set as a property of nave
	window.navsObj = new Array();

	$$('div[id^=block_]').each(function(b) {
		var block_id = b.get('id').substring(6);
		window.blocks[block_id] = b;
		//window.blocksWidths[block_id] = b.getStyle('width').toInt();
		window.blocksWidths[block_id] = b.getCoordinates().width;
		
		window.openedLayer = false;
	
		b.getChildren('div[id^=nav_]').each(function(el) {
			window.navsObj[el.id] = new Nave(el, block_id);	
		});
	});
	
	window.mdlPreviewToggles = Array();
		
})

var Nave = new Class({

	initialize: function(el, block_id) {
		// define block id
		this.block_id = block_id;
		// TODO is a % or px width??????????????????????????
		// define nav container and its width
		this.nav = el;
		this.navWidth = this.nav.getStyle('width').toInt();
		// define all nave controllers
		this.fineLessWidthCtrl = this.nav.getChildren('div[class=navCtrl]')[0]
					     .getChildren('div[class=right]')[0]
					     .getChildren('div[class=fineLessWidthCtrl]')[0];

		this.fineMoreWidthCtrl = this.nav.getChildren('div[class=navCtrl]')[0]
					     .getChildren('div[class=right]')[0]
					     .getChildren('div[class=fineMoreWidthCtrl]')[0];
		
		this.widthCtrl = this.nav.getChildren('div[class=navSizeCtrl]')[0]
					     .getChildren('div[class=widthCtrl]')[0];

		this.disposeCtrl = this.nav.getChildren('div[class=navCtrl]')[0]
					     .getChildren('div[class=right]')[0]
					     .getChildren('div[class=disposeCtrl]')[0];

		this.floatCtrl = this.nav.getChildren('div[class=navCtrl]')[0]
					     .getChildren('div[class=right]')[0]
					     .getChildren('div[class=floatCtrl]')[0];

		// set nave width
		this.widthDisplay = this.nav.getChildren('div[class=navCtrl]')[0]
					    .getChildren('div[class=left]')[1]
					    .getChildren('span')[0];			     

		this.widthDisplay.set('text', this.navWidth);

		// define the container of the sortables elements (mdlContainers)
		this.sortablesContainer = this.nav.getChildren('div[id=sortables_'+this.nav.id+']')[0];

		// get the modules containers
		this.updateMdlContainers();
		// set the internal counter over modules in order to not duplicate div ids
		this.iterModules = this.mdlContainers.length-1;
		// init nave the events
		this.initNaveEvents();
		// set dinamic mdlContainers events
		this.updateMdlEvents();
		// set as sortables the modules containers
		this.initSortables();
	},
	initNaveEvents: function() {
		// fine width regulation controllers
		this.fineLessWidthCtrl.addEvent('click', this.clickFineWidthCtrl.bind(this, -1));	
		this.fineMoreWidthCtrl.addEvent('click', this.clickFineWidthCtrl.bind(this, 1));	
		// mousemove width regulation
		this.widthCtrl.addEvent('mousedown', this.mousedownWidthCtrl.bind(this));	
		// nave dispose controller
		this.disposeCtrl.addEvent('click', this.clickDisposeCtrl.bind(this));	
		// nave float property control
		this.floatCtrl.addEvent('click', this.clickFloatCtrl.bind(this));	
	},
	updateMdlEvents: function() {
		this.mdlContainers.each(function(el) {
				this.mdlContainerEvents(el);	
		}.bind(this));
	},
	mdlContainerEvents: function(mdlContainer) {
		var refillable = $(mdlContainer.id.replace("mdlContainer", "refillable"));
		var fill = $(refillable.id.replace("refillable", "fill"));
		refillable.removeEvents('click');
		refillable.addEvent('click', this.clickFill.bind(this, refillable));

		if(refillable.getProperty('class') == 'refillableFilled') {
			mdlContainer.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=disposeMdl]')[0].removeEvents('click');
			mdlContainer.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=toggleMdl]')[0].removeEvents('click');
			mdlContainer.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=disposeMdl]')[0].addEvent('click', function(event) {
				mdlDispose(mdlContainer.id);
				event.stopPropagation();	
			})
			mdlContainer.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=toggleMdl]')[0].addEvent('click', function(event) {
				event.stopPropagation();	
				mdlPreviewToggle(fill.id);
			});

		}
		
	},
	updateMdlContainers: function() {
		this.mdlContainers = this.sortablesContainer.getChildren('div[id^=mdlContainer_'+this.nav.id+']'); 		     
	},
	initSortables: function() {
		this.sortableInst = new Sortables('#'+this.sortablesContainer.id, {
						constrain: true,
						handle: 'div[class^=sortMdl]',
						clone: true 
					}
		);	 
	},
	clickFineWidthCtrl: function(dx) {
		var navWidth = this.nav.getStyle('width').toInt() + dx;
		this.updateWidth(navWidth);

	},
	mousedownWidthCtrl: function(evt) {
		this.initMoveX = evt.page.x;
		this.initMoveWidth = this.nav.getStyle('width').toInt();	
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
		var newWidth = this.initMoveWidth + (this.nav.getStyle('float')=='right' ? this.initMoveX-evt.page.x : evt.page.x-this.initMoveX);
		this.updateWidth(newWidth);
	},
	mouseupWidthCtrl: function(evt) {
		document.removeEvents('mousemove');
	},
	clickDisposeCtrl: function() {
		window.openedLayer ? '' : this.nav.dispose();		  
	},
	clickFloatCtrl: function(evt) {
		if(window.openedLayer) return false;
		window.openedLayer = true;
		this.floatLayer = new Element('div', {'class':'floatLayer'});
		this.floatLayer.setStyles({'width': this.nav.getStyle('width'), 'opacity':0});
		var onclick = "onclick=\"window.navsObj['"+this.nav.id+"'].updateFloat($(this))\"";
		var html = "<div>Seleziona la proprietà float desiderata</div>";
		html += "<p><button "+onclick
		     +((this.nav.getStyle('float')=='left')? " class=\"selected\"":"")
		     +">left</button>";
       	 	if(/^nav_[0-9]+$/.test(this.nav.id)) 
			html += " <button "+onclick
			     +((this.nav.getStyle('float')=='none')? " class=\"selected\"":"")+">none</button>";
       	 	html += " <button "+onclick
		     +((this.nav.getStyle('float')=='right')? " class=\"selected\"":"")+">right</button></p>";

		this.floatLayer.set('html', html);
		this.floatLayer.inject(this.nav, 'top');
		layerEffect = new Fx.Tween(this.floatLayer, {'duration':500}).start('opacity', '0.9');
		document.addEvent('mousedown', checkCloseLayer.bind(this.floatLayer));
	},
	clickFill: function(refillable) {

		if(window.myWin && window.myWin.showing) return false;
		refillable.addClass("selectedMdlContainer");
		var url = 'index.php?pt[layout-modulesList]&nav_id='+this.nav.id+'&refillable_id='+refillable.id+'&fill_id='+refillable.id.replace("refillable", "fill");
		window.myWin = new layerWindow({'title':$('selMdlTitle').value, 'url':url, 'bodyId':'selMdl', 'width':700, 'height':500, 'destroyOnClose':true, 'closeCallback':unselectMdlContainer, 'closeCallbackParam':refillable});
		window.myWin.display(this.nav, {'left':getViewport().cX-350, 'top':getViewport().cY-250});
   
	},
	updateWidth: function(width) {
		width = width<0 ? 0 : width>window.blocksWidths[this.block_id] ? window.blocksWidths[this.block_id] : width;
		this.nav.setStyle('width', width+'px');
		this.widthDisplay.set('text', width);
	},
	updateFloat: function(btn) {
		this.nav.setStyle('float', btn.get('text'));

		// set nave width
		this.navWidth = this.nav.getStyle('width').toInt();
		this.widthDisplay = this.nav.getChildren('div[class=navCtrl]')[0]
					    .getChildren('div[class=left]')[1]
					    .getChildren('span')[0];			     

		this.widthDisplay.set('text', this.navWidth);

		layerEffect = new Fx.Tween(this.floatLayer, {'duration':500});
		layerEffect.start('opacity', '0').chain(function() {this.floatLayer.dispose();}.bind(this));

		window.openedLayer=false;
	}
})

function checkCloseLayer(evt) {
	if(evt.page.x<this.getCoordinates().left || evt.page.x>this.getCoordinates().right 
	   || evt.page.y<this.getCoordinates().top || evt.page.y>this.getCoordinates().bottom) {
		this.dispose();
		window.openedLayer = false;
		document.removeEvents('mousedown');
	}
}

function closeAll(nav_id, refillable_id, mdl_title, mdl_code) {

	var refillable = $(refillable_id);
	var action = (refillable.get('text') == '')?"new":"modify"; 
	var mdlContainer = $(refillable.id.replace("refillable", "mdlContainer"));
	var navObj = window.navsObj[nav_id];
	var fill = $(refillable.id.replace("refillable", "fill"));
	var mdlContainer = $(refillable.id.replace("refillable", "mdlContainer"));
	// increment internal counter
	if(action=='new') navObj.iterModules++;
	// new elements ids
	var new_refillable_id = refillable_id.replace(/[0-9]*$/, navObj.iterModules);
	var new_fill_id = new_refillable_id.replace("refillable", "fill");
	var new_mdlContainer_id = new_refillable_id.replace("refillable", "mdlContainer");
	// dispose window
	window.myWin.closeWindow();
	// html to insert in refillable div
	html = "<input type=\"hidden\" name=\"navElement\" value=\""+mdl_code+"\" /><div>"+mdl_title+"</div>";
	refillable.set('html', html);
	refillable.setProperty('class', 'refillableFilled');
	if(action=='new') mdlContainer.getChildren('div[class=mdlContainerCtrl]')[0].getChildren('div[class=sortMdlDisabled]')[0].setProperties({'class': 'sortMdl', 'title':'ordina'});
	if(action=='new') {
		mdlContainer.getChildren('div[class=mdlContainerCtrl]')[0]
			    .getChildren('div[class=toggleMdlDisabled]')[0]
			    .setProperties({'class': 'toggleMdl', 'title': 'vedi/nascondi contenuti'});
		mdlContainer.getChildren('div[class=mdlContainerCtrl]')[0]
			    .getChildren('div[class=disposeMdlDisabled]')[0]
			    .setProperties({'class': 'disposeMdl', 'title': 'elimina modulo'});
	}
	// creating new refillable and fill elements
	if(action=='new') {
		
		mdlc = new Element('div', {'id':new_mdlContainer_id});
		mdlc.inject(navObj.sortablesContainer, 'bottom');
		
		mdlc_html = "<div class=\"mdlContainerCtrl\">";
		mdlc_html += "<div class=\"disposeMdlDisabled\"></div>";
		mdlc_html += "<div class=\"sortMdlDisabled\"></div>";
		mdlc_html += "<div class=\"toggleMdlDisabled\"></div>";
		mdlc_html += "<div class=\"null\"></div>";
		mdlc_html += "</div>";

		mdlc.set('html', mdlc_html);
		ref = new Element('div', {'id':new_refillable_id, 'class':'refillable'});
		ref.inject(mdlc, 'bottom');
		// creating new mdl preview container
		f = new Element('div', {'id':new_fill_id, 'style':'display:none;'});
		f.inject(mdlc, 'bottom');

		navObj.updateMdlContainers();
		navObj.sortableInst.addItems(mdlc);
	
	}
	navObj.updateMdlEvents();

	window.openedLayer = false;
}

function mdlPreviewToggle(fill_id) {
	pdisplay = ($(fill_id).getStyle('display')=='none')?"block":"none";
	$(fill_id).setStyle('display', pdisplay);
	return false;
}

function mdlDispose(mdlContainer_id) {
	$(mdlContainer_id).dispose();
}

function unselectMdlContainer(refillable) {
	refillable.removeClass('selectedMdlContainer');
}

function saveTemplate() {

	var prevNaveRow = null;
	var text = '';
	var naves = $$('div[id^=nav_]');
	
	naves.each(function(nave) {
		var re = new RegExp("nav_[0-9]+");
		var m = re.exec(nave.id);
		var naveRow = "";
		for(var i=0; i<m.length; i++) {
			naveRow = naveRow+m[i];
		}
		var mcodes = new Array();
		var mdls = nave.getChildren('div')[1].getChildren('div');

		for(var i=0; i<mdls.length; i++) {
			var mdl = mdls[i];
			var mdlcode = $defined(mdl.getChildren('div')[1].getChildren('input[type=hidden]')[0]) 
			? mdl.getChildren('div')[1].getChildren('input[type=hidden]')[0].value
			: null;	
			mcodes[i] = mdlcode;		
		}

		if(naveRow != prevNaveRow && prevNaveRow) text += "<div class=\"null\"></div>\r\n";
		text += "<div id=\""+nave.id+"\"";
		if($chk(nave.style.float) || $chk(nave.style.width)) {
			text += " style=\"";
			if($chk(nave.style.cssFloat)) text += "float:"+nave.style.cssFloat+";";
			if($chk(nave.style.width)) text += "width:"+nave.style.width+";";
			text += "\"";
		}
		text += ">\r\n";
		for(var ii=0; ii<mcodes.length; ii++) {
			if($chk(mcodes[ii])) text += mcodes[ii]+'\r\n';
		}
		text += "</div>\r\n";
		prevNaveRow = naveRow;
		
	});

	$('tplform_text').value = text+"<div class=\"null\"></div>";
	$('tplform').submit();

}
