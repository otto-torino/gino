window.addEvent('domready', function() {

	var field = $('search_site');

	if($chk($('search_site_check'))) {
		var height_or = $('search_site_check_options').getStyle('height').toInt();
		var width_or = $('search_site_check_options').getStyle('width').toInt();
		$('search_site_check').addEvent('click', viewCheckOptions.bind($('search_site_check'), [height_or, width_or]));
	}

})

function viewCheckOptions(height_or, width_or) {
	var optionLayer = $('search_site_check_options');

	optionLayer.getChildren('div')[0].setStyle('visibility', 'hidden');
	
	if(optionLayer.style.display=='none') {
		var height_f = width_f = 0;
		var height_t = height_or;
		var width_t = width_or;
		var close = false;
		var top = this.getCoordinates($('searchSite')).top;
		var left = this.getCoordinates($('searchSite')).left;
		optionLayer.setStyles({
			'width': '0px',
			'height': '0px',
			'display': 'block',
		});
	}
	else { var height_f = height_or; var height_t = 0; var width_f = width_or; var width_t = 0; var close = true; }

	var myEffect = new Fx.Morph(optionLayer, {duration: 'short', transition: Fx.Transitions.Sine.easeOut});
	myEffect.start({
    		'height': [height_f, height_t],
    		'width': [width_f, width_t] 
	}).chain(function() {optionLayer.getChildren('div')[0].setStyle('visibility', 'visible');if(close) optionLayer.style.display='none';});
	

}
