<?php
// bootstrap our wordpress instance
$bootstrapSearchDir = dirname($_SERVER["SCRIPT_FILENAME"]);
$docRoot = isset($_SERVER["APPL_PHYSICAL_PATH"])?$_SERVER["APPL_PHYSICAL_PATH"]:$_SERVER["DOCUMENT_ROOT"];

if (!current_user_can("edit_pages"))
	wp_die("You can't do anything destructive in here, but you shouldn't be playing around with this anyway.");

global $wp_version, $tinymce_version;
$localJsUri = get_option("siteurl") . "/" . WPINC . "/js/";
?>

<!DOCTYPE html>
<html>
<head>
	<title>Local Market Explorer: Insert Module</title>

	<style type="text/css">
		label {
			cursor: pointer;
		}
		th {
			text-align: left;
			vertical-align: top;
		}
		#module {
			width: 140px;
		}
		#data-table td {
			padding-bottom: 5px;
		}
		.lme-areas-citystate-container label {
			width: 100px;
			float: left;
		}
		.lme-areas-city {
			width: 150px;
		}
		.lme-areas-state {
			width: 30px;
		}
		.lme-areas-neighborhood {
			width: 190px;
		}
		.lme-areas-or {
			margin: 10px 0;
			font-size: 13px;
			font-weight: bold;
		}
		.lme-areas-zip-container {
			margin-top: 10px;
			overflow: auto;
		}
		.lme-areas-zip-container label {
			width: 100px;
			float: left;
		}
		.lme-areas-zip {
			width: 55px;
		}
	</style>
</head>
<body>

	<p>
		Choose a module and enter a location below in order to embed Local Market Explorer data into
		your post or page. 
	</p>
	<table id="data-table">
		<tr>
			<th style="width: 70px; padding-top: 2px;"><label for="module">Module</label></th>
			<td>
				<select id="module">
					<option value="about-area">About Area</option>
					<option value="colleges">Colleges</option>
					<option value="market-activity">Market Activity</option>
					<option value="market-stats">Market Statistics</option>
					<option value="neighborhoods">Neighborhoods</option>
					<option value="schools">Schools</option>
					<option value="walk-score">Walk Score</option>
				</select>
			</td>
		</tr>
		<tr>
			<th style="padding-top: 7px;">Location</th>
			<td>
				<div class="lme-areas-citystate-container">
					<div>
						<label>City, State:</label>
						<input type="text" class="lme-areas-city" name="city" id="city" />,
						<input type="text" class="lme-areas-state" name="state" id="state" />
					</div>
					
					<div style="clear: both; position: relative;">
						<label>
							Neighborhood:
						</label>
						<select class="lme-areas-neighborhood" name="neighborhood" id="neighborhood" disabled="disabled">
							<option value=""> - enter a city / state - </option>
						</select>
					</div>
				</div>
				
				<div class="lme-areas-or">- or -</div>
				
				<div class="lme-areas-zip-container">
					<label>Zip:</label>
					<input type="text" class="lme-areas-zip" name="zip" id="zip" />
				</div>
			</td>
		</tr>
	</table>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="Cancel" onclick="tinyMCEPopup.close();" />
		</div>
		<div style="float: right">
			<input type="button" id="insert" name="insert" value="Insert" onclick="lmeModule.insert();" />
		</div>
	</div>
	
	<script> var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>'; </script>
	<script src="<?php echo $localJsUri ?>tinymce/tiny_mce_popup.js?ver=<?php echo urlencode($tinymce_version) ?>"></script>
	<script src="../../js/admin.js?ver=<?php echo urlencode(LME_PLUGIN_VERSION) ?>"></script>
	<script src="js/dialog.js?ver=<?php echo urlencode(LME_PLUGIN_VERSION) ?>"></script>
	
</body>
</html>
