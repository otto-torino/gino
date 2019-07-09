/* GINO FULL JS LIBRARY */
var gino = {};

// slugify
(function() {
    String.implement({
        slugify: function(replace) {
            if(!replace) replace = '-';
            var str = this.toString().tidy().standardize().replace(/[\s\.]+/g,replace).toLowerCase().replace(new RegExp('[^a-z0-9'+replace+']','g'),replace).replace(new RegExp(replace+'+','g'),replace);
            if(str.charAt(str.length-1) == replace) str = str.substring(0,str.length-1);
            return str;
        }
    });
})();

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
 * data - (string) The datas of the request in the form 'var1=value1&var2=value2'
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
gino.loading_element = new Element('img[src=img/ajax-loader.gif]').setProperty('alt', 'loading...');
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
  $extend(opt, options);
  target = $type(target)=='element'
    ? target
    : $chk($(target))
      ? $(target)
      : null;
  if(opt.cache && $defined(gino.requestCache[url+data]) && ($time() - gino.requestCache[url+data][0] < opt.cacheTime)) {
    if(opt.setvalue) target.value = gino.requestCache[url+data][1];
    else target.set('html', gino.requestCache[url+data][1]); 

    if(opt.callback && opt.callback_params) opt.callback(opt.callback_params);
    else if($chk(opt.callback)) opt.callback();
    return true;
  }

  var opt_load = $chk(opt.load)? ($type(opt.load)=='element'?opt.load:$(opt.load)):null;
  var request = new Request.HTML({
    evalScripts: opt.script,
    url: url,
    method:	method,
    data: data,
    onRequest: function() {
      if(opt_load) opt_load.set('html', gino.loading); 
    },
    onComplete: function(responseTree, responseElements, responseHTML, responseJavaScript) {
      if(opt_load) opt_load.set('html', ''); 
      rexp = /request error:(.*)/;
      var err_match = rexp.exec(responseHTML);
      if($chk(err_match)) alert(err_match[1]);
      else {
        if(opt.setvalue && target) target.setProperty('value',responseHTML);
        else if(target) target.set('html', responseHTML);
        if(opt.cache) gino.requestCache[url+data] = new Array($time(), responseHTML);
        if(opt.script_eval) {
          eval(responseJavaScript);
        }
        if(opt.callback && opt.callback_params) opt.callback(responseHTML, opt.callback_params);
        else if($chk(opt.callback)) opt.callback(responseHTML);
        try {
          parseFunctions();
        }
        catch(e) {}
      }
    }
  }).send();

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
    onRequest: function() {
      if(opt.loading) {
       loader = new gino.Loader();
       loader.show();
      }
    },
    onSuccess: function(responseJSON, responseText) {
      if(opt.loading) loader.remove(); 
      callback.call(this, responseJSON, opt.callback_params);
    }
  }).send();

}

gino.Loader = function() {
    this.show = function() {
        var docDim = document.getScrollSize();
        this.overlay = new Element('div', {'class': 'abiWinOverlay'});
        this.overlay.setStyles({
          'top': '0px',
          'left': '0px',
          'width': docDim.x,
          'height': docDim.y,
          'z-index': ++gino.maxZindex
        });
        this.overlay.inject(document.body);
        var viewport = gino.getViewport();
        this.overlay.adopt($(gino.loading_element).setStyles({
            'position': 'absolute',
            'top': (viewport.cY - 10) + 'px',
            'left': (viewport.cX - 10) + 'px',
        }));
    };

    this.remove = function() {
        this.overlay.dispose();
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

/* FORM LIBRARY */
gino.confirmSubmit = function(msg) {
  var message = $chk(msg)? msg:"Are you sure you wish to continue?";
  var agree = confirm(message);
  return agree? true:false;
}

gino.validateForm = function(formObj) {

  $$('div[class=formErrMsg]').dispose();
  $$('label[class=req2]').setProperty('class', 'req');

  var formid = formObj.getProperty('name');

  var fsubmit = true;

  var labels = $$('#'+formObj.getProperty('id')+' label:not([class^=diji])');

  for(var i=0; i<labels.length; i++) {
  
    var err_detected = false;
    var label = labels[i];
    var felement_name = label.getProperty('for');
    var match_sb = /(.*?)\[\]/.exec(felement_name);
    var felements = (match_sb && match_sb.length>0)
      ? $$('#'+formObj.getProperty('id')+' [name^='+match_sb[1]+'[]')
      : $$('#'+formObj.getProperty('id')+' [name='+felement_name+']');
    var felement = felements[0];

    if(label.hasClass('req') && (!$chk(felement.getParents('.form-row')[0]) || (felement.getParents('.form-row')[0].style.display!='none'))) {

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
      err_mesg = new Element('span', {'class':'form-error-msg'});
      err_mesg.set('html', 'campo obbligatorio');
      err_mesg.inject(label, 'bottom');
      label.className='req2';
      fsubmit = false;
    }

    if(felement && felement.type=='text') {
      var pattern = felement.getProperty('pattern') ?  felement.getProperty('pattern'):null;
      if(felement.value) {
        if((pattern && !new RegExp(pattern).test(felement.value))) {
          err_mesg = new Element('span', {'class':'form-error-msg'});
          err_mesg.set('html', felement.getProperty('placeholder'));
          err_mesg.inject(label, 'bottom');
          label.className='req2';
          fsubmit = false;
        }
      }
    }

  }

  if(!fsubmit) alert('Errore nella compilazione del form');

  return fsubmit;
}

/*
 * Translations
 */
gino.translations = {
  prepareTrlForm: function(lng_code, el, tbl, field, type, id_value, width, toolbar, url) {

    var open = false;
    var el_parent = el.getParent();

    var active_trnsl = el_parent.getChildren('.trnsl-lng-sel')[0];

     // close
    if(active_trnsl) {
      el_parent.getNext('#trnsl-container').dispose();
      try {
        CKEDITOR.remove(CKEDITOR.instances['trnsl_'+field]);
      }
      catch(e) {}
      active_trnsl.removeClass('trnsl-lng-sel');
    }
     // open
    if(el != active_trnsl) {
      el.addClass('trnsl-lng-sel');
      var myTrnsl = new Element('div', {
        'id' : 'trnsl-container',
        'class' : 'form_translation'
      }).inject(el_parent, 'after');

      var data = 'lng_code='+lng_code+'&tbl='+tbl+'&field='+field+'&type='+type+'&id_value='+id_value+'&width='+width+'&toolbar='+toolbar;		
      gino.ajaxRequest('post', url, data, myTrnsl, {'load':tbl+field, script_eval: true, callback: function() { 
      }});
    }
  },
  callAction: function(url, type, tbl, field, id_value, ckeditor, lng_code, action) {

    var text = ckeditor ? encodeURIComponent(CKEDITOR.instances['trnsl_' + field].getData()) : $('trnsl_' + field).getProperty('value');
    gino.ajaxRequest(
        'post', 
        url, 
        'type=' + type + '&tbl=' + tbl + '&field=' + field +'&id_value=' + id_value + '&text='+ encodeURIComponent(text) +'&lng_code=' + lng_code + '&action=' + action, tbl + field, 
        {
          'callback': function() {
            $('trnsl-container').dispose();
            $(tbl + field).getParent().getChildren('.trnsl-lng-sel').removeClass('trnsl-lng-sel');
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
  var form = $(id).getElements('div[data-clone=1]')[0].dispose();
  var cnt = $$('#' + id + ' fieldset').length;

  function rename(field_name, el, i, die) {
    var name = el.getProperty('name');
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
  $(id).getChildren('fieldset').each(function(fieldset) {
    var ctrl = fieldset.getElements('span[data-clone-ctrl]')[0];
    var filled = ctrl.get('data-clone-ctrl') == 'minus' ? true : false;
    if(filled) {		
		fieldset.getElements('input,select,textarea').each(function(el) {
			var name = rename(field_name, el, i, false);
			if(name) {
				var label_for = name;
				var label = fieldset.getElements('label[for=' + el.getProperty('name') + ']')[0];
				if(typeof label != 'undefined') {
					label.setProperty('for', label_for);
					if(typeof label.getParent('.form-row').getChildren('a.form-addrelated')[0] != 'undefined') {
						label.getParent('.form-row').getChildren('a.form-addrelated')[0].setProperty('id', 'add_' + name);
					}
				}
				el.setProperty('name', name);
				if(el.get('type') == 'radio') {
					if(/checked/i.test(el.outerHTML)) { // @todo check browser compatibility
						el.setProperty('checked', 'checked');
					}
				}
            }
		})
		var index = new Element('input[type=hidden]').setProperty('name', 'm2mt_' + field_name + '_ids[]').set('value', i).inject(fieldset.getChildren('div')[0], 'top');
		i++;
	    ctrl.addEvent('click', removeFieldset);
	}
	else {
		ctrl.addEvent('click', addFieldset);
	}
  });

  function removeFieldset() {
    if($$('#' + id + ' fieldset').length > 1) {
      $(this).getParent().getParent().dispose();
    }
    else {
      $(this).getParent().getParent().getChildren('[data-clone=1]').dispose();
      $(this).removeEvents('click');
      $(this).removeClass('fa-minus-circle').addClass('fa-plus-circle');
    }
  }

  function addFieldset() {
    var clone = form.clone();
    clone.inject($(this).getParent().getParent(), 'bottom').removeClass('hidden');
    clone.getElements('input,select,textarea').each(function(el) {
      el.setProperty('name', rename(field_name, el, cnt, true));
    })
    var index = new Element('input[type=hidden]').setProperty('name', 'm2mt_' + field_name + '_ids[]').set('value', cnt).inject(clone, 'top');
    cnt++;
    var fieldset = new Element('fieldset').adopt($(this).getParent().clone()).inject($(id), 'bottom');
    fieldset.getElements('span[data-clone-ctrl]')[0].addEvent('click', addFieldset);
    $(this).removeEvents('click');
    $(this).removeClass('fa-plus-circle').addClass('fa-minus-circle');
    $(this).addEvent('click', removeFieldset);

    gino.EventDispatcher.emit('m2mthrough-' + field_name + '-added');

  }

/*
  addAddEvent($(id).getElements('span[data-clone=add]')[0]);

  cnt = $$('#' + id + ' fieldset').length;

  function addFieldset() {
    var clone = form.clone();
    clone.inject($(this).getParent().getParent(), 'bottom').removeClass('hidden');
    clone.getElements('input,select,textarea').each(function(el) {
      el.setProperty('name', 'm2mt_' + field_name + '_' + el.getProperty('name') + '_' + cnt);
    })
    var index = new Element('input[type=hidden]').setProperty('name', 'm2mt_' + field_name + '_ids[]').set('value', cnt).inject(clone, 'top');
    cnt++;
    var fieldset = new Element('fieldset').adopt($(this).getParent().clone()).inject($(id), 'bottom');
    addAddEvent(fieldset.getElements('span[data-clone=add]')[0]);
    $(this).removeEvents('click');
    $(this).removeClass('fa-plus-circle').addClass('fa-minus-circle');
    addRemoveEvent($(this));
  }
  function addAddEvent(el) {
    el.addEvent('click', addFieldset);
  }
  function addRemoveEvent(el) {
    el.addEvent('click', removeFieldset);
  }*/
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
gino.layerWindow = new Class({

  Implements: [Options, Chain],
  options: {
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
  },
  initialize: function(options) {

    this.showing = false;	

    if($defined(options)) this.setOptions(options);
    this.checkOptions();

    if($chk(this.options.title)) this.title = this.options.title;
    if($chk(this.options.html)) this.html = this.options.html;
    if($chk(this.options.htmlNode)) this.htmlNode = $type(this.options.htmlNode)=='element' ? this.options.htmlNode : $(this.options.htmlNode);
    if($chk(this.options.url)) this.url = this.options.url;
    if(!$chk(this.options.maxHeight)) this.options.maxHeight = gino.getViewport().height-100;

    if(this.options.reloadZindex) gino.maxZindex = gino.getMaxZindex();

  },
  checkOptions: function() {
    var rexp = /[0-9]+/;
    if(!rexp.test(this.options.width) || this.options.width<this.options.minWidth) this.options.width = 400;
  },
  setTitle: function(title) {
    this.title = title;	 
    if(this.showing) this.header.set('html', title);
  },
  setHtml: function(html) {
    this.html = html;	 
    if(this.showing) this.body.set('html', html);
  },
  setUrl: function(url) {
    this.url = url;	 
    if(this.showing) this.request();
  },
  display: function(element, opt) {
    this.loader = new gino.Loader();
    this.loader.show();
    this.delement = !element ? null : $type(element)=='element'? element:$(element);
    this.dopt = opt;
    if(this.options.disableObjects) this.dObjects();
    this.showing = true;
    
    if(this.options.overlay) this.renderOverlay();
    this.renderContainer();
    this.renderHeader();
    this.renderBody();
    this.renderFooter();
    this.container.setStyle('width', (this.body.getCoordinates().width)+'px');
    this.initBodyHeight = this.body.getStyle('height').toInt();
    this.initContainerDim = this.container.getCoordinates();

    if(this.options.draggable) this.makeDraggable();
    if(this.options.resize) this.makeResizable();

  },
  renderOverlay: function() {
    var docDim = document.getScrollSize();
    this.overlay = new Element('div', {'class': 'abiWinOverlay'});
    this.overlay.setStyles({
      'top': '0px',
      'left': '0px',
      'width': docDim.x,
      'height': docDim.y,
      'z-index': ++gino.maxZindex
    });

    this.overlay.inject(document.body);
    
  },	
  dObjects: function() {
    for(var i=0;i<window.frames.length;i++) {
      var myFrame = window.frames[i];
      if(gino.sameDomain(myFrame)) {
        var obs = myFrame.document.getElementsByTagName('object');
        for(var ii=0; ii<obs.length; ii++) {
          obs[ii].style.visibility='hidden';
        }
      }
    }
    $$('object').each(function(item) {
      item.style.visibility='hidden';
    })
  },
  eObjects: function() {
    for(var i=0;i<window.frames.length;i++) {
      var myFrame = window.frames[i];
      if(gino.sameDomain(myFrame)) {
        var obs = myFrame.document.getElementsByTagName('object');
        for(var ii=0; ii<obs.length; ii++) {
          obs[ii].style.visibility='visible';
        }
      }
    }
    $$('object').each(function(item) {
      item.style.visibility='visible';
    })
  },
  renderContainer: function() {
    this.container = new Element('section', {'id':this.options.id, 'class':'abiWin'});

    this.container.setStyles({
      'visibility': 'hidden'
    })
    this.setFocus();
    this.container.addEvent('mousedown', this.setFocus.bind(this));
    this.container.inject(document.body);
  },
  locateContainer: function() {

    var elementCoord = $chk(this.delement) ? this.delement.getCoordinates() : null;
    this.top = (this.dopt && $chk(this.dopt.top)) ? this.dopt.top < 0 ? 0 : this.dopt.top : elementCoord 
      ? elementCoord.top 
      : (gino.getViewport().cY-this.container.getCoordinates().height/2);
    this.left = (this.dopt && $chk(this.dopt.left)) ? this.dopt.left < 0 ? 0 : this.dopt.left : elementCoord 
      ? elementCoord.left 
      : (gino.getViewport().cX-this.container.getCoordinates().width/2);

    this.container.setStyles({
      'top': this.top+'px',
      'left':this.left+'px',
      'visibility': 'visible'
    })

    this.loader.remove();
  },
  renderHeader: function() {
    this.header = new Element('header', {'class':'abiHeader'});
    this.header.set('html', '<h1>' + this.title + '</h1>');

    var closeEl;
    if($chk(this.options.closeButtonUrl) && $type(this.options.closeButtonUrl)=='string') {
      closeEl = new Element('img', {'src':this.options.closeButtonUrl, 'class':'close'});
    }
    else if($chk(this.options.closeButtonLabel)) {
      closeEl = new Element('span', {'class':'close'});
      closeEl.set('html', this.options.closeButtonLabel);
    }
    else 
      closeEl = new Element('span', {'class':'close_img'});

    closeEl.addEvent('click', this.closeWindow.bind(this));
    this.header.inject(this.container, 'top');
    closeEl.inject(this.header, 'before');
            
  },
  renderBody: function() {
    this.body = new Element('div', {'id':this.options.bodyId, 'class':'body'});
    this.body.setStyles({
      'width': this.options.width,
      'height': this.options.height,
      'max-height': this.options.maxHeight
    })
    this.body.inject(this.container, 'bottom');
    $chk(this.url) ? this.request() : $chk(this.htmlNode) ? this.body.set('html', this.htmlNode.clone(true, true).get('html')) : this.body.set('html', this.html);
    if(!$chk(this.url) || this.options.height) this.locateContainer();
  },
  renderFooter: function() {
    this.footer = new Element('footer');
    this.footer.inject(this.container, 'bottom');
            
  },
  renderResizeCtrl: function() {
    this.resCtrl = new Element('div').setStyles({'position':'absolute', 'right':'0', 'bottom':'0', 'width':'10px', 'height':'10px', 'cursor':'se-resize'});
    this.resCtrl.inject(this.footer, 'top');		
  },
  makeDraggable: function() {
    var docDim = document.getCoordinates();
    if(this.options.draggable) {
      var dragInstance = new Drag(this.container, {
        'handle':this.header, 
        'limit':{'x':[0, (docDim.width-this.container.getCoordinates().width)], 'y':[0, ]}
      });
      this.header.setStyle('cursor', 'move');
    }
    
  },
  makeResizable: function() {
    this.renderResizeCtrl();
    var ylimit = $chk(this.options.maxHeight) 
      ? this.options.maxHeight+this.header.getCoordinates().height+this.header.getStyle('margin-top').toInt()+this.header.getStyle('margin-bottom').toInt()+this.container.getStyle('padding-top').toInt()+this.container.getStyle('padding-bottom').toInt() 
      : document.body.getCoordinates().height-20;
    this.container.makeResizable({
      'handle':this.resCtrl, 
      'limit':{'x':[this.options.minWidth, (document.body.getCoordinates().width-20)], 'y':[this.options.minHeight, ylimit]},
      'onDrag': function(container) {this.resizeBody()}.bind(this),
      'onComplete': function(container) {this.makeDraggable()}.bind(this)
    });
  },
  resizeBody: function() {
    this.body.setStyles({
      'width': this.options.width.toInt()+(this.container.getCoordinates().width-this.initContainerDim.width),
      'height': this.initBodyHeight+(this.container.getCoordinates().height-this.initContainerDim.height)		
    });	      
  },
  request: function() {
    gino.ajaxRequest('post', this.url, this.options.url_param, this.body, {'script':true, 'load':this.body, 'callback':this.locateContainer.bind(this)});	 
  },
  setFocus: function() {
    if(!this.container.style.zIndex || (this.container.getStyle('z-index').toInt() < gino.maxZindex))
      this.container.setStyle('z-index', ++gino.maxZindex);
  },
  closeWindow: function() {
    this.showing = false;
    if(this.options.disableObjects) this.chain($(this.container).dispose(), this.eObjects());
    else $(this.container).dispose();
    if(this.options.overlay) this.overlay.dispose();
        if($chk(this.options.closeCallback)) this.options.closeCallback(this.options.closeCallbackParam);		
    if(this.options.destroyOnClose) for(var prop in this) this[prop] = null;
  }
})

gino.getViewport = function() {

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

  top = $chk(self.pageYOffset) 
    ? self.pageYOffset 
    : (document.documentElement && $chk(document.documentElement.scrollTop))
      ? document.documentElement.scrollTop
      : document.body.clientHeight;

  left = $chk(self.pageXOffset) 
    ? self.pageXOffset 
    : (document.documentElement && $chk(document.documentElement.scrollTop))
      ? document.documentElement.scrollLeft
      : document.body.clientWidth;

  cX = left + width/2;

  cY = top + height/2;

  return {'width':width, 'height':height, 'left':left, 'top':top, 'cX':cX, 'cY':cY};

}

gino.getMaxZindex = function() {
  
  var maxZ = 0;
  $$('body *').each(function(el) {if(el.getStyle('z-index').toInt()) maxZ = Math.max(maxZ, el.getStyle('z-index').toInt())});

  return maxZ;

}

gino.maxZindex = gino.getMaxZindex();

/*
 * hScrollingList class
 *
 * hScrollingList method: constructor
 *   Syntax
 *      var myInstance = new hScrollingList(list, vpItems, scrollableWidth, itemWidth, [options]);
 *   Arguments 
 *      1. list - (string|Object) The UL element or its id attribute to be transformed
 *      2. vpItems - (int) The number of element showed in a viewport (the viewport changes (scrolls) when clicking on the arrows)
 *      3. scrollableWidth - (int) The width in px of the scrollable object
 *      4. itemWidth - (int) The width in px of a list element
 *	5. options - (object, optional) The options object.
 *   Options
 *	- id (string: default to null) The id of the object
 *	- list_height (string: default to null) The height of the list, if null the height depends on the contents
 *	- tr_duration (int: default to 1000) The duration in ms of the transaction betwen viewports
 *      ........ maybe many more options in the future.........
 *
 * hScrollingList method: updateCtrl
 * updates the status of the controllers and their actions
 *   Syntax
 *      myInstance.updateCtrl();
 *
 * hScrollingList method: deactivateCtrl
 * Disables the controllers (status OFF and no actions)
 *   Syntax
 *      myInstance.deactivateCtrl();
 *
 */
gino.hScrollingList = new Class({

  Implements: [Options],
  options: {
    id: null,
    list_height: null,
                tr_duration: 1000 
    // maybe more options in the future here
  },
      initialize: function(list, vpItems, scrollableWidth, itemWidth, options) {
  
    if($defined(options)) this.setOptions(options);

    this.list = $type(list)=='element'? list:$(list);
    this.list.setStyle('visibility', 'hidden'); // hide list transformations (vertical to horizontal)
    this.listElements = this.list.getChildren('li');

    this.setWidths(scrollableWidth, itemWidth);
    this.vpItems = vpItems;
    
    this.setSlide();
    this.setStyles();  // vpItems property may change!
    this.setWrapper();

    this.list.setStyle('visibility', 'visible'); // when the structure is ready, the list is showed

    this.vps = 1;
    this.tots = Math.ceil(this.listElements.length/this.vpItems);
    this.updateCtrl();
    this.tr = new Fx.Tween(this.slide, {
        'duration': this.options.tr_duration,
        'transition': 'quad:out',
        'onComplete' : function() {this.busy=false}.bind(this)
      });


  },
  setWidths: function(tw, iw) {
    this.width = tw;
    this.ctrlWidth = 24;
    this.cWidth = this.width - 2*this.ctrlWidth;
    this.iWidth = iw;
  },
  setSlide: function() {
    var clear = new Element('div', {'styles':{'clear':'both'}});
    this.slide = new Element('div', {
      'styles': {'position':'relative', 'width':'10000em'},
      'class': 'slide'	
    });
    this.slide.inject(this.list, 'before');
    this.slide.grab(this.list);
    clear.inject(this.slide, 'bottom');
  },
  setWrapper: function() {
    this.wrapper = new Element('div', {
        'styles':{'width': this.width+'px'}
    });	    
    var ctrlHeight = this.listElements[0].getCoordinates().height;
    for(var i=1; i<this.listElements.length; i++) 
      if(this.listElements[i].getCoordinates().height > ctrlHeight) 
        ctrlHeight = this.listElements[i].getCoordinates().height;

    this.leftCtrl = new Element('div', {
      'styles': {'float': 'left', 'width': this.ctrlWidth+'px', 'height':ctrlHeight+'px'}
    })
    this.rightCtrl = new Element('div', {
      'styles': {'float': 'right', 'width': this.ctrlWidth+'px', 'height':ctrlHeight+'px'}		
    })
    this.itemContainer = new Element('div', {
      'styles': {'position': 'relative', 'overflow': 'hidden', 'float': 'left', 'width': this.cWidth+'px'}		
    })
    this.wrapper.adopt(this.leftCtrl, this.itemContainer, this.rightCtrl);
    this.wrapper.inject(this.slide, 'before');
    this.itemContainer.adopt(this.slide);
  },
  setStyles: function () {
    this.list.setStyles({'margin': '0', 'padding': '0', 'list-style-type':'none', 'list-style-position':'outside'});

    var esw = this.vpItems*this.iWidth;
    while(esw>this.cWidth) esw = --this.vpItems*this.iWidth;
    var margin = (this.cWidth - esw)/2;

    for(var i=0; i<this.listElements.length; i++) {
      var item = this.listElements[i];
      var r = i%this.vpItems;
      item.setStyles({
        'float':'left',
        'width': this.iWidth+'px',
        'margin-left': !i ? margin+'px' : r ? '0px' : 2*margin+'px',	
        'height': this.options.list_height ? this.options.list_height+'px' : 'auto'
      })
    }
  },
  scroll: function(d) {
    
    if(this.busy) return false;

    this.busy = true;
    if(d=='right') 
      this.tr.start('left', '-'+(this.cWidth*this.vps++)+'px');
    else if(d=='left') 
      this.tr.start('left', '-'+(this.cWidth*(--this.vps-1))+'px');
  
    this.updateCtrl();
  },
  updateCtrl: function() {

    var lclass = this.vps == 1 ? 'leftCtrlOff':'leftCtrl';
    var rclass = this.vps == this.tots ? 'rightCtrlOff':'rightCtrl';
    this.leftCtrl.setProperty('class', lclass);		    
    this.rightCtrl.setProperty('class', rclass);	    

    if(this.vps==1) {
      this.leftCtrl.removeEvents('mouseover');
      this.leftCtrl.removeEvents('mouseout');
      this.leftCtrl.removeEvents('click');
      this.le = false;
    }
    else if(!this.le) {
      this.leftCtrl.addEvent('mouseover', function() {this.setProperty('class', 'leftCtrlOver')});
      this.leftCtrl.addEvent('mouseout', function() {this.setProperty('class', 'leftCtrl')});
      this.leftCtrl.addEvent('click', this.scroll.bind(this, 'left'));
      this.le = true;
    }

    if(this.vps == this.tots) {
      this.rightCtrl.removeEvents('mouseover');
      this.rightCtrl.removeEvents('mouseout');
      this.rightCtrl.removeEvents('click');
      this.re = false;
    
    }
    else if(!this.re) {
      this.rightCtrl.addEvent('mouseover', function() {this.setProperty('class', 'rightCtrlOver')});
      this.rightCtrl.addEvent('mouseout', function() {this.setProperty('class', 'rightCtrl')});
      this.rightCtrl.addEvent('click', this.scroll.bind(this, 'right'));
      this.re = true;
    }

  },
  deactivateCtrl: function() {
    
    this.leftCtrl.removeEvents('mouseover');
    this.leftCtrl.removeEvents('mouseout');
    this.leftCtrl.removeEvents('click');
    this.le = false;	
    this.rightCtrl.removeEvents('mouseover');
    this.rightCtrl.removeEvents('mouseout');
    this.rightCtrl.removeEvents('click');
    this.re = false;
    this.leftCtrl.setProperty('class', 'leftCtrlOff');		    
    this.rightCtrl.setProperty('class', 'rightCtrlOff');
  }
});

/*
 * vScrollingList class
 *
 * vScrollingList method: constructor
 *   Syntax
 *      var myInstance = new vScrollingList(list, vpItems, scrollableWidth, itemWidth, [options]);
 *   Arguments 
 *      1. list - (string|Object) The UL element or its id attribute to be transformed
 *      2. vpItems - (int) The number of element showed in a viewport (the viewport changes (scrolls) when clicking on the arrows)
 *      3. scrollableHeight - (int) The height in px of the scrollable object
 *      4. itemHeight - (int) The height in px of a list element
 *	5. options - (object, optional) The options object.
 *   Options
 *	- id (string: default to null) The id of the object
 *	- list_width (string: default to null) The width of the list, if null the width takes all available space
 *	- tr_duration (int: default to 1000) The duration in ms of the transaction betwen viewports
 *      ........ maybe many more options in the future.........
 *
 * vScrollingList method: updateCtrl
 * updates the status of the controllers and their actions
 *   Syntax
 *      myInstance.updateCtrl();
 *
 * vScrollingList method: deactivateCtrl
 * Disables the controllers (status OFF and no actions)
 *   Syntax
 *      myInstance.deactivateCtrl();
 *
 */
gino.vScrollingList = new Class({

  Implements: [Options],
  options: {
    id: null,
    list_width: null,
                tr_duration: 1000 
    // maybe more options in the future here
  },
      initialize: function(list, vpItems, scrollableHeight, itemHeight, options) {
  
    if($defined(options)) this.setOptions(options);

    this.list = $type(list)=='element'? list:$(list);
    this.listElements = this.list.getChildren('li');

    this.setHeights(scrollableHeight, itemHeight);
    this.vpItems = vpItems;
    
    this.setSlide();
    this.setStyles();  // vpItems property may change!
    this.setWrapper();

    this.vps = 1;
    this.tots = Math.ceil(this.listElements.length/this.vpItems);
    this.updateCtrl();
    this.tr = new Fx.Tween(this.slide, {
        'duration': this.options.tr_duration,
        'transition': 'quad:out',
        'onComplete' : function() {this.busy=false}.bind(this)
      });


  },
  setHeights: function(th, ih) {
    this.height = th;
    this.ctrlHeight = 24;
    this.cHeight = this.height - 2*this.ctrlHeight;
    this.iHeight = ih;
  },
  setSlide: function() {
    var clear = new Element('div', {'styles':{'clear':'both'}});
    this.slide = new Element('div', {
      'styles': {'position':'relative', 'height':'10000em', 'padding-top':'2px'},  //margin collapsing	
      'class': 'slide'
    });
    this.slide.inject(this.list, 'before');
    this.slide.grab(this.list);
    clear.inject(this.slide, 'bottom');
  },
  setWrapper: function() {
    this.wrapper = new Element('div', {
      'styles':{'height': this.height+'px'}
    });	    

    this.topCtrl = new Element('div', {
      'styles': {'height': this.ctrlHeight+'px'}
    })
    this.bottomCtrl = new Element('div', {
      'styles': {'height':this.ctrlHeight+'px'}		
    })
    this.itemContainer = new Element('div', {
      'styles': {'position': 'relative', 'overflow': 'hidden', 'height': this.cHeight+'px'}		
    })
    this.wrapper.adopt(this.topCtrl, this.itemContainer, this.bottomCtrl);
    this.wrapper.inject(this.slide, 'before');
    this.itemContainer.adopt(this.slide);
  },
  setStyles: function () {
    this.list.setStyles({'margin': '0', 'padding': '0', 'list-style-type':'none', 'list-style-position':'outside'});

    var realHeight = this.cHeight - 4; // padding of slide element 2px, X2 for symmetry
    var esh = this.vpItems*(this.iHeight+1)+1; // border of li elements
    while(esh>realHeight) esh = --this.vpItems*(this.iHeight+1)+1; 
    var margin = (this.cHeight - esh)/2; // margin is calculated not considering the padding, which is considered in the margin of the first element only

    for(var i=0; i<this.listElements.length; i++) {
      var item = this.listElements[i];
      var r = i%this.vpItems;
      item.setStyles({
        'border-top': (r ? 0:1)+'px solid #000',
        'border-bottom': '1px solid #000',
        'height': this.iHeight+'px',
        'padding': '0',
        'float': 'left',
        'clear': 'left',
        'margin': '0',
        'margin-top': !i ? (margin-2)+'px' : r ? '0px' : 2*margin+'px',	
        'width': this.options.list_width ? this.options.list_width+'px' : '100%'
      })
    }
  },
  scroll: function(d) {
    
    if(this.busy) return false;

    this.busy = true;
    if(d=='bottom') 
      this.tr.start('top', '-'+(this.cHeight*this.vps++)+'px');
    else if(d=='top') 
      this.tr.start('top', '-'+(this.cHeight*(--this.vps-1))+'px');
  
    this.updateCtrl();
  },
  updateCtrl: function() {

    var tclass = this.vps == 1 ? 'topCtrlOff':'topCtrl';
    var bclass = this.vps == this.tots ? 'bottomCtrlOff':'bottomCtrl';
    this.topCtrl.setProperty('class', tclass);		    
    this.bottomCtrl.setProperty('class', bclass);	    

    if(this.vps==1) {
      this.topCtrl.removeEvents('mouseover');
      this.topCtrl.removeEvents('mouseout');
      this.topCtrl.removeEvents('click');
      this.te = false;
    }
    else if(!this.te) {
      this.topCtrl.addEvent('mouseover', function() {this.setProperty('class', 'topCtrlOver')});
      this.topCtrl.addEvent('mouseout', function() {this.setProperty('class', 'topCtrl')});
      this.topCtrl.addEvent('click', this.scroll.bind(this, 'top'));
      this.te = true;
    }

    if(this.vps == this.tots) {
      this.bottomCtrl.removeEvents('mouseover');
      this.bottomCtrl.removeEvents('mouseout');
      this.bottomCtrl.removeEvents('click');
      this.be = false;
    
    }
    else if(!this.be) {
      this.bottomCtrl.addEvent('mouseover', function() {this.setProperty('class', 'bottomCtrlOver')});
      this.bottomCtrl.addEvent('mouseout', function() {this.setProperty('class', 'bottomCtrl')});
      this.bottomCtrl.addEvent('click', this.scroll.bind(this, 'bottom'));
      this.be = true;
    }

  },
  deactivateCtrl: function() {
    
    this.topCtrl.removeEvents('mouseover');
    this.topCtrl.removeEvents('mouseout');
    this.topCtrl.removeEvents('click');
    this.te = false;	
    this.bottomCtrl.removeEvents('mouseover');
    this.bottomCtrl.removeEvents('mouseout');
    this.bottomCtrl.removeEvents('click');
    this.be = false;
    this.topCtrl.setProperty('class', 'topCtrlOff');		    
    this.bottomCtrl.setProperty('class', 'bottomCtrlOff');
  }
});

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
    var elem = document.getElements('select[name=' + name + ']')[0];
    // select
    if (elem) {
        new Element('option[value=' + newId + '][selected=selected]').set('text', newRepr).inject(elem);
    }
    // multicheck
    else {
        var elem = document.getElements('input[type=checkbox][name=' + name + ']')[0];
        if(elem) {
            new Element('tr').adopt(
                new Element('td').set('text', newRepr),
                new Element('td').adopt(
                    new Element('input[type=checkbox][name=' + name + '][value=' + newId + '][checked=checked]')
                )
            ).inject(elem.getParent('tr'), 'before');
        }
        else {
            var label = document.getElements('label[for=' + name + ']')[0];
            label.getNext('.form-multicheck').getElements('table tr')[0].empty().adopt(
                new Element('td').set('text', newRepr),
                new Element('td').adopt(
                    new Element('input[type=checkbox][name=' + name + '][value=' + newId + '][checked=checked]')
                )
            );
        }
    }
    win.close();
}

gino.checkAll = function(controller, container) {
    if(!controller.checked) {
        container.getElements('input[type=checkbox][value]').each(function(c) {
            if($(c).getParent('td')) {
                if(c.getParent('td').getParent('tr').getStyle('display') != 'none') {
                    c.removeProperty('checked');
                }
            }
        });
    }
    else {
        container.getElements('input[type=checkbox][value]').each(function(c) {
            if($(c).getParent('td')) {
                if($(c).getParent('td').getParent('tr').getStyle('display') != 'none' && !c.getProperty('disabled')) {
                    c.setProperty('checked', 'checked');
                }
            }
        });
    }
}

RegExp.escape= function(s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};

gino.filterMulticheck = function(controller, container) {
    var text = controller.get('value');
    var rexp = new RegExp(RegExp.escape(text), 'i');
    container.getElements('tr').each(function(tr) {
        var tds = tr.getChildren('td');
        if(tds.length > 1) {
            var display = false;
            for(var i = 0; i < tds.length; i++) {
                var td = tds[i];
                if(!td.getChildren('input[type=checkbox]').length) {
                    var label = td.get('text');
                    if(rexp.test(label)) {
                        display = true;
                    }
                }
            }
            if(display) {
                tr.setStyle('display', '');
            }
            else {
                tr.setStyle('display', 'none');
            }
        }
    });
}

gino.slugControl = function(slug_field_id, json_fields) {
    var slug_field = $(slug_field_id);
    var fields = JSON.decode(json_fields);

    var onblur = function() {
        var text_parts = [];
        fields.each(function(field_name) {
            var field = slug_field.getParent('form').getElement('input[name=' + field_name + ']');
            text_parts.push(field.get('value'));
        })
        var text = text_parts.join('-');
        slug_field.set('value', text.slugify());
    };

    fields.each(function(field_name) {
        slug_field.getParent('form').getElement('input[name=' + field_name + ']').addEvent('blur', onblur);
    })
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
