<?php
add_filter("rewrite_rules_array", array("LmeModulesPageRewrite", "insertRules"));
add_filter("query_vars", array("LmeModulesPageRewrite", "saveQueryVars"));

class LmeModulesPageRewrite {
	static function insertRules($incomingRules) {
		$lmeRules = array(
			"^local/(\d{5})$"					=> 'index.php?lme-action=1&lme-zip=$matches[1]',
			"^local/([^/]+)/(\w{2})$"			=> 'index.php?lme-action=1&lme-city=$matches[1]&lme-state=$matches[2]',
			"^local/([^/]+)/([^/]+)/(\w{2})$"	=> 'index.php?lme-action=1&lme-neighborhood=$matches[1]&lme-city=$matches[2]&lme-state=$matches[3]'
		);
		return $lmeRules + $incomingRules;
	}
	static function saveQueryVars($queryVars) {
		$queryVars[] = "lme-action";
		$queryVars[] = "lme-zip";
		$queryVars[] = "lme-neighborhood";
		$queryVars[] = "lme-city";
		$queryVars[] = "lme-state";

		return $queryVars;
	}
}
?>