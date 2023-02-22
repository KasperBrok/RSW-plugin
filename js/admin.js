jQuery(document).ready(function(){
	jQuery(function() {
		// jQuery( ".sortable_list" ).sortable({
			// connectWith: ".connectedSortable",
			// dropOnEmpty: true,
			// placeholder: "subfield-setup-placeholder",
			// cancel: ".ui-state-disabled",
			// receive: function (event, ui) {
				// ui.item.find('input').each(function (idx) {
					// var RowName = this.name;
					// RowName = RowName.replace(jQuery(ui.sender[0].closest('ul')).data('color'),jQuery(ui.item.closest('ul')).data('color'))
					// jQuery(this).attr('name', RowName);
				// });
				// if (jQuery('#RSW_Auto_update_sub_divisions').is(':checked')) {
					// jQuery( "#RSW_Subfield_form" ).submit();
				// }
			// }
		// });
		
		
		jQuery( ".subfield-setup-dragable" ).draggable({
			//snap: ".subfield-setup-dropable",
			//snapMode: "inner"
			revert: 'invalid',
			revertDuration: 200,
		});
		
		jQuery( ".subfield-setup-dropable" ).droppable({
			accept: ".subfield-setup-dragable",
			drop: function(event, ui) {
				var prevPosition = ui.draggable.data( "position" );
				var prevDroppableElement;
				
				jQuery("#subfield-setup-container").find(".subfield-setup-dropable").each(function( index ) {
					if(prevPosition == jQuery(this).data( "position" )){
						prevDroppableElement = jQuery(this);
					}
				});
				
				var currPosition = jQuery(this).data( "position" );
				var currDroppableElement = jQuery(this);
				
				jQuery("#subfield-setup-container").find(".subfield-setup-dragable").each(function( index ) {
					if(jQuery(this).data( "position" ) == currPosition){
						jQuery(this).position({
							my: "center",
							at: "center",
							of: prevDroppableElement,
							using: function(pos) {
								jQuery(this).animate(pos, 200, "linear");
							}
						});
						jQuery(this).data( "position", prevPosition)
						
						jQuery(this).find("input").each(function( index ) {
							var InputName = this.name;
							if(jQuery(this).data( "name" ) == "Patrol_subfield_position")jQuery(this).val(prevPosition);
							InputName = InputName.replace(jQuery(this).closest('.subfield-setup').data( "color" ),prevDroppableElement.closest('.subfield-setup').data( "color" ));
							InputName = InputName.replace("["+currPosition+"]","["+prevPosition+"]");
							jQuery(this).attr('name', InputName);
						});
					};
				});
				
				ui.draggable.position({
					my: "center",
					at: "center",
					of: currDroppableElement,
					using: function(pos) {
						jQuery(this).animate(pos, 200, "linear");
					}
				});
				
				ui.draggable.find("input").each(function( index ) {
					var InputName = this.name;
					if(jQuery(this).data( "name" ) == "Patrol_subfield_position")jQuery(this).val(currPosition);
					
					if(ui.draggable.parent('div#subfield-unsorted-container').length){
						
						InputName = InputName.replace("undefined",currDroppableElement.closest('.subfield-setup').data( "color" ));
					}else{
						InputName = InputName.replace(jQuery(this).closest('.subfield-setup').data( "color" ),currDroppableElement.closest('.subfield-setup').data( "color" ));
					};
					
					InputName = InputName.replace("["+prevPosition+"]","["+currPosition+"]");
					jQuery(this).attr('name', InputName);
					
				});
				
				ui.draggable.data( "position",currPosition );
				
				if(jQuery('#RSW_renumber_sub_divisions').is(':checked')){
					var NewPosNrArray = [];
					jQuery("#subfield-setup-container").find(".subfield-setup-dragable").each(function( index ) {
						if(jQuery(this).html().length > 0){
							NewPosNrArray[jQuery(this).data( "position" )] = jQuery(this);
						}
					});
					var NewPosNr = 1;
					NewPosNrArray.forEach(function(patrol) {
						if(jQuery(patrol).html().length > 0){
							jQuery(patrol).find(".Temp_ID").html("("+NewPosNr+")");
							NewPosNr++;
						};
					});
					// jQuery("#subfield-setup-container").find(".Temp_ID").each(function( index ) {
						// alert(jQuery(this).html());
					// });
				};
			}
		});
	});
});
