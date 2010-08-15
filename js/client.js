var lme = lme || {};

lme.loadYelpMaps = function() {
	var $ = jQuery;
	
	$('.lme-map').each(function() {
		var canvas = this;
		var resultsId = canvas.getAttribute('data-resultsid');
		var mapBounds = new google.maps.LatLngBounds();
		var data = lme.yelpData[resultsId];
		var map, mapOptions, marker;
		var infoWindow = new google.maps.InfoWindow();
		
		for (var i = data.length; i--;)
			mapBounds.extend(new google.maps.LatLng(data[i].latitude, data[i].longitude));

		mapOptions = {
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			scrollwheel: false,
			scaleControl: true,
			mapTypeControl: false,
			navigationControlOptions: { position: google.maps.ControlPosition.TOP_LEFT }
		}
		map = new google.maps.Map(canvas, mapOptions);
		map.fitBounds(mapBounds);
		
		for (var i = data.length; i--;) {
			marker = new google.maps.Marker({
				icon: 'http://media3.px.yelpcdn.com/static/200911304213451137/i/map/marker_star.png',
				map: map,
				position: new google.maps.LatLng(data[i].latitude, data[i].longitude)
			});
			(function() {
				var content =
					'<div style="font-size: 11px; font-family: Verdana; width: 250px; height: 50px;">' +
					'<a href="' + data[i].url + '">' + data[i].name + '</a><br />' +
					'<img src="' + data[i].rating_img_url + '" class="lme-rating" /> based on ' + data[i].review_count + ' reviews<br />' +
					'</div>';
				google.maps.event.addListener(marker, 'click', function() {
					console.log(this);
					infoWindow.setContent(content);
					infoWindow.open(map, this);
				});
			})();
		}
	});
}