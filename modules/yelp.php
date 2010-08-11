<?php

class LmeModuleYelp {
	static function getApiUrls($opt_neighborhood, $opt_city, $opt_state, $opt_zip) {
		$options = get_option(LME_OPTION_NAME);
		$apiKey = $options["api-keys"]["yelp"];
		$url = "http://api.yelp.com/business_review_search?ywsid={$apiKey}&limit=10&category=active+food+localflavor+nightlife+restaurants&location=";
		
		if (isset($opt_zip)) {
			$locationParams = "{$opt_zip}";
		} else {
			$encodedCity = urlencode($opt_city);
			$locationParams = "{$encodedCity},{$opt_state}";
			if (strlen($opt_neighborhood) > 0) {
				$encodedNeighborhood = urlencode($opt_neighborhood);
				$locationParams = "{$encodedNeighborhood},{$locationParams}";
			}
		}
		
		return array(
			"yelp"	=> "{$url}{$locationParams}"
		);
	}
	static function getModuleHtml($apiResponses) {
		$yelpResponse = json_decode($apiResponses["yelp"])->businesses;
		
		if (empty($yelpResponse))
			return;
		
		$jsonResults = array();
		$resultsId = rand();
		wp_enqueue_script("jquery");
		wp_enqueue_script("local-market-explorer", LME_PLUGIN_URL . "js/client.js", null, null, true);
		wp_enqueue_script("gmaps3", "http://maps.google.com/maps/api/js?sensor=false&callback=lme.loadYelpMaps", null, null, true);
		
		foreach ($yelpResponse as $business) {
			$jsonResults[] = (object)array(
				"name"				=> $business->name,
				"address1"			=> $business->address1,
				"address2"			=> $business->address2,
				"address3"			=> $business->address3,
				"city"				=> $business->city,
				"state_code"		=> $business->state_code,
				"zip"				=> $business->zip,
				"phone"				=> $business->phone,
				"rating_img_url"	=> $business->rating_img_url,
				"review_count"		=> $business->review_count,
				"url"				=> $business->url,
				"latitude"			=> $business->latitude,
				"longitude"			=> $business->longitude,
				"photo_url"			=> $business->photo_url
			);
		}
		
		$jsonResultsSerialized = json_encode($jsonResults);
		$content = <<<HTML
			<script>
				var lme.yelpData = lme.yelpData || [];
				lme.yelpData.push({"{$resultsId}":{$jsonResultsSerialized}});
			</script>
			<h2 class="lme-module-heading">Yelp</h2>
			<div class="lme-module lme-yelp">
				<div class="lme-map lme-map-{$resultsId}"></div>
				<div class="lme-businesses">
HTML;
		$content .= <<<HTML
					
HTML;
		$content .= <<<HTML
				</div>
				<img class="lme-market-logo" src="http://media2.px.yelpcdn.com/static/20091130149848283/i/developers/yelp_logo_75x38.png" />
			</div>
HTML;
		return $content;
	}
}

?>