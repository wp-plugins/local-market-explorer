<?
class LMEWidget extends WP_Widget {
	function LMEWidget() {        
		parent::WP_Widget(false, $name = 'LME Widget');	
		add_filter('wp_head', array(&$this, 'widget_head'));
		add_filter('admin_head', array(&$this, 'widget_admin_head'));
	}

	function widget($args, $instance) {
		$slug = 'local';
		$wpurl = get_bloginfo('wpurl');
		extract($args);
		
		$lme_area_cities = unserialize(get_option('lme_area_cities'));
		$lme_area_states = unserialize(get_option('lme_area_states'));
		
        $title = esc_attr($instance['title']);
        $badge = esc_attr($instance['badge']);
		
		echo $before_widget;
		?><div class="lme-widget"><?
		
		if($title) { echo $before_title . $title . $after_title; }
		if($badge) { ?><img src="<?= $badge ?>"<? }
		
		for($i=0;$i<sizeOf($lme_area_cities);$i++){
			$title = '';
			$link = '';
			
			if($lme_area_cities[$i] != '' && $lme_area_states[$i] != '') {
				$title = $lme_area_cities[$i] .', '. $lme_area_states[$i];
				$link = $lme_area_cities[$i] .'/'. $lme_area_states[$i];
			}
			
			$link = strtolower(str_replace(' ', '-', $link));
			?><a href="<?= $wpurl .'/'. $slug .'/'. $link ?>"><?= $title ?></a><br /><?
		}
		
		?></div><?
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {				
        return $new_instance;
	}

	function form($instance) {		
        $title = esc_attr($instance['title']);
        $badge = esc_attr($instance['badge']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /> Leave blank for no title</label></p>
            
            <p><label for="<?php echo $this->get_field_id('badge'); ?>"><?php _e('Badge:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('badge'); ?>" name="<?php echo $this->get_field_name('badge'); ?>" type="text" value="<?php echo $badge; ?>" /></label></p>
            <select id="<?php echo $this->get_field_id('badge'); ?>_selector" onchange="LocalMarketExplorerAdmin.SelectWidgetBadge('<?php echo $this->get_field_id('badge'); ?>')">
				<option selected="selected" value="">Custom or None</option>
			<?
			$wpurl = get_bloginfo('wpurl');
			$dir = "../wp-content/plugins/local-market-explorer/images/badges/";
			if(is_dir($dir))
			{
				if($handle = opendir($dir))
				{
					while(($file = readdir($handle)) !== false)
					{
						if($file != "." && $file != ".." && $file != "Thumbs.db"/*pesky windows, images..*/)
						{
							?>									
				<option value="<?= $wpurl ?>/wp-content/plugins/local-market-explorer/images/badges/<?= $file ?>"><?= $file ?></option>
							<?
						}
					}
					closedir($handle);
				}
			}

			?>
			</select>
			<span class="setting-description">Choose a premade bade for your sidebar widget, or enter your own URL</span>
			<div id="<?php echo $this->get_field_id('badge'); ?>_preview" style="margin:5px;display:<?= $badge == '' ? 'none' : 'block' ?>"><img src="<?= $badge ?>" /></div>
        <?php 
	}
	
	function widget_head(){
		$wpurl = get_bloginfo('wpurl');
		echo <<<HEAD
					<link rel="stylesheet" type="text/css" href="{$wpurl}/wp-content/plugins/local-market-explorer/includes/lme-widget.css" />
HEAD;
	}
	
	function widget_admin_head(){
		$wpurl = get_bloginfo('wpurl');
		echo <<<HEAD
			<script type=\"text/javascript\" src=\"{$wpurl}/wp-content/plugins/local-market-explorer/includes/lme-admin.js\"></script>
HEAD;
	}
	
}
?>