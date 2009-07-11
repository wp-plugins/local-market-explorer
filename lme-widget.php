<?

function widget_lme_register(){
	function widget_lme($args) {
		$slug = 'local';
		$wpurl = get_bloginfo('wpurl');
		extract($args);
		
		$lme_area_cities = unserialize(get_option('lme_area_cities'));
		$lme_area_states = unserialize(get_option('lme_area_states'));
		
		?>
		<?php echo $before_widget; ?>
		<?php echo $before_title . 'My Local Markets' . $after_title; ?>
		<?
		for($i=0;$i<sizeOf($lme_area_cities);$i++){
			$title = '';
			$link = '';
			
			if($lme_area_cities[$i] != '' && $lme_area_states[$i] != '') {
				$title = $lme_area_cities[$i] .', '. $lme_area_states[$i];
				$link = $lme_area_cities[$i] .','. $lme_area_states[$i];
			}
			
			$link = strtolower(str_replace(' ', '-', $link));
			?><a href="<?= $wpurl .'/'. $slug .'/'. $link ?>"><?= $title ?></a><br /><?
		}
		?>
		<?php echo $after_widget; ?>
		<?php
	}
	register_sidebar_widget('LME Widget', 'widget_lme');
}

?>