$( document ).ready(function() {
	var pnumber = null;
	
	var path = "/Customizing/global/plugins/Modules/Test/Export/LoePoExport/templates/";
	
	var label_prf = {de:'Prüfungsnummer',en:'Testnumber'}
	var label_sem = {de:'Prüfungssemester',en:'Test semester'}
	var label_date = {de:'Prüfungstermin',en:'Test date'}
	
	$('select#format').on('change', function(event){
		
		if($(this).val() == 'excel')
		{
			$(this).closest('.navbar-form')
				.after('<div class="navbar-form"><label for="lpnum">'+label_date[$('html').attr('lang')]+'<span class="glyphicon glyphicon-info-sign" data-image="löwenportal.png"></span>:</label> <input class="form-control" type="text" id="lpnum" name="lpdate" style="width:40px;" maxlength="2" /></div>')
				.after('<div class="navbar-form"><label for="lpnum">'+label_sem[$('html').attr('lang')]+'<span class="glyphicon glyphicon-info-sign" data-image="löwenportal.png"></span>:</label> <input class="form-control" type="text" id="lpnum" name="lpsem" style="width:70px;" maxlength="5" /></div>')
				.after('<div class="navbar-form"><label for="lpnum">'+label_prf[$('html').attr('lang')]+'<span class="glyphicon glyphicon-info-sign" data-image="löwenportal.png"></span>:</label> <input class="form-control" type="text" id="lpnum" name="lpnum" style="width:100px;" /></div>');
			
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
	
	$(document).on("mouseenter", ".glyphicon.glyphicon-info-sign", function(){		
        if ($(this).parent().children('div.image').length) {
            $(this).parent().children('div.image').show();
        } else {
            var image_name=$(this).data('image');
            var imageTag='<div class="image" style="position:absolute;">'+'<img src="'+path+image_name+'" alt="image" />'+'</div>';
            $(this).parent().append(imageTag);
        }
    });

	$(document).on("mouseleave", ".glyphicon.glyphicon-info-sign", function(){
        $(this).parent().children('div.image').hide();
    });

});