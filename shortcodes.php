<?php
class LmeShortcodes {
	static function Module($atts, $content = null, $code = "") {
		$neighborhood = $atts["neighborhood"];
		$city = $atts["city"];
		$state = $atts["state"];
		$zip = $atts["zip"];
		$modules = array();
		
		if ($atts["module"] == "market-stats") {
			$modules[] = LmeModuleMarketStats::getApiUrls($neighborhood, $city, $state, $zip);
		} else if ($atts["module"] == "market-activity") {
			$modules[] = LmeModuleMarketActivity::getApiUrls($neighborhood, $city, $state, $zip);
		} else if ($atts["module"] == "schools") {
			$modules[] = LmeModuleSchools::getApiUrls($neighborhood, $city, $state, $zip);
		} else if ($atts["module"] == "yelp") {
			$modules[] = LmeModuleYelp::getApiUrls($neighborhood, $city, $state, $zip);
		} else if ($atts["module"] == "teachstreet") {
			$modules[] = LmeModuleTeachStreet::getApiUrls($neighborhood, $city, $state, $zip);
		}
		
		$moduleContent = LmeApiRequester::gatherContent($modules);
	
		if ($atts["module"] == "market-stats") {
			return LmeModuleMarketStats::getModuleHtml($moduleContent[0]);
		} else if ($atts["module"] == "market-activity") {
			return LmeModuleMarketActivity::getModuleHtml($moduleContent[0]);
		} else if ($atts["module"] == "schools") {
			return LmeModuleSchools::getModuleHtml($moduleContent[0]);
		} else if ($atts["module"] == "yelp") {
			return LmeModuleYelp::getModuleHtml($moduleContent[0]);
		} else if ($atts["module"] == "walk-score") {
			return LmeModuleWalkScore::getModuleHtml($moduleContent[0]);
		} else if ($atts["module"] == "teachstreet") {
			return LmeModuleTeachStreet::getModuleHtml($moduleContent[0]);
		} else if ($atts["module"] == "about") {
			return LmeModuleAboutArea::getModuleHtml($moduleContent[0]);
		}
	}
}

add_shortcode("lme-module", array("LmeShortcodes", "Module"));
?>