/*
   ---

script: String.Slugify.js

description: Extends the String native object to have a slugify method, useful for url slugs.

license: MIT-style license

authors:
- Stian Didriksen
- Grzegorz Leoniec

...
 */

(function()
 {
 String.implement(
	 {
slugify: function( replace )
{
if( !replace ) replace = '-';
var str = this.toString().tidy().standardize().replace(/[\s\.]+/g,replace).toLowerCase().replace(new RegExp('[^a-z0-9'+replace+']','g'),replace).replace(new RegExp(replace+'+','g'),replace);
if( str.charAt(str.length-1) == replace ) str = str.substring(0,str.length-1);
return str;
}
});
 })();

// prettify
window.addEvent('load', function() {
	if(typeof prettyPrint == 'function') 
		prettyPrint();
});

var PageSlider = new Class({

	Implements: [Options],
	options: {
		auto_start: false,
		auto_interval: 5000
	},
	initialize: function(wrapper, ctrl_begin, options) {
		this.setOptions(options);
		this.wrapper = $(wrapper);
		this.current = 0;
		this.slides = this.wrapper.getChildren();
		this.slides[this.current].addClass('active');
		this.ctrls = $$('div[id^=' + ctrl_begin + ']');
		this.ctrls[this.current].addClass('on');
		if(this.options.auto_start) {
			this.timeout = setTimeout(this.autoSet.bind(this), this.options.auto_interval);
		}
		// if true does nothing when clicking a controller
		this.idle = false;
	},
	set: function(index) {

		if(this.options.auto_start) {
			clearTimeout(this.timeout);
		}

		if(!this.idle) {

			// content fade
			var myfx = new Fx.Tween(this.slides[this.current], {'property': 'opacity'});
			current_zindex = this.slides[this.current].getStyle('z-index');
			this.slides[this.current].setStyle('z-index', current_zindex.toInt() + 1);
			this.slides[index].setStyle('z-index', current_zindex);

			myfx.start(1,0).chain(function() {
				if(this.slides.length > 1) {
					this.slides[this.current].setStyle('z-index', current_zindex.toInt() - 1);
				}
				myfx.set(1);
				this.slides[this.current].removeClass('active');
				this.slides[index].addClass('active');
				this.current = index; 
				this.idle = false;
			}.bind(this));
			
			// controllers animation
			var current = this.current;
			var next = current;
			var i = 0;
			// chain, loop over every intermediate state
			while(i < Math.abs(index - next)) {
				var prev = next;
				next = index > current ? next + 1 : next - 1;
				var self = this;
				// closure to pass prev and next by value
				(function(c, n) {
					setTimeout(function() { self.setCtrl(n, c) }, 100 * (Math.abs(n-current) - 1));
				})(prev, next)
			}
		}

		if(this.options.auto_start) {
			this.timeout = setTimeout(this.autoSet.bind(this), this.options.auto_interval);
		}
		
	},
	setCtrl: function(next, current) {
		
		// current transition, fwd or rwd
		this.ctrls[current].removeClass('on');
		this.ctrls[current].addClass(next > current ? 'fwd' : 'rwd');

		// next transition
		this.ctrls[next].addClass('on');

		// prepare all controllers for the next transition
		for(var i = next + 1; i < this.ctrls.length; i++) {
			this.ctrls[i].removeClass('fwd');
			this.ctrls[i].addClass('rwd');
		}
		for(var i = next - 1; i >= 0; i--) {
			this.ctrls[i].removeClass('rwd');
			this.ctrls[i].addClass('fwd');
		}

		// avoid click actions till the chain as finished
		if(next == this.current) {
			this.idle = false;
		}
		else {
			this.idle = true;
		}

	},
	autoSet: function() {
		if(this.current >= this.slides.length - 1) {
			var index = 0;
		}
		else {
			var index = this.current + 1;
		}
		this.set(index);
	}

});
