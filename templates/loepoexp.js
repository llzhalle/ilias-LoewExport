$( document ).ready(function() {
	var pnumber = null;
	
	var label = {de:'Prüfungsnummer',en:'Testnumber'}
	
	$('select#format').on('change', function(event){
		
		if($(this).val() == 'excel')
		{
			$(this).closest('.navbar-form').after('<div class="navbar-form"><label for="lpnum">'+label[$('html').attr('lang')]+':</label> <input class="form-control" type="text" id="lpnum" name="lpnum"></div>');
			
			$('input[name="cmd[createExportFile]"]').prop('disabled', true);
		}
		else
		{
			$("input#lpnum").parent().remove();
			$('input[name="cmd[createExportFile]"]').prop('disabled', false);
		}
		
	});
	
	$(document).on("input", "input#lpnum", function (event) {
		
		if($(this).val() == ""){
			$('input[name="cmd[createExportFile]"]').prop('disabled', true);
		}
		else{
			$('input[name="cmd[createExportFile]"]').prop('disabled', false);
		}
		
	});

});