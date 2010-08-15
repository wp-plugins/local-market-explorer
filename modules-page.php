<?php

// much of this code is inspired or directly copied from dsIDXpress's code as permitted by the ASL 2.0

add_action("pre_get_posts", array("LmeModulesPage", "preActivate"));
add_filter("posts_request", array("LmeModulesPage", "clearQuery"));
add_filter("the_posts", array("LmeModulesPage", "activate"));

require_once("api-requester.php");
require_once("modules/market-stats.php");
require_once("modules/market-activity.php");
require_once("modules/schools.php");
require_once("modules/yelp.php");
require_once("modules/walk-score.php");

class LmeModulesPage {
	// this is a roundabout way to make sure that any other plugin / widget / etc that uses the WP_Query object doesn't get our IDX data
	// in their query. since we don't actually get the query itself in the "the_posts" filter, we have to step around the issue by
	// checking it BEFORE it gets to the the_posts filter. later, in the the_posts filter, we restore the previous state of things.
	static function preActivate($q) {
		global $wp_query;

		if (!is_array($wp_query->query) || !is_array($q->query) || isset($wp_query->query["suppress_filters"]) || isset($q->query["suppress_filters"])) {
			return;
		}

		if (isset($wp_query->query["lme-action"])) {
			if (!isset($q->query["lme-action"])) {
				$wp_query->query["lme-action-swap"] = $wp_query->query["lme-action"];
				unset($wp_query->query["lme-action"]);
			} else {
				$q->query_vars["caller_get_posts"] = true;
			}
		}
	}
	static function activate($posts) {
		global $wp_query;

		// see comment above preActivate
		if (is_array($wp_query->query) && isset($wp_query->query["lme-action-swap"])) {
			$wp_query->query["lme-action"] = $wp_query->query["lme-action-swap"];
			unset($wp_query->query["lme-action-swap"]);
			return $posts;
		}

		if (!is_array($wp_query->query) || !isset($wp_query->query["lme-action"])) {
			return $posts;
		}

		wp_enqueue_style("lme", LME_PLUGIN_URL . "css/client.css", null, LME_PLUGIN_VERSION);
		
		// keep wordpress from mucking up our HTML
		remove_filter("the_content", "wptexturize");
		remove_filter("the_content", "convert_smilies");
		remove_filter("the_content", "convert_chars");
		remove_filter("the_content", "wpautop");
		remove_filter("the_content", "prepend_attachment");

		add_filter("page_link", array("LmeModulesPage", "getPermalink")); // for any plugin that needs it

		// no RSS feeds
		remove_action("wp_head", "feed_links");
		remove_action("wp_head", "feed_links_extra");

		$wp_query->found_posts = 0;
		$wp_query->max_num_pages = 0;
		$wp_query->is_page = 1;
		$wp_query->is_home = null;
		$wp_query->is_singular = 1;

		set_query_var("name", "local-market-explorer"); // at least a few themes require _something_ to be set here to display a good <title> tag
		set_query_var("pagename", "local-market-explorer"); // setting pagename in case someone wants to do a custom theme file for this "page"
		$posts = array((object)array(
			"ID"				=> time(), // this needs to be a non-negative number that doesn't conflict with another post id
			"comment_count"		=> 0,
			"comment_status"	=> "closed",
			"ping_status"		=> "closed",
			"post_author"		=> 1,
			"post_content"		=> self::getPageContent(),
			"post_date"			=> date("c"),
			"post_date_gmt"		=> gmdate("c"),
			"post_name"			=> "dsidxpress-data",
			"post_parent"		=> 0,
			"post_status"		=> "publish",
			"post_title"		=> self::getPageTitle(),
			"post_type"			=> "page"
		));
		return $posts;
	}
	static function clearQuery($query) {
		global $wp_query;

		if (!is_array($wp_query->query) || !isset($wp_query->query["lme-action"]))
			return $query;

		return "";
	}
	static function getPageTitle() {
		$neighborhood = self::getNeighborhood();
		$city = self::getCity();
		$state = self::getState();
		$zip = self::getZip();
	
		if (!empty($zip)) {
			$title = $zip;
		} else {
			$title = "{$city}, {$state}";
			if (!empty($neighborhood)) {
				$title = "{$neighborhood}, {$title}";
			}
		}
		$title = ucwords($title);
		
		return "{$title} Local Area Information";
	}
	static function getPageContent() {
		$options = get_option(LME_OPTION_NAME);
		$modules = self::getFinalApiUrls();
		$content = "";
		LmeApiRequester::gatherContent(&$modules);
		
		$neighborhood = self::getNeighborhood();
		$city = self::getCity();
		$state = self::getState();
		$zip = self::getZip();
		
		foreach ($options["global-modules"] as $order => $module) {
			if ($module == "market-stats")
				$content .= LmeModuleMarketStats::getModuleHtml($modules["market-stats"]);
			if ($module == "market-activity")
				$content .= LmeModuleMarketActivity::getModuleHtml($modules["market-activity"]);
			if ($module == "schools")
				$content .= LmeModuleSchools::getModuleHtml($modules["schools"]);
			if ($module == "yelp")
				$content .= LmeModuleYelp::getModuleHtml($modules["yelp"]);
			if ($module == "walk-score")
				$content .= LmeModuleWalkScore::getModuleHtml($neighborhood, $city, $state, $zip);
		}
		
		return $content;
	}
	static function getPermalink($permalink) {
		return $permalink;
	}
	static function getFinalApiUrls() {
		$neighborhood = self::getNeighborhood();
		$city = self::getCity();
		$state = self::getState();
		$zip = self::getZip();
		
		$options = get_option(LME_OPTION_NAME);
		$modules = array();
		
		foreach ($options["global-modules"] as $order => $module) {
			if ($module == "market-stats")
				$modules[$module] = LmeModuleMarketStats::getApiUrls($neighborhood, $city, $state, $zip);
			if ($module == "market-activity")
				$modules[$module] = LmeModuleMarketActivity::getApiUrls($neighborhood, $city, $state, $zip);
			if ($module == "schools")
				$modules[$module] = LmeModuleSchools::getApiUrls($neighborhood, $city, $state, $zip);
			if ($module == "yelp")
				$modules[$module] = LmeModuleYelp::getApiUrls($neighborhood, $city, $state, $zip);
		}
		return $modules;
	}
	static function getNeighborhood() {
		global $wp_query;
		return str_replace(array("-", "_"), array(" ", "-"), $wp_query->query["lme-neighborhood"]);
	}
	static function getCity() {
		global $wp_query;
		return str_replace(array("-", "_"), array(" ", "-"), $wp_query->query["lme-city"]);
	}
	static function getState() {
		global $wp_query;
		return $wp_query->query["lme-state"];
	}
	static function getZip() {
		global $wp_query;
		return $wp_query->query["lme-zip"];
	}
}
?>
