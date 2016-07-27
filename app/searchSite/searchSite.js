window.addEvent('domready', function() {

    var field = $('search_site');

    if($('search_site_check')) {	// (typeof $('search_site_check') != 'undefined')
        $('search_site_check').addEvent('click', viewCheckOptions.bind($('search_site_check')));
    }
})

function viewCheckOptions(height_or, width_or) {

    var optionLayer = $('search_site_check_options');

    if(optionLayer.getStyle('visibility') == 'hidden') {
        optionLayer.setStyle('visibility', 'visible').fade('in');
    }
    else {
        optionLayer.fade('out');
        setTimeout(function() { optionLayer.setStyle('visibility', 'hidden'); }, 500);
    }

}
