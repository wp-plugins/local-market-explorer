<?php

add_action("sm_buildmap", array("LmeXmlSitemaps", "buildSitemap"));

class LmeXmlSitemaps {
	static function buildSitemap() {
		global $wpdb;
		
		$options = get_option(LME_OPTION_NAME);
		$blogUrl = get_bloginfo("url");

		$generatorObject = &GoogleSitemapGenerator::GetInstance();

		if ($generatorObject != null) {
			if ($options["disallow-sitemap-without-description"] == "on")
				$areas = $wpdb->get_results("SELECT neighborhood, city, state, zip FROM " . LME_AREAS_TABLE . " WHERE description <> '' ORDER BY state, city, neighborhood, zip");
			else
				$areas = $wpdb->get_results("SELECT neighborhood, city, state, zip FROM " . LME_AREAS_TABLE . " ORDER BY state, city, neighborhood, zip");

			foreach ($areas as $area) {
				if (!empty($area->zip)) {
					$locationUrl = $area->zip;
				} else {
					$cityUrl = urlencode(strtolower(str_replace(array("-", " "), array("_", "-"), $area->city)));
					if (!empty($area->neighborhood))
						$neighborhoodUrl = urlencode(strtolower(str_replace(array("-", " "), array("_", "-"), $area->neighborhood))) . "/";
					$locationUrl = "{$neighborhoodUrl}{$cityUrl}/" . strtolower($area->state);
					
				}
				$url = "{$blogUrl}/local/{$locationUrl}/";
				$generatorObject->AddUrl($url, time(), "daily", ".5");
			}
		}
	}
}

?>