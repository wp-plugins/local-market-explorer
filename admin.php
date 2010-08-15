<?php
add_action("admin_init", array("LmeAdmin", "initialize"));
add_action("admin_menu", array("LmeAdmin", "addMenu"));
add_action("wp_ajax_lme-proxy_zillow_api_call", array("LmeAdmin", "proxyZillowApiRequest"));

class LmeAdmin {
	static function addMenu() {
		$optionsPage = add_options_page("Local Market Explorer Options", "Local Market Explorer", "manage_options", "lme",
			array("LmeAdmin", "editOptions"));
		add_action("admin_print_scripts-{$optionsPage}", array("LmeAdmin", "loadHeader"));
	}
	static function initialize() {
		register_setting(LME_OPTION_NAME, LME_OPTION_NAME, array("LmeAdmin", "sanitizeOptions"));
	}
	static function loadHeader() {
		$pluginUrl = LME_PLUGIN_URL;
		wp_enqueue_script("yui-3", "http://yui.yahooapis.com/3.1.1/build/yui/yui-min.js", null, "3.1.1", true);
		wp_enqueue_script("lme-admin", "{$pluginUrl}js/admin.js", array("jquery", "jquery-ui-sortable"), LME_PLUGIN_VERSION, true);

		echo <<<HTML
			<link rel="stylesheet" type="text/css" href="{$pluginUrl}css/jquery-ui-1.7.2.custom.css" />
			<link rel="stylesheet" type="text/css" href="{$pluginUrl}css/admin.css" />
HTML;
	}
	static function editOptions() {
		global $wpdb;
		
		$options = get_option(LME_OPTION_NAME);
		$areas = $wpdb->get_results("SELECT * FROM " . LME_AREAS_TABLE . " ORDER BY state, city, neighborhood, zip");
		$checkedModules = array(); 
		
		$moduleInfo = array(
			"about"				=> array("name" => "About area",		"description" => "your own description"),
			"market-stats"		=> array("name" => "Market statistics",	"description" => "area statistics from Zillow"),
			"market-activity"	=> array("name" => "Market activity",	"description" => "recent sales from Zillow"),
			"local-photos"		=> array("name" => "Local photos",		"description" => "from Panoramio"),
			"schools"			=> array("name" => "Schools",			"description" => "from Education.com"),
			"walk-score"		=> array("name" => "Walk Score",		"description" => "see www.walkscore.com"),
			"yelp"				=> array("name" => "Yelp reviews",		"description" => "from Yelp"),
			"classes"			=> array("name" => "Classes",			"description" => "from Teachstreet"),
			"nileguide"			=> array("name" => "Things to do",		"description" => "from NileGuide")
		);
		
		$listItemHtml = <<<HTML
			<li id="lme-areas-#{id}">
				<div class="lme-areas-citystate-container">
					<div>
						<label>City, State:</label>
						<input type="text" class="lme-areas-city" name="lme-areas[#{id}][city]" value="#{city}" />,
						<input type="text" class="lme-areas-state" name="lme-areas[#{id}][state]" value="#{state}" />
					</div>
					
					<div style="clear: both;">
						<label>
							Neighborhood <span class="lme-small">(optional)</span>:
						</label>
						<select class="lme-areas-neighborhood" name="lme-areas[#{id}][neighborhood]" disabled="disabled">
							#{neighborhoodOptions}
						</select>
					</div>
				</div>
				
				<div class="lme-areas-or">- or -</div>
				
				<div class="lme-areas-zip-container">
					<label>Zip:</label>
					<input type="text" class="lme-areas-zip" name="lme-areas[#{id}][zip]" value="#{zip}" />
				</div>
				
				<div class="lme-areas-description-container">
					<label>Description <span class="lme-small">(HTML allowed)</span>:</label><br />
					<textarea name="lme-areas[#{id}][description]">#{description}</textarea>
				</div>
				
				<div class="lme-araes-remove-container">
					<input type="button" class="lme-areas-remove button-secondary" value="Remove this area's description" />
				</div>
			</li>
HTML;
		$moduleOrderHtml = <<<HTML
			<li class="ui-state-default">
				<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
				<input type="checkbox" id="local-market-explorer[global-modules][#{internal-name}]"
					name="local-market-explorer[global-modules][#{internal-name}]" #{checked} />
				<label for="local-market-explorer[global-modules][#{internal-name}]">#{name}</label>
				<span class="lme-small">(#{short-description})</span>
			</li>
HTML;
?>
	<div class="wrap lme">
		<div class="icon32" id="icon-options-general"><br/></div>
		<h2>Local Market Explorer Options</h2>
		<form method="post" action="options.php">
<?php
			settings_fields(LME_OPTION_NAME);
?>

			<div id="lme-options">
				<ul>
					<li>General options</li>
					<li>Module page options</li>
					<li>Help</li>
				</ul>
				<ul>
					<li>general options</li>
					<li>module page</li>
					<li>help</li>
				</ul>
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
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][zillow]"
							name="local-market-explorer[api-keys][zillow]"
							value="<?php echo $options["api-keys"]["zillow"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][walk-score]">Walk Score API key:</label>
					</th>
					<td>
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][walk-score]"
							name="local-market-explorer[api-keys][walk-score]"
							value="<?php echo $options["api-keys"]["walk-score"] ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="local-market-explorer[api-keys][yelp]">Yelp API key:</label>
					</th>
					<td>
						<input class="lme-api-key" type="text" id="local-market-explorer[api-keys][yelp]"
							name="local-market-explorer[api-keys][yelp]"
							value="<?php echo $options["api-keys"]["yelp"] ?>" />
					</td>
				</tr>
			</table>

			<h3 style="margin-top: 40px;">Modules to Display and Module Order</h3>
			<p>
				<i>These settings only apply to the pre-built Local Market Explorer "virtual" pages.</i> Place a check in the
				checkbox for all of the modules you want to display and / or reorder the modules by simply dragging and
				dropping them to the desired order.
			</p>
			<ul id="lme-modules-to-display">
<?php
		$moduleOrderReplacements = array("#{internal-name}", "#{checked}", "#{name}", "#{short-description}");
		foreach ($options["global-modules"] as $module) {
			$moduleOrderValues = array(
				$module,
				"checked='checked'",
				$moduleInfo[$module]["name"],
				$moduleInfo[$module]["description"]
			);
			echo str_replace($moduleOrderReplacements, $moduleOrderValues, $moduleOrderHtml);
		}
		foreach (array_keys($moduleInfo) as $module) {
			if (in_array($module, $options["global-modules"]))
				continue;
			$moduleOrderValues = array(
				$module,
				"",
				$moduleInfo[$module]["name"],
				$moduleInfo[$module]["description"]
			);
			echo str_replace($moduleOrderReplacements, $moduleOrderValues, $moduleOrderHtml);
		}
?>
			</ul>

			<h3 style="margin-top: 40px;">Area Descriptions</h3>
			<p>
				<i>These settings only apply to the pre-built Local Market Explorer "virtual" pages.</i> You can add descriptions
				for different areas that will show up when the virtual page for that area loads.
			</p>
			<ul id="lme-areas-descriptions">
<?php
		$listItemReplacements = array("#{id}", "#{city}", "#{state}", "#{neighborhoodOptions}", "#{zip}", "#{description}");
		foreach ($areas as $area) {
			if ($area->neighborhood)
				$neighborhood = "<option value='{$area->neighborhood}'>{$area->neighborhood}</option>";
			else
				$neighborhood = "<option value=''>(click to load neighborhoods)</option>"; 
			$listItemValues = array(
				$area->id,
				$area->city,
				$area->state,
				$neighborhood,
				$area->zip,
				htmlentities($area->description)
			);
			echo str_replace($listItemReplacements, $listItemValues, $listItemHtml);
		}
		echo str_replace($listItemReplacements, array("new", "", "", "<option value=''>(enter a city / state first)</option>", "", ""), $listItemHtml);
?>
				<li>
					<div style="text-align: right;">
						<input id="lme-areas-add" type="button" class="button-secondary" value="Add description for a new area" />
					</div>
				</li>
			</ul>
			
			<h3 style="margin-top: 40px;">Other Options</h3>
			<ul id="lme-other-options">
				<li>
					<input type="checkbox" name="local-market-explorer[disallow-sitemap]"
						<?php $options["disallow-sitemap"] ? "checked='checked" : "" ?> />
					<label><i>Don't</i> allow pages with descriptions to be added to your sitemap?</label>
				</li>
				<li>
					<input type="checkbox" name="local-market-explorer[disallow-loading-without-description]"
						<?php $options["disallow-loading-without-description"] ? "checked='checked" : "" ?> />
					<label><i>Don't</i> allow visitors and search engines to load pages without you first adding a description?</label>
				</li>
				<li>
					<input type="text" name="local-market-explorer[zillow-username]"
						value="<?php $options["zillow-username"] ?>" />
					<label>Your username on Zillow.com (for your branding when clicking through on the links)</label>
				</li>
			</ul>
			
			<div style="text-align: right; margin: 30px 0;">
				<input id="lme-save-options" type="submit" class="button-primary" name="Submit" value="Save Options" />
			</div>
		</form>
	</div>
<?php
	}
	static function sanitizeOptions($options) {
		$areas = $_POST["lme-areas"];
		
		if (sizeof($options["global-module-orders"]) > 0 && isset($options["global-module-orders"][0]))
			$options["global-modules"] = explode(",", $options["global-module-orders"]);
		unset($options["global-module-orders"]);
		
		//print_r("<pre>");
		//print_r($options);
		//print_r($areas);
		//LmeAdmin::addNewAreaDescriptions($areas);
		//exit();
		
		return $options;
	}
	static function addNewAreaDescriptions($newAreasArray) {
		global $wpdb;
		
		unset($newAreasArray["new"]);
		foreach ($newAreasArray as $area) {
			$wpdb->insert(
				LME_AREAS_TABLE,
				array(
					"city"			=> $area["city"],
					"neighborhood"	=> $area["neighborhood"],
					"zip"			=> $area["zip"],
					"state"			=> $area["state"],
					"description"	=> $area["description"]
				),
				array("%s", "%s", "%s", "%s", "%s")
			);
		}
	}
	static function proxyZillowApiRequest() {
		$apiBase = "http://www.zillow.com/webservice/" . $_GET["api"] . ".htm?";
		$apiParams = $_GET["apiParams"];
		
		$finalApiUrl = $apiBase;
		foreach ($apiParams as $k => $v)
			$finalApiUrl .= $k . "=" . urlencode($v) . "&";
		
		$apiResponse = wp_remote_get($finalApiUrl);
		
		header("Cache-Control: max-age=86400"); // we'll consider responses to be valid for a day
		echo json_encode(simplexml_load_string($apiResponse["body"]));
		die();
	}
}
?>
