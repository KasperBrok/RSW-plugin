jQuery(document).ready(function(){
	$( "#RSW_Sign_up_form" ).on( "click",".RSW_add", function() {
		var $t = $(this);
		
		var item = $(this).data('item');
		
		if(item == 'scout'){
			
			var PatrolNr = $(this).data('patrolnr');
			
			var data = {
                'action': 'RSW_Scout_Row',
                'Jquery_RSW_PatrolNr': PatrolNr
            };
			
			jQuery.ajax({
                url: RSW.ajaxurl,
                type: 'POST',
                data: data,
                success: function (response) {
					$t.closest("tr").after(response.substr(response.length-1, 1) === '0'? response.substr(0, response.length-1) : response);  
					
                }
            });
			
		}else if(item == 'patrol'){
			
			var data = {
                'action': 'RSW_Patrol_Row',
            };
			
			jQuery.ajax({
                url: RSW.ajaxurl,
                type: 'POST',
                data: data,
                success: function (response) {
					$t.closest("table").after(response.substr(response.length-1, 1) === '0'? response.substr(0, response.length-1) : response);  
                }
            });
		};
	});

	$( "#RSW_Sign_up_form" ).on( "change",'[class^="RSW_Radio_PL"]', function() {
		var getClass_PL = this.className;
		var getClass_APL = getClass_PL.replace("PL", "APL");
		$( "."+getClass_PL ).each(function( index ) {
			$(this).prop("checked", false);
		});
		$(this).prop("checked", true);
		
		$( "."+getClass_APL ).each(function( index ) {
			$(this).prop('disabled', false);
		});
		$(this).closest('tr').find('[class^="RSW_Radio_APL"]').prop("checked", false);
		$(this).closest('tr').find('[class^="RSW_Radio_APL"]').prop('disabled', true);
	});
	
	$( "#RSW_Sign_up_form" ).on( "change",'[class^="RSW_Radio_APL"]', function() {
		var getClass_APL = this.className;
		var getClass_PL = getClass_APL.replace("APL", "PL");
		$( "."+getClass_APL ).each(function( index ) {
			$(this).prop("checked", false);
		});
		$(this).prop("checked", true);
		
		$( "."+getClass_PL ).each(function( index ) {
			$(this).prop('disabled', false);
		});
		$(this).closest('tr').find('[class^="RSW_Radio_PL"]').prop("checked", false);
		$(this).closest('tr').find('[class^="RSW_Radio_PL"]').prop('disabled', true);
		
	});

	$( "#RSW_Sign_up_form" ).on( "click",".RSW_remove", function() {
		var item = $(this).data('item');
		if(item == 'scout'){
			$(this).closest("tr").remove();
		}else if(item == 'patrol'){
			$(this).closest("table").remove();
		};
	});
	
	$( "#RSW_Sign_up_form" ).on( "click",".RSW_expand", function() {
		$(this).closest("table").find('tr').not('.RSW_header').toggle();
		$( this ).find("span").toggleClass( "dashicons-arrow-down-alt2" );
		$( this ).find("span").toggleClass( "dashicons-arrow-up-alt2" );
	});
	
	$( ".RSW_Patrol_table > tbody ").sortable({
		connectWith: ".RSW_Patrol_table > tbody ",
		handle: ".RSW_move",
		
		receive: function (event, ui) {
			ui.item.find('input').each(function (idx) {
				var RowName = this.name;
				RowName = RowName.replace($(ui.sender[0].closest('table')).data('patrolnr'),$(ui.item.closest('table')).data('patrolnr'))
				$(this).attr('name', RowName);
			});
			ui.item.find('[class^="RSW_Radio_PL"]').prop("checked", false);
			ui.item.find('[class^="RSW_Radio_APL"]').prop("checked", false);
			ui.item.find('[class^="RSW_Radio_PL"]').prop("disabled", false);
			ui.item.find('[class^="RSW_Radio_APL"]').prop("disabled", false);
		}
    });

	
});

  $( function() {
    $( "#RSW_Sign_up_Dialog" ).dialog({
		buttons: [
			{
				text: "Annuleren",
				click: function() {
					$( this ).dialog( "close" );
				}
				//Annuleren
			},
			{
				text: "Inschrijven",
				click: function() {
					var data = {
						'action': 'RSW_handle_Sign_up',
						'formdata': $( "#RSW_Sign_up_form" ).serialize()
					};
					
					jQuery.ajax({
						url: RSW.ajaxurl,
						type: 'POST',
						data: data,
						success: function (response) {
							//alert(response);
							$( "#RSW_Sign_up_Dialog" ).dialog( "close" );
						}
					});
				}
				//Inschrijven. Inschrijving kan later aangepast worden.
			}
		]
	});
  } );