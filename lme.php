<?
/*
Plugin Name: Local Market Explorer
Plugin URI: http://wordpress.org/extend/plugins/local-market-explorer/
Description: This plugin allows WordPress to load data from a number of real estate and neighborhood APIs to be presented all within a single page in WordPress.
Version: 2.0
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

if(is_admin()) {
	include('lme-admin.php');
	add_action('admin_head', 'lme_admin_head'); 
	add_action('admin_menu', 'lme_admin_menu');
} else {
	include('lme-widget.php');
	include('lme-client.php');
	//add_action('init', widget_lme_register);
	add_action('widgets_init', create_function('', 'return register_widget("LMEWidget");'));
	new LMEPage;
}

register_activation_hook(__FILE__, 'set_lme_options');
register_activation_hook(__FILE__, 'upgrade_lme_options');

function set_lme_options() {
	add_option('lme_panels_show_market_stats', '1', '', 'yes');
	add_option('lme_panels_show_zillow_homevalue', '1', '', 'yes');
	add_option('lme_panels_show_educationcom', '1', '', 'yes');
	add_option('lme_panels_show_zillow_marketactivity', '1', '', 'yes');
	add_option('lme_panels_show_aboutarea', '1', '', 'yes');
	add_option('lme_panels_show_flickr', '1', '', 'yes');
	add_option('lme_panels_show_walkscore', '1', '', 'yes');
	add_option('lme_panels_show_teachstreet', '1', '', 'yes');
	add_option('lme_panels_show_yelp', '1', '', 'yes');
	
	add_option('lme_apikey_zillow', '', '', 'yes');
	add_option('lme_apikey_flickr', '', '', 'yes');
	add_option('lme_apikey_walkscore', '', '', 'yes');
	add_option('lme_apikey_yelp', '', '', 'yes');
	
	add_option('lme_username_zillow', '', '', 'yes');
	add_option('lme_zillow_mylistings_widget', '', '', 'yes');
	add_option('lme_sold_listings_to_show', '', '', 'yes');
	
	add_option('lme_areas', '', '', 'yes');
	
	add_option('lme_module_order', array(
		'market-statistics'	=> 1,
		'about-area'		=> 2,
		'market-activity'	=> 3,
		'schools'			=> 4,
		'walk-score'		=> 5,
		'yelp'				=> 6,
		'teachstreet'		=> 7
	));
}
function upgrade_lme_options() {
	if (get_option('lme_areas'))
		return;
	
	$lme_areas = array();
	$lme_area_cities = unserialize(get_option('lme_area_cities'));
	$lme_area_states = unserialize(get_option('lme_area_states'));
	$lme_area_descriptions = unserialize(get_option('lme_area_descriptions'));
	
	for ($i = 0; $i < sizeOf($lme_area_cities); $i++) {
		$lme_areas[$i] = array();
		$lme_areas[$i]['city'] = $lme_area_cities[$i];
		$lme_areas[$i]['state'] = $lme_area_states[$i];
		$lme_areas[$i]['description'] = $lme_area_descriptions[$i];
	}
	
	update_option('lme_areas', $lme_areas);
	
	delete_option('lme_area_cities');
	delete_option('lme_area_states');
	delete_option('lme_area_descriptions');
}
?>
