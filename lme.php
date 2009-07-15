<?
/*
Plugin Name: Local Market Explorer
Plugin URI: http://wordpress.org/extend/plugins/local-market-explorer/
Description: This plugin allows WordPress to load data from a number of real estate and neighborhood APIs to be presented all within a single page in WordPress.
Version: 1.0-b13
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

if(is_admin()){
	include('lme-admin.php');
	add_action('admin_head', 'lme_admin_head' ); 
	add_action('admin_menu', 'lme_admin_menu');
}

register_activation_hook(__FILE__, 'set_lme_options');
register_deactivation_hook(__FILE__, 'unset_lme_options');

include('lme-widget.php');
include('lme-client.php');
//add_action('init', widget_lme_register);
add_action('widgets_init', create_function('', 'return register_widget("LMEWidget");'));
new LMEPage;
?>
