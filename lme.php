<?php
/*
Plugin Name: Local Market Explorer
Plugin URI: http://wordpress.org/extend/plugins/local-market-explorer/
Description: This plugin allows WordPress to load data from a number of real estate and neighborhood APIs to be presented all within a single page in WordPress.
Version: 3.0
Author: Andrew Mattie & Jonathan Mabe
*/

/*  Copyright 2009-2010, Andrew Mattie & Jonathan Mabe

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wp_version, $wpdb;

require_once(ABSPATH . "wp-admin/includes/plugin.php");
$pluginData = get_plugin_data(__FILE__);

define("LME_OPTION_NAME", "local-market-explorer");
define("LME_MIN_VERSION_PHP", "5.2.0");
define("LME_MIN_VERSION_WORDPRESS", "2.8.5");
define("LME_PLUGIN_URL", WP_PLUGIN_URL . "/local-market-explorer/");
define("LME_PLUGIN_VERSION", $pluginData["Version"]);
define("LME_PLUGIN_DB_VERSION", "1.0");
define("LME_AREAS_TABLE", $wpdb->prefix . "lme_areas");

register_activation_hook(__FILE__, array("Lme", "InitializeAreasSchema"));
//register_activation_hook(__FILE__, array("Lme", "UpgradeOptionsFromVersion1"));
//register_activation_hook(__FILE__, array("Lme", "UpgradeOptionsFromVersion2"));
register_activation_hook(__FILE__, array("Lme", "FlushRewriteRules"));

if (is_admin()) {
	require_once(WP_PLUGIN_DIR . "/local-market-explorer/admin.php");
} else {
	require_once("modules-page.php");
}
require_once("modules-page-rewrite.php");
require_once("api-requester.php");
require_once("modules/market-stats.php");
require_once("modules/market-activity.php");
require_once("modules/schools.php");
require_once("modules/yelp.php");
require_once("modules/walk-score.php");
require_once("modules/teachstreet.php");
require_once("modules/about-area.php");
require_once("modules/neighborhoods.php");
require_once("shortcodes.php");

class Lme {
	static function InitializeAreasSchema() {
		global $wpdb;
		
		$options = get_option(LME_OPTION_NAME);
		if (!$options)
			$options = array();
		
		if ($options["db-version"] == LME_PLUGIN_DB_VERSION)
			return;
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$sql = "CREATE TABLE " . LME_AREAS_TABLE . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			city VARCHAR(30),
			neighborhood VARCHAR(70),
			zip CHAR(5),
			state CHAR(2),
			description TEXT,
			PRIMARY KEY  (id)
		);";
		dbDelta($sql);
		
		$options["db-version"] = LME_PLUGIN_DB_VERSION;
		update_option(LME_OPTION_NAME, $options);
	}
	static function UpgradeOptionsFromVersion1() {
		if (get_option("lme_areas"))
			return;
		
		$lme_areas = array();
		$lme_area_cities = unserialize(get_option("lme_area_cities"));
		$lme_area_states = unserialize(get_option("lme_area_states"));
		$lme_area_descriptions = unserialize(get_option("lme_area_descriptions"));
		
		for ($i = 0; $i < sizeOf($lme_area_cities); $i++) {
			$lme_areas[$i] = array();
			$lme_areas[$i]["city"] = $lme_area_cities[$i];
			$lme_areas[$i]["state"] = $lme_area_states[$i];
			$lme_areas[$i]["description"] = $lme_area_descriptions[$i];
		}
		
		update_option("lme_areas", $lme_areas);
		delete_option("lme_area_cities");
		delete_option("lme_area_states");
		delete_option("lme_area_descriptions");
	}
	static function UpgradeOptionsFromVersion2() {
		if (get_option("local-market-explorer"))
			return;
		
		global $wpdb;
		
		$options = array();
		$options["api-keys"] = array(
			"zillow"			=> get_option("lme_apikey_zillow"),
			"educationdotcom"	=> "bd23bb5cb91e37c39282f6bf75d56fb9",
			"walk-score"		=> get_option("lme_apikey_walkscore"),
			"yelp"				=> get_option("lme_apikey_yelp")
		);
		$options["panels"] = array_merge(array(), get_option("lme_module_order"));
		
		if (!get_option("lme_panels_show_market_stats") && $options["panels"]["market-statistics"])
			unset($options["panels"]["market-statistics"]);
			
		if (!get_option("lme_panels_show_aboutarea") && $options["panels"]["about-area"])
			unset($options["panels"]["about-area"]);
			
		if (!get_option("lme_panels_show_zillow_marketactivity") && $options["panels"]["market-activity"])
			unset($options["panels"]["market-activity"]);
			
		if (!get_option("lme_panels_show_educationcom") && $options["panels"]["schools"])
			unset($options["panels"]["schools"]);
			
		if (!get_option("lme_panels_show_walkscore") && $options["panels"]["walk-score"])
			unset($options["panels"]["walk-score"]);
			
		if (!get_option("lme_panels_show_yelp") && $options["panels"]["yelp"])
			unset($options["panels"]["yelp"]);
			
		if (!get_option("lme_panels_show_teachstreet") && $options["panels"]["teachstreet"])
			unset($options["panels"]["teachstreet"]);
	}
	static function FlushRewriteRules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
}
?>
