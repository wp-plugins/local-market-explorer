<?php
class LMEPage
{
	var $slug = 'local';
	var $city = '';
	var $state = '';
	var $neighborhood = '';
	var $zip = '';

	// these will be set from the initial zillow request we pull
	var $center_lat = '';
	var $center_long = '';
	
	var $location_for_display = '';
	var $is_lme = false;
	
	// zillow's GetDemographics call returns a link that we later need in the market activity module
	var $zillow_for_sale_link = '';
	
	function LMEPage(){
		add_action('the_posts', array(&$this, 'check_url'));
		add_filter('the_posts', array(&$this, 'get_post'));
		add_filter('wp_head', array(&$this, 'get_head'));
		add_filter('posts_request', array(&$this, 'query_override_for_lme'));
		add_action('wp_footer', array(&$this, 'get_footer'));
	}
	
	// this will speed up requests by making the query to MySQL SUPER simple
	function query_override_for_lme($query){
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
			$lme_post->post_title = $this->location_for_display . ' Real Estate Information';
			$lme_post->post_category = 0;
			$lme_post->post_excerpt = '';
			$lme_post->post_status = 'publish';
			$lme_post->comment_status = 'closed';
			$lme_post->ping_status = 'closed';
			$lme_post->post_password = '';
			$lme_post->post_name = $lme_post->post_title;
			$lme_post->to_ping = '';
			$lme_post->pinged = '';
			$lme_post->post_content_filtered = '';
			$lme_post->post_parent = 0;
			$lme_post->guid = get_bloginfo('wpurl') . '/' . $this->slug . '/' . $this->zip . '/' . $this->neighborhood . '/' . $this->city . '/' . $this->state;
			$lme_post->menu_order = 0;
			$lme_post->post_type = 'page';
			$lme_post->post_mime_type = '';
			$lme_post->comment_count = 0;
		
			return array($lme_post);
		} else {
			return $posts;
		}
	}
	
	function get_head() {
		//filter wp_head
		$wpurl = get_bloginfo('wpurl');
		
		if ($this->is_lme){
			echo <<<HEAD
				<link rel="stylesheet" type="text/css" href="{$wpurl}/wp-content/plugins/local-market-explorer/includes/lme-client.css" />
				<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
				<script type="text/javascript">
					var \$j = jQuery.noConflict();
				</script>
				<script type="text/javascript" src="{$wpurl}/wp-content/plugins/local-market-explorer/includes/lme-client.js"></script>
				<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
HEAD;
		}
	}
	function get_footer($content) {
		//filter wp_footer
		$current_year = date('Y');
		
		$lme_username_zillow = get_option('lme_username_zillow');
		if (strlen($lme_username_zillow) > 0)
			$zillow_scrnm = '#{scrnnm=' . $lme_username_zillow . '}';
		else
			$zillow_scrnm = '';
		
		if ($this->is_lme) {
			echo <<<FOOTER
				<div id="lme_footer">
					<p>
						&copy; Zillow, Inc., {$current_year}. Use is subject to <a href="http://www.zillow.com/corp/Terms.htm$zillow_scrnm" target="_blank">Terms of Use</a>.
						<a href="http://www.zillow.com/howto/WhatsaZindex.htm$zillow_scrnm" target="_blank">What's a Zindex</a>?
					</p>
					<p>This product uses the Flickr API but is not endorsed or certified by Flickr.</p>
				</div>
FOOTER;
		}
	}
	
	function check_url($posts){
		global $wp;
		global $wp_query;

		$cityStateRegex = "/". $this->slug ."\/((?P<neighborhood>[^\/]+)\/)?(?P<city>[^\/]+)\/(?P<state>\w{2})/";
		$zipRegex = "/". $this->slug ."\/(?P<zip>\d{5})/";
		$cityStateRegexSuccess = preg_match($cityStateRegex, $wp->request, $cityStateUrlMatch);
		$zipRegexSuccess = preg_match($zipRegex, $wp->request, $zipUrlMatch);
		
		if ($cityStateRegexSuccess > 0) {
			$this->city = trim(ucwords(str_replace('-', ' ', $cityStateUrlMatch['city'])));
			$this->state = trim(strtoupper(str_replace('-', ' ', $cityStateUrlMatch['state'])));
			
			if ($cityStateUrlMatch['neighborhood'] != '') {
				$this->neighborhood = trim(ucwords(str_replace('-', ' ', $cityStateUrlMatch['neighborhood'])));
				$this->location_for_display = $this->neighborhood . ', ' . $this->city . ', ' . $this->state;
			} else {
				$this->location_for_display = $this->city . ', ' . $this->state;
			}
		} else if ($zipRegexSuccess > 0) {
			$this->zip = $zipUrlMatch['zip'];
			$this->location_for_display = $this->zip;
		} else {
			$this->is_lme = false;
			return $posts;
		}
		
		$this->is_lme = true;
		
		$wp_query->is_page = true;
		$wp_query->is_singular = true;
		$wp_query->is_home = false;
		$wp_query->is_404 = false;
		
		return $posts;
	}
	
	function get_url_data($url, $nocache = false) {
		if ($url && !$nocache) {
			$cacheKey = "lme-" & sha1($url);
			$cacheValue = get_transient($cacheKey);
			
			if ($cacheValue)
				return $cacheValue;
		}
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$raw = curl_exec($ch);
		
		if ($raw && $url && !$nocache)
			set_transient("lme-" & sha1($url), $raw, 60*60*24);
		return $raw;
	}
	function get_url_data_as_xml($url, $nocache = false) {
		$xml = simplexml_load_string($this->get_url_data($url, $nocache));
		return $xml;
	}
	function get_string_from_xml($xml){
		return trim((string)$xml);
	}
	function get_money_from_xml($xml){
		return number_format(doubleval(trim((string)$xml)));
	}
	
	// content functions
	function get_content() {
		$lme_panels_show_market_stats = get_option('lme_panels_show_market_stats');
		$lme_panels_show_aboutarea = get_option('lme_panels_show_aboutarea');
		$lme_panels_show_marketactivity = get_option('lme_panels_show_marketactivity');
		$lme_panels_show_educationcom = get_option('lme_panels_show_educationcom');
		$lme_panels_show_walkscore = get_option('lme_panels_show_walkscore');
		$lme_panels_show_yelp = get_option('lme_panels_show_yelp');
		$lme_panels_show_teachstreet = get_option('lme_panels_show_teachstreet');
		
		if ($lme_panels_show_teachstreet && !function_exists('json_decode'))
			$lme_panels_show_teachstreet = FALSE;
		
		$lme_apikey_flickr = get_option('lme_apikey_flickr');
		$lme_apikey_walkscore = get_option('lme_apikey_walkscore');
		
		$lme_navigation = array();
		$lme_content = array();
		
		$moduleOrder = get_option('lme_module_order');
		asort($moduleOrder);

		$lme_content_html = <<<LME_CONTENT
			<div class="local_market_explorer">
				<!-- HEADER (LOCATION) WITH PAGE ANCHOR LINKS FOR SECTIONS -->
				<div class="lme_header">
					<div class="lme_left"></div>
					<div class="lme_middle" id="lme_navigation">
LME_CONTENT;

		if ($lme_panels_show_market_stats)
			$lme_navigation['market-statistics'] = '<a href="#lme-zillow-home-value-index">Market Statistics</a> | ';
		if ($lme_panels_show_aboutarea)
			$lme_navigation['about-area'] = '<a href="#lme-about-area">About Area</a> | ';
		if ($lme_panels_show_marketactivity)
			$lme_navigation['market-activity'] = '<a href="#lme-market-activity">Market Activity</a> | ';
		if ($lme_panels_show_educationcom)
			$lme_navigation['schools'] = '<a href="#lme-schools">Schools</a> | ';
		if ($lme_panels_show_walkscore)
			$lme_navigation['walk-score'] = '<a href="#lme-walk-score">Walk Score</a> | ';
		if ($lme_panels_show_yelp)
			$lme_navigation['yelp'] = '<a href="#lme-yelp">Yelp Local Reviews</a> | ';
		if ($lme_panels_show_teachstreet)
			$lme_navigation['teachstreet'] = '<a href="#lme-teachstreet">Local Classes</a> | ';
		
		
		foreach ($moduleOrder as $key => $value) {
			if (isset($lme_navigation[$key]))
				$lme_content_html .= $lme_navigation[$key];
		}
		
		$home_value_data = $this->get_zillow_home_value_data();
		$lme_content_html .= <<<LME_CONTENT
					</div>
					<div class="lme_right"></div>
				</div>
				
				<script>
					LocalMarketExplorer.latitude = '{$this->center_lat}';
					LocalMarketExplorer.longitude = '{$this->center_long}';
				</script>

LME_CONTENT;

		if ($lme_panels_show_market_stats) {
			$lme_content['market-statistics'] = <<<LME_CONTENT
				<!-- "Market Statistics" SECTION -->
				<a name="lme-zillow-home-value-index"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>Market Statistics</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_zillow_index" class="lme_container_body">{$home_value_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		}

		$about_area_data = $this->get_about_area_data();
		if ($lme_panels_show_aboutarea) {
			$lme_content['about-area'] = <<<LME_CONTENT
				<!-- "ABOUT {LOCATION}" (CONFIGS + FLICKR) SECTION -->
				<a name="lme-about-area"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>About</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_about_area" class="lme_container_body">{$about_area_data[html]}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		}
		if (!empty($about_area_data['idx-link'])) {
			$lme_content['idx-link'] = <<<LME_CONTENT
				<!-- "IDX LINK" SECTION -->
				<a name="lme-idx-link"></a>
				<div id="lme-idx-link-container">
					{$about_area_data["idx-link"]}
				</div>
LME_CONTENT;
		}
		
		$market_activity_data = $this->get_zillow_market_activity_data();
		if ($lme_panels_show_marketactivity) {
			$lme_content['market-activity'] = <<<LME_CONTENT
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
		
		if ($lme_panels_show_educationcom) {
			$educationdotcom_data = $this->get_educationdotcom_data();
			$lme_content['schools'] = <<<LME_CONTENT
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
		}
		
		if ($lme_panels_show_walkscore) {
			$walk_score_data = $this->get_walk_score_data();
			$lme_content['walk-score'] = <<<LME_CONTENT
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
		
		if ($lme_panels_show_teachstreet) {
			$teachstreet_data = $this->get_teachstreet_data();
			$lme_content['teachstreet'] = <<<LME_CONTENT
				<!-- TEACHSTREET SECTION -->
				<a name="lme-teachstreet"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>New Classes in {$this->location_for_display} (via TeachStreet)</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_teachstreet" class="lme_container_body">{$teachstreet_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		}
		
		if ($lme_panels_show_yelp) {
			$yelp_data = $this->get_yelp_reviews_data();
			$lme_content['yelp'] = <<<LME_CONTENT
				<!-- YELP SECTION -->
				<a name="lme-yelp"></a>
				<div class="lme_container">
					<div class="lme_container_top lme_container_cap">
						<div class="lme_container_top_left lme_container_left"></div>
						<h3>Yelp Local Reviews</h3>
						<div class="lme_container_top_right lme_container_right"></div>
					</div>
					<div id="lme_yelp" class="lme_container_body">{$yelp_data}</div>
					<div class="lme_container_bottom lme_container_cap">
						<div class="lme_container_bottom_left lme_container_left"></div>
						<div class="lme_container_bottom_right lme_container_right"></div>
					</div>
				</div>
LME_CONTENT;
		}
		
		foreach ($moduleOrder as $key => $value) {
			if (isset($lme_content[$key]))
				$lme_content_html .= $lme_content[$key];
		}
		
		$lme_content_html .= '</div>';
		
		return $lme_content_html;
	}

	function get_zillow_home_value_data() {
		$lme_apikey_zillow = get_option('lme_apikey_zillow');
		$lme_username_zillow = get_option('lme_username_zillow');
		$lme_panels_show_market_stats = get_option('lme_panels_show_market_stats');
		
		$location_for_api = '';
		
		if ($this->state != '') {
			$location_for_api .= '&city=' . urlencode($this->city) . '&state=' .$this->state;
			if ($this->neighborhood != '') {
				$location_for_api .= '&neighborhood=' . urlencode($this->neighborhood);
			}
		} else if ($this->zip != '') {
			$location_for_api .= '&zip=' . urlencode($this->zip);
		}
		
		$zillow_xml_url = "http://www.zillow.com/webservice/GetDemographics.htm?zws-id=$lme_apikey_zillow$location_for_api";
		$zillow_chart_url = "http://www.zillow.com/webservice/GetRegionChart.htm?zws-id=$lme_apikey_zillow$location_for_api&unit-type=percent&width=400&height=200";
		
		$zillow_xml = $this->get_url_data_as_xml($zillow_xml_url, true);
		$node = $zillow_xml->xpath("response/region/latitude");
		$this->center_lat = $node[0];
		$node = $zillow_xml->xpath("response/region/longitude");
		$this->center_long = $node[0];
		
		$node = $zillow_xml->xpath("response/links/forSale"); $this->zillow_for_sale_link = (string)$node[0];
		
		if (!$lme_panels_show_market_stats)
			return '';
		
		$zillow_chart = $this->get_url_data_as_xml($zillow_chart_url, true);
		
		$node = $zillow_chart->xpath("response"); $region_chart = array($node[0]);
		$node = $zillow_xml->xpath("response/charts/chart[name='Average Home Value']"); $avg_home_value = array($node[0]);
		$node = $zillow_xml->xpath("response/charts/chart[name='Average Condo Value']"); $avg_condo_value = array($node[0]);
		$node = $zillow_xml->xpath("response/links/affordability"); $affordability_link = array($node[0]);

		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='Zillow Home Value Index']/values"); $zillow_home_value = array($node[0]);
		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='1-Yr. Change']/values"); $one_yr_change = array($node[0]);
		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='Median Condo Value']/values"); $median_condo_value = array($node[0]);
		$node = $zillow_xml->xpath("response/pages/page[name='Affordability']/tables/table[name='Affordability Data']/data/attribute[name='Median Single Family Home Value']/values"); $median_single_family = array($node[0]);
		
		$node = $zillow_xml->xpath("response/market/attribute[name='Median List Price']/values"); $market_median_list_price = array($node[0]);
		$node = $zillow_xml->xpath("response/market/attribute[name='Median Sale Price']/values"); $market_median_sale_price = array($node[0]);
		$node = $zillow_xml->xpath("response/market/attribute[name='Median List Price Per Sq Ft']/values"); $market_median_list_ppsf = array($node[0]);
		$node = $zillow_xml->xpath("response/market/attribute[name='Homes For Sale']/values"); $market_homes_for_sale = array($node[0]);
		
		if ($this->neighborhood != '') {
			$local_node_name = 'neighborhood';
		} elseif ($this->zip != '') {
			$local_node_name = 'zip';
		} else {
			$local_node_name = 'city';
		}
		
		$local_home_value = $zillow_home_value[0]->$local_node_name->value;
		$local_year_change_percent = (string)$one_yr_change[0]->$local_node_name->value;

		$formatted_local_home_value = "$" . number_format($local_home_value);
		$formatted_local_year_change = "$" . number_format($local_home_value - ($local_home_value * (1 - $local_year_change_percent)));
		$formatted_local_condo_value = "$" . number_format($median_condo_value[0]->$local_node_name->value);
		$formatted_local_sfr_value = "$" . number_format($median_single_family[0]->$local_node_name->value);

		$national_home_value = $zillow_home_value[0]->nation->value;
		$national_year_change_percent = (string)$one_yr_change[0]->nation->value[0];
		
		$formatted_national_home_value = "$" . number_format($national_home_value);
		$formatted_national_year_change = "$" . number_format($national_home_value - ($national_home_value * (1 - $national_year_change_percent)));
		$formatted_national_condo_value = "$" . number_format($median_condo_value[0]->nation->value);
		$formatted_national_sfr_value = "$" . number_format($median_single_family[0]->nation->value);
		
		$market_median_list_price_local = "$" . number_format($market_median_list_price[0]->$local_node_name->value);
		$market_median_sale_price_local = "$" . number_format($market_median_sale_price[0]->$local_node_name->value);
		$market_median_list_ppsf_local = "$" . number_format($market_median_list_ppsf[0]->$local_node_name->value);
		$market_homes_for_sale_local = number_format($market_homes_for_sale[0]->$local_node_name->value);
		
		$market_median_list_price_nation = "$" . number_format($market_median_list_price[0]->nation->value);
		$market_median_sale_price_nation = "$" . number_format($market_median_sale_price[0]->nation->value);
		$market_median_list_ppsf_nation = "$" . number_format($market_median_list_ppsf[0]->nation->value);
		$market_homes_for_sale_nation = number_format($market_homes_for_sale[0]->nation->value);
		
		$zindex = number_format(trim($region_chart[0]->zindex));
		$affordability_link = (string)$affordability_link[0];
		
		$lme_username_zillow = get_option('lme_username_zillow');
		if (strlen($lme_username_zillow) > 0)
			$zillow_scrnm = '&scrnnm=' . $lme_username_zillow;
		else
			$zillow_scrnm = '';
		
		$html = <<<HTML
			<div id="lme_zillow_header">
				<h4>Zillow Home Value Index:</h4>
				<h3><a href="{$affordability_link}#{scid=gen-api-wplugin$zillow_scrnm}" target="_blank">\${$zindex}</a></h3>
			</div>

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
				<img src="{$region_chart[0]->url}" id="lme_zillow_region_chart" alt="{$this->location_for_display} real estate market value change over time" />
			</div>
			
			<div id="lme_zillow_home_value">
				<div class="lme_float_50">
					<h4>Avg. Home Value</h4>
					<img src="{$avg_home_value[0]->url}" alt="{$this->location_for_display} home prices and values" />
				</div>
				
				<div class="lme_float_50">
					<h4>Avg. Condo Value</h4>
					<img src="{$avg_condo_value[0]->url}" alt="{$this->location_for_display} condo prices and values" />
				</div>
			</div>
			<div class="clear"></div>
			
			<h4>{$this->location_for_display} Affordability Data</h4>
			<table id="lme_zillow_affordability_data">
				<tr>
					<td>&nbsp;</td>
					<th>Local</th>
					<th>National</th>
				</tr>
HTML;
		if (!$this->zip)
			$html .= <<<HTML
				<tr class="lme_primary">
					<td>Zillow Home Value Index</td>
					<td class="lme_number lme_primary_value">{$formatted_local_home_value}</td>
					<td class="lme_number">{$formatted_national_home_value}</td>
				</tr>
				<tr class="lme_secondary">
					<td>1-Yr. Change</td>
					<td class="lme_number lme_primary_value">{$formatted_local_year_change}</td>
					<td class="lme_number">{$formatted_national_year_change}</td>
				</tr>
				<tr class="lme_primary">
					<td>Median Condo Value</td>
					<td class="lme_number lme_primary_value">{$formatted_local_condo_value}</td>
					<td class="lme_number">{$formatted_national_condo_value}</td>
				</tr>
				<tr class="lme_secondary">
					<td>Median Single Family Home Value</td>
					<td class="lme_number lme_primary_value">{$formatted_local_sfr_value}</td>
					<td class="lme_number">{$formatted_national_sfr_value}</td>
				</tr>
HTML;
		$html .= <<<HTML
				<tr class="lme_primary">
					<td>Median List Price</td>
					<td class="lme_number lme_primary_value">{$market_median_list_price_local}</td>
					<td class="lme_number">{$market_median_list_price_nation}</td>
				</tr>
				<tr class="lme_secondary">
					<td>Median Sale Price</td>
					<td class="lme_number lme_primary_value">{$market_median_sale_price_local}</td>
					<td class="lme_number">{$market_median_sale_price_nation}</td>
				</tr>
				<tr class="lme_primary">
					<td>Median List Price Per Sq Ft</td>
					<td class="lme_number lme_primary_value">{$market_median_list_ppsf_local}</td>
					<td class="lme_number">{$market_median_list_ppsf_nation}</td>
				</tr>
				<tr class="lme_secondary">
					<td>Homes For Sale</td>
					<td class="lme_number lme_primary_value">{$market_homes_for_sale_local}</td>
					<td class="lme_number">{$market_homes_for_sale_nation}</td>
				</tr>
			</table>
			
			<div id="lme_zillow_see_more_link" class="lme_float_50">
				<a href="{$affordability_link}#{scid=gen-api-wplugin$zillow_scrnm}" target="_blank">See {$this->location_for_display} home values at Zillow.com</a>
			</div>
			<div id="lme_zillow_logo" class="lme_float_50">
				<a href="http://www.zillow.com/#{scid=gen-api-wplugin$zillow_scrnm}" target="_blank"><img src="http://www.zillow.com/static/logos/Zillowlogo_150x40.gif" alt="Zillow - Real Estate" /></a>
			</div>
			<div class="clear"></div>
HTML;
		return $html;
	}
	
	function get_about_area_data(){
		$show_flickr_panel = get_option('lme_panels_show_flickr');
		
		if ($show_flickr_panel) {
			$flickr_api_key = get_option('lme_apikey_flickr');
			$flickr_api_request_url = 'http://api.flickr.com/services/rest/?';
			$flickr_photo_url_base = 'http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}_s.jpg';
			
			/* not needed anymore since we now have latitude / longitude from the prior API call to Zillow
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
			$flickr_response = unserialize($this->get_url_data($flickr_places_request_url));*/
			
			$flickr_min_taken_date = strtotime(date("Y-m-d") . " -6 month");
			$flickr_search_params = array(
				'method'	=> 'flickr.photos.search',
				'api_key'	=> $flickr_api_key,
				'per_page'	=> '6',
				'radius'	=> '5',
				'radius_units'	=> 'mi',
				'accuracy'	=> '11',
				//'place_id'	=> $flickr_response['places']['place'][0]['place_id'],
				'lat'		=> $this->center_lat,
				'lon'		=> $this->center_long,
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
			$flickr_response = unserialize($this->get_url_data($flickr_search_request_url));
			$flickr_image_html = '';
			
			if($flickr_response && $flickr_response['photos'] && $flickr_response['photos']['photo']){
				foreach ($flickr_response['photos']['photo'] as $key => $value){
					$img_src = str_replace(array('{farm-id}', '{server-id}', '{id}', '{secret}'), array($value['farm'], $value['server'], $value['id'], $value['secret']), $flickr_photo_url_base);
					$flickr_image_html .= '<img src="' . $img_src . '" class="lme_flickr_image" />';
				}
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
		
		$area = $this->get_description();
		$panel_html = '';

		if ($area["idx_link"])
			$idxLink = "<h4 class=\"lme_idx_link\"><a href=\"{$area[idx_link]}\">Search for homes in {$this->location_for_display}</a></h4>";
		
		if ($show_flickr_panel) {
			$panel_html .= <<<HTML
			<div id="lme_about_area_flickr">
				<h5>{$this->location_for_display} Photos</h5>
				<div id="lme_about_area_flickr_photos">
					$flickr_image_html
				</div>
				<div id="lme_about_area_flickr_provided_by">... provided by flickr&reg;</div>
			</div>
HTML;
		}
		
		$panel_html .= <<<HTML
			<div id="lme_about_area_description">{$area[description]}</div>
			$related_posts_html
			<div class="clear"></div>
HTML;
		
		return array("html" => $panel_html, "idx-link" => $idxLink);
	}
	function get_description(){
		$lme_areas = get_option('lme_areas');

		for ($i = 0; $i < sizeOf($lme_areas); $i++) {
			if (
				!empty($this->zip)
				&& strtolower($lme_areas[$i]['zip']) == strtolower($this->zip)
				)
				return $lme_areas[$i];
			if (
				!empty($this->neighborhood)
				&& strtolower($lme_areas[$i]['neighborhood']) == strtolower($this->neighborhood)
				&& strtolower($lme_areas[$i]['city']) == strtolower($this->city)
				&& strtolower($lme_areas[$i]['state']) == strtolower($this->state)
				)
				return $lme_areas[$i];
			if (
				empty($this->neighborhood) && !$lme_areas[$i]['neighborhood']
				&& strtolower($lme_areas[$i]['city']) == strtolower($this->city)
				&& strtolower($lme_areas[$i]['state']) == strtolower($this->state)
				)
				return $lme_areas[$i];
		}
		for ($i = 0; $i < sizeOf($lme_areas); $i++) {
			if (
				!$lme_areas[$i]['neighborhood']
				&& strtolower($lme_areas[$i]['city']) == strtolower($this->city)
				&& strtolower($lme_areas[$i]['state']) == strtolower($this->state)
				)
				return $lme_areas[$i];
		}
		return '';
	}

	function get_zillow_market_activity_data() {
		$lme_apikey_zillow = get_option('lme_apikey_zillow');
		
		$recent_sales_url = "http://www.zillow.com/webservice/FMRWidget.htm?status=recentlySold&zws-id=$lme_apikey_zillow&region=";
		if ($this->zip != "") {
			$recent_sales_url .= "$this->zip"; 
		} else {
			if ($this->neighborhood != "") {
				$recent_sales_url .= urlencode("$this->neighborhood,");				
			}
			$recent_sales_url .= urlencode("$this->city,$this->state");
		}
		
		$zillow_fmr = $this->get_url_data_as_xml($recent_sales_url, true);
		
		$lme_username_zillow = get_option('lme_username_zillow');
		if (strlen($lme_username_zillow) > 0)
			$zillow_scrnm = '&scrnnm=' . $lme_username_zillow;
		else
			$zillow_scrnm = '';
		
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
					<a href="{$this->zillow_for_sale_link}#{scid=gen-api-wplugin$zillow_scrnm}" target="_blank">See $this->location_for_display real estate and homes for sale</a>
				</div>
				<div class="clear"></div>
			</div>
HTML;
	}
	
	function get_recent_sold_html($xml){
		$html = '';
		$lme_sold_listings_to_show = get_option('lme_sold_listings_to_show');
		$lme_username_zillow = get_option('lme_username_zillow');
		if (strlen($lme_username_zillow) > 0)
			$zillow_scrnm = '&scrnnm=' . $lme_username_zillow;
		else
			$zillow_scrnm = '';
		
		if ($lme_sold_listings_to_show == '') {
			$lme_sold_listings_to_show = 4;
		}
		
		for($i=0;$i< (sizeOf($xml) > $lme_sold_listings_to_show ? $lme_sold_listings_to_show : sizeOf($xml)) ;$i++){
			$listingImage = str_replace('size=134,98', 'size=82,60', $xml[$i]->largeImageLink);
			$formatted_last_sold_price = $this->get_money_from_xml($xml[$i]->lastSoldPrice);
			$html .= "<div class='lme_recently_sold_item'>".					 	
					 	//"<div></div>".
					 	"<div><a href='{$xml[$i]->detailPageLink}#{scid=gen-api-wplugin$zillow_scrnm}' target='_blank'><img src='{$listingImage}' class='lme_recently_sold_item_photo' /></a>".
					 	"<a href='{$xml[$i]->detailPageLink}#{scid=gen-api-wplugin$zillow_scrnm}' target='_blank'>{$xml[$i]->address->street}</a><br />".
					 	"Recently Sold ({$xml[$i]->lastSoldDate}): \${$formatted_last_sold_price}<br />".
					 	"{$xml[$i]->bedrooms} beds {$xml[$i]->bathrooms} baths {$xml[$i]->finishedSqFt} sqft</div>".
					 "</div>";
			$html .= "<div class='clear'></div>";
		}
		
		return $html;
	}
	
	function get_state_translation() {
		$states = array();
		
		$states["AL"] = "ALABAMA";
		$states["AK"] = "ALASKA";
		$states["AZ"] = "ARIZONA";
		$states["AR"] = "ARKANSAS";
		$states["CA"] = "CALIFORNIA";
		$states["CO"] = "COLORADO";
		$states["CT"] = "CONNECTICUT";
		$states["DE"] = "DELAWARE";
		$states["DC"] = "DISTRICT OF COLUMBIA";
		$states["FL"] = "FLORIDA";
		$states["GA"] = "GEORGIA";
		$states["HI"] = "HAWAII";
		$states["ID"] = "IDAHO";
		$states["IL"] = "ILLINOIS";
		$states["IN"] = "INDIANA";
		$states["IA"] = "IOWA";
		$states["KS"] = "KANSAS";
		$states["KY"] = "KENTUCKY";
		$states["LA"] = "LOUISIANA";
		$states["ME"] = "MAINE";
		$states["MD"] = "MARYLAND";
		$states["MA"] = "MASSACHUSETTS";
		$states["MI"] = "MICHIGAN";
		$states["MN"] = "MINNESOTA";
		$states["MS"] = "MISSISSIPPI";
		$states["MO"] = "MISSOURI";
		$states["MT"] = "MONTANA";
		$states["NE"] = "NEBRASKA";
		$states["NV"] = "NEVADA";
		$states["NH"] = "NEW HAMPSHIRE";
		$states["NJ"] = "NEW JERSEY";
		$states["NM"] = "NEW MEXICO";
		$states["NY"] = "NEW YORK";
		$states["NC"] = "NORTH CAROLINA";
		$states["ND"] = "NORTH DAKOTA";
		$states["OH"] = "OHIO";
		$states["OK"] = "OKLAHOMA";
		$states["OR"] = "OREGON";
		$states["PA"] = "PENNSYLVANIA";
		$states["RI"] = "RHODE ISLAND";
		$states["SC"] = "SOUTH CAROLINA";
		$states["SD"] = "SOUTH DAKOTA";
		$states["TN"] = "TENNESSEE";
		$states["TX"] = "TEXAS";
		$states["UT"] = "UTAH";
		$states["VT"] = "VERMONT";
		$states["VA"] = "VIRGINIA ";
		$states["WA"] = "WASHINGTON";
		$states["WV"] = "WEST VIRGINIA";
		$states["WI"] = "WISCONSIN";
		$states["WY"] = "WYOMING";
		
		return $states;
	}

	function get_educationdotcom_data() {
		//$lme_apikey_educationcom = get_option('lme_apikey_educationcom');
		$lme_apikey_educationcom = 'bd23bb5cb91e37c39282f6bf75d56fb9'; // education.com wants this embedded
		$educationdotcom_url = 'http://www.education.com/service/service.php?f=schoolSearch&sn=sf&resf=php&key='. $lme_apikey_educationcom;
		
		$state_translation = $this->get_state_translation();
		if ($this->zip != "") {
			$educationdotcom_url .= "&zip=$this->zip"; 
		} else {
			if ($this->neighborhood != "") {
				$educationdotcom_url .= "&latitude={$this->center_lat}&longtude={$this->center_long}";				
			} else {
				$educationdotcom_url .= '&city='. urlencode($this->city) .'&state='. urlencode($this->state);
			}
		}
		
		$educationdotcom_data_raw = $this->get_url_data($educationdotcom_url);
		$educationdotcom_data = unserialize($educationdotcom_data_raw);

		$elementary_school_html = '';
		$middle_school_html = '';
		$high_school_html = '';
		$full_state = strtolower(str_replace(' ', '-', $state_translation[$this->state]));
		$city_for_link = strtolower(str_replace(' ', '-', $this->city));
		
		for ($i = 0; $i < sizeof($educationdotcom_data); $i++) {
			$school = $educationdotcom_data[$i]['school'];
			$schoolType = strtolower($school['schooltype']);
			$hyphenatedSchoolDistrict = strtolower(str_replace(' ', '-', $school['schooldistrictname']));
			
			$list_item_html = <<<HTML
				<li schooltype="{$schoolType}">
					<a class="lme_school_name" target="_blank" href="{$school['url']}">{$school['schoolname']}</a>
					<div>{$school['address']}, {$school['phonenumber']}</div>
					<div>{$school['gradesserved']} |
					<a href="http://www.education.com/schoolfinder/us/{$full_state}/district/{$hyphenatedSchoolDistrict}/" target="_blank">{$school['schooldistrictname']}</a></div>
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
					<h5 class="lme_schools_list_subheader"><a href="http://www.education.com/schoolfinder/us/{$full_state}/{$city_for_link}/elementary/" target="_blank">{$this->location_for_display} Elementary Schools</a></h5>
					<div class="lme_schools_list_container">
						<ul id="lme_schools_elementary_list" class="lme_schools_list">$elementary_school_html</ul>
					</div>
				</div>
				<div class="lme_schools_panel_left lme_hide" id="lme_schools_panel_middle">
					<h5 class="lme_schools_list_subheader"><a href="http://www.education.com/schoolfinder/us/{$full_state}/{$city_for_link}/middle/" target="_blank">{$this->location_for_display} Middle Schools</a></h5>
					<div class="lme_schools_list_container">
						<ul id="lme_schools_middle_list" class="lme_schools_list">$middle_school_html</ul>
					</div>
				</div>
				<div class="lme_schools_panel_left lme_hide" id="lme_schools_panel_high">
					<h5 class="lme_schools_list_subheader"><a href="http://www.education.com/schoolfinder/us/{$full_state}/{$city_for_link}/high/" target="_blank">{$this->location_for_display} High Schools</a></h5>
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
					<a href="http://www.education.com/schoolfinder/us/{$full_state}/{$city_for_link}/" target="_blank">See more info on {$this->location_for_display} schools</a>
				</div>
				<div id="lme_educationdotcom_logo">
					<a href="http://www.education.com/schoolfinder/tools" target="_blank"><img src="http://www.education.com/i/logo/edu-logo-150x32.jpg" /></a>
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
					var ws_wsid = "{$walkscore_api_key}";
					var ws_lat = "{$this->center_lat}";
					var ws_lon = "{$this->center_long}";
					var ws_width = "400";
					var ws_height = "286";
					var ws_layout = "horizontal";
				</script>
				
				<div id="ws-walkscore-tile">
					<div id="ws-footer">
						<form id="ws-form">
							<a id="ws-a" href="http://www.walkscore.com/" target="_blank">Find out your home's Walk Score:</a>
							<input type="text" id="ws-street" style="position:absolute;top:0px;left:225px;width:231px" />
							<input type="image" id="ws-go" src="http://www2.walkscore.com/images/tile/go-button.gif" height="15" width="22" border="0" alt="get my Walk Score" style="position:absolute;top:0px;right:0px" />
						</form>
					</div>
				</div>
				
				<script type="text/javascript" src="http://www.walkscore.com/tile/show-walkscore-tile.php"></script>
			</div>
HTML;
	}
	
	function get_teachstreet_data() {
		$api_url = 'http://www.teachstreet.com/lme/classes.json?where=' . urlencode($this->city) . ',' . urlencode($this->state);
		$api_data = $this->get_url_data($api_url);
		$api_data_decoded = json_decode($api_data);
		$html = '';

		for ($i = 0; $i < sizeof($api_data_decoded->items); $i++) {
			$description = $api_data_decoded->items[$i]->description;
			$title = $api_data_decoded->items[$i]->title;
			$url = $api_data_decoded->items[$i]->url;
			$image = $api_data_decoded->items[$i]->image;
			
			$teacher_name = $api_data_decoded->items[$i]->teacher->name;
			$teacher_url = $api_data_decoded->items[$i]->teacher->url;
			
			$category_name = $api_data_decoded->items[$i]->category->name;
			$category_url = $api_data_decoded->items[$i]->category->url;
			
			$html .= <<<HTML
				<div class="ts_item">
					<div class="ts_item_image">
						<a href="$url" target="_blank"><img alt="$title" src="$image" /></a>
					</div>
					<div class="ts_item_details">
						<p><a href="$url" target="_blank">$title</a></p>
						<p>Taught by $teacher_name</p>
						<p>More <a href="$category_url" target="_blank">$category_name classes in {$this->location_for_display}</a></p>
					</div>
					<div class="clear"></div> 
				</div>
HTML;
		}
		
		$html .= <<<HTML
			<div class="ts_footer">
				<a href="{$api_data_decoded->region_browse_url}" target="_blank">Find more classes and teachers in {$this->location_for_display}</a>
			</div>
HTML;
		
		return $html;
	}
	
	function get_yelp_reviews_data() {
		$lme_apikey_yelp = get_option('lme_apikey_yelp');
		$yelp_request = "http://api.yelp.com/business_review_search?lat={$this->center_lat}&long={$this->center_long}&radius=2&ywsid={$lme_apikey_yelp}&num_biz_requested=10&category=active+food+localflavor+nightlife+restaurants";
		$yelp_reviews_raw = $this->get_url_data($yelp_request);
		$review_html = $this->get_yelp_review_list_html(json_decode($yelp_reviews_raw));
		
		return <<<HTML
			<script>LocalMarketExplorer.Yelp.Data = {$yelp_reviews_raw};</script>
			<div id="lme-yelp-map"></div>
			<em>Local reviews near {$this->location_for_display}</em>
			<div id="lme-yelp-list">{$review_html}</div>
			<div style='text-align:right'><a href='http://www.yelp.com/' target='_blank'><img title='Powered by Yelp' alt='Powered by Yelp' src='http://static.px.yelp.com/static/20090709/i/new/developers/yelp_logo_75x38.png' /></a></div>
HTML;
	}
	
	function get_yelp_review_list_html($yelp_json){
		$html = '';
		
		if (!is_array($yelp_json->businesses))
			return $html;
		
		foreach($yelp_json->businesses as $key => $business){	
			$category_name = (sizeof($business->categories) > 0 ? $business->categories[0]->name : "n/a");
			$phone = preg_replace('/(\d{3})(\d{3})(\d{4})/',"\\1-\\2-\\3", $business->phone);
			$address = $business->address1 . ($business->address2 != "" ? " " + $business->address1 : "");
			$html .= "<div class='lme-yelp-item'>
        		<a href='{$business->url}' target='_blank'>{$business->name}</a><br />
        		<div class=\"lme-yelp-item-description\">
	        		<img src='{$business->rating_img_url}' title='{$business->avg_rating}' alt='{$business->avg_rating}' /> <em>based on {$business->review_count} reviews</em><br />
	        		Category: {$category_name}<br />
	        		{$address}, {$business->city}, {$phone}
        		</div>
        	</div>";
		}
		
		return $html;
	}
}
?>
