<?php
class LMEWidget extends WP_Widget {
	function LMEWidget() {        
		parent::WP_Widget(false, $name = 'Local Market Explorer');	
		add_filter('wp_head', array(&$this, 'widget_head'));
		add_filter('admin_head', array(&$this, 'widget_admin_head'));
	}

	function widget($args, $instance) {
		$slug = 'local';
		$wpurl = get_bloginfo('wpurl');
		extract($args);
		
		$areas = get_option('lme_areas');
		
        $title = esc_attr($instance['title']);
        $badge = esc_attr($instance['badge']);
		
		echo $before_widget;
		?><div class="lme-widget"><?php
		
		if($title) { echo $before_title . $title . $after_title; }
		if($badge) { ?> <img src="<?php echo $badge ?>" /> <? }
		
		for($i=0;$i<sizeOf($areas);$i++){
			if ($areas[$i]['zip']) {
				$title = $areas[$i]['zip'];
				$link = $areas[$i]['zip'];
			} else {
				if ($areas[$i]['neighborhood']) {
					$title = $areas[$i]['neighborhood'] . ', ' . $areas[$i]['city'] . ', '. $areas[$i]['state'];
					$link = $areas[$i]['neighborhood'] . '/' . $areas[$i]['city'] . '/' . $areas[$i]['state'];
				} else {
					$title = $areas[$i]['city'] .', '. $areas[$i]['state'];
					$link = $areas[$i]['city'] .'/'. $areas[$i]['state'];
				}
			}
			
			$link = strtolower(str_replace(' ', '-', $link));
			?><a href="<?php echo $wpurl .'/'. $slug .'/'. $link ?>"><?php echo $title ?></a><br /><?php
		}
		
		?></div><?php
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
			<?php
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
				<option value="<?php echo $wpurl ?>/wp-content/plugins/local-market-explorer/images/badges/<?php echo $file ?>"><?php echo $file ?></option>
							<?php
						}
					}
					closedir($handle);
				}
			}

			?>
			</select>
			<span class="setting-description">Choose a premade badge for your sidebar widget, or enter your own URL</span>
			<div id="<?php echo $this->get_field_id('badge'); ?>_preview" style="margin:5px;display:<?php echo $badge == '' ? 'none' : 'block' ?>"><img src="<?php echo $badge ?>" /></div>
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