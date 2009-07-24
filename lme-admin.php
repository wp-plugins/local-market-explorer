<?
function lme_admin_head(){
	// call this to add any options that don't exist in the current install
	set_lme_options();
	
	$wpurl = get_bloginfo('wpurl');
	echo "<script type=\"text/javascript\" src=\"{$wpurl}/wp-content/plugins/local-market-explorer/includes/lme-admin.js\"></script>";
}

function lme_admin_menu() {
   if (function_exists('add_options_page')) {
        add_options_page('LME Options Page', 'Local Market Explorer', 8, basename(__FILE__), 'lme_plugin_options');
	}
}

function lme_plugin_options(){
	?>
	<div class="wrap">
		<h2>Local Market Explorer</h2><?
	if($_REQUEST['Submit']){
		update_lme_options();
	}
	
	print_lme_options();
	?></div><?
}

function update_lme_options(){
	if($_REQUEST['lme_apikey_zillow']){
		update_option('lme_apikey_zillow', $_REQUEST['lme_apikey_zillow']);
	}
	if($_REQUEST['lme_username_zillow']){
		update_option('lme_username_zillow', $_REQUEST['lme_username_zillow']);
	}
	
	if($_REQUEST['lme_panels_show_aboutarea']){
		update_option('lme_panels_show_aboutarea', $_REQUEST['lme_panels_show_aboutarea']);
	} else {
		update_option('lme_panels_show_aboutarea', '0');
	}
	if($_REQUEST['lme_apikey_flickr']){
		update_option('lme_apikey_flickr', $_REQUEST['lme_apikey_flickr']);
	}
	
	if($_REQUEST['lme_panels_show_marketactivity']){
		update_option('lme_panels_show_marketactivity', $_REQUEST['lme_panels_show_marketactivity']);
	} else {
		update_option('lme_panels_show_marketactivity', '0');
	}
	
	if($_REQUEST['lme_sold_listings_to_show']) {
		update_option('lme_sold_listings_to_show', $_REQUEST['lme_sold_listings_to_show']);
	} else {
		update_option('lme_sold_listings_to_show', '0');
	}
	
	if($_REQUEST['lme_apikey_educationcom']){
		update_option('lme_apikey_educationcom', $_REQUEST['lme_apikey_educationcom']);
	}
	
	if($_REQUEST['lme_panels_show_walkscore']){
		update_option('lme_panels_show_walkscore', $_REQUEST['lme_panels_show_walkscore']);
	} else {
		update_option('lme_panels_show_walkscore', '0');
	}
	if($_REQUEST['lme_apikey_walkscore']){
		update_option('lme_apikey_walkscore', $_REQUEST['lme_apikey_walkscore']);
	}
	
	if($_REQUEST['lme_panels_show_yelp']){
		update_option('lme_panels_show_yelp', $_REQUEST['lme_panels_show_yelp']);
	} else {
		update_option('lme_panels_show_yelp', '0');
	}
	if($_REQUEST['lme_apikey_yelp']){
		update_option('lme_apikey_yelp', $_REQUEST['lme_apikey_yelp']);
	}
	
	$lme_area_cities = array();
	$lme_area_states = array();
	$lme_area_descriptions = array();
	
	foreach ( $_REQUEST as $key => $value ) { 
		if(strpos($key, 'lme_area_cities__') !== false){
			$lme_area_cities[sizeof($lme_area_cities)] = $value;
		}
	}
	
	foreach ( $_REQUEST as $key => $value ) { 
		if(strpos($key, 'lme_area_states__') !== false){
			$lme_area_states[sizeof($lme_area_states)] = $value;
		}
	}
	
	foreach ( $_REQUEST as $key => $value ) { 
		if(strpos($key, 'lme_area_descriptions__') !== false){
			$lme_area_descriptions[sizeof($lme_area_descriptions)] = $value;
		}
	}
	
	$lme_area_cities_new = $_REQUEST['lme_area_cities_new'];
	$lme_area_states_new = $_REQUEST['lme_area_states_new'];
	$lme_area_descriptions_new = $_REQUEST['lme_area_descriptions_new'];
	
	if($lme_area_cities_new != '' && $lme_area_states_new != ''){
		$lme_area_cities[sizeof($lme_area_cities)] = $lme_area_cities_new;
		$lme_area_states[sizeof($lme_area_states)] = $lme_area_states_new;
		$lme_area_descriptions[sizeof($lme_area_descriptions)] = $lme_area_descriptions_new;		
	}
	
	update_option('lme_area_cities', serialize($lme_area_cities));
	update_option('lme_area_states', serialize($lme_area_states));
	update_option('lme_area_descriptions', serialize($lme_area_descriptions));
			
	?><div id="message" class="updated fade"><p><strong>Options Saved</p></strong></div><?
}

function print_lme_options() {		
	$lme_panels_show_zillow_homevalue = get_option('lme_panels_show_zillow_homevalue');
	$lme_panels_show_zillow_marketactivity = get_option('lme_panels_show_zillow_marketactivity');
	$lme_apikey_zillow = get_option('lme_apikey_zillow');
	$lme_username_zillow = get_option('lme_username_zillow');
	$lme_zillow_mylistings_widget = get_option('lme_zillow_mylistings_widget');
	
	$lme_panels_show_aboutarea = get_option('lme_panels_show_aboutarea');
	$lme_apikey_flickr = get_option('lme_apikey_flickr');
	
	$lme_panels_show_marketactivity = get_option('lme_panels_show_marketactivity');
	$lme_sold_listings_to_show = get_option('lme_sold_listings_to_show');
	
	$lme_apikey_educationcom = get_option('lme_apikey_educationcom');
	
	$lme_panels_show_walkscore = get_option('lme_panels_show_walkscore');
	$lme_apikey_walkscore = get_option('lme_apikey_walkscore');
	
	$lme_panels_show_yelp = get_option('lme_panels_show_yelp');
	$lme_apikey_yelp = get_option('lme_apikey_yelp');
	
	$lme_area_cities = unserialize(get_option('lme_area_cities'));
	$lme_area_states = unserialize(get_option('lme_area_states'));
	$lme_area_descriptions = unserialize(get_option('lme_area_descriptions'));
	
	?>
		<form method="post">
			<h3>Zillow Home Value Index</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<label for="lme_apikey_zillow">API Key</label>
					</th>
					<td>
						<input id="lme_apikey_zillow" class="regular-text code" type="text" value="<?= $lme_apikey_zillow ?>" name="lme_apikey_zillow"/>
						<span class="setting-description">Get your key here: <a href="https://www.zillow.com/webservice/Registration.htm" target="_blank">https://www.zillow.com/webservice/Registration.htm</a></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_username_zillow">Zillow Username</label>
					</th>
					<td>
						<input id="lme_username_zillow" class="regular-text code" type="text" value="<?= $lme_username_zillow ?>" name="lme_username_zillow"/>
						<span class="setting-description">Filling in this option will co-brand the Zillow experience on clickthrough</span>
					</td>
				</tr>
			</table>
			
			<h3>About Area</h3>
			<table class="form-table">
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<label for="lme_panels_show_aboutarea">
							<input id="lme_panels_show_aboutarea" type="checkbox" <?= $lme_panels_show_aboutarea == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_aboutarea"/>
							Show "About Area" Panel
						</label>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_apikey_flickr">Flickr API Key</label>
					</th>
					<td>
						<input id="lme_apikey_flickr" class="regular-text code" type="text" value="<?= $lme_apikey_flickr ?>" name="lme_apikey_flickr"/>
						<span class="setting-description">Get your key here: <a href="http://www.flickr.com/services/api/keys/apply/" target="_blank">http://www.flickr.com/services/api/keys/apply/</a></span>
					</td>
				</tr>
			</table>
			
			<h3>Market Activity</h3>
			<table class="form-table">
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<p>In order to show market activity, you'll need to <a href="http://www.zillow.com/webservice/APIUpgradeRequest.htm" target="_blank">
						submit an upgrade request</a> for your Zillow API key. After signing in, please choose "Local Market Explorer Wordpress Plugin"
						from the "API Request Type" drop down box.</p>
						<br />
						<label for="lme_panels_show_marketactivity">
							<input id="lme_panels_show_marketactivity" type="checkbox" <?= $lme_panels_show_marketactivity == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_marketactivity"/>
							Show Market Activity Panel
						</label>
					</th>
				</tr>
				<tr>
					<th scope="row">
						<label for="lme_sold_listings_to_show"># of Sold Listings</label>
					</th>
					<td>
						<input id="lme_sold_listings_to_show" class="regular-text code" type="text" value="<?= $lme_sold_listings_to_show ?>" name="lme_sold_listings_to_show" maxlength="2" style="width:40px;"/>
						<span class="setting-description">Set this to a value between 0 and 20 to define how many sold listings the plugin should display</span>
					</td>
				</tr>
			</table>
			
			<h3>Walk Score</h3>
			<table class="form-table">
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<label for="lme_panels_show_walkscore">
							<input id="lme_panels_show_walkscore" type="checkbox" <?= $lme_panels_show_walkscore == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_walkscore"/>
							Show Walk Score&trade; Panel
						</label>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_apikey_walkscore">Walk Score API Key</label>
					</th>
					<td>
						<input id="lme_apikey_walkscore" class="regular-text code" type="text" value="<?= $lme_apikey_walkscore ?>" name="lme_apikey_walkscore"/>
						<span class="setting-description">Get your key here: <a href="http://www.walkscore.com/request-tile-key.php" target="_blank">http://www.walkscore.com/request-tile-key.php</a></span>
					</td>
				</tr>
			</table>
			
			<h3>Yelp</h3>
			<table class="form-table">
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<label for="lme_panels_show_yelp">
							<input id="lme_panels_show_yelp" type="checkbox" <?= $lme_panels_show_yelp == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_yelp"/>
							Show Yelp Panel
						</label>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_apikey_yelp">Yelp API Key</label>
					</th>
					<td>
						<input id="lme_apikey_yelp" class="regular-text code" type="text" value="<?= $lme_apikey_yelp ?>" name="lme_apikey_yelp"/>
						<span class="setting-description">Get your key here: <a href="http://www.yelp.com/developers/getting_started/api_access" target="_blank">http://www.yelp.com/developers/getting_started/api_access</a></span>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<input class="button-primary" type="submit" value="Save Changes" name="Submit"/>
			</p>
			
			<h3>Target Areas</h3>
		<?
		if(is_array($lme_area_cities)){
			for($i=0;$i<sizeOf($lme_area_cities);$i++){
			?>
				<table class="form-table" id="lme_area_table__<?= $i ?>">
					<tr valign="top">
						<th scope="row">
							<label for="lme_area_cities__<?= $i ?>">City</label>
						</th>
						<td>
							<input class="regular-text code" type="text" value="<?= $lme_area_cities[$i] ?>" name="lme_area_cities__<?= $i ?>"/>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="lme_area_states__<?= $i ?>">State</label>
						</th>
						<td>
							<input class="regular-text code" type="text" value="<?= $lme_area_states[$i] ?>" name="lme_area_states__<?= $i ?>"/>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="lme_area_descriptions__<?= $i ?>">Description</label>
						</th>
						<td>
							<textarea class="regular-text code" name="lme_area_descriptions__<?= $i ?>" style="width: 325px; height: 200px;" wrap="soft"><?= $lme_area_descriptions[$i] ?></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input class="button-primary" type="button" onclick="LocalMarketExplorerAdmin.RemoveArea(<?= $i ?>)" value="Remove Target Area" />
						</td>
					</tr>
					<tr>
						<td colspan="2"><hr /></td>
					</tr>
				</table>
			<?
			}
		}
		?>
			
			<h3>Add New Target Area</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<label for="lme_area_cities_new">City</label>
					</th>
					<td>
						<input class="regular-text code" type="text" value="" name="lme_area_cities_new"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_area_states_new">State</label>
					</th>
					<td>
						<input class="regular-text code" type="text" value="" name="lme_area_states_new"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_area_descriptions_new">Description</label>
					</th>
					<td>
						<textarea class="regular-text code" name="lme_area_descriptions_new"></textarea>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<input class="button-primary" type="submit" value="Save Changes" name="Submit"/>
			</p>
		</form>
	<?
}

function set_lme_options(){
	add_option('lme_panels_show_zillow_homevalue', '1', '', 'yes');
	add_option('lme_panels_show_zillow_marketactivity', '1', '', 'yes');
	add_option('lme_apikey_zillow', '', '', 'yes');
	add_option('lme_username_zillow', '', '', 'yes');
	add_option('lme_zillow_mylistings_widget', '', '', 'yes');
	
	add_option('lme_panels_show_aboutarea', '1', '', 'yes');
	add_option('lme_apikey_flickr', '', '', 'yes');
	
	add_option('lme_panels_show_marketactivity', '1', '', 'yes');
	add_option('lme_sold_listings_to_show', '', '', 'yes');
	
	add_option('lme_apikey_educationcom', '', '', 'yes');
	
	add_option('lme_panels_show_walkscore', '1', '', 'yes');
	add_option('lme_apikey_walkscore', '', '', 'yes');
	
	add_option('lme_panels_show_yelp', '1', '', 'yes');
	add_option('lme_apikey_yelp', '', '', 'yes');
	
	add_option('lme_area_cities', '', '', 'yes');
	add_option('lme_area_states', '', '', 'yes');
	add_option('lme_area_descriptions', '', '', 'yes');
}

function unset_lme_options(){
	delete_option('lme_panels_show_zillow_homevalue');
	delete_option('lme_panels_show_zillow_marketactivity');
	delete_option('lme_apikey_zillow');
	delete_option('lme_username_zillow');
	delete_option('lme_zillow_mylistings_widget');
	
	delete_option('lme_sold_listings_to_show');
	
	delete_option('lme_apikey_educationcom');
	
	delete_option('lme_panels_show_aboutarea');
	delete_option('lme_apikey_flickr');
	
	delete_option('lme_panels_show_walkscore');
	delete_option('lme_apikey_walkscore');
	
	delete_option('lme_panels_show_yelp');
	delete_option('lme_apikey_yelp');
	
	delete_option('lme_area_cities');
	delete_option('lme_area_states');
	delete_option('lme_area_descriptions');
	
	// these stay in because they may have been added at one point depending on how long the plugin has been installed
	delete_option('lme_area_neighborhoods');
	delete_option('lme_area_zips');
	delete_option('lme_sidebar_badge');
}
?>
