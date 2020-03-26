/* GINO FULL JS LIBRARY */
var gino = {};

function convertToSlug (Text, replace) {
	if(!replace) replace = '-';
	
	str = Text.replace(/^\s+|\s+$/g, ''); // trim
	str = str.toLowerCase();
	
	// remove accents, swap ñ for n, etc
	var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
	var to   = "aaaaaeeeeeiiiiooooouuuunc------";
	for (var i=0, l=from.length ; i<l ; i++) {
		str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	}
	
	str = str.replace(new RegExp('[^a-z0-9'+replace+']', 'g'), replace)	// remove invalid chars
	.replace(/\s+/g, replace) // collapse whitespace
	.replace(new RegExp(replace+'+','g'), replace); // collapse dashes
	
	if(str.charAt(str.length-1) == replace) str = str.substring(0,str.length-1);
	return str;
};

gino.slugControl = function(slug_field_id, json_fields) {
	var slug_field = $('#' + slug_field_id);
	var fields = jQuery.parseJSON(json_fields);
	
	var onblur = function() {
		var text_parts = [];
		$.each(fields, function(index, field_name) {
			var field = slug_field.parents('form').find('input[name=' + field_name + ']');
			text_parts.push(field.val());
		})
		
		var text = text_parts.join('-');
		slug_field.attr('value', convertToSlug(text));
	};

	$.each(fields, function(index, field_name) {
		slug_field.parents('form').find('input[name=' + field_name + ']').on('blur', onblur);
	})
}

/* SAME POLICY FRAMES */
gino.sameDomain = function(win){
  var H=location.href,
  local= H.substring(0, H.indexOf(location.pathname));
      try {
          win=win.document;
          return win && win.URL && win.URL.indexOf(local)== 0;
      }
      catch(er){
          return false;
      }
}
/* html5 lib */
document.createElement('hgroup');
document.createElement('header');
document.createElement('nav');
document.createElement('section');
document.createElement('article');
document.createElement('aside');
document.createElement('footer');
/*
 * Ajax requests function
 * performs post and get asynchronous requests
 *
 * Arguments
 * method - (string) The method can be either post or get
 * url - (string) The requested url
 * data - (string) The serialized data of the request in the form 'var1=value1&var2=value2'
 * target - (mixed) The element DOM Object or the element id of the DOM element that have to be updated with the request response
 *
 * Options (object)
 * cache - (bool default to false) Whether to cache the request result or not
 * cacheTime - (int default 3600000 [1hr]) The time in milliseconds to keep the request in cache
 * load - (mixed default null) The element DOM Object or the element id of the DOM element to use to show the loading image
 * script - (bool default false) True if scripts have to be executed, false if not
 * setvalue - (bool default false) True if script response must be set as the target value
 * callback - (function dafault null) The function to call after the request has been executed
 * callback_params - (string default null) The params passed to the callback function
 *
 * if the called method has to return an error, must return a string like:
 * request error:Error description
 * this way the method is not executed and an alert is displayed with the message "Error description"
 *
 */
gino.loading = "<img src='img/ajax-loader.gif' alt='loading...'>";
gino.requestCache = new Array();
gino.ajaxRequest = function(method, url, data, target, options) {

	var opt = {
			cache: false,
			cacheTime: 3600000,
			load: null,
			script: false,
			script_eval: false,
			setvalue: false,
			callback: null,
			callback_params: null
	};
	// // Merge options into opt
	$.extend(opt, options);
	
	target = target ? ((typeof target === 'string') ? $('#' + target) : $(target)) : null;
	if(opt.cache && gino.requestCache[url+data] && ($.now() - gino.requestCache[url+data][0] < opt.cacheTime)) {
		if(opt.setvalue && target) target.val(gino.requestCache[url+data][1]);
		else if(target) target.html(gino.requestCache[url+data][1]); 

		if(opt.callback && opt.callback_params) opt.callback(opt.callback_params);
		else if(opt.callback) opt.callback();
		return true;
	}

	var opt_load = opt.load ? $('#' + opt.load) : null;
	
	var request = $.ajax({
		evalScripts: opt.script,
		url: url,
		method:	method,
		data: data,
		beforeSend: function() {
			if(opt_load) opt_load.html(gino.loading); 
		},
		success: function(responseHTML) {
			if(opt_load) opt_load.html(''); 
			rexp = /request error:(.*)/;
			var err_match = rexp.exec(responseHTML);
			if(err_match) alert(err_match[1]);
			else {
				if(opt.setvalue && target) target.val(responseHTML);
				else if(target) target.html(responseHTML);
				
				if(opt.cache) gino.requestCache[url+data] = new Array($.now(), responseHTML);
				
				if(opt.callback && opt.callback_params) opt.callback(responseHTML, opt.callback_params);
				else if(opt.callback) opt.callback(responseHTML);
				try {
					parseFunctions();	// defined in class.Javascript.php
				}
				catch(e) {}
			}
		}
	});
}

gino.jsonRequest = function(method, url, data, callback, options) {

	var opt = {
			loading: false,
			callback_params: null
	};
	$extend(opt, options);
	var loader = null;

	var request = new Request.JSON({
		url: url,
		method:	method,
		data: data,
		beforeSend: function() {
			if(opt.loading) {
				loader = new gino.Loader();
				loader.show();
			}
		},
		success: function(responseJSON, responseText) {
			if(opt.loading) loader.remove(); 
			callback.call(this, responseJSON, opt.callback_params);
		}
	}).send();
}

gino.Loader = function() {
    this.show = function() {
        //var docDim = document.getScrollSize();
    	this.overlay = $('<div \>', {class: 'abiWinOverlay'});
        this.overlay.css({
          'top': '0px',
          'left': '0px',
          'width': $('body').width(),
          'height': $('body').height(),
          'z-index': ++gino.maxZindex
        });
        this.overlay.appendTo(document.body);
        var viewport = gino.getViewport();
        
        this.overlay.append($('<img \>', {src: 'img/ajax-loader.gif'}).attr('alt', 'loading...').css({
            'position': 'absolute',
            'top': (viewport.cY - 10) + 'px',
            'left': (viewport.cX - 10) + 'px',
        }));
    };

    this.remove = function() {
        this.overlay.remove();
    }
}

gino.EventDispatcher = {
	_prefix: 'on_',
	listeners: {},
	register: function(evt_name, bind, callback) {
		var _evt_name = this._prefix + evt_name;
		if(typeof this.listeners[_evt_name] == 'undefined') {
			this.listeners[_evt_name] = [];
		}
		this.listeners[_evt_name].push([bind === null ? this : bind, callback]);
	},
	emit: function(evt_name, params) {
		var _evt_name = this._prefix + evt_name;
		if(typeof this.listeners[_evt_name] != 'undefined') {
			for(var i = 0, l = this.listeners[_evt_name].length; i < l; i++) {
				this.listeners[_evt_name][i][1].call(this.listeners[_evt_name][i][0], evt_name, params);
			}
		}
	}
}

gino.confirmSubmit = function(msg) {
	var message = $chk(msg) ? msg : "Are you sure you wish to continue?";
	var agree = confirm(message);
	return agree ? true : false;
}
/*
gino.validateForm = function(formObj) {
	
	$('div[class=formErrMsg]').remove();	//$$('div[class=formErrMsg]').dispose();
	$('label[class=req2]').prop('class', 'req');
	
	var fsubmit = true;
	
	var labels = $('#'+formObj.attr('id')+' label:not([class^=diji])');

  for(var i=0; i<labels.length; i++) {
  
    var err_detected = false;
    var label = labels[i];
    var felement_name = label.prop('for');
    var match_sb = /(.*?)\[\]/.exec(felement_name);
    var felements = (match_sb && match_sb.length>0)
      ? $('#'+formObj.prop('id')+' [name^='+match_sb[1]+'[]')
      : $('#'+formObj.prop('id')+' [name='+felement_name+']');
    var felement = felements[0];

    if(label.hasClass('req') && (!$chk(felement.parent('.form-row')[0]) || (felement.parent('.form-row')[0].style.display!='none'))) {

      if(typeof CKEDITOR != 'undefined' && typeof CKEDITOR.instances[felement_name] != 'undefined') {
        if(!$chk(CKEDITOR.instances[felement_name].getData())) err_detected = true;
      }		
      else if(felement.type=='text' || felement.type=='password' || felement.match('textarea') || felement.match('select') || felement.type=='hidden') {
        if(!felement.value) err_detected = true;
      }
      else if(felement.type=='radio' || felement.type=='checkbox') {
        var checked = false;
        for(var ii=0;ii<felements.length;ii++) 
          if(felements[ii].checked) {checked = true;break;}
        if(!checked) err_detected = true;
      }
    }
    if(err_detected) {
      err_mesg = $('<span \>', {'class':'form-error-msg'});
      err_mesg.attr('html', 'campo obbligatorio');
      err_mesg.appendTo(label, 'bottom');
      //label.className='req2';
      label.text()='req2';
      fsubmit = false;
    }

    if(felement && felement.type=='text') {
      var pattern = felement.prop('pattern') ?  felement.prop('pattern'):null;
      if(felement.value) {
        if((pattern && !new RegExp(pattern).test(felement.value))) {
          err_mesg = $('<span \>', {'class':'form-error-msg'});
          err_mesg.attr('html', felement.prop('placeholder'));
          err_mesg.appendTo(label, 'bottom');
          //label.className='req2';
          label.text()='req2';
          fsubmit = false;
        }
      }
    }

  }

  if(!fsubmit) alert('Errore nella compilazione del form');

  return fsubmit;
}
*/

/*
 * Translations
 */
gino.translations = {
	prepareTrlForm: function(lng_code, el, tbl, field, type, id_value, width, toolbar, url) {

		var open = false;
		var el_parent = el.parent();
		var active_trnsl = el_parent.children('.trnsl-lng-sel');
		
		// close
		if(active_trnsl[0]) {
			
			el_parent.children('#trnsl-container').remove();
			
			try {
				CKEDITOR.remove(CKEDITOR.instances['trnsl_'+field]);
			}
			catch(e) {}
			active_trnsl.removeClass('trnsl-lng-sel');
		}
		// open
		if(el[0] != active_trnsl[0]) {
			el.addClass('trnsl-lng-sel');
			var myTrnsl = $('<div \>', {
				'id' : 'trnsl-container',
				'class' : 'form_translation'
			}).appendTo(el_parent);

			var data = 'lng_code='+lng_code+'&tbl='+tbl+'&field='+field+'&type='+type+'&id_value='+id_value+'&width='+width+'&toolbar='+toolbar;		
			gino.ajaxRequest('post', url, data, myTrnsl, {'load':tbl+field, script_eval: true, callback: function() { }});
		}
	},
	callAction: function(url, type, tbl, field, id_value, ckeditor, lng_code, action) {

		var text = ckeditor ? CKEDITOR.instances['trnsl_' + field].getData() : $('#trnsl_' + field).val();
		gino.ajaxRequest(
				'post', 
				url, 
				'type=' + type + '&tbl=' + tbl + '&field=' + field +'&id_value=' + id_value + '&text='+ encodeURIComponent(text) +'&lng_code=' + lng_code + '&action=' + action, tbl + field, 
				{
					'callback': function() {
						$('#trnsl-container').remove();
						$(tbl + field).parent().children('.trnsl-lng-sel').removeClass('trnsl-lng-sel');
						try {
							CKEDITOR.remove(CKEDITOR.instances['trnsl_' + field]);
						}
						catch(e) {}
					}
				}
		);
	}
}

/* many to many through model */
gino.m2mthrough = function(id, field_name) {

	// dispose to be cloned form
	var form = $('#' + id).find('div[data-clone=1]')[0];
	var cnt = $('#' + id + ' fieldset').length;

	form.remove();

	function rename(field_name, el, i, die) {
		var name = $(el).attr('name');
		if(name) {
			if(die) {
				name = name.replace('m2mtdie_', '');
			}
			if(/\[\]/.test(name)) {
				return 'm2mt_' + field_name + '_' + name.replace('[]', '') + '_' + i + '[]';
			}
			else {
				return 'm2mt_' + field_name + '_' + name + '_' + i;
			}
		}
		else {
			return null;
		}
	}

	var i = 1;
	$('#' + id).children('fieldset').each(function(ii, fieldset) {
		var ctrl = $(fieldset).find('span[data-clone-ctrl]')[0];

		var filled = $(ctrl).attr('data-clone-ctrl') == 'minus' ? true : false;
		if(filled) {
			$(fieldset).find('input,select,textarea').each(function(ii, el) {
				var name = rename(field_name, el, i, false);
				if(name) {
					var label_for = name;
					var label = $(fieldset).find('label[for=' + el.getProperty('name') + ']')[0];
					if(typeof label != 'undefined') {
						label.setProperty('for', label_for);
						if(typeof label.parent('.form-row').children('a.form-addrelated')[0] != 'undefined') {
							label.parent('.form-row').children('a.form-addrelated')[0].attr('id', 'add_' + name);
						}
					}
					el.attr('name', name);
					if(el.attr('type') == 'radio') {
						if(/checked/i.test(el.outerHTML)) { // @todo check browser compatibility
							el.prop('checked', 'checked');
						}
					}
				}
			})
			var index = $('<input \>', {type: 'hidden'}).attr('name', 'm2mt_' + field_name + '_ids[]').val(i).appendTo(fieldset.children('div')[0], 'top');
			i++;
			$(ctrl).on('click', removeFieldset);
		}
		else {
			$(ctrl).on('click', addFieldset);
		}
	});

	function removeFieldset () {
		if($('#' + id + ' fieldset').length > 1) {
			$(this).parent().parent().remove();
		}
		else {
			$(this).parent().parent().children('[data-clone=1]').remove();
			$(this).off('click');
			$(this).removeClass('fa-minus-circle').addClass('fa-plus-circle');
		}
	}

	function addFieldset () {
		var clone = $(form).clone();

		clone.appendTo($(this).parent().parent()).removeClass('hidden');
		clone.find('input,select,textarea').each(function(ii, el) {
			$(el).attr('name', rename(field_name, el, cnt, true));
		})

		var index = $('<input \>', {type: 'hidden'}).attr('name', 'm2mt_' + field_name + '_ids[]').val(cnt).prependTo(clone);
		cnt++;
		var fieldset = $('<fieldset />').append($(this).parent().clone()).appendTo($('#' + id));
		fieldset.find('span[data-clone-ctrl]').on('click', addFieldset);	// [0]
		$(this).off('click');
		$(this).removeClass('fa-plus-circle').addClass('fa-minus-circle');
		$(this).on('click', removeFieldset);

		gino.EventDispatcher.emit('m2mthrough-' + field_name + '-added');
	}
}

/*
 * layerWindow class
 *
 * layerWindow method: constructor
 *   Syntax
 *      var myLayerWindowInstance = new layerWindow([options]);
 *   Arguments 
 *	1. options - (object, optional) The options object.
 *   Options
 *	- id (string: default to null) The id attribute of the window container
 *	- bodyId (string: default to null) The id attribute of the body container
 *   	- title (string: default to null) The window title
 * 	- width (int: default to 400) The width in px of the window body
 * 	- height (int: default to null) The height in px of the body. By default its value depends on contained text
 *  	- minWidth (int: default to 300) The minimum width when resizing
 *	- minHeight (int: default to 100) The minimum height when resizing
 * 	- maxHeight (int: default to viewport-height minus 100px) The max-height css property of the window body
 *	- draggable (bool: default to true) Whether or not to make the window draggable
 *	- resize (bool: default to true) Whether or not to make the window resizable
 *	- closeButtonUrl (string: default to null) The url of the image to use as close button
 *	- closeButtonLabel (string: default to close) The string to use as close button if the closeButtonUrl is null
 *	- destroyOnClose (bool: default to true) Whether or not to destroy all object properties when closing the window
 *	- overlay (bool: default to true) Whether or not to set a base overlay with opacity isolating the window from the below elements
 *  	- url (string: default to null) The url to be called by ajax request to get initial window body content
 *	- htmlNode (mixed: default to null) The html node which content is injected into the window body. May be a node element or its id.
 *	- html (string: default to null) The initial html content of the window body if url is null
 *	- closeCallback (function: default to null) The function to be called when the window is closed
 *	- closeCallbackParam (mixed: default to null) The paramether to pass to the callback function when the window is closed
 *	- disableObjects (bool: default to false) Whether or not to hide objects when window is showed (and show them when window is closed)
 *
 * layerWindow method: setTitle
 *  sets the title of the window and updates it if the window is showed
 *   Syntax
 *	myLayerWindowInstance.setTitle(title);
 *   Arguments
 *	1. title - (string) The title of the window
 *
 * layerWindow method: setHtml
 *  sets the content of the window and updates it if the window is showed
 *   Syntax
 *	myLayerWindowInstance.setHtml(html);
 *   Arguments
 *	1. html - (string) The html content of the window body
 *
 * layerWindow method: setUrl
 *  sets the content of the window and updates it if the window is showed
 *   Syntax
 *	myLayerWindowInstance.setUrl(url);
 *   Arguments
 *	1. url - (string) The url called by ajax request to get window body content
 *
 * layerWindow method: display
 *  displays the window in the position pointed by the element passed, or by the given coordinates. If no element nor coordinates are given,
 *  the window is centered in the viewport.
 *   Syntax
 *	myLayerWindowInstance.display(el, [opt]);
 *   Arguments
 *	1. el - (element) The element respect to which is rendered the window (top left of the window coincide with top left of the element)
 *      2. opt - (object) The top and left coordinates of the top left edge of the window. If only one is given the other is taken from the el passed
 *
 * layerWindow method: setFocus
 *  set focus on the object window, giving it the greatest z-index in the document
 *   Syntax
 *	myLayerWindowInstance.setFocus();
 *
 * layerWindow method: closeWindow
 *  closes the window and destroyes the object properties if the option destroyOnClose is true
 *   Syntax
 *	myLayerWindowInstance.closeWindow();
 *
 * layerWindow method: getViewport
 *  returns viewport properties (width, height, top, left, center-left, center-top)
 *   Syntax
 *	myLayerWindowInstance.getViewport();
 *  
 * layerWindow method: getMaxZindex
 *  returns the max z-index value present in the document
 *   Syntax
 *	myLayerWindowInstance.getMaxZindex();
 *
 */
gino.layerWindow = function (opt) {

	this.options = {
			id: null,
			bodyId: null,
			title: null,
			width: 400,
			height: null,
			minWidth: 300,
			minHeight: 100,
			maxHeight: null,
			draggable: true,
			resize: true,
			closeButtonUrl: null,
			closeButtonLabel: null,
			destroyOnClose: true,
			overlay: true,
			url:'',
			url_param:'',
			html: ' ',
			htmlNode: null,
			closeCallback: null,
			closeCallbackParam: null,
			disableObjects: false,
			reloadZindex: false
	}
	$.extend(this.options, opt);

	this.initialize = function() {

		this.showing = false;	

		this.checkOptions();

		if(this.options.title) this.title = this.options.title;
		if(this.options.html) this.html = this.options.html;
		if(this.options.htmlNode) this.htmlNode = $(this.options.htmlNode);
		if(this.options.url) this.url = this.options.url;
		if(!this.options.maxHeight) this.options.maxHeight = gino.getViewport().height-100;

		if(this.options.reloadZindex) gino.maxZindex = gino.getMaxZindex();
	};
	
	this.checkOptions = function() {
		var rexp = /[0-9]+/;
		if(!rexp.test(this.options.width) || this.options.width<this.options.minWidth) this.options.width = 400;
	};
	
	this.setTitle = function(title) {
		this.title = title;	 
		if(this.showing) this.header.set('html', title);
	};
	this.setHtml = function(html) {
		this.html = html;	 
		if(this.showing) this.body.set('html', html);
	};
	this.setUrl = function(url) {
		this.url = url;	 
		if(this.showing) this.request();
	};
	
	this.display = function(element, opt) {
		this.loader = new gino.Loader();
		this.loader.show();
		this.delement = !element ? null : jQuery.type(element)=='element' ? element : $(element);
		this.dopt = opt;
		if(this.options.disableObjects) this.dObjects();
		this.showing = true;
		
		var check_exist = $(".abiWin");
		//console.log(check_exist)
		if(check_exist) {
			
			$(check_exist).remove();
		}

		if(this.options.overlay) this.renderOverlay();
		this.renderContainer();
		this.renderHeader();
		this.renderBody();
		this.renderFooter();
		
		this.container.css('width', this.options.width);
		//this.initBodyHeight = $(this.body).height();
		//this.initContainerDim = this.container.position();
	};
	
	this.renderOverlay = function() {
		this.overlay = $('<div \>', {'class': 'abiWinOverlay'});
		this.overlay.css({
			'top': '0px',
			'left': '0px',
			'width': $('body').width(),
			'height': $('body').height(),
			'z-index': ++gino.maxZindex
		});

		this.overlay.appendTo(document.body);
	};

	this.dObjects = function() {
		for(var i=0;i<window.frames.length;i++) {
			var myFrame = window.frames[i];
			if(gino.sameDomain(myFrame)) {
				var obs = myFrame.document.getElementsByTagName('object');
				for(var ii=0; ii<obs.length; ii++) {
					obs[ii].style.visibility='hidden';
				}
			}
		}
		$('object').each(function(i, item) {
			item.style.visibility='hidden';
		})
	};
	
	this.eObjects = function() {
		for(var i=0;i<window.frames.length;i++) {
			var myFrame = window.frames[i];
			if(gino.sameDomain(myFrame)) {
				var obs = myFrame.document.getElementsByTagName('object');
				for(var ii=0; ii<obs.length; ii++) {
					obs[ii].style.visibility='visible';
				}
			}
		}
		$('object').each(function(i, item) {
			item.style.visibility='visible';
		})
	};
	
	this.renderContainer = function() {
		this.container = $('<section \>', {'id': this.options.id, 'class': 'abiWin'});

		this.container.css({
			'visibility': 'hidden',
		})
		var self = this;
		this.setFocus();
		this.container.on('mousedown', function () {self.setFocus()});
		this.container.appendTo(document.body);
	};
	
	this.locateContainer = function() {

		var elementCoord = this.delement ? this.delement.position() : null;
		
		this.top = (this.dopt && (this.dopt.top)) ? this.dopt.top < 0 ? 0 : this.dopt.top : elementCoord 
			? elementCoord.top 
			: (gino.getViewport().cY-this.container.height()/2);
		this.left = (this.dopt && (this.dopt.left)) ? this.dopt.left < 0 ? 0 : this.dopt.left : elementCoord 
			? elementCoord.left 
			: (gino.getViewport().cX-this.container.width()/2);

		this.container.css({
			'top': this.top+'px',
			'left':this.left+'px',
			'visibility': 'visible',
		})

		this.loader.remove();
	};
	
	this.renderHeader = function() {
		this.header = $('<header \>', {'class': 'abiHeader'});
		this.header.html('<h1>' + this.title + '</h1>');

		var closeEl;
		if(this.options.closeButtonUrl && typeof this.options.closeButtonUrl === 'string') {
			closeEl = $('<img \>', {'src':this.options.closeButtonUrl, 'class':'close'});
		}
		else if(this.options.closeButtonLabel) {
			closeEl = $('<span \>', {'class':'close'});
			closeEl.html(this.options.closeButtonLabel);
		}
		else {
			closeEl = $('<span \>', {'class':'close_img'});
		}
		
		closeEl.on('click', this.closeWindow.bind(this));
		this.header.prependTo(this.container);
		closeEl.appendTo(this.header);
	};
	
	this.renderBody = function() {
		this.body = $('<div \>', {'id': this.options.bodyId, 'class': 'body'});
		
		this.body.css({
			'width': this.options.width,
			'height': this.options.height,
			'max-height': this.options.maxHeight
		})
		this.body.appendTo(this.container);
		this.url ? this.request() : this.htmlNode ? this.body.html(this.htmlNode.clone(true, true).html()) : this.body.html(this.html);
		if(!this.url || this.options.height) this.locateContainer();
	};
	
	this.renderFooter = function() {
		this.footer = $('<footer \>');
		this.footer.appendTo(this.container);
	};
	
	/*
	////////////////////////////////////////non chiamato
	this.renderResizeCtrl = function() {
		this.resCtrl = $('<div \>').css({
			'position':'absolute', 
			'right':'0', 
			'bottom':'0', 
			'width':'10px', 
			'height':'10px', 
			'cursor':'se-resize'
		});
		this.resCtrl.appendTo(this.footer, 'top');		
	};
	this.makeDraggable = function() {
		return;
	};
	
	this.makeResizable = function() {	  
		return ;
	};
	
	//////////////////////////////////////// non chiamato
	this.resizeBody = function() {
		this.body.css({
			'width': parseInt(this.options.width)+(this.container.width()-this.initContainerDim.width),
			'height': this.initBodyHeight+(this.container.height()-this.initContainerDim.height)		
		});
	};
	*/

	this.request = function() {
		var self = this;
		this.locateContainer();

		gino.ajaxRequest('post', this.url, this.options.url_param, this.body, {'script':true, 'load':this.body, 'callback': function () {self.locateContainer()}});	 
	};
  
	this.setFocus = function() {
		if(!this.container.css('z-index') || (parseInt(this.container.css('z-index')) < gino.maxZindex))
		this.container.css('z-index', ++gino.maxZindex);
	};
	this.closeWindow = function() {
		this.showing = false;
		if(this.options.disableObjects) this.chain($(this.container).remove(), this.eObjects());
		else $(this.container).remove();
		if(this.options.overlay) this.overlay.remove();
		if(this.options.closeCallback) this.options.closeCallback(this.options.closeCallbackParam);		
		if(this.options.destroyOnClose) for(var prop in this) this[prop] = null;
	}

	this.initialize();
}

gino.getViewport = function () {

	var width, height, left, top, cX, cY;

	// the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	if (typeof window.innerWidth != 'undefined') {
		width = window.innerWidth,
		height = window.innerHeight
	}
	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth !='undefined' && document.documentElement.clientWidth != 0) {
		width = document.documentElement.clientWidth,
		height = document.documentElement.clientHeight
	}

	top = typeof self.pageYOffset !== 'undefined' 
		? self.pageYOffset 
		: (document.documentElement && typeof document.documentElement.scrollTop !== 'undefined')
		? document.documentElement.scrollTop
		: document.body.clientHeight;

	left = typeof self.pageXOffset !== 'undefined' 
		? self.pageXOffset 
		: (document.documentElement && typeof document.documentElement.scrollTop !== 'undefined')
		? document.documentElement.scrollLeft
		: document.body.clientWidth;

	cX = left + width/2;
	cY = top + height/2;

	return {'width':width, 'height':height, 'left':left, 'top':top, 'cX':cX, 'cY':cY};
}

gino.getMaxZindex = function () {
	var maxZ = 0;
	$('body *').each(function(i, el) {if(parseInt(el.css('z-index'))) maxZ = Math.max(maxZ, parseInt(el.css('z-index')))});

	return maxZ;
}

var maxZindex = this.getMaxZIndex;

// Handles related-objects functionality: lookup link for raw_id_fields
// and Add Another links.

gino.html_unescape = function(text) {
    // Unescape a string that was escaped using django.utils.html.escape.
    text = text.replace(/&lt;/g, '<');
    text = text.replace(/&gt;/g, '>');
    text = text.replace(/&quot;/g, '"');
    text = text.replace(/&#39;/g, "'");
    text = text.replace(/&amp;/g, '&');
    return text;
}

// IE doesn't accept periods or dashes in the window name, but the element IDs
// we use to generate popup window names may contain them, therefore we map them
// to allowed characters in a reversible way so that we can locate the correct 
// element when the popup window is dismissed.
gino.id_to_windowname = function(text) {
    text = text.replace(/\./g, '__dot__');
    text = text.replace(/\-/g, '__dash__');
    return text;
}

gino.windowname_to_id = function(text) {
    text = text.replace(/__dot__/g, '.');
    text = text.replace(/__dash__/g, '-');
    return text;
}

gino.showAddAnotherPopup = function(triggeringLink) {
    var name = triggeringLink.id.replace(/^add_/, '');
    name = gino.id_to_windowname(name);
    href = triggeringLink.href
    if (href.indexOf('?') == -1) {
        href += '?_popup=1';
    } else {
        href  += '&_popup=1';
    }
    var win = window.open(href, name, 'height=500,width=800,resizable=yes,scrollbars=yes');
    win.focus();
    return false;
}

gino.dismissAddAnotherPopup = function(win, newId, newRepr) {
    newId = gino.html_unescape(newId);
    newRepr = gino.html_unescape(newRepr);
    var name = gino.windowname_to_id(win.name);
    var elem = document.find('select[name=' + name + ']')[0];
    // select
    if (elem) {
        $('<option \>', {value: ' + newId + '}).prop('selected', 'selected').attr('text', newRepr).appendTo(elem);
    }
    // multicheck
    else {
        var elem = document.find('input[type=checkbox][name=' + name + ']')[0];
        if(elem) {
        	$('<tr \>').append(
                $('<td \>').attr('text', newRepr),
                $('<td \>').append(
                    $('<input \>', {type: 'checkbox', name: ' + name + ', value: ' + newId + '}).prop('checked', 'checked')
                )
            ).appendTo(elem.parent('tr'), 'before');
        }
        else {
            var label = document.find('label[for=' + name + ']')[0];
            label.getNext('.form-multicheck').find('table tr')[0].empty().append(
                $('<td \>').attr('text', newRepr),
                $('<td \>').append(
                    $('<input \>', {type: 'checkbox', name: ' + name + ', value: ' + newId + '}).prop('checked', 'checked')
                )
            );
        }
    }
    win.close();
}

gino.checkAll = function(controller, container) {
	
	if(!$(controller)[0].checked) {
    	$(container).find('input[type=checkbox][value]').each(function(i, c) {
            if($(c).parent('td')) {
                if($(c).parent('td').parent('tr').css('display') != 'none') {
                	$(c).prop('checked', '');
                }
            }
        });
    }
    else {
        $(container).find('input[type=checkbox][value]').each(function(i, c) {
            if($(c).parent('td')) {
                if($(c).parent('td').parent('tr').css('display') != 'none' && !$(c).prop('disabled')) {
                    $(c).prop('checked', 'checked');
                }
            }
        });
    }
}

RegExp.escape= function(s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};

gino.filterMulticheck = function(controller, container) {
	
    var text = controller.val();
    var rexp = new RegExp(RegExp.escape(text), 'i');
    
    $(container).find('tr').each(function(index, tr) {
        var tds = $(tr).children('td');
        
        if(tds.length > 1) {
            var display = false;
            for(var i = 0; i < tds.length; i++) {
                var td = tds[i];
                if(!$(td).children('input[type=checkbox]').length) {
                	var label = $(td)[0].textContent;
                    if(rexp.test(label)) {
                        display = true;
                    }
                }
            }
            if(display) {
                $(tr).css('display', '');
            }
            else {
                $(tr).css('display', 'none');
            }
        }
    });
}

gino.externalLinks = function() {
	if (!document.getElementsByTagName) return;
	var anchors = document.getElementsByTagName("a");
	for (var i=0; i<anchors.length; i++) {
		var anchor = anchors[i];
		if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "external") {
			anchor.target = "_blank";
			/*if (anchor.title) anchor.title += " (Il link apre una nuova finestra)";
			if (!anchor.title) anchor.title = "Il link apre una nuova finestra";*/
		}
	}
}
window.onload = gino.externalLinks;
