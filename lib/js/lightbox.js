/**
 * gino Lightbox v0.1
 * license: MIT-style
 * authors:
 * - Marco Guidotti <guidottim@gmail.com>
 * - abidibo <dev@abidibo.net>
 * 
 * requires:
 * - core/1.3
 * 
 * provides:
 * - moogallery
 * 
 * #Class options {Array}:
 * - show_bullets (boolean): show bullets (if images number is greater than one)
 * - show_numbering (boolean): show images numbering (if images number is greater than one)
 * - show_info (boolean): show image informations
 * - fx_duration (integer|string): transition duration; integer (milliseconds) or string ([short|long])
 * - fx_transition (string): transition effect
 * 
 * #Description
 * The bullets and infos navigation are not displayed when there is only one image.
 * 
 * #Example of use in the view
 * 
 * <script>
 * Lightbox.autoload.init({ 'show_bullets': true, 'fx_duration': 500 })
 * </script>
 */

var Lightbox = {};

(function () {

    Lightbox.autoload = {
        init: function (options) {
        	
        	var dft_options = {
            	show_bullets: true,
            	show_numbering: true,
            	show_info: false,
            	fx_duration: 'short',
            	fx_transition: '',
            };
            
            this.setOptions(dft_options, options);
        	this.addEvents();
        },
        // Merges given options with defaults
        setOptions: function(dft_options, options) {
        	this.options = Object.assign({}, dft_options, options);
        },
        addEvents: function () {
            var self = this
            $$('a[rel^=lightbox]').addEvent('click', function (evt) {
                evt.preventDefault();
                self.onClick(evt, this);
            })
        },
        onClick: function (evt, el) {
            var lightboxGroup = el.getAttribute('rel');
            var allImages = $$('a[rel=' + lightboxGroup + ']');
            
            // It redefines the options
            if(allImages.length < 2) {
            	this.options.show_bullets = false
            	this.options.show_numbering = false
            	this.options.single_item = true;
            }
            else {
            	this.options.single_item = false;
            }
            var lightboxInstance = new Lightbox.Widget(allImages, el.getAttribute('href'), this.options);
        }
    }

    Lightbox.Widget = function (images, clickedHref, options) {

        this.init = function (images, clickedHref, options) {
            this.images = images;
            this.options = options;
            this.max_z_index = this.getMaxZindex();
            
            // array with all href properties of the images
            var imagesHref = this.images.map(function (img) {
                return $(img).getAttribute('href');
            })
            
            // current index
	        this.current = imagesHref.indexOf(clickedHref);
            
            this.renderOverlay();
            this.renderContent();
        };
        
        /**
    	 * @summary Make main container and image container
    	 * @return void
    	 */
        this.renderContent = function() {
        	
        	this.container = new Element('div', { 'class': 'lightbox-container' });
            this.container.inject(this.overlay);
            this.containerImage = new Element('div', { 'class': 'lightbox-container-image' }).inject(this.container)

            this.renderImage();
            if(!this.options.single_item) {
            	this.renderNavigation();
            }
            if(this.options.show_info) {
            	this.renderInformations();
            }
            
            this.addContainerEvents();
        }
        
        /**
    	 * @summary Gets the maximum z-index in the document.
    	 * @return {Number} The maximum z-index
    	 */
    	this.getMaxZindex = function() {
    		var max_z = 0;
    		$$('body *').each(function(el) {
    			try{
    				// second condition due to automatically inserted skype icons by fucking IE
    				if(el.getStyle('z-index').toInt() && el.getStyle('z-index').toInt() != 2147483647) {
    					max_z = Math.max(max_z, el.getStyle('z-index').toInt());
    				}
    			}
    			catch(err) {
    				// IE can't get z-index of some elements (span, img)
    			}
    		});

    		return max_z;
    	};
        
    	/**
    	 * @summary Make overlay
    	 * @return void
    	 */
        this.renderOverlay = function () {
            this.overlay = new Element('div', { 'class': 'lightbox-overlay' });
            this.overlay.setStyles({
    			'z-index': this.max_z_index++,
    		});
            this.overlay.inject(document.body)
            
            // click outside image container to close
            this.overlay.addEvent('click', function(e) {
            	
            	var event = new DOMEvent(e);
    			if(event.target == this.overlay) {
    				var myfx = new Fx.Tween(this.overlay, {'property': 'opacity'});
    				myfx.start(0.9, 0).chain(function() {
    					this.overlay.dispose();
    				}.bind(this));
    			}
    		}.bind(this));
        }
        
        /**
    	 * @summary Add image informations.
    	 * @return void
    	 */
        this.renderInformations = function () {
        	
        	var item = this.images[this.current];
        	
        	var item_info = new Element('div', { 'class': 'lightbox-info' });
        	var item_info_title = new Element('div', { 'class': 'lightbox-info-title' }).set('html', item.title);
        	
        	item_info.adopt(item_info_title);
        	item_info.inject(this.containerImage);
        }
        
        /**
    	 * @summary Add events to container.
    	 * @return void
    	 */
        this.addContainerEvents = function () {
        	
        	var index = this.current;
        	
        	// click event
    		this.container.addEvent('click', function(e) {
    			if(e.target.get('tag') == 'a') {
    				return true;
    			}
    			
    			var cont_dim = this.container.getCoordinates();
    			if(e.page.x < cont_dim.left + cont_dim.width/2) {
    				if(index == 0) {
    					return false;
    				}
    				this.changeItem(index - 1);
    			}
    			else {
    				if(index == this.images.length-1) {
    					return false;
    				}
    				this.changeItem(index + 1);
    			}
    		}.bind(this));	

    		// mouseover shows next prev arrows
    		this.container.addEvent('mouseover', function(e) {
    			var cont_dim = this.container.getCoordinates();
    			if(e.page.x < cont_dim.left + cont_dim.width/2) {
    				if(this.arrow_next.getStyle('opacity') != '0') {
    					this.arrow_next.fade('hide');
    				}
    				if(index==0) return false;
    				this.arrow_prev.fade('in');
    			}
    			else {
    				if(this.arrow_prev.getStyle('opacity') != '0') {
    					this.arrow_prev.fade('hide');
    				}
    				if(index==this.images.length-1) return false;
    				this.arrow_next.fade('in');
    			}
    		}.bind(this));

    		this.container.addEvent('mouseleave', function(e) {
    			this.arrow_next.fade('hide');
    			this.arrow_prev.fade('hide');
    		}.bind(this));
        }

        /**
    	 * @summary Add image to image container.
    	 * @return void
    	 */
        this.renderImage = function () {
            // removes the previous image
            this.containerImage.empty();
        	
            var image = this.images[this.current];
            
            this.imgEl = new Element('img', {'src': image.getAttribute('href'), 'class': 'img-responsive'})
            .inject(this.containerImage);
        }
        
        /**
    	 * @summary Image navigation.
    	 * @return void
    	 */
        this.renderNavigation = function() {

    		var index = this.current;
    		var nav = new Element('div', { 'class': 'lightbox-nav' })
    		.inject(this.containerImage)

    		this.arrow_next = new Element('div', {'class': 'arrow_next fa fa-angle-right fa-2x'}).setStyle('opacity', '0');
    		this.arrow_prev = new Element('div', {'class': 'arrow_prev fa fa-angle-left fa-2x'}).setStyle('opacity', '0');
    		var arrows = new Element('div.nav-arrows').adopt(this.arrow_next, this.arrow_prev);
    		
    		if(this.options.show_numbering) {
    			var nav_info_text = (index + 1) + '/' + this.images.length;
    		}
    		else {
    			var nav_info_text = ''
    		}
    		var nav_info = new Element('div.lightbox-nav-info').set('text', nav_info_text);

    		nav.grab(arrows);
    		
    		// bullets
    		if(this.options.show_bullets) {
    			var nav_table = new Element('table.lightbox-nav-table');
    			var tr = new Element('tr').inject(nav_table);
    			this.images.each(function(item, i) {
    				var td = new Element('td').inject(tr);
    				var bullet = new Element('div.lightbox-nav-bullet').inject(td);
    				if(i == index) bullet.addClass('bullet-selected');
    				else {
    					bullet.addEvent('click', function(e) {
    						e.stopPropagation();
    						this.changeItem(i);
    					}.bind(this))
    				}
    			}.bind(this));

    			nav_table.inject(nav);
    		}

    		nav.grab(nav_info);
    	}
        
    	/**
    	 * @summary Changes the image displayed in the lightbox widget; transition on the child div.
    	 * @memberof moogallery.prototype
    	 * @method
    	 * @param {Number} index the index of the image to show
    	 * @return void
    	 */
    	this.changeItem = function(index) {
    		
    		this.current = index;
    		var myfx = new Fx.Tween(this.container.getChildren('div')[0], {property: 'opacity', duration: this.options.fx_duration, transition: this.options.fx_transition,});
    		myfx.start(1, 0).chain(function() {
    			this.container.empty();
    			this.renderContent();
    		}.bind(this));
    	}

        this.init(images, clickedHref, options);
    }

})()

    