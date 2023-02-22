<?php
function RSWSpelmiddagformulierenPagina(){
	global $wpdb;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	if(isset($_POST['Action']) && $_POST['Action'] == "updaten"){
		foreach($_POST['Patrol_score'] as $PatrolId => $Patrol_scores){
			
			$SpelmiddagTotal = 0;
			foreach($Patrol_scores as $Patrol_score){
				$SpelmiddagTotal = $SpelmiddagTotal + $Patrol_score;
			};
			
			$PatrolData = $wpdb->get_row("
				SELECT 
					*
				FROM $RSW_Scout_table WHERE id = $PatrolId
			");
			$scouts = $wpdb->get_results("
				SELECT 
					*
				FROM $RSW_Scout_table
				WHERE
					Association_Name = '{$PatrolData->Association_Name}' AND
					Association_Sub_name = '{$PatrolData->Association_Sub_name}' AND
					Patrol_Name = '{$PatrolData->Patrol_Name}' AND
					Year = '$Year'
			");
			
			$Patrol_Points_json = json_decode($PatrolData->Patrol_Points_json,true);
			$Patrol_Points_json['Spelmiddag:Score'] = $SpelmiddagTotal;
			
			foreach($scouts as $scout){
				$updated = $wpdb->update( $RSW_Scout_table, array("Patrol_Spelmiddag_json" => json_encode($Patrol_scores),"Patrol_Points_json" => json_encode($Patrol_Points_json)), array("id" => $scout->id) );
				if ( false === $updated ) {
					echo 'update failed at id: '.$scout->id;
				};
			};
			
		};
	};
	
	$SQLSubfieldFilter = "";
	if(isset($_POST['SubField']) && $_POST['SubField'] != 'all'){
		$SQLSubfieldFilter = "AND Patrol_subfield = '" . $_POST['SubField'] . "'";
	};
	
	$criterias = $wpdb->get_results("
		SELECT 
			Name,
			Description,
			Weeg_factor,
			Max_value
		FROM $RSW_Criteria_table WHERE Categorie = 'Spelmiddag' $SQLSubfieldFilter
	");
	
	$SubFields = $wpdb->get_results("SELECT * FROM $RSW_Subfield_table WHERE Year = '$Year'");
	$Categorys = array();
	$SubCategorys = array();
	
	$Patrols = $wpdb->get_results("
		SELECT 
			id,
			Patrol_Points_total,
			Patrol_Spelmiddag_json,
			Patrol_Position,
			Patrol_Number,
			Patrol_Name
		FROM $RSW_Scout_table 
		WHERE 
			Year = '$Year'
			$SQLSubfieldFilter
		GROUP BY 
			Association_Name,
			Association_Sub_name,
			Patrol_Name
		ORDER BY Patrol_Number
	");
	
	echo'<form method="post">';
		//echo'<input type="hidden" id="RSW_Page_Name" name="page" value="'.$_GET['page'].'">';
		
		echo'<select name="SubField" id="RSW_Sub_Field" onchange="this.form.submit()">';
			echo'<option value="all" ';
				if(isset($_POST['SubField']) && $_POST['SubField'] == 'all'){
					echo'selected';
				};
			echo' >Alle Subkampen</option>';
			foreach($SubFields as $SubField){
				echo'<option value="'.$SubField->color.'" ';
				if(isset($_POST['SubField']) && $_POST['SubField'] == $SubField->color){
					echo'selected';
				};
				echo' >'.$SubField->color.'</option>';
			};
		echo'</select>';
		
		echo '<input type="submit" class="button" value="filter"/>';
		echo '<input type="submit" class="button" name="Action" value="updaten"/>';
		
		echo '<table id="RSW_Patrol_score_overview_table">';
			echo '<tr>';
				echo '<th rowspan="2">Patrouille</th>';
				foreach($criterias as $criteria){
						echo '<th>';
							echo $criteria->Name;
						echo '</th>';
				};
			echo '</tr>';
			echo '<tr>';
				foreach($SubCategorys as $SubCategory){
					echo '<th>';
						echo $SubCategory;
					echo '</th>';
				};
			echo '</tr>';
			foreach($Patrols as $Patrol){
				echo '<tr>';
					echo '<th>';
						echo $Patrol->Patrol_Number.' - '.$Patrol->Patrol_Name;
					echo '</th>';
					$Spelmiddag_scores = json_decode($Patrol->Patrol_Spelmiddag_json,true);
					$tabIndex = 1;
					foreach($criterias as $criteria){
						echo '<th>';
							echo'<input onchange="if(this.value > '.$criteria->Max_value.') this.value = '.$criteria->Max_value.';" tabindex="'.$tabIndex.'" type="number" name="Patrol_score['.$Patrol->id.']['.$criteria->Name.']" min="0" max="'.$criteria->Max_value.'" value="';
							if(isset($Spelmiddag_scores[$criteria->Name])){
								echo $Spelmiddag_scores[$criteria->Name];
							}elseif(isset($_POST['Patrol_score'][$Patrol->id][$criteria->Name])){
								echo $_POST['Patrol_score'][$Patrol->id][$criteria->Name];
							}else{
								echo 0;
							};
							echo'">';
						echo '</th>';
						$tabIndex++;
					};
				echo '</tr>';
			};
		echo '</table>';
	echo'</form>';
	
};
?>