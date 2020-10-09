$( document ).ready(function() {	
	var l = $('html').attr('lang') || 'en';

	var elem_lpdate = '<div class="navbar-form"><label for="lpnum">'+loepoexp_lang.label_date[l]+'<span class="glyphicon glyphicon-info-sign" data-image="löwenportal.png"></span>:</label> <select class="form-control" id="lpdate" name="lpdate" style="width:40px;padding:0;">'+
						'<option value="01">01</option>'+
						'<option value="02">02</option>'+
						'<option value="03">03</option>'+
						'</select></div>';
	
	var year = new Date().getFullYear();
	
	var elem_lpsem = '<div class="navbar-form"><label for="lpnum">'+loepoexp_lang.label_sem[l]+'<span class="glyphicon glyphicon-info-sign" data-image="löwenportal.png"></span>:</label> <select class="form-control" id="lpsem" name="lpsem" style="width:185px;">'+
						'<option value="'+(year-1)+'1">'+loepoexp_lang.label_ss[l]+' '+(year-1)+'</option>'+
						'<option value="'+(year-1)+'2">'+loepoexp_lang.label_ws[l]+' '+(year-1)+'/'+year+'</option>'+
						'<option value="'+year+'1" selected="selected">'+loepoexp_lang.label_ss[l]+' '+(year)+'</option>'+
						'<option value="'+year+'2">'+loepoexp_lang.label_ws[l]+' '+(year)+'/'+(year+1)+'</option>'+
						'<option value="'+(year+1)+'1">'+loepoexp_lang.label_ss[l]+' '+(year+1)+'</option>'+
						'<option value="'+(year+1)+'2">'+loepoexp_lang.label_ws[l]+' '+(year+1)+'/'+(year+2)+'</option>'+
						'</select></div>';
	
	var elem_lpnum = '<div class="navbar-form"><label for="lpnum">'+loepoexp_lang.label_prf[l]+'<span class="glyphicon glyphicon-info-sign" data-image="löwenportal.png"></span>:</label> <input class="form-control" type="text" id="lpnum" name="lpnum" style="width:75px;" /></div>';
	
	$('select#format').on('change', function(event){
		
		if($(this).val() == 'excel')
		{
			$(this).closest('.navbar-form')
				.after(elem_lpdate)
				.after(elem_lpsem)
				.after(elem_lpnum);
			
			$('input[name="cmd[createExportFile]"]').prop('disabled', true);
		}
		else
		{
			$("input#lpnum").parent().remove();
			$("select#lpsem").parent().remove();
			$("select#lpdate").parent().remove();
			$('input[name="cmd[createExportFile]"]').prop('disabled', false);
		}
		
	});
	
	$(document).on("input", "[id^=lp]", function (event) {
		
		if($("input[id^=lp]").filter(function () {
				return $.trim($(this).val()).length === 0
				}).length !== 0) {
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
            var imageTag='<div class="image" style="position:absolute;">'+'<img src="'+loepoexp_lang.path+image_name+'" alt="'+image_name+'" />'+'</div>';
            $(this).parent().append(imageTag);
        }
	});

	$(document).on("mouseleave", ".glyphicon.glyphicon-info-sign", function(){
        $(this).parent().children('div.image').hide();
    });

});