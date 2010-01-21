<?php
add_action("admin_init", "LME_Admin::Initialize");
add_action("admin_menu", "LME_Admin::AddMenu");

class LME_Admin {
	static function AddMenu() {
		$optionsPage = add_options_page(
			"Local Market Explorer Options",
			"Local Market Explorer",
			"manage_options",
			"lme",
			"LME_Admin::EditOptions"
		);
		add_action("admin_print_scripts-{$optionsPage}", "LME_Admin::LoadHeader");
	}
	static function Initialize() {
		register_setting(LME_OPTION_NAME, LME_OPTION_NAME, "LME_Admin::SanitizeOptions");
	}
	static function LoadHeader() {
		$pluginUrl = LME_PLUGIN_URL;
		wp_enqueue_script("lme-admin", "{$pluginUrl}js/admin.js", array("jquery", "jquery-ui-sortable"), LME_PLUGIN_VERSION, true);

		echo <<<HTML
			<link rel="stylesheet" type="text/css" href="{$pluginUrl}css/admin.css" />
			<link rel="stylesheet" type="text/css" href="{$pluginUrl}css/jquery-ui-1.7.2.custom.css" />
HTML;
	}
	static function EditOptions() {
		$options = get_option(LME_OPTION_NAME);

?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br/></div>
		<h2>Local Market Explorer Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields(LME_OPTION_NAME); ?>

			<div class="lme-notification-area">
				<p>This plugin is open-source donationware. I'm willing to accept and integrate well-written patches into the code,
				but the continued development of the module (new features, bug fixes, etc) by the plugin author is funded by
				donations. If you'd like to donate, please
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10178626">donate via PayPal</a>.</p>

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
			<table class="form-table lme-api-keys">
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][zillow]">Zillow API key:</label>
					</th>
					<td>
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][zillow]" name="local-market-explorer[api-keys][zillow]" value="<?php echo $options["api-keys"]["zillow"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][flickr]">Flickr API key:</label>
					</th>
					<td>
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][flickr]" name="local-market-explorer[api-keys][flickr]" value="<?php echo $options["api-keys"]["flickr"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][walk-score]">Walk Score API key:</label>
					</th>
					<td>
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][walk-score]" name="local-market-explorer[api-keys][walk-score]" value="<?php echo $options["api-keys"]["walk-score"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][yelp]">Yelp API key:</label>
					</th>
					<td>
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][yelp]" name="local-market-explorer[api-keys][yelp]" value="<?php echo $options["api-keys"]["yelp"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][teachstreet]">Teachstreet API key:</label>
					</th>
					<td>
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][teachstreet]" name="local-market-explorer[api-keys][teachstreet]" value="<?php echo $options["api-keys"]["teachstreet"] ?>" />
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" name="Submit" value="Save API Keys" />
			</p>

			<h3>Modules to Display</h3>
			<ul id="lme-modules-to-display">
				<li class="ui-state-default">
					<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
					Market statistics <span class="lme-small">(area statistics from Zillow)</span>
				</li>
				<li class="ui-state-default">
					<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
					About area <span class="lme-small">(your own description)</span>
				</li>
				<li class="ui-state-default">
					<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
					Market activity <span class="lme-small">(recent sales from Zillow)</span>
				</li>
				<li class="ui-state-default">
					<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
					Schools <span class="lme-small">(from Education.com)</span>
				</li>
				<li class="ui-state-default">
					<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
					Walk Score
				</li>
				<li class="ui-state-default">
					<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
					Place reviews <span class="lme-small">(from Yelp)</span>
				</li>
				<li class="ui-state-default">
					<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
					Classes <span class="lme-small">(from Teachstreet)</span>
				</li>

			</ul>
		</form>
	</div>
<?php
	}
	static function SanitizeOptions($options) {
		return $options;
	}
}
?>
