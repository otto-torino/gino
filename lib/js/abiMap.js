/*
 * Class AddressToPointConverter 
 *
 * This class converts an address into latitude and longitude coordinates. Shows the point indicated by the address with a marker
 * that can be moved in order to adjust the right position. The calculated coordinates are then setted as values of given input fields.
 *
 * AddressToPointConverter method: constructor
 *   Syntax
 *      var myAddressToPointConverter = new AddressToPointConverter(element, latField, lngField, address, [options]);
 *   Arguments 
 *      1. element (string|element) the element id or object respect to which open or insert the map layer   
 *      2. latField (string|element) the element id or object of the input field where the latitude will be inserted after calculating it   
 *      3. lngField (string|element) the element id or object of the input field where the longitude will be inserted after calculating it   
 *      4. address (string) the address used to find the geographical coordinates
 *	5. options - (object, optional) The options object.
 *   Options
 *	- canvasPosition (string: default to over) Possible values are 'over' and 'inside'. 
 *        over: the map container stands on a layer outside the flow of the DOM.
 *        inside: the map container is injected in the DOM after the element given as first argument.
 *	- canvasW: (string: default 400px) the width of the map.
 *	- canvasH: (string: default 300px) the height of the map.
 *   	- zoom (string: default to 13) The zoom level of the map.
 *   	- noResZoom (string: default to 5) The zoom level of the map when convertion fails.
 * 	- dftLat (float: default to 45) The default latitude when convertion fails
 * 	- dftLng (float: default to 7) The default longitude when convertion fails
 *
 * AddressToPointConverter method: showMap
 *  shows the map
 *   Syntax
 *	myAddressToPointConverter.showMap();
 *
 */
var AddressToPointConverter = new Class({
	
	Implements: [Options],
	options: {
		canvasPosition: 'over', // over | inside
		canvasW: '400px',
		canvasH: '300px',
		zoom: '13',
		noResZoom: '5',
		dftLat: '45',
		dftLng: '7'
	},
	initialize: function(element, latField, lngField, address, options) {
	
		if($defined(options)) this.setOptions(options);
		this.checkOptions();

		this.element = $type(element)=='element'? element:$(element);
		this.latField = $type(latField)=='element'? latField:$(latField);
		this.lngField = $type(lngField)=='element'? lngField:$(lngField);
		this.address = address;

	},
	checkOptions: function() {
		if(this.options.canvasPosition == 'over') {
			var rexp = /[0-9]+px/;
			if(!rexp.test(this.options.canvasW)) this.options.canvasW = '400px';
			if(!rexp.test(this.options.canvasH)) this.options.canvasW = '300px';
		}
	},
	showMap: function() {
		this.renderContainer();
		this.renderCanvas();
		this.canvasContainer.setStyle('width', (this.canvas.getCoordinates().width)+'px');
		this.renderCtrl();
		this.renderMap();
	},
	renderContainer: function() {
		this.canvasContainer = new Element('div', {'id':'map_canvas_container'});
		this.canvasContainer.setStyles({
				'padding': '1px',
				'background-color': '#000',
				'border': '1px solid #000'
			})
		if(this.options.canvasPosition == 'inside') {
			this.canvasContainer.inject(this.element);
		}
		else { // over
			var elementCoord = this.element.getCoordinates();
			this.canvasContainer.setStyles({
				'position': 'absolute',
				'top': elementCoord.top+'px',
				'left':elementCoord.left+'px'
			})
			this.canvasContainer.inject(document.body);
		}
		document.body.addEvent('mousedown', this.checkDisposeContainer.bind(this));	
	},
	renderCanvas: function() {
		this.canvas = new Element('div', {'id':'map_canvas'});
		this.canvas.setStyles({
			'width': this.options.canvasW,
			'height': this.options.canvasH
		})
		this.canvas.inject(this.canvasContainer, 'top');
	},
	renderCtrl: function() {
		var divCtrl = new Element('div').setStyles({'background-color': '#ccc', 'padding': '2px 0px', 'text-align': 'center'});
		var convertButton = new Element('input', {'type':'button', 'value':'convert'});
		convertButton.setStyles({'cursor': 'pointer', 'border': '1px solid #999', 'margin-top': '2px'});
		divCtrl.inject(this.canvasContainer, 'bottom');		
		convertButton.inject(divCtrl, 'top');
		convertButton.addEvent('click', function() {
			this.latField.value = this.point.lat(); 
			this.lngField.value = this.point.lng(); 
			this.canvasContainer.dispose();
		}.bind(this));
	},
	checkDisposeContainer: function(evt) {
		if(evt.page.x<this.canvasContainer.getCoordinates().left || 
		   evt.page.x>this.canvasContainer.getCoordinates().right || 
		   evt.page.y<this.canvasContainer.getCoordinates().top || 
		   evt.page.y>this.canvasContainer.getCoordinates().bottom) {
			this.canvasContainer.dispose();
			for(var prop in this) this[prop] = null;
			document.body.removeEvent('mousedown', this.checkDisposeContainer);
		}
	       
	},
	renderMap: function() {
		var mapOptions = {
      			zoom: this.options.zoom.toInt(),
      			mapTypeId: google.maps.MapTypeId.ROADMAP
    		};
		var map = new google.maps.Map(this.canvas, mapOptions);
		var point;
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address':this.address}, function(results, status) {
			if(status == google.maps.GeocoderStatus.OK) {
				this.point = results[0].geometry.location;
				this.insertMarker(map);
			}	
			else {
				alert("Geocode was not successfull for the following reason: "+status);
				this.point = new google.maps.LatLng(this.options.dftLat, this.options.dftLng);
				map.setZoom(this.options.noResZoom.toInt());
				this.insertMarker(map);
			}
		}.bind(this))

	},
	insertMarker: function(map) {
		map.setCenter(this.point);      
		var marker = new google.maps.Marker({
              		map: map, 
              		position: this.point,
			draggable: true,
			title: this.point.lat()+' - '+this.point.lng()
          	});
		google.maps.event.addListener(marker, 'mouseup', function() {this.point = marker.getPosition();}.bind(this))

	}
})

/*
 * Class AbidiMap 
 *
 * This class converts an address into latitude and longitude coordinates. Shows the point indicated by the address with a marker
 * that can be moved in order to adjust the right position. The calculated coordinates are then setted as values of given input fields.
 *
 * AbidiMap method: constructor
 *   Syntax
 *      var myAbidiMap = new AbidiMap(pointsJson, [options]);
 *   Arguments 
 *      1. pointsJson (string of type Json) the json string defining the points to be viewed in the map. Each point may have some properties:
 *         - id: identifier
 *         - lat: latitude 
 *         - lng: longitude 
 *         - label: point label setted as html title 
 *         - mImageUrl: the url of the image used as marker 
 *         - description: the description inserted in the info window 
 *         - descriptionNode: the id of the DOM element containing the html to be taken as the point description
 *         - descriptionIdValue: the id of the DOM element having the value to be taken as the point description
 *	2. options - (object, optional) The options object.
 *   Options
 *	- id (string: default to 0) The id of the object which is part of map container and map canvas ids. 
 *	- title (string: default to null) The title of the map container. 
 *	- canvasPosition (string: default to over) Possible values are 'over' and 'inside'. 
 *        over: the map container stands on a layer window outside the flow of the DOM.
 *        inside: the map container is injected in the DOM after the element given as first argument.
 *	- canvasW: (string: default to 400px) the width of the map.
 *	- canvasH: (string: default to 300px) the height of the map.
 *	- dftType: (string: default to roadmap) The map type. Possible values are hybrid | roadmap | satellite | terrain.
 *	- dftCtrlType: (string: default to default) The map controller type. Possible values are dropdown | horizontal | default.
 *	- scrollwheel: (bool: default to false) Whether or not to zoom with the mouse scrollwheel.
 *  - zoom (string: default to null) The zoom level of the map. If no value is given, the zoom and the center of the map are calculated respect to map points.
 *					 If a value is given the center of the map is setted to the (lat,lng) coordinates of the first point given.
 *  - controller (bool: default to true) Whether or not to show the container controller (close button and drag element id the option draggable is set to true).
 *  - draggable (bool: default to true) Whether or not to make the container draggable (only for canvasPosition=over).
 *  - resize (bool: default to true) Whether or not to make the container resizable (only for canvasPosition=over).
 *  - closeButtonUrl (string default to null): the url of the image used as close button 
 *  - closeButtonLabel: (string default to close): the label of the image used as close button if no close button url is given 
 * 	- destroyOnClose (bool: default to true) Whether or not to destroy the object when the container is closed
 *
 * AbidiMap method: showMap
 *  shows the map
 *   Syntax
 *	myAbidiMap.showMap(element);
 *   Arguments 
 *      1. element (string or element): the dom element near which is inserted the map container.
 *
 * AbidiMap method: closeInfoWindow
 *  close the infoWindow of the map point given or of all points if none is given
 *   Syntax
 *	myAbidiMap.closeInfoWindow([pointId]);
 *   Arguments 
 *      1. pointId (string): the id property of the point you want to close the infoWindow  
 *
 * AbidiMap method: openInfoWindow
 *  open the infoWindow of the map point given
 *   Syntax
 *	myAbidiMap.openInfoWindow(pointId);
 *   Arguments 
 *      1. pointId (string): the id property of the point you want to display the infoWindow 
 *
 * AbidiMap method: addMapPoints
 *  adds points to the map
 *   Syntax
 *	myAbidiMap.addMapPoints(pointsJson);
 *   Arguments 
 *      1. pointsJson (string of type Json): the json string defining the points to be viewed in the map. Each point may have the properties described above 
 *
 * AbidiMap method: addMapPoint
 *  adds a point to the map
 *   Syntax
 *	myAbidiMap.addMapPoint(pointObj);
 *   Arguments 
 *      1. pointsObj (object): the point Object with the properties described above 
 *
 * AbidiMap method: removeMapPoint
 *  remove a point from the map
 *   Syntax
 *	myAbidiMap.removeMapPoint(pointId, resizeBounds);
 *   Arguments 
 *      1. pointId (string): the id property of the point to be removed 
 *      2. resizeBounds (bool default to true): Whether or not to recalculate the bounds of the map after removing the point
 *
 * AbidiMap method: setFocus
 *  set focus on the object container, giving it the greatest z-index in the document
 *   Syntax
 *	myAbidiMap.setFocus();
 *
 * AbidiMap method: mapDispose
 *  disposes the map container
 *   Syntax
 *	myAbidiMap.mapDispose();
 */

var AbidiMap = new Class({

	Implements: [Options],
	options: {
		id:0,
		title: null,
		canvasPosition: 'over', // over | inside
		canvasW: '400px',
		canvasH: '300px',
		dftType: 'roadmap', // hybrid | roadmap | satellite | terrain
		dftCtrlType: 'default', // dropdown | horizontal | default 
		scrollwheel: false,
		zoom: null,
		controller: true, // false
		draggable: true,
		resize: true,
		closeButtonUrl: null,
		closeButtonLabel: 'close',
		destroyOnClose: true
	},
    	initialize: function(pointsJson, options) {
		
		if($defined(options)) this.setOptions(options);
		this.checkOptions();

		this.pointsObj = JSON.decode(pointsJson);
		this.map = null;
		this.bounds = null;
		this.markers = new Array();
		this.infos = new Array();

		this.dftType = (this.options.dftType == 'roadmap')?
			google.maps.MapTypeId.ROADMAP:(this.options.dftType == 'hybrid')?
			google.maps.MapTypeId.HYBRID:(this.options.dftType == 'satellite')?
			google.maps.MapTypeId.SATELLITE:(this.options.dftType == 'terrain')?
			google.maps.MapTypeId.TERRAIN:google.maps.MapTypeId.ROADMAP;

		this.dftCtrlType = (this.options.dftCtrlType == 'default')?
			google.maps.MapTypeControlStyle.DEFAULT:(this.options.dftCtrlType == 'dropdown')?
			google.maps.MapTypeControlStyle.DROPDOWN_MENU:(this.options.dftCtrlType == 'horizontal')?
			google.maps.MapTypeControlStyle.HORIZONTAL_BAR:google.maps.MapTypeControlStyle.DEFAULT;

		this.dftCtrlNav = (this.options.dftCtrlNav == 'default')?
			google.maps.NavigationControlStyle.DEFAULT:(this.options.dftCtrlNav == 'android')?
			google.maps.NavigationControlStyle.ANDROID:(this.options.dftCtrlNav == 'small')?
			google.maps.NavigationControlStyle.SMALL:(this.options.dftCtrlNav == 'zoom_pan')?
			google.maps.NavigationControlStyle.ZOOM_PAN:google.maps.NavigationControlStyle.DEFAULT;


	},
	checkOptions: function() {
		if(this.options.canvasPosition == 'over') {
			var rexp = /[0-9]+px/;
			if(!rexp.test(this.options.canvasW)) this.options.canvasW = '400px';
			if(!rexp.test(this.options.canvasH)) this.options.canvasW = '300px';
		}
	},
	showMap: function(element, opt) {
		this.display = true;
		this.element = $type(element)=='element'? element:$(element);

		this.top = (opt && $chk(opt.top)) ? opt.top < 0 ? 0 : opt.top : null;
		this.left = (opt && $chk(opt.left)) ? opt.left < 0 ? 0 : opt.left : null;

		this.renderContainer();
		if(this.options.controller || this.options.title) this.renderCtrl();
		this.renderCanvas();
		if(this.options.resize && this.options.canvasPosition=='over') this.renderResizeCtrl();
		this.canvasContainer.setStyle('width', (this.canvas.getCoordinates().width)+'px');
		this.renderMap();
	},
	renderContainer: function() {
		this.canvasContainer = new Element('div', {'id':'map_canvas_container'+this.options.id});
		this.canvasContainer.setStyles({
				'padding': '1px',
				'background-color': '#ccc',
				'border': '1px solid #000'
		});
		if(this.options.canvasPosition == 'inside') {
			this.canvasContainer.inject(this.element);
		}
		else { // over
			var elementCoord = this.element.getCoordinates();
			
			this.canvasContainer.setStyles({
				'position': 'absolute',
				'top': ($chk(this.top) ? this.top : elementCoord.top)+'px',
				'left': ($chk(this.left) ? this.left : elementCoord.left)+'px'
			})
			this.setFocus();
			this.canvasContainer.addEvent('mousedown', this.setFocus.bind(this));
			this.canvasContainer.inject(document.body);
		}
	},
	renderCtrl: function() {
		this.canvasCtrl = new Element('div').setStyles({'text-align': 'center', 'margin': '5px 0px'});
		if(this.options.canvasPosition=='over' && this.options.draggable && this.options.controller) {
			this.canvasCtrl.addEvent('mousedown', this.startDragContainer.bind(this));
			this.canvasCtrl.addEvent('mousedown', this.setFocus.bind(this));
			this.canvasCtrl.setStyle('cursor', 'move');
		}
		this.canvasCtrl.inject(this.canvasContainer, 'top');
		if(this.options.title) this.canvasCtrl.set('html', this.options.title);

		if(this.options.controller) {
			var closeEl;
			if($chk(this.options.closeButtonUrl) && $type(this.options.closeButtonUrl)=='string') {
				closeEl = new Element('img', {'src':this.options.closeButtonUrl}).setStyle('cursor', 'pointer');
			}
			else if(this.options.controller) {
				closeEl = new Element('span').setStyles({'background-color':'#fff', 'border':'1px solid #000', 'cursor':'pointer', 'padding':'2px', 'float':'right'});
				closeEl.set('html', this.options.closeButtonLabel);
			}
	
			closeEl.addEvent('click', this.mapDispose.bind(this));
			closeEl.inject(this.canvasCtrl);

			var clearDiv = new Element('div', {'style':'clear:both'});
			clearDiv.inject(this.canvasCtrl, 'bottom');
		}

    				
	},
	renderResizeCtrl: function() {
		var resCtrl = new Element('div').setStyles({'position':'absolute', 'right':'0', 'bottom':'0', 'width':'20px', 'height':'20px', 'cursor':'se-resize'});
		resCtrl.addEvent('mousedown', this.startResizeContainer.bind(this));
		resCtrl.inject(this.canvasContainer, 'bottom');		
	},
	startDragContainer: function(evt) {
		this.initX = evt.page.x;
		this.initY = evt.page.y;
		this.initCanvasContainerTop = this.canvasContainer.getCoordinates().top;
		this.initCanvasContainerLeft = this.canvasContainer.getCoordinates().left;
		document.addEvent('mousemove', this.dragContainer.bind(this));
		document.addEvent('mouseup', this.dropContainer.bind(this));
		// cancel out any text selections 
		document.body.focus();
	       	// prevent text selection in IE 
		document.onselectstart = function () { return false; }; 
		// prevent IE from trying to drag an image 
		this.canvasContainer.ondragstart = function() { return false; }; 
		// prevent text selection (except IE) 
		return false; 
		
	},
	dragContainer: function(evt) {
		evt.stopPropagation();
		var pX = evt.page.x;	       
		var pY = evt.page.y;
		var newTop = (this.initCanvasContainerTop+(pY-this.initY))>0?(this.initCanvasContainerTop+(pY-this.initY)):0;
		var newLeft = (this.initCanvasContainerLeft+(pX-this.initX))<0?
			0:(this.initCanvasContainerLeft+(pX-this.initX))> (document.getCoordinates().width - this.canvasContainer.getCoordinates().width)?
			(document.getCoordinates().width - this.canvasContainer.getCoordinates().width):(this.initCanvasContainerLeft+(pX-this.initX));
 		this.canvasContainer.setStyles({'top': newTop+'px', 'left': newLeft+'px'});		
	},
	dropContainer: function(evt) {
		document.removeEvents('mousemove', this.dragContainer);	       
	},
	startResizeContainer: function(evt) {
		this.rinitX = evt.page.x;
		this.rinitY = evt.page.y;
		this.initCanvasContainerHeight = this.canvasContainer.getCoordinates().height;
		this.initCanvasContainerWidth = this.canvasContainer.getCoordinates().width;
		this.initCanvasHeight = this.canvas.getCoordinates().height;
		this.initCanvasWidth = this.canvas.getCoordinates().width;
		document.addEvent('mousemove', this.resizeContainer.bind(this));
		document.addEvent('mouseup', this.stopResizeContainer.bind(this));
		// cancel out any text selections 
		document.body.focus();
	       	// prevent text selection in IE 
		document.onselectstart = function () { return false; }; 
		// prevent IE from trying to drag an image 
		this.canvasContainer.ondragstart = function() { return false; }; 
		// prevent text selection (except IE) 
		return false; 
	},
	resizeContainer: function(evt) {
		evt.stopPropagation();
		var pX = evt.page.x;	       
		var pY = evt.page.y;
		var newHeight = (this.initCanvasContainerHeight+(pY-this.rinitY))>0?(this.initCanvasContainerHeight+(pY-this.rinitY)):0;
		var newWidth = (this.initCanvasContainerWidth+(pX-this.rinitX))<0?
			0:(this.initCanvasContainerWidth+(pX-this.rinitX))> document.getCoordinates().width?
			document.getCoordinates().width:(this.initCanvasContainerWidth+(pX-this.rinitX));
		var newMapHeight = (this.initCanvasHeight+(pY-this.rinitY))>0?(this.initCanvasHeight+(pY-this.rinitY)):0;
		var newMapWidth = (this.initCanvasWidth+(pX-this.rinitX))<0?
			0:(this.initCanvasWidth+(pX-this.rinitX))> document.getCoordinates().width?
			document.getCoordinates().width:(this.initCanvasWidth+(pX-this.rinitX));

 		this.canvasContainer.setStyles({'height': newHeight+'px', 'width': newWidth+'px'});		
 		this.canvas.setStyles({'height': newMapHeight+'px', 'width': newMapWidth+'px'});		
		google.maps.event.trigger(this.map, 'resize');
	},
	stopResizeContainer: function(evt) {
		document.removeEvents('mousemove', this.resizeContainer);	       
	},

	renderCanvas: function() {
		this.canvas = new Element('div', {'id':'map_canvas'+this.options.id});
		this.canvas.setStyles({
			'width': this.options.canvasW,
			'height': this.options.canvasH,
			'border': '1px solid #fff'
		})
		this.canvas.inject(this.canvasContainer, 'bottom');
	},
	renderMap: function() {
		var mapOptions = {
      			mapTypeId: this.dftType,
			mapTypeControlOptions: {style: this.dftCtrlType}, 
			navigationControlOptions: {style: this.dftCtrlNav}, 
			scrollwheel: this.options.scrollwheel,
			zoom: this.options.zoom ? this.options.zoom : 1,
    			center: new google.maps.LatLng(48, 7)
    		};
		this.map = new google.maps.Map(this.canvas, mapOptions);
		this.bounds = new google.maps.LatLngBounds();
		for(i=0;i<this.pointsObj.length;i++) {
			this.insertMarker(this.pointsObj[i]);
			this.bounds.extend(new google.maps.LatLng(this.pointsObj[i].lat, this.pointsObj[i].lng));
		}
		if(!this.options.zoom) {
			this.map.fitBounds(this.bounds);
		}
		else {
			var cpoint = new google.maps.LatLng(this.pointsObj[0].lat.toFloat(), this.pointsObj[0].lng.toFloat());
			this.map.setCenter(cpoint);
			this.map.setZoom(this.options.zoom);
		}
  
	},
	insertMarker: function(pointObj) {
		var point = new google.maps.LatLng(pointObj.lat.toFloat(), pointObj.lng.toFloat());
		this.markers[pointObj.id] = new google.maps.Marker({
              		map: this.map, 
              		position: point,
			title: pointObj.label
          	});
		if($chk(pointObj.mImageUrl)) this.markers[pointObj.id].setIcon(new google.maps.MarkerImage(pointObj.mImageUrl));
		if($chk(pointObj.descriptionNode) || $chk(pointObj.descriptionIdValue) || $chk(pointObj.description)) {
			var description = $chk(pointObj.description)?
				pointObj.description:$chk(pointObj.descriptionIdValue)?
				$(pointObj.descriptionIdValue).value:$(pointObj.descriptionNode).clone();
			this.infos[pointObj.id] = new google.maps.InfoWindow({'content': description, 'maxWidth':300});
			google.maps.event.addListener(this.markers[pointObj.id], 'click', function() {this.infos[pointObj.id].open(this.map, this.markers[pointObj.id])}.bind(this))
		}
		
	},
	closeInfoWindow: function(pointId) {
		if($chk(pointId) && $chk(this.infos[pointId])) this.infos[pointId].close();	
		else for(var i=0; i<this.pointsObj.length; i++) if($chk(this.infos[this.pointsObj[i].id])) this.infos[this.pointsObj[i].id].close();
	},
	openInfoWindow: function(pointId) {
		if($chk(this.infos[pointId])) this.infos[pointId].open(this.map, this.markers[pointId]);	
	},
	addMapPoints: function(pointsJson) {
		var pointsObj = JSON.decode(pointsJson);
		if($type(pointsObj) == 'array') for(var i=0; i<pointsObj.length; i++) this.addMapPoint(pointsObj[i]);				
		else this.addMapPoint(pointsObj);
		this.map.fitBounds(this.bounds);
	},
	addMapPoint: function(pointObj) {
		this.pointsObj.push(pointObj);
		this.insertMarker(pointObj);
		this.bounds.extend(new google.maps.LatLng(pointObj.lat, pointObj.lng));
	},
	removeMapPoint: function(pointId, resizeBounds) {
		resizeBounds = $chk(resizeBounds)?resizeBounds:true;
		for(var i=0; i<this.pointsObj.length; i++) {
			if(this.pointsObj[i].id == pointId) this.pointsObj.splice(i,1);	
		}
		this.markers[pointId].setMap(null);
		if(resizeBounds) {
			this.renderMap();
		}
	},
	setFocus: function() {
		if(!this.canvasContainer.style.zIndex || (this.canvasContainer.getStyle('z-index').toInt() < window.maxZindex)) 
			this.canvasContainer.setStyle('z-index', ++window.maxZindex);	  
	},
	mapDispose: function() {
		this.display = false;
		this.canvasContainer.dispose();	    
		if(this.options.destroyOnClose) for(var prop in this) this[prop] = null;
	}

})


