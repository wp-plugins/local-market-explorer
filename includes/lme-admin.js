var LocalMarketExplorerAdmin = {
	RemoveArea : function(index){
		if(confirm("Are you sure you wish to remove this Target Area?")){
			var AreaTables = jQuery('#lme_area_table__' + index);
			AreaTables.remove();
		}
	}
}