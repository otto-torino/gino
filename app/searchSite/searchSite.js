
$(function () {
	var field = $('.search_site');

	if($('.search_site_check')) {
		$('.search_site_check').click(viewCheckOptions).bind($('.search_site_check'));
	}
})

function viewCheckOptions(height_or, width_or) {

    var optionLayer = $('.search_site_check_options');

    if(optionLayer.attr('visibility') == 'hidden') {
        optionLayer.attr('visibility', 'visible').fadeIn();
    }
    else {
        optionLayer.fadeOut();
        setTimeout(function() { optionLayer.attr('visibility', 'hidden'); }, 500);
    }

}
