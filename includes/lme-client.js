var LocalMarketExplorer = {
	
	ZillowIndex: function() {
		var returnObj;
		var zillowUrl = "";
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
					$j('#lme_zillow_percentage').css('text-decoration', zillowShowPercent == 'false' ? 'none' : 'underline');
					$j('#lme_zillow_dollar').css('text-decoration', zillowShowPercent == 'true' ? 'none' : 'underline');
					
					$j('#lme_zillow_market_1_yr').css('text-decoration', zillowChartDuration == '1year' ? 'underline' : 'none');
					$j('#lme_zillow_market_5_yr').css('text-decoration', zillowChartDuration == '5years' ? 'underline' : 'none');
					$j('#lme_zillow_market_10_yr').css('text-decoration', zillowChartDuration == '10years' ? 'underline' : 'none');

					$j('#lme_zillow_region_chart').attr('src', zillowUrl.
						replace("{show_percent}", zillowShowPercent). 
						replace("{char_duration}", zillowChartDuration));
				}
			}
		}
		
		$j(function() {
			pageReady = true;
			
			if (!$j('#lme_zillow_region_chart').length)
				return;
			
			zillowUrl = $j('#lme_zillow_region_chart').attr('src');
			zillowUrl = zillowUrl.replace(/chartDuration=[^&]+&/, "chartDuration={char_duration}&");
			zillowUrl = zillowUrl.replace(/showPercent=[^&]+&/, "showPercent={show_percent}&");
			
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
	}(),
	Yelp: function(){
	
		var returnObj;

		returnObj = {
			loadMap: function(){
				var openedWindow = null;

				var map = new google.maps.Map(document.getElementById("lme-yelp-map"), {
			      mapTypeId: google.maps.MapTypeId.ROADMAP,
			      mapTypeControl: false
			    });
			    
			    var bounds = new google.maps.LatLngBounds();
				
				map.scrollwheel = false;
			    
			    for(var i=0;i< LocalMarketExplorer.Yelp.Data.businesses.length;i++){
			    	var business = LocalMarketExplorer.Yelp.Data.businesses[i];
					
					if (!business.latitude)
						continue;
					
			    	var latLng = new google.maps.LatLng(business.latitude, business.longitude);
			    	
			    	var marker = new google.maps.Marker({
				        position: latLng, 
				        map: map,
				        title:business.name
				    });

				    var infowindow = new google.maps.InfoWindow({
				        content: 
				        	"<div class='lme-yelp-preview'>"+
				        		"<a href='"+ business.url +"' target='_blank'>"+ business.name +"</a><br />"+
				        		"<div class='lme-yelm-preview-description'>"+
				        		"<img src='"+ business.rating_img_url +"' title='"+ business.avg_rating +"' alt='"+ business.avg_rating +"' /> <em>based on "+ business.review_count +" reviews</em><br />"+
				        		"Category: "+ (business.categories.length > 0 ? business.categories[0].name : "n/a") +"<br />"+
				        		business.phone.replace(/(\d{3})(\d{3})(\d{4})/,"$1-$2-$3") +"<br />"+
				        		business.address1 + (business.address2 != "" ? " " + business.address1 : "") + "<br />"+
				        		business.city +", "+ business.state +" "+ business.zip +
				        		"</div>" +
				        	"</div>"
				    });
				    
				    (function(map,marker,infowindow){
					    google.maps.event.addListener(marker, 'click', function() {
					    	if(openedWindow != null) openedWindow.close();
							infowindow.open(map, marker);
							openedWindow = infowindow;
					    });
				    })(map,marker,infowindow);
				    
				    bounds.extend(latLng);
			    }
			    
			    map.set_center(bounds.getCenter());
			    map.fitBounds(bounds);
			}
		}

		$j(function() {
			if($j('#lme_yelp').length > 0) returnObj.loadMap();
		})
		
		return returnObj;
	}()
}