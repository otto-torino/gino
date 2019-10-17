
/**
 * Require osmdrawer/dist/osmdrawer.min.js
 * 
 */
(function($) {
	$(window).on("load", function() {
		var cb = function () {
			var mymap = new OpenStreetMapDrawer.Map('#map-canvas', {
				tools: {
					point: {
						options: {
							maxItemsAllowed: 3,
						}
					},
					polyline: {},
					polygon: {},
					circle: {}
				},
				exportMapCb: function (data) {
					console.log('exported data: ', data);
					//django.jQuery('#id_coordinates').val(JSON.stringify(data.point[0]));
				}
			});
			mymap.render();
			/*
			var coordinates = django.jQuery('#id_coordinates').val()
			if(coordinates) {
				data = {point: [JSON.parse(coordinates)]};
				mymap.importMap(data);
			}*/
		}
		OpenStreetMapDrawer.ready(cb);
	})
})(jQuery);
