<?php

class LmeModuleStreetAdvisor {
	static function getModuleHtml($opt_neighborhood, $opt_city, $opt_state, $opt_zip) {

    $level=0;    
		if (!empty($opt_zip)) {
			$locationParams = "{$opt_zip}";
      $level = 4;
		} 
    else {
			$encodedCity = urlencode($opt_city);
			$locationParams = "{$encodedCity},{$opt_state}";
      if ($level==0) {
		    $level=4;
      }
    	if (strlen($opt_neighborhood) > 0) {
				$encodedNeighborhood = urlencode($opt_neighborhood);
				$locationParams = "{$encodedNeighborhood},{$locationParams}";
        $level=5;
			}
		}
    
    $geocodeUrl = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=";
		$geocodeUrl .= $locationParams;
		$geocodeContent = json_decode(LmeApiRequester::getContent($geocodeUrl));
		$latLng = $geocodeContent->results[0]->geometry->location;
    $long_name = $geocodeContent->results[0]->address_components[0]->long_name;
    $lat = $latLng->lat;
    $lng = $latLng->lng;
    
		return <<<HTML
    
      <div id="streetadvisor-widget-984">
        <a href="http://www.streetadvisor.com/{$lat}/{$lng}/{$level}">Best places to live in {$long_name}</a>
      </div>
      <script src="http://widget.www.streetadvisor.com/what-the-locals-think/{$lat}/{$lng}/{$level}?id=streetadvisor-widget-984&width=589&height=500&isOkToBubbleUpIfLocationIsNotFound=false"></script>
HTML;
	}
}

?>