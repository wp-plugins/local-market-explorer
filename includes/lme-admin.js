var LocalMarketExplorerAdmin = {
	RemoveArea : function(index){
		if(confirm("Are you sure you wish to remove this Target Area?")){
			var AreaTables = jQuery('#lme_area_table__' + index);
			AreaTables.remove();
		}
	},
	
	SelectWidgetBadge : function(context){
		var url = jQuery('#'+context+'_selector').val();
		
		jQuery('#'+context).val(url);
		
		jQuery('#'+context+'_preview').show();
		jQuery('#'+context+'_preview img').attr('src', url);
	}
}