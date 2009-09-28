var LocalMarketExplorerAdmin = {
	RemoveArea : function(index){
		if(confirm("Are you sure you wish to remove this target area?")){
			var AreaTables = jQuery('#lme_area_table__' + index);
			AreaTables.remove();
		}
	},
	
	SelectWidgetBadge : function(context){
		var url = jQuery('#'+context+'_selector').val();
		
		jQuery('#'+context).val(url);
		
		jQuery('#'+context+'_preview').show();
		jQuery('#'+context+'_preview img').attr('src', url);
	},
	
	LoadNeighborhoods: function(el) {
		el = jQuery(el);
		
		var city, state, originalNeighborhood;
		var parentTable = el.closest('.form-table');
		var requiredFields = parentTable.find('.lme_area_city,.lme_area_state').filter(function() {
			var requiredField = jQuery(this);
			if (requiredField.hasClass('lme_area_city'))
				city = this.value.toLowerCase();
			else if (requiredField.hasClass('lme_area_state'))
				state = this.value.toLowerCase();
			return !!this.value;
		});
		var neighborhoodEl;
		var apiUrl = 'http://idx.diversesolutions.com/API/Locations.ashx?m=zillow-neighborhoods&c=' + encodeURIComponent(city) + '&s=' + encodeURIComponent(state);
		
		neighborhoodEl = parentTable.find('.lme_area_neighborhood').eq(0);
		if (requiredFields.length == 0) {
			neighborhoodEl.html('<option value="">Fill in city and state to enable neighborhood selection</option>').attr('disabled', 'true')
			return;
		}
		parentTable.find('.lme_area_neighborhood_loader').remove();
		originalNeighborhood = neighborhoodEl.val();
		
		YAHOO.util.Get.script(apiUrl, { 
			onSuccess: function() {
				var z = ZillowNeighborhoods,
					zn = z.Neighborhoods;
				
				if (z.City != city || z.State != state) {
					neighborhoodEl.html('<option value="">Fill in city and state to enable neighborhood selection</option>').attr('disabled', 'true')
				}
				
				neighborhoodEl.html('<option value="">- None -</option>').removeAttr('disabled');
				for (var i = 0, j = zn.length; i < j; ++i) {
					var neighborhoodToAdd = zn[i];
					
					if (neighborhoodToAdd.toLowerCase() == originalNeighborhood.toLowerCase())
						neighborhoodEl.append('<option value="' + neighborhoodToAdd + '" selected="true">' + neighborhoodToAdd + '</option>')
					else
						neighborhoodEl.append('<option value="' + neighborhoodToAdd + '">' + neighborhoodToAdd + '</option>')
				}
			}
		});
	},
	
	UpdateLink: function(el) {
		el = jQuery(el);
		
		var parentTable = el.closest('.form-table');
		var city = parentTable.find('.lme_area_city').val();
		var state = parentTable.find('.lme_area_state').val();
		var zip = parentTable.find('.lme_area_zip').val();
		var neighborhood = parentTable.find('.lme_area_neighborhood').val();
		var cityStateLink, zipLink;
		
		if (!!city && !!state) {
			cityStateLink = '/local/';
			if (!!neighborhood)
				cityStateLink += neighborhood.replace(/ /g, '-') + '/';
			cityStateLink += city.replace(/ /g, '-') + '/' + state.replace(/ /g, '-');
			
			cityStateLink = LocalMarketExplorerAdmin.BlogUrl + cityStateLink.toLowerCase();
		}
		if (!!zip) {
			zipLink = LocalMarketExplorerAdmin.BlogUrl + '/local/' + zip;
		}
		
		parentTable.find('.lme_area_link_display').html('');
		if (!!cityStateLink)
			parentTable.find('.lme_area_link_display').append('<a target="_blank" href="' + cityStateLink + '">' + cityStateLink + '</a>');
		if (!!zipLink)
			parentTable.find('.lme_area_link_display').append('<br /><a target="_blank" href="' + zipLink + '">' + zipLink + '</a>');
	}
}

jQuery(function() {
	jQuery('#lme_options_form .lme_area_city,#lme_options_form .lme_area_state').blur(function(e) {
		LocalMarketExplorerAdmin.LoadNeighborhoods(this);
	});
	jQuery('#lme_options_form .lme_area_neighborhood').change(function(e) {
		LocalMarketExplorerAdmin.UpdateLink(this);
	});
	jQuery('#lme_options_form .lme_area_city,#lme_options_form .lme_area_state,#lme_options_form .lme_area_zip')
		.keyup(function(e) {
			LocalMarketExplorerAdmin.UpdateLink(this);
		})
		.keyup()
	;
});
