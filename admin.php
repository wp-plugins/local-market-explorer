<?php
add_action("admin_init", "LME_Admin::Initialize");
add_action("admin_menu", "LME_Admin::AddMenu");

if(!defined('PHP_VERSION_ID'))
{
    $version = explode('.',PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

class LME_Admin {
	static function AddMenu() {
		$optionsPage = add_options_page(
			"Local Market Explorer Options",
			"Local Market Explorer",
			"manage_options",
			"lme",
			"dsSearchAgent_Admin::EditOptions"
		);
		add_action("admin_print_scripts-{$optionsPage}", "dsSearchAgent_Admin::LoadHeader");
	}
	static function Initialize() {
		register_setting("lme", "local-market-explorer", "LME::SanitizeOptions");
	}
	static function LoadHeader() {
		global $LME_PluginUrl;
		
		echo <<<HTML
			<script type="text/javascript" src="{$LME_PluginUrl}includes/lme-admin.js"></script>
			<script type="text/javascript" src="http://yui.yahooapis.com/combo?2.8.0r4/build/yahoo/yahoo-min.js&2.8.0r4/build/get/get-min.js"></script>
HTML;
	}
	static function EditOptions() {
		$options = get_option("local-market-explorer");
		
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br/></div>
		<h2>Local Market Explorer Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields("local-market-explorer"); ?>
			
			<div class="lme-notification-area">
				<p>This plugin is open-source donationware. I'm willing to accept and integrate well-written patches into the code,
				but the continued development of the module (new features, bug fixes, etc) by the plugin author is funded by
				donations. If you'd like to donate, please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10178626">donate via PayPal</a>.</p>
	
				<p>If you'd like to contribute a feature suggestion or need to document a bug, please use the <a href="http://localmarketexplorer.uservoice.com/">User Voice forum</a> set
				up specifically for that purpose. With User Voice, each user gets a fixed number of votes that they can cast for
				any particular bug or feature. The higher the number of votes for an item, the higher the priority will be for
				that item as development commences on the plugin itself.</p>
			</div>
		
			<h3>API Keys</h3>
			<p>
				In order for Local Market Explorer to load the data for the different panels, you'll need to collect a few API
				keys around the web and plug them in here.
			</p>
			<table class="form-table">
				<tr>
					<th style="width: 100px;">
						<label for="local-market-explorer[api-keys][zillow]">Zillow API key:</label>
					</th>
					<td>
						<input type="text" id="local-market-explorer[api-keys][zillow]" name="local-market-explorer[api-keys][zillow]" value="<?php echo $options["api-keys"]["zillow"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][flickr]">Flickr API key:</label>
					</th>
					<td>
						<input type="text" id="local-market-explorer[api-keys][flickr]" name="local-market-explorer[api-keys][flickr]" value="<?php echo $options["api-keys"]["flickr"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][walk-score]">Walk Score API key:</label>
					</th>
					<td>
						<input type="text" id="local-market-explorer[api-keys][walk-score]" name="local-market-explorer[api-keys][walk-score]" value="<?php echo $options["api-keys"]["walk-score"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][yelp]">Yelp API key:</label>
					</th>
					<td>
						<input type="text" id="local-market-explorer[api-keys][yelp]" name="local-market-explorer[api-keys][yelp]" value="<?php echo $options["api-keys"]["yelp"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][teachstreet]">Teachstreet API key:</label>
					</th>
					<td>
						<input type="text" id="local-market-explorer[api-keys][teachstreet]" name="local-market-explorer[api-keys][teachstreet]" value="<?php echo $options["api-keys"]["teachstreet"] ?>" />
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" name="Submit" value="Save API Keys" />
			</p>
		</form>
	</div>
<?php
	}
	static function SanitizeOptions($options) {
		return $options;
	}
}
?>

	?>
			
		
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
