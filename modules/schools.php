<?php

class LmeModuleSchools {
	static function getApiUrls($opt_neighborhood, $opt_city, $opt_state, $opt_zip) {
		$options = get_option(LME_OPTION_NAME);
		$apiKey = "bd23bb5cb91e37c39282f6bf75d56fb9"; // education.com wants this embedded
		$url = "http://www.education.com/service/service.php?f=schoolSearch&sn=sf&resf=php&v3&key={$apiKey}&";
		
		if (isset($opt_zip)) {
			$locationParams = "zip={$opt_zip}";
		} else {
			$encodedCity = urlencode($opt_city);
			$locationParams = "city={$encodedCity}&state={$opt_state}";
		}
		
		return array(
			"school-search"	=> "{$url}{$locationParams}"
		);
	}
	static function getModuleHtml($apiResponses) {
		$schoolSearch = unserialize($apiResponses["school-search"]);
		
		if (empty($schoolSearch))
			return;
		
		$location = $schoolSearch[0]["school"]["city"];
		preg_match("/^(.+?)[^\/]+\/$/", $schoolSearch[0]["school"]["url"], $locationUrlMatches);
		$locationUrl = $locationUrlMatches[1];
		
		$content = <<<HTML
			<h2 class="lme-module-heading">Schools</h2>
			<div class="lme-module lme-schools">
				<div class="lme-left">
HTML;

		foreach ($schoolSearch as $school) {
			$school = $school["school"];
			$hyphenizedDistrict = str_replace(" ", "-", strtolower($school["schooldistrictname"]));
			
			$content .= <<<HTML

					<div class="lme-school" data-grade="{$school["gradelevel"]}" data-type="{$school["schooltype"]}">
						<h4><a href="{$school["url"]}">{$school["schoolname"]}</a></h4>
						<div>{$school["address"]} | {$school["phonenumber"]}</div>
						<div>
							{$school["gradesserved"]} | 
							<a href="{$locationUrl}district/{$hyphenizedDistrict}/">{$school["schooldistrictname"]}</a>
						</div>
					</div>
HTML;
		}

		$content .= <<<HTML
				</div>
				<div class="lme-right">
					<div class="lme-filter">
						<b>Grade level filter</b>
						<div>
							<input type="radio" name="lme-grade-level" id="lme-grade-level-all" checked />
							<label for="lme-grade-level-all">All</label>
						</div>
						<div>
							<input type="radio" name="lme-grade-level" id="lme-grade-level-elem" />
							<label for="lme-grade-level-elem">Elem. Schools</label>
						</div>
						<div>
							<input type="radio" name="lme-grade-level" id="lme-grade-level-middle" />
							<label for="lme-grade-level-middle">Middle Schools</label>
						</div>
						<div>
							<input type="radio" name="lme-grade-level" id="lme-grade-level-high" />
							<label for="lme-grade-level-high">High Schools</label>
						</div>
					</div>
					
					<div class="lme-filter">
						<b>Schoool type filter</b>
						<div>
							<input type="radio" name="lme-school-type" id="lme-school-type-all" checked />
							<label for="lme-school-type-all">All</label>
						</div>
						<div>
							<input type="radio" name="lme-school-type" id="lme-school-type-public" />
							<label for="lme-school-type-public">Public Schools</label>
						</div>
						<div>
							<input type="radio" name="lme-school-type" id="lme-school-type-private" />
							<label for="lme-school-type-private">Private Schools</label>
						</div>
						<div>
							<input type="radio" name="lme-school-type" id="lme-school-type-charter" />
							<label for="lme-school-type-charter">Charter Schools</label>
						</div>
					</div>
				</div>
				<div class="lme-market-location-url">
					<a href="{$locationUrl}">See more info on {$location} schools</a>
				</div>
				<img class="lme-market-logo" src="http://www.education.com/i/logo/edu-logo-150x32.jpg" />
			</div>
HTML;
		return $content;
	}
}

?>