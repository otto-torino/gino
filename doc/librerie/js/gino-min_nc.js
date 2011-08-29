/* GINO FULL JS LIBRARY */
/* SAME POLICY FRAMES */
function sameDomain(win){
	var H=location.href,
	local= H.substring(0, H.indexOf(location.pathname));
    	try {
        	win=win.document;
        	return win && win.URL && win.URL.indexOf(local)== 0;
    	}
    	catch(er){
        	return false
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
var loading = "<img src='img/ajax-loader.gif' alt='loading...'>";
var requestCache = new Array();
function ajaxRequest(method, url, data, target, options) {

	var opt = {
		cache: false,
		cacheTime: 3600000,
		load: null,
		script: false,
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
	if(opt.cache && $defined(requestCache[url+data]) && ($time() - requestCache[url+data][0] < opt.cacheTime)) {
		if(opt.setvalue) target.value = requestCache[url+data][1];
		else target.set('html', requestCache[url+data][1]); 

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
			if(opt_load) opt_load.set('html', loading); 
		},
		onComplete: function(responseTree, responseElements, responseHTML, responseJavaScript) {
			if(opt_load) opt_load.set('html', ''); 
			rexp = /request error:(.*)/;
			var err_match = rexp.exec(responseHTML);
			if($chk(err_match)) alert(err_match[1]);
			else {
				if(opt.setvalue && target) target.setProperty('value',responseHTML);
				else if(target) target.set('html', responseHTML);
				if(opt.cache) requestCache[url+data] = new Array($time(), responseHTML);
				if(opt.callback && opt.callback_params) opt.callback(opt.callback_params);
				else if($chk(opt.callback)) opt.callback();
				parseFunctions();
			}
		}
	}).send();

}
/* FORM LIBRARY */
function confirmSubmit(msg) {
	var message = $chk(msg)? msg:"Are you sure you wish to continue?";
	var agree = confirm(message);
	return agree? true:false;
}

function validateForm(formObj) {

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

		if(typeof window.dojo_textareas != 'undefined') {
			console.log(window.dojo_textareas);
		    if(typeof window.dojo_textareas[formid+'_'+felement_name] != 'undefined') {
			felement.value = dojo_textareas[formid+'_'+felement_name].get('value');
			felement.type = 'text';
			if(/^<br( \/)?>\n$/.test(felement.value)) felement.value = '';
		    }
		}

		if(label.hasClass('req') && (!$chk(felement.getParents('td[class=formCell]')[0]) || (felement.getParents('td[class=formCell]')[0].style.display!='none'))) {

			if(felement.type=='text' || felement.type=='password' || felement.match('textarea') || felement.match('select') || felement.type=='hidden') {
				if(!$chk(felement.value)) err_detected = true;
			}
			else if(felement.type=='radio' || felement.type=='checkbox') {
				var checked = false;
				for(var ii=0;ii<felements.length;ii++) 
					if(felements[ii].checked) {checked = true;break;}
			       	if(!checked) err_detected = true;
			}
		}

		if(err_detected) {
			divMsg = new Element('div', {'class':'formErrMsg'});
			divMsg.set('html', 'campo obbligatorio');
			divMsg.inject(felement, 'before');
			label.className='req2';
			fsubmit = false;
		}

		if(felement.type=='text') {
			var pattern = felement.getProperty('pattern') ?  felement.getProperty('pattern'):null;
			if($chk(felement.value)) {
				if((pattern && !new RegExp(pattern).test(felement.value))) {
					divMsg = new Element('div', {'class':'formErrMsg'});
					divMsg.set('html', felement.getProperty('placeholder'));
					divMsg.inject(felement, 'before');
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
function prepareTrlForm(lng_code, el, tbl, field, type, id_value, width, fck_toolbar, home_file) {
	 
	 var close = false;
	 var el_parent = el.getParent();	
	 
	 // remove all translations forms to avoid duplication of input ids
	 if($('trnsl_container')) {
	 	if(el_parent==$('trnsl_container').getPrevious('div') && el.hasClass('trnsl_lng_sel')) close=true;
	 	var previous = $('trnsl_container').getPrevious();
	 	previous.getChildren('span').removeClass('trnsl_lng_sel');
	 	$('trnsl_container').dispose();
		CKEDITOR.remove(CKEDITOR.instances['trnsl_'+field]);
	 }
	 // create a new element thet contains the form
	 var myTrnsl = new Element('div', {
		'id' : 'trnsl_container',
		'class' : 'form_translation'
	});				
									
	el_parent.getChildren('span').removeClass('trnsl_lng_sel');
	
	if(!close){
		el.addClass('trnsl_lng_sel');						
		myTrnsl.inject(el_parent, 'after');
					
		var url = home_file+'?pt[language-formTranslation]';	
		var data = 'lng_code='+lng_code+'&tbl='+tbl+'&field='+field+'&type='+type+'&id_value='+id_value+'&width='+width+'&fck_toolbar='+fck_toolbar;		
		ajaxRequest('post', url, data, myTrnsl, {'load':tbl+field, 'cache':true, 'cacheTime':5000, callback: function() { 
			var url2 = home_file+'?pt[language-replaceTextarea]';	
			ajaxRequest('post', url2, 'fck_toolbar='+fck_toolbar+'&width='+width+'&field='+field, null, {'script': true }); 
		}});
	}

}
/**
 * datepicker.js - MooTools Datepicker class
 * @version 1.16
 * 
 * by MonkeyPhysics.com
 *
 * Source/Documentation available at:
 * http://www.monkeyphysics.com/mootools/script/2/datepicker
 * 
 * --
 * 
 * Smoothly animating, very configurable and easy to install.
 * No Ajax, pure Javascript. 4 skins available out of the box.
 * 
 * --
 *
 * Some Rights Reserved
 * http://creativecommons.org/licenses/by-sa/3.0/
 * 
 */

var DatePicker = new Class({
	
	Implements: Options,
	
	// working date, which we will keep modifying to render the calendars
	d: '',
	
	// just so that we need not request it over and over
	today: '',
	
	// current user-choice in date object format
	choice: {}, 
	
	// size of body, used to animate the sliding
	bodysize: {}, 
	
	// to check availability of next/previous buttons
	limit: {}, 
	
	// element references:
	attachTo: null,    // selector for target inputs
	picker: null,      // main datepicker container
	slider: null,      // slider that contains both oldContents and newContents, used to animate between 2 different views
	oldContents: null, // used in animating from-view to new-view
	newContents: null, // used in animating from-view to new-view
	input: null,       // original input element (used for input/output)
	visual: null,      // visible input (used for rendering)
	
	options: { 
		pickerClass: 'datepicker',
		days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		dayShort: 2,
		monthShort: 3,
		startDay: 1, // Sunday (0) through Saturday (6) - be aware that this may affect your layout, since the days on the right might have a different margin
		timePicker: false,
		timePickerOnly: false,
		yearPicker: true,
		yearsPerPage: 20,
		format: 'd-m-Y',
		allowEmpty: false,
		inputOutputFormat: 'U', // default to unix timestamp
		animationDuration: 400,
		useFadeInOut: !Browser.ie, // dont animate fade-in/fade-out for IE
		startView: 'month', // allowed values: {time, month, year, decades}
		positionOffset: { x: 0, y: 0 },
		minDate: null, // { date: '[date-string]', format: '[date-string-interpretation-format]' }
		maxDate: null, // same as minDate
		debug: false,
		toggleElements: null,
		
		// and some event hooks:
		onShow: function(){},   // triggered when the datepicker pops up
		onClose: function(){},  // triggered after the datepicker is closed (destroyed)
		onSelect: function(){}  // triggered when a date is selected
	},
	
	initialize: function(attachTo, options) {
		this.attachTo = attachTo;
		this.setOptions(options).attach();
		if (this.options.timePickerOnly) {
			this.options.timePicker = true;
			this.options.startView = 'time';
		}
		this.formatMinMaxDates();
		document.addEvent('mousedown', this.close.bind(this));
	},
	
	formatMinMaxDates: function() {
		if (this.options.minDate && this.options.minDate.format) {
			this.options.minDate = this.unformat(this.options.minDate.date, this.options.minDate.format);
		}
		if (this.options.maxDate && this.options.maxDate.format) {
			this.options.maxDate = this.unformat(this.options.maxDate.date, this.options.maxDate.format);
			this.options.maxDate.setHours(23);
			this.options.maxDate.setMinutes(59);
			this.options.maxDate.setSeconds(59);
		}
	},
	
	attach: function() {
		// toggle the datepicker through a separate element?
		if ($chk(this.options.toggleElements)) {
			var togglers = $$(this.options.toggleElements);
			document.addEvents({
				'keydown': function(e) {
					if (e.key == "tab") {
						this.close(null, true);
					}
				}.bind(this)
			});
		};
		
		// attach functionality to the inputs		
		$$(this.attachTo).each(function(item, index) {
			
			// never double attach
			if (item.retrieve('datepicker')) return;
			
			// determine starting value(s)
			if ($chk(item.get('value'))) {
				var init_clone_val = this.format(new Date(this.unformat(item.get('value'), this.options.inputOutputFormat)), this.options.format);
			} else if (!this.options.allowEmpty) {
				var init_clone_val = this.format(new Date(), this.options.format);
			} else {
				var init_clone_val = '';
			}
			
			// create clone
			var display = item.getStyle('display');
			var clone = item
			.setStyle('display', this.options.debug ? display : 'none')
			.store('datepicker', true) // to prevent double attachment...
			.clone()
			.store('datepicker', true) // ...even for the clone (!)
			.removeProperty('name')    // secure clean (form)submission
			.removeProperty('pattern')    // chrome and safari self-pattern-check
			.setStyle('display', display)
			.set('value', init_clone_val)
			.inject(item, 'before');
			
			// events
			if ($chk(this.options.toggleElements)) {
				togglers[index]
					.setStyle('cursor', 'pointer')
					.addEvents({
						'click': function(e) {
							this.onFocus(item, clone);
						}.bind(this)
					});
				clone.addEvents({
					'blur': function() {
						item.set('value', clone.get('value'));
					}
				});
			} else {
				clone.addEvents({
					'keydown': function(e) {
						if (this.options.allowEmpty && (e.key == "delete" || e.key == "backspace")) {
							item.set('value', '');
							e.target.set('value', '');
							this.close(null, true);
						} else if (e.key == "tab") {
							this.close(null, true);
						} else {
							e.stop();
						}
					}.bind(this),
					'focus': function(e) {
						this.onFocus(item, clone);
					}.bind(this)
				});
			}
		}.bind(this));
	},
	
	onFocus: function(original_input, visual_input) {
		var init_visual_date, d = visual_input.getCoordinates();
		
		if ($chk(original_input.get('value'))) {
			init_visual_date = this.unformat(original_input.get('value'), this.options.inputOutputFormat).valueOf();
		} else {
			init_visual_date = new Date();
			if ($chk(this.options.maxDate) && init_visual_date.valueOf() > this.options.maxDate.valueOf()) {
				init_visual_date = new Date(this.options.maxDate.valueOf());
			}
			if ($chk(this.options.minDate) && init_visual_date.valueOf() < this.options.minDate.valueOf()) {
				init_visual_date = new Date(this.options.minDate.valueOf());
			}
		}
		
		this.show({ left: d.left + this.options.positionOffset.x, top: d.top + d.height + this.options.positionOffset.y }, init_visual_date);
		this.input = original_input;
		this.visual = visual_input;
		this.options.onShow();
	},
	
	dateToObject: function(d) {
		return {
			year: d.getFullYear(),
			month: d.getMonth(),
			day: d.getDate(),
			hours: d.getHours(),
			minutes: d.getMinutes(),
			seconds: d.getSeconds()
		};
	},
	
	dateFromObject: function(values) {
		var d = new Date();
		d.setDate(1);
		['year', 'month', 'day', 'hours', 'minutes', 'seconds'].each(function(type) {
			var v = values[type];
			if (!$chk(v)) return;
			switch (type) {
				case 'day': d.setDate(v); break;
				case 'month': d.setMonth(v); break;
				case 'year': d.setFullYear(v); break;
				case 'hours': d.setHours(v); break;
				case 'minutes': d.setMinutes(v); break;
				case 'seconds': d.setSeconds(v); break;
			}
		});
		return d;
	},
	
	show: function(position, timestamp) {
		this.formatMinMaxDates();
		if ($chk(timestamp)) {
			this.d = new Date(timestamp);
		} else {
			this.d = new Date();
		}
		this.today = new Date();
		this.choice = this.dateToObject(this.d);
		this.mode = (this.options.startView == 'time' && !this.options.timePicker) ? 'month' : this.options.startView;
		this.render();
		this.picker.setStyles(position);
	},
	
	render: function(fx) {
		if (!$chk(this.picker)) {
			this.constructPicker();
		} else {
			// swap contents so we can fill the newContents again and animate
			var o = this.oldContents;
			this.oldContents = this.newContents;
			this.newContents = o;
			this.newContents.empty();
		}
		
		// remember current working date
		var startDate = new Date(this.d.getTime());
		
		// intially assume both left and right are allowed
		this.limit = { right: false, left: false };
		
		// render! booty!
		if (this.mode == 'decades') {
			this.renderDecades();
		} else if (this.mode == 'year') {
			this.renderYear();
		} else if (this.mode == 'time') {
			this.renderTime();
			this.limit = { right: true, left: true }; // no left/right in timeview
		} else {
			this.renderMonth();
		}
		
		this.picker.getElement('.previous').setStyle('visibility', this.limit.left ? 'hidden' : 'visible');
		this.picker.getElement('.next').setStyle('visibility', this.limit.right ? 'hidden' : 'visible');
		this.picker.getElement('.titleText').setStyle('cursor', this.allowZoomOut() ? 'pointer' : 'default');
		
		// restore working date
		this.d = startDate;
		
		// if ever the opacity is set to '0' it was only to have us fade it in here
		// refer to the constructPicker() function, which instantiates the picker at opacity 0 when fading is desired
		if (this.picker.getStyle('opacity') == 0) {
			this.picker.tween('opacity', 0, 1);
		}
		
		// animate
		if ($chk(fx)) this.fx(fx);
	},
	
	fx: function(fx) {
		if (fx == 'right') {
			this.oldContents.setStyles({ left: 0, opacity: 1 });
			this.newContents.setStyles({ left: this.bodysize.x, opacity: 1 });
			this.slider.setStyle('left', 0).tween('left', 0, -this.bodysize.x);
		} else if (fx == 'left') {
			this.oldContents.setStyles({ left: this.bodysize.x, opacity: 1 });
			this.newContents.setStyles({ left: 0, opacity: 1 });
			this.slider.setStyle('left', -this.bodysize.x).tween('left', -this.bodysize.x, 0);
		} else if (fx == 'fade') {
			this.slider.setStyle('left', 0);
			this.oldContents.setStyle('left', 0).set('tween', { duration: this.options.animationDuration / 2 }).tween('opacity', 1, 0);
			this.newContents.setStyles({ opacity: 0, left: 0}).set('tween', { duration: this.options.animationDuration }).tween('opacity', 0, 1);
		}
	},
	
	constructPicker: function() {
		this.picker = new Element('div', { 'class': this.options.pickerClass }).setStyle('z-index', ++window.maxZindex).inject(document.body);
		if (this.options.useFadeInOut) {
			this.picker.setStyles({opacity:0}).set('tween', { duration: this.options.animationDuration });
		}
		
		var h = new Element('div', { 'class': 'header' }).inject(this.picker);
		var titlecontainer = new Element('div', { 'class': 'title' }).inject(h);
		new Element('div', { 'class': 'previous' }).addEvent('click', this.previous.bind(this)).set('text', '«').inject(h);
		new Element('div', { 'class': 'next' }).addEvent('click', this.next.bind(this)).set('text', '»').inject(h);
		new Element('div', { 'class': 'closeButton' }).addEvent('click', this.close.bindWithEvent(this, true)).set('text', 'x').inject(h);
		new Element('span', { 'class': 'titleText' }).addEvent('click', this.zoomOut.bind(this)).inject(titlecontainer);
		
		var b = new Element('div', { 'class': 'body' }).inject(this.picker);
		this.bodysize = b.getSize();
		this.slider = new Element('div', { styles: { position: 'absolute', top: 0, left: 0, width: 2 * this.bodysize.x, height: this.bodysize.y }})
					.set('tween', { duration: this.options.animationDuration, transition: Fx.Transitions.Quad.easeInOut }).inject(b);
		this.oldContents = new Element('div', { styles: { position: 'absolute', top: 0, left: this.bodysize.x, width: this.bodysize.x, height: this.bodysize.y }}).inject(this.slider);
		this.newContents = new Element('div', { styles: { position: 'absolute', top: 0, left: 0, width: this.bodysize.x, height: this.bodysize.y }}).inject(this.slider);
	},
	
	renderTime: function() {
		var container = new Element('div', { 'class': 'time' }).inject(this.newContents);
		
		if (this.options.timePickerOnly) {
			this.picker.getElement('.titleText').set('text', 'Select a time');
		} else {
			this.picker.getElement('.titleText').set('text', this.format(this.d, 'j M, Y'));
		}
		
		new Element('input', { type: 'text', 'class': 'hour' })
			.set('value', this.leadZero(this.d.getHours()))
			.addEvents({
				mousewheel: function(e) {
					var i = e.target, v = i.get('value').toInt();
					i.focus();
					if (e.wheel > 0) {
						v = (v < 23) ? v + 1 : 0;
					} else {
						v = (v > 0) ? v - 1 : 23;
					}
					i.set('value', this.leadZero(v));
					e.stop();
				}.bind(this)
			})
			.set('maxlength', 2)
			.inject(container);
			
		new Element('input', { type: 'text', 'class': 'minutes' })
			.set('value', this.leadZero(this.d.getMinutes()))
			.addEvents({
				mousewheel: function(e) {
					var i = e.target, v = i.get('value').toInt();
					i.focus();
					if (e.wheel > 0) {
						v = (v < 59) ? v + 1 : 0;
					} else {
						v = (v > 0) ? v - 1 : 59;
					}
					i.set('value', this.leadZero(v));
					e.stop();
				}.bind(this)
			})
			.set('maxlength', 2)
			.inject(container);
		
		new Element('div', { 'class': 'separator' }).set('text', ':').inject(container);
		
		new Element('input', { type: 'submit', value: 'OK', 'class': 'ok' })
			.addEvents({
				click: function(e) {
					e.stop();
					this.select($merge(this.dateToObject(this.d), { hours: this.picker.getElement('.hour').get('value').toInt(), minutes: this.picker.getElement('.minutes').get('value').toInt() }));
				}.bind(this)
			})
			.set('maxlength', 2)
			.inject(container);
	},
	
	renderMonth: function() {
		var month = this.d.getMonth();
		
		this.picker.getElement('.titleText').set('text', this.options.months[month] + ' ' + this.d.getFullYear());
		
		this.d.setDate(1);
		while (this.d.getDay() != this.options.startDay) {
			this.d.setDate(this.d.getDate() - 1);
		}
		
		var container = new Element('div', { 'class': 'days' }).inject(this.newContents);
		var titles = new Element('div', { 'class': 'titles' }).inject(container);
		var d, i, classes, e, weekcontainer;

		for (d = this.options.startDay; d < (this.options.startDay + 7); d++) {
			new Element('div', { 'class': 'title day day' + (d % 7) }).set('text', this.options.days[(d % 7)].substring(0,this.options.dayShort)).inject(titles);
		}
		
		var available = false;
		var t = this.today.toDateString();
		var currentChoice = this.dateFromObject(this.choice).toDateString();
		
		for (i = 0; i < 42; i++) {
			classes = [];
			classes.push('day');
			classes.push('day'+this.d.getDay());
			if (this.d.toDateString() == t) classes.push('today');
			if (this.d.toDateString() == currentChoice) classes.push('selected');
			if (this.d.getMonth() != month) classes.push('otherMonth');
			
			if (i % 7 == 0) {
				weekcontainer = new Element('div', { 'class': 'week week'+(Math.floor(i/7)) }).inject(container);
			}
			
			e = new Element('div', { 'class': classes.join(' ') }).set('text', this.d.getDate()).inject(weekcontainer);
			if (this.limited('date')) {
				e.addClass('unavailable');
				if (available) {
					this.limit.right = true;
				} else if (this.d.getMonth() == month) {
					this.limit.left = true;
				}
			} else {
				available = true;
				e.addEvent('click', function(e, d) {
					if (this.options.timePicker) {
						this.d.setDate(d.day);
						this.d.setMonth(d.month);
						this.mode = 'time';
						this.render('fade');
					} else {
						this.select(d);
					}
				}.bindWithEvent(this, { day: this.d.getDate(), month: this.d.getMonth(), year: this.d.getFullYear() }));
			}
			this.d.setDate(this.d.getDate() + 1);
		}
		if (!available) this.limit.right = true;
	},
	
	renderYear: function() {
		var month = this.today.getMonth();
		var thisyear = this.d.getFullYear() == this.today.getFullYear();
		var selectedyear = this.d.getFullYear() == this.choice.year;
		
		this.picker.getElement('.titleText').set('text', this.d.getFullYear());
		this.d.setMonth(0);
		
		var i, e;
		var available = false;
		var container = new Element('div', { 'class': 'months' }).inject(this.newContents);
		
		for (i = 0; i <= 11; i++) {
			e = new Element('div', { 'class': 'month month'+(i+1)+(i == month && thisyear ? ' today' : '')+(i == this.choice.month && selectedyear ? ' selected' : '') })
			.set('text', this.options.monthShort ? this.options.months[i].substring(0, this.options.monthShort) : this.options.months[i]).inject(container);
			
			if (this.limited('month')) {
				e.addClass('unavailable');
				if (available) {
					this.limit.right = true;
				} else {
					this.limit.left = true;
				}
			} else {
				available = true;
				e.addEvent('click', function(e, d) {
					this.d.setDate(1);
					this.d.setMonth(d);
					this.mode = 'month';
					this.render('fade');
				}.bindWithEvent(this, i));
			}
			this.d.setMonth(i);
		}
		if (!available) this.limit.right = true;
	},
	
	renderDecades: function() {
		// start neatly at interval (eg. 1980 instead of 1987)
		while (this.d.getFullYear() % this.options.yearsPerPage > 0) {
			this.d.setFullYear(this.d.getFullYear() - 1);
		}

		this.picker.getElement('.titleText').set('text', this.d.getFullYear() + '-' + (this.d.getFullYear() + this.options.yearsPerPage - 1));
		
		var i, y, e;
		var available = false;
		var container = new Element('div', { 'class': 'years' }).inject(this.newContents);
		
		if ($chk(this.options.minDate) && this.d.getFullYear() <= this.options.minDate.getFullYear()) {
			this.limit.left = true;
		}
		
		for (i = 0; i < this.options.yearsPerPage; i++) {
			y = this.d.getFullYear();
			e = new Element('div', { 'class': 'year year' + i + (y == this.today.getFullYear() ? ' today' : '') + (y == this.choice.year ? ' selected' : '') }).set('text', y).inject(container);
			
			if (this.limited('year')) {
				e.addClass('unavailable');
				if (available) {
					this.limit.right = true;
				} else {
					this.limit.left = true;
				}
			} else {
				available = true;
				e.addEvent('click', function(e, d) {
					this.d.setFullYear(d);
					this.mode = 'year';
					this.render('fade');
				}.bindWithEvent(this, y));
			}
			this.d.setFullYear(this.d.getFullYear() + 1);
		}
		if (!available) {
			this.limit.right = true;
		}
		if ($chk(this.options.maxDate) && this.d.getFullYear() >= this.options.maxDate.getFullYear()) {
			this.limit.right = true;
		}
	},
	
	limited: function(type) {
		var cs = $chk(this.options.minDate);
		var ce = $chk(this.options.maxDate);
		if (!cs && !ce) return false;
		
		switch (type) {
			case 'year':
				return (cs && this.d.getFullYear() < this.options.minDate.getFullYear()) || (ce && this.d.getFullYear() > this.options.maxDate.getFullYear());
				
			case 'month':
				// todo: there has got to be an easier way...?
				var ms = ('' + this.d.getFullYear() + this.leadZero(this.d.getMonth())).toInt();
				return cs && ms < ('' + this.options.minDate.getFullYear() + this.leadZero(this.options.minDate.getMonth())).toInt()
					|| ce && ms > ('' + this.options.maxDate.getFullYear() + this.leadZero(this.options.maxDate.getMonth())).toInt()
				
			case 'date':
				return (cs && this.d < this.options.minDate) || (ce && this.d > this.options.maxDate);
		}
	},
	
	allowZoomOut: function() {
		if (this.mode == 'time' && this.options.timePickerOnly) return false;
		if (this.mode == 'decades') return false;
		if (this.mode == 'year' && !this.options.yearPicker) return false;
		return true;
	},
	
	zoomOut: function() {
		if (!this.allowZoomOut()) return;
		if (this.mode == 'year') {
			this.mode = 'decades';
		} else if (this.mode == 'time') {
			this.mode = 'month';
		} else {
			this.mode = 'year';
		}
		this.render('fade');
	},
	
	previous: function() {
		if (this.mode == 'decades') {
			this.d.setFullYear(this.d.getFullYear() - this.options.yearsPerPage);
		} else if (this.mode == 'year') {
			this.d.setFullYear(this.d.getFullYear() - 1);
		} else if (this.mode == 'month') {
			this.d.setMonth(this.d.getMonth() - 1);
		}
		this.render('left');
	},
	
	next: function() {
		if (this.mode == 'decades') {
			this.d.setFullYear(this.d.getFullYear() + this.options.yearsPerPage);
		} else if (this.mode == 'year') {
			this.d.setFullYear(this.d.getFullYear() + 1);
		} else if (this.mode == 'month') {
			this.d.setMonth(this.d.getMonth() + 1);
		}
		this.render('right');
	},
	
	close: function(e, force) {
		if (!$(this.picker)) return;
		var clickOutside = ($chk(e) && e.target != this.picker && !this.picker.hasChild(e.target) && e.target != this.visual);
		if (force || clickOutside) {
			if (this.options.useFadeInOut) {
				this.picker.set('tween', { duration: this.options.animationDuration / 2, onComplete: this.destroy.bind(this) }).tween('opacity', 1, 0);
			} else {
				this.destroy();
			}
		}
	},
	
	destroy: function() {
		this.picker.destroy();
		this.picker = null;
		this.options.onClose();
	},
	
	select: function(values) {
		this.choice = $merge(this.choice, values);
		var d = this.dateFromObject(this.choice);
		this.input.set('value', this.format(d, this.options.inputOutputFormat));
		this.visual.set('value', this.format(d, this.options.format));
		this.options.onSelect(d);
		this.close(null, true);
	},
	
	leadZero: function(v) {
		return v < 10 ? '0'+v : v;
	},
	
	format: function(t, format) {
		var f = '';
		var h = t.getHours();
		var m = t.getMonth();
		
		for (var i = 0; i < format.length; i++) {
			switch(format.charAt(i)) {
				case '\\': i++; f+= format.charAt(i); break;
				case 'y': f += (100 + t.getYear() + '').substring(1); break
				case 'Y': f += t.getFullYear(); break;
				case 'm': f += this.leadZero(m + 1); break;
				case 'n': f += (m + 1); break;
				case 'M': f += this.options.months[m].substring(0,this.options.monthShort); break;
				case 'F': f += this.options.months[m]; break;
				case 'd': f += this.leadZero(t.getDate()); break;
				case 'j': f += t.getDate(); break;
				case 'D': f += this.options.days[t.getDay()].substring(0,this.options.dayShort); break;
				case 'l': f += this.options.days[t.getDay()]; break;
				case 'G': f += h; break;
				case 'H': f += this.leadZero(h); break;
				case 'g': f += (h % 12 ? h % 12 : 12); break;
				case 'h': f += this.leadZero(h % 12 ? h % 12 : 12); break;
				case 'a': f += (h > 11 ? 'pm' : 'am'); break;
				case 'A': f += (h > 11 ? 'PM' : 'AM'); break;
				case 'i': f += this.leadZero(t.getMinutes()); break;
				case 's': f += this.leadZero(t.getSeconds()); break;
				case 'U': f += Math.floor(t.valueOf() / 1000); break;
				default:  f += format.charAt(i);
			}
		}
		return f;
	},
	
	unformat: function(t, format) {
		var d = new Date();
		var a = {};
		var c, m;
		t = t.toString();
		
		for (var i = 0; i < format.length; i++) {
			c = format.charAt(i);
			switch(c) {
				case '\\': r = null; i++; break;
				case 'y': r = '[0-9]{2}'; break;
				case 'Y': r = '[0-9]{4}'; break;
				case 'm': r = '0[1-9]|1[012]'; break;
				case 'n': r = '[1-9]|1[012]'; break;
				case 'M': r = '[A-Za-z]{'+this.options.monthShort+'}'; break;
				case 'F': r = '[A-Za-z]+'; break;
				case 'd': r = '0[1-9]|[12][0-9]|3[01]'; break;
				case 'j': r = '[1-9]|[12][0-9]|3[01]'; break;
				case 'D': r = '[A-Za-z]{'+this.options.dayShort+'}'; break;
				case 'l': r = '[A-Za-z]+'; break;
				case 'G': 
				case 'H': 
				case 'g': 
				case 'h': r = '[0-9]{1,2}'; break;
				case 'a': r = '(am|pm)'; break;
				case 'A': r = '(AM|PM)'; break;
				case 'i': 
				case 's': r = '[012345][0-9]'; break;
				case 'U': r = '-?[0-9]+$'; break;
				default:  r = null;
			}
			
			if ($chk(r)) {
				m = t.match('^'+r);
				if ($chk(m)) {
					a[c] = m[0];
					t = t.substring(a[c].length);
				} else {
					if (this.options.debug) alert("Fatal Error in DatePicker\n\nUnexpected format at: '"+t+"' expected format character '"+c+"' (pattern '"+r+"')");
					return d;
				}
			} else {
				t = t.substring(1);
			}
		}
		
		for (c in a) {
			var v = a[c];
			switch(c) {
				case 'y': d.setFullYear(v < 30 ? 2000 + v.toInt() : 1900 + v.toInt()); break; // assume between 1930 - 2029
				case 'Y': d.setFullYear(v); break;
				case 'm':
				case 'n': d.setMonth(v - 1); break;
				// FALL THROUGH NOTICE! "M" has no break, because "v" now is the full month (eg. 'February'), which will work with the next format "F":
				case 'M': v = this.options.months.filter(function(item, index) { return item.substring(0,this.options.monthShort) == v }.bind(this))[0];
				case 'F': d.setMonth(this.options.months.indexOf(v)); break;
				case 'd':
				case 'j': d.setDate(v); break;
				case 'G': 
				case 'H': d.setHours(v); break;
				case 'g': 
				case 'h': if (a['a'] == 'pm' || a['A'] == 'PM') { d.setHours(v == 12 ? 0 : v.toInt() + 12); } else { d.setHours(v); } break;
				case 'i': d.setMinutes(v); break;
				case 's': d.setSeconds(v); break;
				case 'U': d = new Date(v.toInt() * 1000);
			}
		};
		
		return d;
	}
});
/*
 * FORM CALENDAR
 */
function printCalendar(icon,inputField,days,months) {

	if(!days) {
		days=["Domenica","Lunedì","Martedì","Mercoledì","Giovedì","Venerdì","Sabato"]
	}
	if(!months) {
		months=["Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre"]
	}
	var e=new DatePicker(inputField,{
		pickerClass:"datepicker_jqui",
		days:days,
		months:months,
		format:"d/m/Y",
		inputOutputFormat:"d/m/Y",
		startDay:1,
		allowEmpty:true
	});
	inputField.getPrevious("input").fireEvent("focus")
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
var layerWindow = new Class({

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
		if(!$chk(this.options.maxHeight)) this.options.maxHeight = getViewport().height-100;

		if(this.options.reloadZindex) window.maxZindex = getMaxZindex();

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
			'z-index': ++window.maxZindex
		});

		this.overlay.inject(document.body);
		
	},	
	dObjects: function() {
		for(var i=0;i<window.frames.length;i++) {
			var myFrame = window.frames[i];
			if(sameDomain(myFrame)) {
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
			if(sameDomain(myFrame)) {
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
		this.container = new Element('div', {'id':this.options.id, 'class':'abiWin'});

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
			: (getViewport().cY-this.container.getCoordinates().height/2);
		this.left = (this.dopt && $chk(this.dopt.left)) ? this.dopt.left < 0 ? 0 : this.dopt.left : elementCoord 
			? elementCoord.left 
			: (getViewport().cX-this.container.getCoordinates().width/2);

		this.container.setStyles({
			'top': this.top+'px',
			'left':this.left+'px',
			'visibility': 'visible'
		})
	},
	renderHeader: function() {
		this.header = new Element('header', {'class':'abiHeader'});
		this.header.set('html', this.title);

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
		ajaxRequest('post', this.url, '', this.body, {'script':true, 'load':this.body, 'callback':this.locateContainer.bind(this)});	 
	},
	setFocus: function() {
		if(!this.container.style.zIndex || (this.container.getStyle('z-index').toInt() < window.maxZindex))
			this.container.setStyle('z-index', ++window.maxZindex);
	},
	closeWindow: function() {
		this.showing = false;
		if(this.options.disableObjects) this.chain(this.container.dispose(), this.eObjects());
		else this.container.dispose();
		if(this.options.overlay) this.overlay.dispose();
    		if($chk(this.options.closeCallback)) this.options.closeCallback(this.options.closeCallbackParam);		
		if(this.options.destroyOnClose) for(var prop in this) this[prop] = null;
	}
})

function getViewport() {

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

window.maxZindex = getMaxZindex();

function getMaxZindex() {
	
	var maxZ = 0;
	$$('body *').each(function(el) {if(el.getStyle('z-index').toInt()) maxZ = Math.max(maxZ, el.getStyle('z-index').toInt())});

	return maxZ;

}

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
var hScrollingList = new Class({

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
var vScrollingList = new Class({

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
