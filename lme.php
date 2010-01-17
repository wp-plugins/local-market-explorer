<?
/*
Plugin Name: Local Market Explorer
Plugin URI: http://wordpress.org/extend/plugins/local-market-explorer/
Description: This plugin allows WordPress to load data from a number of real estate and neighborhood APIs to be presented all within a single page in WordPress.
Version: 2.1
Author: Andrew Mattie & Jonathan Mabe
*/

/*  Copyright 2009, Andrew Mattie & Jonathan Mabe

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

DEFINE("LME_DB_VERSION", 1.0);
DEFINE("LME_OPTION_KEY", "local-market-explorer");

register_activation_hook(__FILE__, "LME::InitializeAreasSchema");
register_activation_hook(__FILE__, "LME::UpgradeOptionsFromVersion1");
register_activation_hook(__FILE__, "LME::UpgradeOptionsFromVersion2");
register_activation_hook(__FILE__, "LME::FlushRewriteRules");
add_action("widgets_init", "LME::InitWidgets");

$LME_PluginUrl = WP_PLUGIN_URL . "/" . basename(__FILE__, ".php") . "/";
$LME_PluginPath = dirname(__FILE__) . "/";

require_once($LME_PluginPath . "widget-saved-areas.php");
require_once($LME_PluginPath . "rewrite.php");

if (is_admin()) {
	require_once($LME_PluginPath . "admin.php");
} else {
	require_once($LME_PluginPath . "client.php");
}

class dsSearchAgent {
	static function InitializeAreasSchema() {
		global $wpdb;
		
		$options = get_option(LME_OPTION_KEY);
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name || $options["db-version"] != LME_DB_VERSION) {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			$table_name = $wpdb->prefix . "lme_areas";
			$sql = "CREATE TABLE " . $table_name . " (
				id MEDIUMINT NOT NULL AUTO_INCREMENT,
				city VARCHAR(100),
				state CHAR(2),
				zip CHAR(5),
				neighborhood VARCHAR(100),
				description TEXT,
				idx_link VARCHAR(200),
				PRIMARY KEY  (id)
			);";
			dbDelta($sql);
			
			$options["db-version"] = LME_DB_VERSION;
			update_option(LME_OPTION_KEY, $options);
		}
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
			"flickr"			=> get_option("lme_apikey_flickr"),
			"educationdotcom"	=> "bd23bb5cb91e37c39282f6bf75d56fb9",
			"walk-score"		=> get_option("lme_apikey_walkscore"),
			"yelp"				=> get_option("lme_apikey_yelp"),
			"teachstreet"		=> get_option("lme_apikey_teachstreet")
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
	
	static function InitWidgets() {
		register_widget("LME_ListAreasWidget");
	}
}
?>
