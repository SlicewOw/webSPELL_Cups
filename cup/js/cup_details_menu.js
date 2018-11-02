$(document).ready(function(e) {
    
	$("#cup_menu_details").click(function() { 
	
		$("#cup_menu_details_container").show(); 
		$("#cup_menu_teams_container").hide();
		$("#cup_menu_group_container").hide();
		$("#cup_menu_playoffs_container").hide();
		$("#cup_menu_rules_container").hide();
		
	});
	
	$("#cup_menu_teams").click(function() { 
	
		$("#cup_menu_details_container").hide(); 
		$("#cup_menu_teams_container").show();
		$("#cup_menu_group_container").hide();
		$("#cup_menu_playoffs_container").hide();
		$("#cup_menu_rules_container").hide();
		
	});
	
	$("#cup_menu_group").click(function() { 
	
		$("#cup_menu_details_container").hide(); 
		$("#cup_menu_teams_container").hide();
		$("#cup_menu_group_container").show();
		$("#cup_menu_playoffs_container").hide();
		$("#cup_menu_rules_container").hide();
		
	});
	
	$("#cup_menu_playoffs").click(function() { 
	
		$("#cup_menu_details_container").hide(); 
		$("#cup_menu_teams_container").hide();
		$("#cup_menu_group_container").hide();
		$("#cup_menu_playoffs_container").show();
		$("#cup_menu_rules_container").hide();
		
	});
	
	$("#cup_menu_rules").click(function() { 
	
		$("#cup_menu_details_container").hide(); 
		$("#cup_menu_teams_container").hide();
		$("#cup_menu_group_container").hide();
		$("#cup_menu_playoffs_container").hide();
		$("#cup_menu_rules_container").show();
		
	});
	
});