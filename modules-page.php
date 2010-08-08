<?php

// much of this code is inspired or directly copied from dsIDXpress's code as permitted by the ASL 2.0

add_action("pre_get_posts", array("LmeModulesPage", "preActivate"));
add_filter("posts_request", array("LmeModulesPage", "clearQuery"));
add_filter("the_posts", array("LmeModulesPage", "activate"));

require_once("modules/api-requester.php");
require_once("modules/market-stats.php");

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

		// keep wordpress from mucking up our HTML
		remove_filter("the_content", "wptexturize");
		remove_filter("the_content", "convert_smilies");
		remove_filter("the_content", "convert_chars");
		remove_filter("the_content", "wpautop");
		remove_filter("the_content", "prepend_attachment");

		add_filter("page_link", array("LmeModulesPage", "GetPermalink")); // for any plugin that needs it

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
	}
	static function clearQuery($query) {
		global $wp_query;

		if (!is_array($wp_query->query) || !isset($wp_query->query["lme-action"]))
			return $query;

		return "";
	}
	static function getPageTitle() {
		$neighborhood = ucwords(str_replace(array("-", "_"), array(" ", "-"), $wp_query->query["lme-neighborhood"]));
		$city = ucwords(str_replace(array("-", "_"), array(" ", "-"), $wp_query->query["lme-city"]));
		$state = strtoupper($wp_query->query["lme-state"]);
		$zip = $wp_query->query["lme-zip"];
		$title = null;
	
		if (!empty($zip)) {
			$title = $zip;
		} else {
			$title = "{$city}, {$state}";
			if (!empty($neighborhood)) {
				$title = "{$neighborhood}, {$title}";
			}
		}
		return "{$title} Local Area Information";
	}
	static function getPageContent() {
		$modules = self::getFinalApiUrls();
		LmeApiRequester::gatherContent(&$modules);
	}
	static function getFinalApiUrls() {
		global $wp_query;
		
		$neighborhood = str_replace(array("-", "_"), array(" ", "-"), $wp_query->query["lme-neighborhood"]);
		$city = str_replace(array("-", "_"), array(" ", "-"), $wp_query->query["lme-city"]);
		$state = $wp_query->query["lme-state"];
		$zip = $wp_query->query["lme-zip"];
		
		$options = get_option(LME_OPTION_NAME);
		$modules = array();
		
		foreach ($options["global-modules"] as $order => $module) {
			if ($module == "market-stats")
				$modules[$module] = LmeModuleMarketStats::getApiUrls($neighborhood, $city, $state, $zip);
		}
		return $modules;
	}
}
?>
