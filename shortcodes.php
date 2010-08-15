<?php
class LmeShortcodes {
	static function Module($atts, $content = null, $code = "") {
		
	}
}

add_shortcode("lme-module", array("LmeShortcodes", "Module"));
?>