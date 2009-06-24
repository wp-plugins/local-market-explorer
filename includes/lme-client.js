var LocalMarketExplorer = {
	
	ZillowIndex: function() {
		var returnObj;
		
		var zillowUrl = "http://www.zillow.com/app?chartDuration={char_duration}&chartType=partner&cityRegionId=12447&countyRegionId=0&height=200&nationRegionId=0&page=webservice%2FGetRegionChart&service=chart&showCity=true&showPercent={show_percent}&stateRegionId=0&width=400&zipRegionId=0";
	
		var zillowShowPercent = 'true', zillowChartDuration = '1year', pageReady = false;
		
		returnObj = {
		
			setPercent: function(value){
				if(zillowShowPercent != value){
					zillowShowPercent = value;
					this.processChanges();
				}
			},
			
			setDuration: function(value){
				if(zillowChartDuration != value){
					zillowChartDuration = value;			
					this.processChanges();
				}
			},
			
			processChanges: function(){		
				if(pageReady){	
					$j('#lme_zillow_percentage').css('text-decoration', zillowShowPercent == 'false' ? 'underline' : 'none');
					$j('#lme_zillow_dollar').css('text-decoration', zillowShowPercent == 'true' ? 'underline' : 'none');
					
					$j('#lme_zillow_market_1_yr').css('text-decoration', zillowChartDuration == '1year' ? 'none' : 'underline');
					$j('#lme_zillow_market_5_yr').css('text-decoration', zillowChartDuration == '5years' ? 'none' : 'underline');
					$j('#lme_zillow_market_10_yr').css('text-decoration', zillowChartDuration == '10years' ? 'none' : 'underline');
				
					$j('#lme_zillow_region_chart').attr('src', zillowUrl.
						replace("{show_percent}", zillowShowPercent). 
						replace("{char_duration}", zillowChartDuration));
				}
			}
		}
		
		$j(function() {
			pageReady = true;
			returnObj.processChanges();
		})

		return returnObj;
	}(),
	About: function() {
		var returnObj;

		returnObj = {
		}

		return returnObj;
	}(),
	MarketActivity: function() {
		var returnObj;

		returnObj = {
		}

		return returnObj;
	}(),
	Schools: function() {
		var returnObj;
		var currentGradeLevel = 'elementary', currentType = 'all';

		returnObj = {
			showSchools: function(config) {
				var panelId = 'lme_schools_panel_' + config.gradeLevel;
				var elementsShown = 0;

				$j('#lme_schools .lme_schools_panel_left').addClass('lme_hide');
				$j('#' + panelId).removeClass('lme_hide');
				$j('#' + panelId + ' li').each(function(i){
					var el = $j(this);
					
					if (config.type == 'all') {
						el.show();
						elementsShown++;
					} else if (el.attr('schooltype').indexOf(config.type) != -1) {
						el.show();
						elementsShown++;
					} else {
						el.hide();
					}
				});

				currentGradeLevel = config.gradeLevel;
				currentType = config.type;
				
				if (elementsShown == 0)
					$j('#' + panelId + ' ul').append('<li schooltype="' + currentType + '">There are no ' + currentType + ' ' + currentGradeLevel + ' schools in this area</li>');
			},
			page: function(direction) {
				var scrollEl = $j('#lme_schools_panel_left_container .lme_schools_panel_left:not(.lme_hide) .lme_schools_list_container');
				var scrollAmount = scrollEl.height();
				var currentPosition = scrollEl[0].scrollTop;
				
				if (direction == '-=' && currentPosition - scrollAmount < 0)
					scrollAmount = 0;
				
				scrollEl.animate({scrollTop: direction + scrollAmount});
			}
		}

		$j(function() {
			$j('#lme_schools_grade_choices input').bind('click', function() {
				returnObj.showSchools({gradeLevel:this.value, type:currentType});
			});
			$j('#lme_schools_type_choices input').bind('click', function() {
				returnObj.showSchools({gradeLevel:currentGradeLevel, type:this.value});
			});
		});

		return returnObj;
	}(),
	WalkScore: function() {
		var returnObj;

		returnObj = {
		}

		return returnObj;
	}()
}