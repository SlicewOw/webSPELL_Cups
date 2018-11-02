$(document).ready(function(e) {
    
	$("#tickets_menu_1").click(function() { 
	
		$("#tickets_menu_open").show(); 
		$("#tickets_menu_status").hide();
		$("#tickets_menu_closed").hide();
		
	});
	
	$("#tickets_menu_2").click(function() { 
	
		$("#tickets_menu_open").hide(); 
		$("#tickets_menu_status").show();
		$("#tickets_menu_closed").hide();
		
	});
	
	$("#tickets_menu_3").click(function() { 
	
		$("#tickets_menu_open").hide(); 
		$("#tickets_menu_status").hide();
		$("#tickets_menu_closed").show();
		
	});
	
});