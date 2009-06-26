<?php
class LMEPage
{
	var $slug = 'local';
	var $city = '';
	var $state = '';
	//var $zip = '';

	// the following 3 vars will only be set if the neighborhood is part of the URL
	//var $neighborhood = '';
	// these will be set from the initial zillow request we pull
	//var $center_lat = '';
	//var $center_long = '';
	
	var $location_for_display = '';
	var $is_lme = false;
	
	// zillow's GetDemographics call returns a link that we later need in the market activity module
	var $zillow_for_sale_link = '';
	
	function LMEPage(){
		add_filter('posts_request', array(&$this, 'query_override_for_lme'));
		add_filter('the_posts', array(&$this, 'get_post'));
		add_filter('wp_head', array(&$this, 'get_head'));
		add_filter('wp_footer', array(&$this, 'get_footer'));
	}
	
	// this will speed up requests by making the query to MySQL SUPER simple
	function query_override_for_lme($query){
		$this->check_url();
		
		if ($this->is_lme){
			return 'SELECT NULL WHERE 1 = 0';
		} else {
			return $query;
		}
	}
	
	// hooked filters
	function get_post($posts) {
		// filter 'the_posts'

		if ($this->is_lme){
			remove_filter('the_content', 'wpautop'); // keep wordpress from mucking up our HTML
			$formattedNow = date('Y-m-d H:i:s');
			
			$lme_post = new stdClass();
			$lme_post->ID = -1;
			$lme_post->post_author = 1;
			$lme_post->post_date = $formattedNow;
			$lme_post->post_date_gmt = $formattedNow;
			$lme_post->post_content = $this->get_content();
			$lme_post->post_title = $this->city . ', ' . $this->state;
			$lme_post->post_category = 0;
			$lme_post->post_excerpt = '';
			$lme_post->post_status = 'static';
			$lme_post->comment_status = 'closed';
			$lme_post->ping_status = 'closed';
			$lme_post->post_password = '';
			$lme_post->post_name = $this->slug . '/' . $this->city . ',' . $this->state; // (ex. $slug/%city,%state)';
			$lme_post->to_ping = '';
			$lme_post->pinged = '';
			$lme_post->post_modified = $formattedNow; // maybe this and the gmt should be some static date for WP caching reasons?
			$lme_post->post_modified_gmt = $formattedNow;
			$lme_post->post_content_filtered = '';
			$lme_post->post_parent = 0;
			$lme_post->guid = get_bloginfo('wpurl') . '/' . $this->slug . '/' . $this->city . ',' . $this->state;
			$lme_post->menu_order = 0;
			$lme_post->post_type = 'page';
			$lme_post->post_mime_type = '';
			$lme_post->comment_count = 0;
		
			$wp_query->is_page = true;
			//Not sure if this one is necessary but might as well set it like a true page
			$wp_query->is_singular = true;
			$wp_query->is_home = false;
			$wp_query->is_archive = false;
			$wp_query->is_category = false;
			//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
			unset($wp_query->query["error"]);
			$wp_query->query_vars["error"]="";
			$wp_query->is_404=false;
		
			return array($lme_post);
		} else {
			return $posts;
		}
	}
	
	function get_head() {
		//filter wp_head
		$this->check_url();
		$wpurl = get_bloginfo('wpurl');
		
		if ($this->is_lme){
			echo <<<HEAD
				<link rel="stylesheet" type="text/css" href="{$wpurl}/wp-content/plugins/lme/includes/lme-client.css" />
				<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
				<script type="text/javascript">
					var \$j = jQuery.noConflict();
				</script>
				<script type="text/javascript" src="{$wpurl}/wp-content/plugins/lme/includes/lme-client.js"></script>
HEAD;
		}
	}
	function get_footer() {
		//filter wp_footer
		$current_year = date('Y');
		if ($this->is_lme) {
			echo <<<FOOTER
				<div id="lme_footer">
					<p>
						&copy; Zillow, Inc., {$current_year}. Use is subject to <a href="http://www.zillow.com/corp/Terms.htm?scid=gen-api-wplugin" target="_blank">Terms of Use</a>.
						<a href="http://www.zillow.com/howto/Zestimate.htm?scid=gen-api-wplugin" target="_blank">What\'s a Zestimate</a>?
					</p>
					<p>This product uses the Flickr API but is not endorsed or certified by Flickr.</p>
				</div>
FOOTER;
		}
	}
	
	function check_url(){
		global $wp;
		global $wp_query;
		
		$cityStateRegex = "/". $this->slug ."\/(?<City>[^,\/]+),(?<State>[\w]{2})\/?(?<Neighborhood>[0-9a-zA-Z])?/";
		$zipRegex = "/". $this->slug ."\/(?<Zip>[\d]{5})\/?/";

		$cityStateRegexSuccess = preg_match($cityStateRegex, $wp->request, $cityStateUrlMatch);
		$zipRegexSuccess = preg_match($zipRegex, $wp->request, $zipUrlMatch);

		if ($cityStateRegexSuccess == 0 && $zipRegexSuccess == 0) {
			$this->is_lme = false;
			return;
		}

		$this->city = trim(ucwords(str_replace('-', ' ', $cityStateUrlMatch['City'])));
		$this->state = trim(strtoupper(str_replace('-', ' ', $cityStateUrlMatch['State'])));
		//$this->neighborhood = trim(str_replace('-', ' ', $cityStateUrlMatch['Neighborhood']));
		//$this->zip = $zipUrlMatch['Zip'];
		
		$this->location_for_display = $this->city . ', ' . $this->state;

		/*if ($this->neighborhood != '') {
			$this->location_for_display = $this->neighborhood . ' in ' . $this->city . ', ' . $this->state;
		} else if ($this->city != '' && $this->state != '') {
			$this->location_for_display = $this->city . ', ' . $this->state;
		} else if ($this->zip != '') {
			$this->location_for_display = $this->zip;
		}*/

		$this->is_lme = true;
	}
	
	function get_string_from_xml($xml){
		return trim((string)$xml);
	}
	function get_money_from_xml($xml){
		return number_format(doubleval(trim((string)$xml)));
	}
	
	// content functions
	function get_content() {
		$lme_panels_show_aboutarea = get_option('lme_panels_show_aboutarea');
		$lme_panels_show_marketactivity = get_option('lme_panels_show_marketactivity');
		$lme_panels_show_walkscore = get_option('lme_panels_show_walkscore');
		
		$lme_apikey_flickr = get_option('lme_apikey_flickr');
		$lme_apikey_walkscore = get_option('lme_apikey_walkscore');
		
		$lme_content = <<<LME_CONTENT
			<div class="local_market_explorer">
				<!-- HEADER (LOCATION) WITH PAGE ANCHOR LINKS FOR SECTIONS -->
				<div class="lme_header">
					<div class="lme_left"></div>
					<div class="lme_middle" id="lme_navigation">
						<a href="#lme-zillow-home-value-index">Zillow Home Value</a> |
LME_CONTENT;

		if ($lme_panels_show_aboutarea) {
			$lme_content .= <<<LME_CONTENT
						<a href="#lme-about-area">About Area</a> |
LME_CONTENT;
		}
		if ($lme_panels_show_marketactivity) {
			$lme_content .= <<<LME_CONTENT
						<a href="#lme-market-activity">Market Activity</a> |
LME_CONTENT;
		}
		
		$lme_content .= <<<LME_CONTENT
						<a href="#lme-schools">Schools</a> |
LME_CONTENT;
		if ($lme_panels_show_walkscore) {
			$lme_content .= <<<LME_CONTENT
						<a href="#lme-walk-score">Walk Score</a>
LME_CONTENT;
		}
		
		$home_value_data = $this->get_zillow_home_value_data();
		$lme_content .= <<<LME_CONTENT
					</div>
					<div class="lme_right"></div>
				</div>

				<!-- "ZILLOW HOME VALUE INDEX" SECTION -->
				<a name="lme-zillow-home-value-index"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>Zillow Home Value Index</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_zillow_index" class="lme_container_body">{$home_value_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;

		if ($lme_panels_show_aboutarea) {
			$about_area_data = $this->get_about_area_data();
			$lme_content .= <<<LME_CONTENT
				<!-- "ABOUT {LOCATION}" (CONFIGS + FLICKR) SECTION -->
				<a name="lme-about-area"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>About</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_about_area" class="lme_container_body">{$about_area_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		}
		
		if ($lme_panels_show_marketactivity) {
			$market_activity_data = $this->get_zillow_market_activity_data();
			$lme_content .= <<<LME_CONTENT
				<!-- "MARKET ACTIVITY" (ZILLOW) SECTION -->
				<a name="lme-market-activity"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>Market Activity</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_market_activity" class="lme_container_body">{$market_activity_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		}
		
		$educationdotcom_data = $this->get_educationdotcom_data();
		$lme_content .= <<<LME_CONTENT
				<!-- "SCHOOLS" (EDUCATION.COM) SECTION -->
				<a name="lme-schools"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>Schools</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_schools" class="lme_container_body">{$educationdotcom_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		
		if ($lme_panels_show_walkscore) {
			$walk_score_data = $this->get_walk_score_data();
			$lme_content .= <<<LME_CONTENT
				<!-- WALK SCORE SECTION -->
				<a name="lme-walk-score"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>Walk Score</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_walk_score" class="lme_container_body">{$walk_score_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		}
		
		$lme_content .= '</div>';
		
		return $lme_content;
	}

	function get_zillow_home_value_data() {
		$lme_apikey_zillow = get_option('lme_apikey_zillow');
		$lme_username_zillow = get_option('lme_username_zillow');
		
		$zillow_chart_url = "http://www.zillow.com/webservice/GetRegionChart.htm?zws-id=$lme_apikey_zillow&state=$this->state&city=$this->city&unit-type=percent&width=400&height=200";
		$zillow_xml = simplexml_load_file("http://www.zillow.com/webservice/GetDemographics.htm?zws-id=$lme_apikey_zillow&state=$this->state&city=$this->city"); // .'&neighborhood=Ballard'
		$zillow_chart = simplexml_load_file("http://www.zillow.com/webservice/GetRegionChart.htm?zws-id=$lme_apikey_zillow&state=$this->state&city=$this->city&unit-type=percent&width=400&height=200");
		
		$node = $zillow_chart->xpath("response"); $region_chart = $node[0];
		$node = $zillow_xml->xpath("response/charts/chart[name='Average Home Value']"); $avg_home_value = $node[0];
		$node = $zillow_xml->xpath("response/charts/chart[name='Average Condo Value']"); $avg_condo_value = $node[0];
		$node = $zillow_xml->xpath("response/links/affordability"); $affordability_link = $node[0];
		$node = $zillow_xml->xpath("response/links/forSale"); $this->zillow_for_sale_link = $node[0];
		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='Zillow Home Value Index']"); $zillow_home_value = $node[0];
		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='1-Yr. Change']"); $one_yr_change = $node[0];
		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='Median Condo Value']"); $median_condo_value = $node[0];
		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='Median Single Family Home Value']"); $median_single_family = $node[0];
		
		$city_home_value = $this->get_string_from_xml($zillow_home_value->values->city->value);
		$national_home_value = $this->get_string_from_xml($zillow_home_value->values->nation->value);
		$city_year_change_percent = $this->get_string_from_xml($one_yr_change->values->city->value);
		$national_year_change_percent = $this->get_string_from_xml($one_yr_change->values->nation->value);
		
		$row1_name = $this->get_string_from_xml($zillow_home_value->name);
		$formatted_city_home_value = "$" . number_format($city_home_value);
		$formatted_national_home_value = "$" . number_format($national_home_value);
		
		$row2_name = $this->get_string_from_xml($one_yr_change->name);
		$formatted_city_year_change = "$" . number_format($city_home_value - ($city_home_value * (1 - $city_year_change_percent)));
		$formatted_national_year_change = "$" . number_format($national_home_value - ($national_home_value * (1 - $national_year_change_percent)));
		
		$row3_name = $this->get_string_from_xml($median_condo_value->name);
		$formatted_city_condo_value = "$" . number_format($this->get_string_from_xml($median_condo_value->values->city->value));
		$formatted_national_condo_value = "$" . number_format($this->get_string_from_xml($median_condo_value->values->nation->value));
		
		$row4_name = $this->get_string_from_xml($median_single_family->name);
		$formatted_city_sfr_value = "$" . number_format($this->get_string_from_xml($median_single_family->values->city->value));
		$formatted_national_sfr_value = "$" . number_format($this->get_string_from_xml($median_single_family->values->nation->value));
		
		$zindex = $this->get_money_from_xml($region_chart->zindex);
		$affordability_link = $this->get_string_from_xml($affordability_link);
		
		return <<<HTML
			<h3>\${$zindex}</h3>

			<h4>Market Value Change</h4>
			<div id="lme_zillow_region_chart_container">
				<div id="lme_zillow_region_chart_actions">
					<div>
						Show:
							<a href="javascript:void(0);" onclick="LocalMarketExplorer.ZillowIndex.setPercent('false')" id="lme_zillow_dollar">$ Dollar</a> |
							<a href="javascript:void(0);" onclick="LocalMarketExplorer.ZillowIndex.setPercent('true')" id="lme_zillow_percentage">% Percentage</a>
					</div>
					<div>
						Time frame:
							<a href="javascript:void(0);" onclick="LocalMarketExplorer.ZillowIndex.setDuration('1year')" id="lme_zillow_market_1_yr">1 YR</a> |
							<a href="javascript:void(0);" onclick="LocalMarketExplorer.ZillowIndex.setDuration('5years')" id="lme_zillow_market_5_yr">5 YR</a> |
							<a href="javascript:void(0);" onclick="LocalMarketExplorer.ZillowIndex.setDuration('10years')" id="lme_zillow_market_10_yr">10 YR</a>
					</div>
				</div>
				<img src="$region_chart->url" id="lme_zillow_region_chart" />
			</div>
			
			<div id="lme_zillow_home_value">
				<div class="lme_float_50">
					<h4>Avg. Home Value</h4>
					<img src="$avg_home_value->url" />
				</div>
				
				<div class="lme_float_50">
					<h4>Avg. Condo Value.</h4>
					<img src="$avg_condo_value->url" />
				</div>
			</div>
			<div class="clear"></div>
			
			<h4>{$this->location_for_display} Affordability Data</h4>
			<table id="lme_zillow_affordability_data">
				<tr class="lme_primary">
					<td>{$row1_name}</td>
					<td class="lme_number lme_primary_value">{$formatted_city_home_value}</td>
					<td class="lme_number">{$formatted_national_home_value}</td>
				</tr>
				<tr class="lme_secondary">
					<td>{$row2_name}</td>
					<td class="lme_number lme_primary_value">{$formatted_city_year_change}</td>
					<td class="lme_number">{$formatted_national_year_change}</td>
				</tr>
				<tr class="lme_primary">
					<td>{$row3_name}</td>
					<td class="lme_number lme_primary_value">{$formatted_city_condo_value}</td>
					<td class="lme_number">{$formatted_national_condo_value}</td>
				</tr>
				<tr class="lme_secondary">
					<td>{$row4_name}</td>
					<td class="lme_number lme_primary_value">{$formatted_city_sfr_value}</td>
					<td class="lme_number">{$formatted_national_sfr_value}</td>
				</tr>
			</table>
			
			<div id="lme_zillow_see_more_link" class="lme_float_50">
				<a href="{$affordability_link}?scid=gen-api-wplugin" target="_blank">See {$this->city} home values at Zillow.com</a>
			</div>
			<div id="lme_zillow_logo" class="lme_float_50">
				<a href="http://www.zillow.com/?scid=gen-api-wplugin"><img src="http://www.zillow.com/static/logos/Zillowlogo_150x40.gif" /></a>
			</div>
			<div class="clear"></div>
HTML;
	}
	
	function get_about_area_data(){
		$flickr_api_key = get_option('lme_apikey_flickr');
		$flickr_api_request_url = 'http://api.flickr.com/services/rest/?';
		$flickr_photo_url_base = 'http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}_s.jpg';
		
		$flickr_places_params = array(
			'method'	=> 'flickr.places.find',
			'api_key'	=> $flickr_api_key,
			'query'		=> $this->location_for_display,
			'format'	=> 'php_serial'
		);
		$encoded_flickr_places_params = array();		
		foreach ($flickr_places_params as $key => $value){
			$encoded_flickr_places_params[] = urlencode($key).'='.urlencode($value);
		}
		$flickr_places_request_url = $flickr_api_request_url . implode('&', $encoded_flickr_places_params);
		$flickr_response = unserialize(file_get_contents($flickr_places_request_url));
		
		$flickr_min_taken_date = strtotime(date("Y-m-d") . " -6 month");
		$flickr_search_params = array(
			'method'	=> 'flickr.photos.search',
			'api_key'	=> $flickr_api_key,
			'per_page'	=> '6',
			'radius'	=> '5',
			'radius_units'	=> 'mi',
			'accuracy'	=> '11',
			'place_id'	=> $flickr_response['places']['place'][0]['place_id'],
			//'tag'		=> $this->city,
			'sort'		=> 'interestingness-desc',
			'min_taken_date'=> date("Y-m-d H:i:s", $flickr_min_taken_date),
			'format'	=> 'php_serial'
		);
		$encoded_flickr_search_params = array();		
		foreach ($flickr_search_params as $key => $value){
			$encoded_flickr_search_params[] = urlencode($key).'='.urlencode($value);
		}
		$flickr_search_request_url = $flickr_api_request_url . implode('&', $encoded_flickr_search_params);
		$flickr_response = unserialize(file_get_contents($flickr_search_request_url));
		$flickr_image_html = '';
		
		if($flickr_response && $flickr_response['photos'] && $flickr_response['photos']['photo']){
			foreach ($flickr_response['photos']['photo'] as $key => $value){
				$img_src = str_replace(array('{farm-id}', '{server-id}', '{id}', '{secret}'), array($value['farm'], $value['server'], $value['id'], $value['secret']), $flickr_photo_url_base);
				$flickr_image_html .= '<img src="' . $img_src . '" class="lme_flickr_image" />';
			}
		}
		
		$tag_to_search = str_replace(" ", "-", strtolower($this->city));
		$related_posts_html = '';
		
		if($tag_to_search){
			$related_posts = get_posts('tag='.$tag_to_search.'&showposts=4');
				
			foreach ($related_posts as $post) {
				$related_posts_html .= '<li><a href="'. get_permalink($post->id) .'">'.$post->post_title.'</a></li>';
			}
		}
		
		if ($related_posts_html) {
			$related_posts_html = <<<HTML
				<div id="lme_about_area_recent_posts">
					<strong>Recent posts about {$this->location_for_display}</strong>
					<ul id="lme_about_area_recent_posts_list">
						$related_posts_html
					</ul>
				</div>
HTML;
		}
		
		$description = $this->get_description();
		return <<<HTML
			<div id="lme_about_area_flickr">
				<h5>{$this->location_for_display} Photos</h5>
				<div id="lme_about_area_flickr_photos">
					$flickr_image_html
				</div>
				<div id="lme_about_area_flickr_provided_by">... provided by flickr&reg;</div>
			</div>
			<div id="lme_about_area_description">$description</div>
			$related_posts_html
			<div class="clear"></div>
HTML;

	}
	function get_description(){
		$lme_area_cities = unserialize(get_option('lme_area_cities'));
		$lme_area_zips = unserialize(get_option('lme_area_zips'));
		$lme_area_states = unserialize(get_option('lme_area_states'));
		$lme_area_neighborhoods = unserialize(get_option('lme_area_neighborhoods'));
		$lme_area_descriptions = unserialize(get_option('lme_area_descriptions'));
		
		for($i=0;$i<sizeOf($lme_area_cities);$i++){
			if(
				(
					trim(strtolower($lme_area_cities[$i])) == trim(strtolower($this->city))/* &&
					trim(strtolower($lme_area_states[$i])) == trim(strtolower($this->state)) &&
					trim(strtolower($lme_area_neighborhoods[$i])) == trim(strtolower($this->neighborhood))*/
				)/* || (
					trim(strtolower($lme_area_zips[$i])) == trim(strtolower($this->zip)) &&
					trim(strtolower($lme_area_neighborhoods[$i])) == trim(strtolower($this->neighborhood))
				)*/
			){
				return $lme_area_descriptions[$i];
			}
		}
	}

	function get_zillow_market_activity_data() {
		$lme_apikey_zillow = get_option('lme_apikey_zillow');
		$lme_username_zillow = get_option('lme_username_zillow');
		$zillow_fmr = simplexml_load_file("http://www.zillow.com/webservice/FMRWidget.htm?region=$this->city+$this->state&status=recentlySold&zws-id=$lme_apikey_zillow");

		$recent_sales = $zillow_fmr->xpath("response/results/result");
		$recently_sold_html = $this->get_recent_sold_html($recent_sales);

		return <<<HTML
			<div id="lme_market_activity">
				<div id="lme_market_snapshot">
					<table>
						<tr>
							<td>Median Listing Price</td>
							<td></td>
						</tr>
						<tr>
							<td>Total Price</td>
							<td></td>
						</tr>
						<tr>
							<td>For Sale By Owner</td>
							<td></td>
						</tr>
						<tr>
							<td>Foreclosures</td>
							<td></td>
						</tr>
					</table>
				</div>
				<div id="lme_recently_sold">
					<h4>Recently Sold $this->city Homes</h4>
					{$recently_sold_html}
				</div>
				<div id="lme_recently_sold_link">
					<a href="{$this->zillow_for_sale_link}?scid=gen-api-wplugin" target="_blank">See $this->city real estate and homes for sale</a>
				</div>
				<div class="clear"></div>
			</div>
HTML;
	}
	
	function get_recent_sold_html($xml){
		$html = '';
		
		for($i=0;$i< (sizeOf($xml) > 4 ? 4 : sizeOf($xml)) ;$i++){
			$listingImage = str_replace('size=134,98', 'size=82,60', $xml[$i]->largeImageLink);
			$formatted_last_sold_price = $this->get_money_from_xml($xml[$i]->lastSoldPrice);
			$html .= "<div class='lme_recently_sold_item'>".					 	
					 	//"<div></div>".
					 	"<div><a href='{$xml[$i]->detailPageLink}' target='_blank'><img src='{$listingImage}' class='lme_recently_sold_item_photo' /></a>".
					 	"<a href='{$xml[$i]->detailPageLink}' target='_blank'>{$xml[$i]->address->street}</a><br />".
					 	"Recently Sold ({$xml[$i]->lastSoldDate}): \${$formatted_last_sold_price}<br />".
					 	"{$xml[$i]->bathrooms} beds {$xml[$i]->bedrooms} baths {$xml[$i]->finishedSqFt} sqft</div>".
					 "</div>";
			$html .= "<div class='clear'></div>";
		}
		
		
		return $html;
	}

	function get_educationdotcom_data() {
		$lme_apikey_educationcom = get_option('lme_apikey_educationcom');
		$educationdotcom_url = 'http://www.education.com/service/service.php?f=schoolSearch&sn=sf&resf=php&key='. $lme_apikey_educationcom;
		
		$educationdotcom_url .= '&city='. urlencode($this->city) .'&state='. urlencode($this->state);
		/*if ($this->neighborhood != '' && $this->center_lat != '' && $this->center_long != '') {
			$educationdotcom_url .= '&latitude='. $this->center_lat .'&longitude='. $this->center_long;
		} else if ($this->city != '' && $this->state != '') {
			$educationdotcom_url .= '&city='. $this->city .'&state='. $this->state;
		} else if ($this->zip != '') {
			$educationdotcom_url .= '&city='. $this->city .'&state='. $this->state;
		}*/
		// otherwise, we shouldn't be here
		
		$educationdotcom_data_raw = file_get_contents($educationdotcom_url);
		$educationdotcom_data = unserialize($educationdotcom_data_raw);

		$elementary_school_html = '';
		$middle_school_html = '';
		$high_school_html = '';
		
		for ($i = 0; $i < sizeof($educationdotcom_data); $i++) {
			$school = $educationdotcom_data[$i]['school'];
			$schoolType = strtolower($school['schooltype']);
			$hyphenatedSchoolDistrict = strtolower(str_replace(' ', '-', $school['schooldistrictname']));
			
			$list_item_html = <<<HTML
				<li schooltype="{$schoolType}">
					<a class="lme_school_name" href="{$school['url']}">{$school['schoolname']}</a>
					<div>{$school['address']}, {$school['phonenumber']}</div>
					<div>{$school['gradesserved']} |
					<a href="http://www.education.com/schoolfinder/us/{$this->state}/district/{$hyphenatedSchoolDistrict}/" target="_blank">{$school['schooldistrictname']}</a></div>
				</li>
HTML;
			
			if (strpos($school['gradelevel'], 'Elementary') !== false) {
				$elementary_school_html .= $list_item_html;
			}
			if (strpos($school['gradelevel'], 'Middle') !== false) {
				$middle_school_html .= $list_item_html;
			}
			if (strpos($school['gradelevel'], 'High') !== false) {
				$high_school_html .= $list_item_html;
			}
		}

		return <<<HTML
			<div id="lme_schools_panel_left_container">
				<div class="lme_schools_panel_left" id="lme_schools_panel_elementary">
					<h5 class="lme_schools_list_subheader"><a href="http://www.education.com/schoolfinder/us/{$this->state}/{$this->city}/elementary/" target="_blank">{$this->location_for_display} Elementary Schools</a></h5>
					<div class="lme_schools_list_container">
						<ul id="lme_schools_elementary_list" class="lme_schools_list">$elementary_school_html</ul>
					</div>
				</div>
				<div class="lme_schools_panel_left lme_hide" id="lme_schools_panel_middle">
					<h5 class="lme_schools_list_subheader"><a href="http://www.education.com/schoolfinder/us/{$this->state}/{$this->city}/middle/" target="_blank">{$this->location_for_display} Middle Schools</a></h5>
					<div class="lme_schools_list_container">
						<ul id="lme_schools_middle_list" class="lme_schools_list">$middle_school_html</ul>
					</div>
				</div>
				<div class="lme_schools_panel_left lme_hide" id="lme_schools_panel_high">
					<h5 class="lme_schools_list_subheader"><a href="http://www.education.com/schoolfinder/us/{$this->state}/{$this->city}/high/" target="_blank">{$this->location_for_display} High Schools</a></h5>
					<div class="lme_schools_list_container">
						<ul id="lme_schools_high_list" class="lme_schools_list">$high_school_html</ul>
					</div>
				</div>
				<div id="lme_schools_pager">
					<a id="lme_schools_pager_previous" href="javascript:void(0)" onclick="LocalMarketExplorer.Schools.page('-=')">&#171; Previous</a>
					<a id="lme_schools_pager_next" href="javascript:void(0)" onclick="LocalMarketExplorer.Schools.page('+=')">Next &#187;</a>
				</div>
			</div>

			<div class="lme_schools_panel_right">
				<h5 id="lme_schools_choose_grade_level" style="margin-top: 0">Choose grade level:</h5>
				<div id="lme_schools_grade_choices">
					<div>
						<input type="radio" name="lme_schools_grade_choices" id="lme_schools_grade_choice_elementary" value="elementary" checked="checked" />
						<label for="lme_schools_grade_choice_elementary">Elem. Schools</label>
					</div>
					<div>
						<input type="radio" name="lme_schools_grade_choices" id="lme_schools_grade_choice_middle" value="middle" />
						<label for="lme_schools_grade_choice_middle">Middle Schools</label>
					</div>
					<div>
						<input type="radio" name="lme_schools_grade_choices" id="lme_schools_grade_choice_high" value="high" />
						<label for="lme_schools_grade_choice_high">High Schools</label>
					</div>
				</div>

				<h5 id="lme_schools_choose_type">Choose school type:</h5>
				<div id="lme_schools_type_choices">
					<div>
						<input type="radio" name="lme_schools_type_choices" id="lme_schools_type_choice_all" value="all" checked="checked" />
						<label for="lme_schools_type_choice_all">All School Types</label>
					</div>
					<div>
						<input type="radio" name="lme_schools_type_choices" id="lme_schools_type_choice_public" value="public" />
						<label for="lme_schools_type_choice_public">Public Schools</label>
					</div>
					<div>
						<input type="radio" name="lme_schools_type_choices" id="lme_schools_type_choice_private" value="private" />
						<label for="lme_schools_type_choice_private">Private Schools</label>
					</div>
					<div>
						<input type="radio" name="lme_schools_type_choices" id="lme_schools_type_choice_charter" value="charter" />
						<label for="lme_schools_type_choice_charter">Charter Schools</label>
					</div>
				</div>
				
				<h5 id="lme_schools_search_zip">Search by zip</h5>
				<div id="lme_schools_search_zip_container">
					<div>
						<form method="get" action="http://www.education.com/schoolfinder/searchresult/" target="_blank">
							<input type="hidden" name="searchType" value="simple" />
							<input type="text" name="searchTerms" maxlength="5" size="5" />
							<input type="submit" value="Go" />
						</form>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			
			<div id="lme_educationdotcom_footer">
				<div id="lme_educationdotcom_see_more_link">
					<a href="http://www.education.com/schoolfinder/us/{$this->state}/{$this->city}/" target="_blank">See more info on {$this->location_for_display} schools</a>
				</div>
				<div id="lme_educationdotcom_logo">
					<a href="http://www.education.com"><img src="http://www.education.com/i/logo/edu-logo-150x32.jpg" /></a>
				</div>
			</div>
			<div class="clear"></div>
HTML;
	}
	
	function get_walk_score_data() {
		$walkscore_api_key = get_option('lme_apikey_walkscore');
		
		return <<<HTML
			<div id="lme_walk_score_container">
				<script type="text/javascript">
					var ws_address = '{$this->city},{$this->state}';
					var ws_width = '400';
				</script>
				<script type="text/javascript" src="http://www.walkscore.com/tile/show-tile.php?wsid={$walkscore_api_key}"></script>
			</div>
HTML;
	}
}
?>
