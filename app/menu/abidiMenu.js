// Also known as IE fix
var TridentFix = new Class({
	tridentFix: function(item){
		item.addEvents({
			'mouseover':function(){
				this.addClass('iehover');
			},
			'mouseout':function(){
				this.removeClass('iehover');
			}
		});
	}
});


var AbidiMenu = new Class({
	Implements: [Options,TridentFix],
	options: {
		fmode: 'horizontal',
		initShowIcon: true,
		selectVoiceSnake: true
	},
	menu: null,
	initialize: function(menu,options){
		if(options) this.setOptions(options);
	
		this.menu = $(menu);
		this.selectedItem = $$('#'+menu+' li[class=selectedVoice]')[0];
		
		// grab all of the menus children - LI's in this case
		var children = this.menu.getChildren();
		
		// loop through children
		children.each(function(item,index){
			// declare some variables 
			var fChild, list;
			
			/* 
				fChild = first child - which should be an A tag
				list = submenu UL
			*/
			fChild = item.getFirst();
			list = fChild.getNext('ul');
			
			if(this.selectedItem && this.options.selectVoiceSnake && item.contains(this.selectedItem)) fChild.addClass('withSelected');

			// check if IE, if so apply fix
			if(Browser.Engine.trident) this.tridentFix(item);
			
			// if there is a sub menu UL
			if(list){
				fChild.css = (this.options.fmode=='horizontal')?'withChildrenH':'withChildrenV';
				if(this.options.initShowIcon) fChild.addClass(fChild.css);
				item.more = this.options.initShowIcon;

				item.mel = list; // mel = menu element
				item.fChild = fChild;
				list.pel = item; // pel = parent element
				new SubMenu(list, null, this.options); // hook up the subMenu
			}
		},this); // binding loop to this object for trident fix

	}	
});



var SubMenu = new Class({
	Implements: [Options,TridentFix],
	options: {
		smode: 'vertical',
		initShowIcon: true,
		clickEvent: false
	},
	menu: null, // storage for menu object
	depth: 0, // storage for current menu depth
	initialize: function(el,depth,options){
		if(options) this.setOptions(options); // set options
		isIE6 = /msie|MSIE 6/.test(navigator.userAgent);
		if(Browser.Platform.ipod || isIE6) this.options.clickEvent = true;
		if(depth) this.depth = depth;// set depth
		
		this.selectedItem = $$('li[class=selectedVoice]')[0];
		this.menu = el; //attach menu to object
		
		if(this.depth == 0)	this.menu.addClass('submenu'); // class for first level
		if(this.depth >= 1)	this.menu.addClass('sub_submenu'); // class for deeper levels - in case :P
		
		this.menu.fade('hide'); // set menu to hid

		/*
			hook up menu's parent with event
			to trigger menu
		*/
		var openEvent = (this.options.clickEvent)?'click':'mouseover';
		this.menu.pel.fChild.addEvent(
			openEvent, function(e) {
				e.stop(); // prevent link action
				if(!el.pel.more) this.addClass(this.css); // if icon is not shown, show it now
			
				// fade in menu
				el.pel.mel.fade('in');		
			}
		); 
		this.menu.pel.addEvents({
			'mouseleave': function() {
				if(!this.more) this.fChild.removeClass(this.fChild.css);
			
				// fade out menu
				this.mel.fade('out');
			}
		})
		
		// get menu's child elements
		var children = this.menu.getChildren();
			
		// loop through children
		children.each(function(item,index){
			// declare some variables 
			var fChild, list;
			
			/* 
				fChild = first child - which should be an A tag
				list = submenu UL
			*/
			fChild = item.getFirst();
			list = fChild.getNext('ul');
			
			if(this.options.selectVoiceSnake && (item.hasChild(this.selectedItem) || item==this.selectedItem)) fChild.addClass('withSelected');

			// check if IE, if so apply fix
			if(Browser.Engine.trident) this.tridentFix(item);
			
			// if the menu item has a sub_submenu
			if(list){
			
				fChild.css = 'withChildrenV';
				if(this.options.initShowIcon) fChild.addClass(fChild.css);
				item.more = this.options.initShowIcon;

				item.fChild = fChild;
				item.mel = list; // mel = menu element
				list.pel = item; // pel = parent element
				
				// create new subMenu with depth incremented
				new SubMenu(list,this.depth+1,this.options);
			}
		},this); //bound to this for trident fix
	}
});

