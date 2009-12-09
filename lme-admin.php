<?
function lme_admin_head(){
	$wpurl = get_bloginfo('wpurl');
	echo <<<HTML
		<script type="text/javascript" src="{$wpurl}/wp-content/plugins/local-market-explorer/includes/lme-admin.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/combo?2.8.0r4/build/yahoo/yahoo-min.js&2.8.0r4/build/get/get-min.js"></script>
		<script>LocalMarketExplorerAdmin.BlogUrl = '$wpurl';</script>
		<style type="text/css">
			.lme_notification_area {
				background-color:#EEEEEE;
				border:1px solid #CCCCCC;
				font-weight:bold;
				margin-top:10px;
				padding:15px;
				text-align:center;
				width:525px;
			}
		</style>
HTML;
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
	if($_REQUEST['lme_panels_show_market_stats']){
		update_option('lme_panels_show_market_stats', $_REQUEST['lme_panels_show_market_stats']);
	} else {
		update_option('lme_panels_show_market_stats', '0');
	}
	
	if($_REQUEST['lme_panels_show_aboutarea']){
		update_option('lme_panels_show_aboutarea', $_REQUEST['lme_panels_show_aboutarea']);
	} else {
		update_option('lme_panels_show_aboutarea', '0');
	}
	if($_REQUEST['lme_panels_show_flickr']){
		update_option('lme_panels_show_flickr', $_REQUEST['lme_panels_show_flickr']);
	} else {
		update_option('lme_panels_show_flickr', '0');
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
	
	if($_REQUEST['lme_panels_show_educationcom']){
		update_option('lme_panels_show_educationcom', $_REQUEST['lme_panels_show_educationcom']);
	} else {
		update_option('lme_panels_show_educationcom', '0');
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
	
	if($_REQUEST['lme_panels_show_teachstreet']){
		update_option('lme_panels_show_teachstreet', $_REQUEST['lme_panels_show_teachstreet']);
	} else {
		update_option('lme_panels_show_teachstreet', '0');
	}
	if($_REQUEST['lme_apikey_teachstreet']){
		update_option('lme_apikey_teachstreet', $_REQUEST['lme_apikey_teachstreet']);
	}
	
	$lme_areas = array();
	
	foreach ( $_REQUEST as $key => $value ) { 
		if (strpos($key, 'lme_areas_') === false)
			continue;
		
		$area_data = substr($key, 10);
		$index = substr($area_data, 0, strpos($area_data, '_'));
		$type = substr($area_data, strpos($area_data, '_') + 1);

		if (!$lme_areas[$index])
			$lme_areas[$index] = array();

		$lme_areas[$index][$type] = str_replace('\"', '"', htmlspecialchars_decode($value, ENT_NOQUOTES));
	}

	$lme_area_new_neighborhood = $_REQUEST['lme_area_new_neighborhood'];
	$lme_area_new_city = $_REQUEST['lme_area_new_city'];
	$lme_area_new_state = $_REQUEST['lme_area_new_state'];
	$lme_area_new_zip = $_REQUEST['lme_area_new_zip'];
	$lme_area_new_description = $_REQUEST['lme_area_new_description'];
	
	if ($lme_area_new_description != '') {
		$new_index = sizeof($lme_areas);
		$lme_areas[$new_index] = array();
		
		if ($lme_area_new_neighborhood != '')
			$lme_areas[$new_index]['neighborhood'] = $lme_area_new_neighborhood;
		if ($lme_area_new_city != '')
			$lme_areas[$new_index]['city'] = $lme_area_new_city;
		if ($lme_area_new_state != '')
			$lme_areas[$new_index]['state'] = $lme_area_new_state;
		if ($lme_area_new_zip != '')
			$lme_areas[$new_index]['zip'] = $lme_area_new_zip;
		$lme_areas[$new_index]['description'] = $lme_area_new_description;
	}
	
	foreach ($lme_areas as $key => $value) {
		if (empty($value["idx_link"]) && empty($value["description"])) {
			unset($lme_areas[$key]);
		}
	}
	update_option('lme_areas', $lme_areas);
	
	$moduleOrder = array();
	foreach ( $_REQUEST as $key => $value ) {
		if (strpos($key, 'lme-order-') === false)
			continue;
		
		$moduleOrder[substr($key, 10)] = $value;
	}
	update_option('lme_module_order', $moduleOrder);
			
	?><div id="message" class="updated fade"><p><strong>Options Saved</p></strong></div><?
}

function print_lme_options() {
	$lme_panels_show_market_stats = get_option('lme_panels_show_market_stats');
	$lme_apikey_zillow = get_option('lme_apikey_zillow');
	$lme_username_zillow = get_option('lme_username_zillow');
	$lme_zillow_mylistings_widget = get_option('lme_zillow_mylistings_widget');
	
	$lme_panels_show_aboutarea = get_option('lme_panels_show_aboutarea');
	$lme_panels_show_flickr = get_option('lme_panels_show_flickr');
	$lme_apikey_flickr = get_option('lme_apikey_flickr');
	
	$lme_panels_show_marketactivity = get_option('lme_panels_show_marketactivity');
	$lme_sold_listings_to_show = get_option('lme_sold_listings_to_show');
	
	$lme_panels_show_educationcom = get_option('lme_panels_show_educationcom');
	
	$lme_panels_show_walkscore = get_option('lme_panels_show_walkscore');
	$lme_apikey_walkscore = get_option('lme_apikey_walkscore');
	
	$lme_panels_show_yelp = get_option('lme_panels_show_yelp');
	$lme_apikey_yelp = get_option('lme_apikey_yelp');
	
	$lme_panels_show_teachstreet = get_option('lme_panels_show_teachstreet');
	
	$moduleOrder = get_option('lme_module_order');

	$lme_areas = get_option('lme_areas');
	?>
			
		<div class="lme_notification_area" style="margin: 0 auto; font-weight: normal;">
			<p>This plugin is open-source donationware. I'm willing to accept and integrate well-written patches into the code,
			but the continued development of the module (new features, bug fixes, etc) by the plugin author is funded by
			donations. If you'd like to donate, please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10178626">donate via PayPal</a>.</p>

			<p>If you'd like to contribute a feature suggestion or need to document a bug, please use the <a href="http://localmarketexplorer.uservoice.com/">User Voice forum</a> set
			up specifically for that purpose. With User Voice, each user gets a fixed number of votes that they can cast for
			any particular bug or feature. The higher the number of votes for an item, the higher the priority will be for
			that item as development commences on the plugin itself.</p>
		</div>
		
		<form method="post" id="lme_options_form">
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
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<label for="lme_panels_show_market_stats">
							<input id="lme_panels_show_market_stats" type="checkbox" <?= $lme_panels_show_market_stats == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_market_stats"/>
							Show Market Stats Panel
						</label>
					</th>
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
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<label for="lme_panels_show_flickr">
							<input id="lme_panels_show_flickr" type="checkbox" <?= $lme_panels_show_flickr == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_flickr"/>
							Show Flickr Panel
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
			
			<h3>Education.com</h3>
			<table class="form-table">
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<label for="lme_panels_show_educationcom">
							<input id="lme_panels_show_educationcom" type="checkbox" <?= $lme_panels_show_educationcom == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_educationcom"/>
							Show Education.com Panel
						</label>
					</th>
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
			
			<h3>TeachStreet (Local Classes)</h3>
		<?
		if (function_exists('json_decode')) {
		?>
			<table class="form-table">
				<tr>
					<th class="th-full" colspan="2" scope="row">
						<label for="lme_panels_show_teachstreet">
							<input id="lme_panels_show_teachstreet" type="checkbox" <?= $lme_panels_show_teachstreet == '1' ? 'checked="checked"' : ''?> value="1" name="lme_panels_show_teachstreet"/>
							Show TeachStreet Panel
						</label>
					</th>
				</tr>
			</table>
		<?
		} else {
		?>
			<p>
				Unfortunately, it seems that the version of PHP that you're running is too old to be able to support the data available via TeachStreet.
				We highly recommend that you ask your web host to upgrade their version of PHP on the server that your website is on as it is buggy and more
				than 3 years old.
			</p> 
		<?
		}
		?>
			
			<p class="submit">
				<input class="button-primary" type="submit" value="Save Changes" name="Submit"/>
			</p>
			
			<h3>Module order</h3>
			
			<table class="form-table">
				<tr>
					<td width="100"><label for="lme-order-market-statistics">Market Statistics</label></td>
					<td><input name="lme-order-market-statistics" type="text" value="<?= $moduleOrder['market-statistics'] ?>" style="width: 20px" /></td>
				</tr>
				<tr>
					<td><label for="lme-order-about-area">About Area</label></td>
					<td><input name="lme-order-about-area" type="text" value="<?= $moduleOrder['about-area'] ?>" style="width: 20px" /></td>
				</tr>
				<tr>
					<td><label for="lme-order-market-activity">Market Activity</label></td>
					<td><input name="lme-order-market-activity" type="text" value="<?= $moduleOrder['market-activity'] ?>" style="width: 20px" /></td>
				</tr>
				<tr>
					<td><label for="lme-order-schools">Schools</label></td>
					<td><input name="lme-order-schools" type="text" value="<?= $moduleOrder['schools'] ?>" style="width: 20px" /></td>
				</tr>
				<tr>
					<td><label for="lme-order-walk-score">Walk Score</label></td>
					<td><input name="lme-order-walk-score" type="text" value="<?= $moduleOrder['walk-score'] ?>" style="width: 20px" /></td>
				</tr>
				<tr>
					<td><label for="lme-order-yelp">Yelp</label></td>
					<td><input name="lme-order-yelp" type="text" value="<?= $moduleOrder['yelp'] ?>" style="width: 20px" /></td>
				</tr>
				<tr>
					<td><label for="lme-order-teachstreet">Teachstreet</label></td>
					<td><input name="lme-order-teachstreet" type="text" value="<?= $moduleOrder['teachstreet'] ?>" style="width: 20px" /></td>
				</tr>
				<tr>
					<td><label for="lme-order-idx-link">IDX Link</label></td>
					<td><input name="lme-order-idx-link" type="text" value="<?= $moduleOrder['idx-link'] ?>" style="width: 20px" /></td>
				</tr>
			</table>
			
			<p class="submit">
				<input class="button-primary" type="submit" value="Save Changes" name="Submit"/>
			</p>
			
			<h3>Target Areas</h3>
			<p>(please note that you DO NOT need to create / save an area below if you don't want to add a description --
			you can simply link to the area in your blog)</p>
		<?
		if(is_array($lme_areas)){
			for ($i = 0; $i < sizeOf($lme_areas); $i++){
			?>
				<table class="form-table" id="lme_area_table__<?= $i ?>">
					<tr valign="top">
						<th scope="row">
							<label for="lme_areas_<?= $i ?>_city">City, State</label>
						</th>
						<td>
							<input class="lme_area_city regular-text code" type="text" value="<?= $lme_areas[$i]['city'] ?>" name="lme_areas_<?= $i ?>_city" style="width: 200px" />,
							<input class="lme_area_state regular-text code" type="text" value="<?= $lme_areas[$i]['state'] ?>" name="lme_areas_<?= $i ?>_state" style="width: 25px" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="lme_areas_<?= $i ?>_neighborhood">Neighborhood</label>
						</th>
						<td>
							<select class="lme_area_neighborhood" id="lme_areas_<?= $i ?>_neighborhood" name="lme_areas_<?= $i ?>_neighborhood" disabled="true">
								<option value="<?= $lme_areas[$i]['neighborhood'] ?>"><?= $lme_areas[$i]['neighborhood'] ?></option>
							</select>
							<input class="lme_area_neighborhood_hidden" type="hidden" name="lme_areas_<?= $i ?>_neighborhood" value="<?= $lme_areas[$i]['neighborhood'] ?>" />
							<a href="javascript:void(0);" onclick="LocalMarketExplorerAdmin.LoadNeighborhoods(this);" class="lme_area_neighborhood_loader">(load available neighborhoods)</a>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="lme_areas_<?= $i ?>_zip">Zip</label>
						</th>
						<td>
							<input class="lme_area_zip regular-text code" type="text" value="<?= $lme_areas[$i]['zip'] ?>" name="lme_areas_<?= $i ?>_zip" style="width: 70px" />
						</td>
					</tr>
					<tr>
						<th>Link(s)</th>
						<td class="lme_area_link_display"></td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="lme_areas_<?= $i ?>_idx_link">IDX link for this area</label>
						</th>
						<td>
							<input class="regular-text code" type="text" value="<?= $lme_areas[$i]['idx_link'] ?>" name="lme_areas_<?= $i ?>_idx_link" style="width: 200px" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="lme_areas_<?= $i ?>_description">Description</label>
						</th>
						<td>
							<textarea class="regular-text code" name="lme_areas_<?= $i ?>_description" style="width: 325px; height: 200px;" wrap="soft"><?= htmlentities($lme_areas[$i]['description'], ENT_NOQUOTES) ?></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input class="button-secondary" type="button" onclick="LocalMarketExplorerAdmin.RemoveArea(<?= $i ?>)" value="Remove Target Area" />
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
						<label for="lme_area_new_city">City, State</label>
					</th>
					<td>
						<input class="lme_area_city regular-text code" type="text" value="" name="lme_area_new_city" style="width: 200px" />
						<input class="lme_area_state regular-text code" type="text" value="" name="lme_area_new_state" style="width: 25px" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_area_new_neighborhood">Neighborhood</label>
					</th>
					<td>
						<select class="lme_area_neighborhood" id="lme_area_new_neighborhood" name="lme_area_new_neighborhood" disabled="true">
							<option value="">Fill in city and state to enable neighborhood selection</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_area_new_zip">Zip</label>
					</th>
					<td>
						<input class="lme_area_zip regular-text code" type="text" value="" name="lme_area_new_zip" style="width: 70px" />
					</td>
				</tr>
				<tr>
					<th>Link(s)</th>
					<td class="lme_area_link_display"></td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_area_new_idx_link">IDX link for this area</label>
					</th>
					<td>
						<input class="regular-text code" type="text" value="" name="lme_areas_new_idx_link" style="width: 300px" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="lme_area_new_description">Description</label>
					</th>
					<td>
						<textarea class="regular-text code" name="lme_area_new_description" style="width: 325px; height: 200px;" wrap="soft"></textarea>
					</td>
				</tr>
			</table>
			
			<div class="lme_notification_area">
				Neighborhood lookup provided courtesy of <a href="http://www.diversesolutions.com/?r=lme-blog-admin" target="_blank">Diverse Solutions</a>.
				<br /><br />
				<a href="http://www.diversesolutions.com/?r=lme-blog-admin" target="_blank">
				<img src="<?= get_bloginfo('wpurl') ?>/wp-content/plugins/local-market-explorer/images/diverse-solutions-logo.gif" alt="Diverse Solutions - Real Estate Technology Made Easy" />
				</a>
			</div>
			
			<p class="submit">
				<input class="button-primary" type="submit" value="Save Changes" name="Submit"/>
			</p>
		</form>
	<?
}
?>
