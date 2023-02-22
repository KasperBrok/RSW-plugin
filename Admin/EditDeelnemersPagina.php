<?php
function RSWEditDeelnemersPagina(){
	global $wpdb;
	global $RSW_Scout_table;
	global $RSW_Edition_table;
	global $RSWYear;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );

	KBRScreenMeta($ScreenMeta);
	
	if(is_array($_GET['KBRItemList'])){
		if(count($_GET['KBRItemList']) == 1){
			$Scoutid = $_GET['KBRItemList'][0];
			
			$initialScoutData = $wpdb->get_row("
				SELECT 
					* 
				FROM {$RSW_Scout_table} 
				WHERE id = '$Scoutid' 
			");
			
			if(isset($_POST['Association_Name'])){
				$Association_Name = $_POST['Association_Name'];
			}else{
				$Association_Name = $initialScoutData->Association_Name;
			};
			
			if(isset($_POST['Association_Sub_name'])){
				$Association_Sub_name = $_POST['Association_Sub_name'];
			}else{
				$Association_Sub_name = $initialScoutData->Association_Sub_name;
			};
			
			if(isset($_POST['Association_Contact_name'])){
				$Association_Contact_name = $_POST['Association_Contact_name'];
			}else{
				$Association_Contact_name = $initialScoutData->Association_Contact_name;
			};
			
			if(isset($_POST['Association_Contact_Phone_number'])){
				$Association_Contact_Phone_number = $_POST['Association_Contact_Phone_number'];
			}else{
				$Association_Contact_Phone_number = $initialScoutData->Association_Contact_Phone_number;
			};
			
			if(isset($_POST['Association_Contact_Email'])){
				$Association_Contact_Email = $_POST['Association_Contact_Email'];
			}else{
				$Association_Contact_Email = $initialScoutData->Association_Contact_Email;
			};
			
			if(isset($_POST['Association_catering_nr'])){
				$Association_catering_nr = $_POST['Association_catering_nr'];
			}else{
				$Association_catering_nr = $initialScoutData->Association_catering_nr;
			};
			
			if(isset($_POST['Year'])){
				$Year = $_POST['Year'];
			}else{
				$Year = $initialScoutData->Year;
			};
			
		};
	};
	
	$LSWDate = $wpdb->get_var( "SELECT LSW_date FROM {$RSW_Edition_table} WHERE Year = '$Year'" );
	
	if(isset($_POST['RSWupdate'])){ // && $_POST['Action'] == 'updaten'
		foreach($_POST['Patrol'] as $PatrolNr => $patrol){
			if(!isset($patrol['Patrol_youngest'])){
				$_POST['Patrol'][$PatrolNr]['Patrol_youngest'] = 0;
			};
			
			$Agetotal = 0;
			
			foreach($patrol['Scouts'] as $ScoutNr => $Scout){
				if(isset($_POST['Patrol'][$PatrolNr]['Patrol_Count'])){
					$_POST['Patrol'][$PatrolNr]['Patrol_Count'] = $_POST['Patrol'][$PatrolNr]['Patrol_Count'] + 1;
				}else{
					$_POST['Patrol'][$PatrolNr]['Patrol_Count'] = 1;
				};
				
				$age = date_diff(date_create($Scout['Scout_birth_date']),date_create($LSWDate));
				$_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNr]['Scout_age'] = $age->format('%y');
				
				$Agetotal = $Agetotal + $age->format('%y');
				
				if(isset($_POST['Patrol'][$PatrolNr]['PL']) && $_POST['Patrol'][$PatrolNr]['PL'] == $Scout['id']){
					$_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNr]['Scout_PL'] = 1;
				}else{
					$_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNr]['Scout_PL'] = 0;
				};
				if(isset($_POST['Patrol'][$PatrolNr]['APL']) && $_POST['Patrol'][$PatrolNr]['APL'] == $Scout['id']){
					$_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNr]['Scout_APL'] = 1;
				}else{
					$_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNr]['Scout_APL'] = 0;
				};
				
			};
			
			$_POST['Patrol'][$PatrolNr]['Patrol_Avarage_age'] = $Agetotal / $_POST['Patrol'][$PatrolNr]['Patrol_Count'];
			
		};
		
		foreach($_POST['Patrol'] as $patrol){
			foreach($patrol['Scouts'] as $Scout){
				
				$data = array(
					"Year" => $Year,
					"Association_Name" => $_POST['Association_Name'],
					//"Association_acronym" => $_POST['Association_acronym'],
					"Association_Sub_name" => $_POST['Association_Sub_name'],
					"Association_Contact_name" => $_POST['Association_Contact_name'],
					"Association_Contact_Phone_number" => $_POST['Association_Contact_Phone_number'],
					"Association_Contact_Email" => $_POST['Association_Contact_Email'],
					"Association_catering_nr" => $_POST['Association_catering_nr'],
					//Patrol_subfield
					//Patrol_Points_total
					//Patrol_Points_json
					//Patrol_Position
					//Patrol_Number
					"Patrol_Name" => $patrol['Patrol_Name'],
					"Patrol_Count" => $patrol['Patrol_Count'],
					"Patrol_remark" => $patrol['Patrol_remark'],
					"Patrol_youngest" => $patrol['Patrol_youngest'],
					"Patrol_Avarage_age" => $patrol['Patrol_Avarage_age'],
					"Scout_first_name" => $Scout['Scout_first_name'],
					"Scout_last_name" => $Scout['Scout_last_name'],
					"Scout_birth_date" => $Scout['Scout_birth_date'],
					"Scout_age" => $Scout['Scout_age'],
					"Scout_ScoutNL_number" => $Scout['Scout_ScoutNL_number'],
					"Scout_PL" => $Scout['Scout_PL'],
					"Scout_APL" => $Scout['Scout_APL']
				
				);
				$updated = $wpdb->update( $RSW_Scout_table, $data, array( 'id' => $Scout['id']) );
				if(false === $updated){
					echo 'Error in update id: '.$Scout['id'];
				};
			};
		};
		$_POST = array();
	}elseif(isset($_POST['RSWRemoveRow'])){
		$wpdb->delete( $RSW_Scout_table, array( 'id' => $_POST['RSWRemoveRow'] ) );
	}elseif(isset($_POST['RSWAddRow'])){
		
		$AddScoutData = $wpdb->get_row("
			SELECT 
				* 
			FROM {$RSW_Scout_table} 
			WHERE id = '{$_POST['RSWAddRow']}' 
		");
		
		unset($AddScoutData->id);
		$AddScoutData->Scout_first_name = "";
        $AddScoutData->Scout_last_name = "";
        $AddScoutData->Scout_birth_date = "";
		$AddScoutData->Scout_age = "";
        $AddScoutData->Scout_ScoutNL_number = "";
		$AddScoutData->Scout_PL = 0;
		$AddScoutData->Scout_APL = 0;
		
		$AddScoutArray = get_object_vars($AddScoutData);
		
		$wpdb->insert( $RSW_Scout_table, $AddScoutArray );
		
	}elseif(isset($_POST['RSWRemovePatrol'])){
		// echo $_POST['RSWRemovePatrol'];
		// echo '<br>';
		$TMPScoutData = $wpdb->get_row("
			SELECT 
				* 
			FROM {$RSW_Scout_table} 
			WHERE id = '{$_POST['RSWRemovePatrol']}' 
		");
		$RemovePatrolScouts = $wpdb->get_results("
			SELECT 
				*
			FROM {$RSW_Scout_table} 
			WHERE 
				Year = '$Year' AND 
				Association_Name = '{$TMPScoutData->Association_Name}' AND 
				Association_Sub_name = '{$TMPScoutData->Association_Sub_name}' AND 
				Patrol_Name = '{$TMPScoutData->Patrol_Name}'
		");
		foreach($RemovePatrolScouts as $RemovePatrolScout){
			$wpdb->delete( $RSW_Scout_table, array( 'id' => $RemovePatrolScout->id ) );
			//echo $RemovePatrolScout->Scout_first_name;
			foreach($_POST['Patrol'] as $PatrolNr => $PatrolValue){
				foreach($PatrolValue['Scouts'] as $ScoutNr => $ScoutValue){
					if($ScoutValue['id'] == $RemovePatrolScout->id){
						unset($_POST['Patrol'][$PatrolNr]);
					};
				};
			};
		};
		// print_r($_POST['Patrol']);
		// echo '<br><br>';
	};
	
	$AssociationList = $wpdb->get_results("
		SELECT 
			Association_Name
		FROM {$RSW_Scout_table} 
		WHERE Year = '$Year' 
		GROUP BY Association_Name
	");
	
	if(isset($_POST['Association_Name']) || isset($Association_Name)){
		
		if(isset($_POST['Association_Name'])){
			$TMPAssociationName = $_POST['Association_Name'];
		}else{
			$TMPAssociationName = $Association_Name;
		};
		
		$AssociationSubList = $wpdb->get_results("
			SELECT 
				Association_Sub_name
			FROM {$RSW_Scout_table} 
			WHERE 
				Year = '$Year' AND
				Association_Name = '{$TMPAssociationName}'
			GROUP BY Association_Sub_name
		");
	};
	
	if(isset($Association_Sub_name) && isset($Association_Name)){
		$PatrolList = $wpdb->get_results("
			SELECT 
				id,
				Patrol_subfield,
				Patrol_Points_total,
				Patrol_Points_json,
				Patrol_Position,
				Patrol_Number,
				Patrol_Name,
				Patrol_Count,
				Patrol_remark,
				Patrol_youngest,
				Patrol_Avarage_age
			FROM {$RSW_Scout_table} 
			WHERE Year = '$Year' AND Association_Name = '{$Association_Name}' AND Association_Sub_name = '{$Association_Sub_name}'
			GROUP BY Patrol_Name
		");
	};
	
	echo '<form method="post" id="RSWEditDeelnemerForm">';
		echo '<table>';
			echo '<tr>';
				echo '<td>';
					echo '<label for="Association_Name">Naam vereniging:</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Association_Name" name="Association_Name" list="AssociationNameList" value="';
						if(isset($Association_Name)){
							echo $Association_Name;
						};
					echo '" onchange="this.form.submit()">';
					echo '<datalist id="AssociationNameList">';
						foreach($AssociationList as $Association){
							echo '<option value="'.$Association->Association_Name.'">';
						};
					echo '</datalist>';
				echo '</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>';
					echo '<label for="Association_Name">Naam Groep:</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Association_Sub_name" name="Association_Sub_name" list="AssociationSubNameList" value="';
						if(isset($Association_Sub_name)){
							echo $Association_Sub_name;
						};
					echo '" onchange="this.form.submit()">';
					if(isset($AssociationSubList) && !empty($AssociationSubList)){
						echo '<datalist id="AssociationSubNameList">';
							foreach($AssociationSubList as $AssociationSub){
								echo '<option value="'.$AssociationSub->Association_Sub_name.'">';
							};
						echo '</datalist>';
					};
				echo '</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>';
					echo '<label for="Association_Contact_name">Contactpersoon:</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Association_Contact_name" name="Association_Contact_name" value="';
						if(isset($Association_Contact_name)){
							echo $Association_Contact_name;
						};
					echo '">';
				echo '</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>';
					echo '<label for="Association_Contact_Phone_number">Telefoonnummer:</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Association_Contact_Phone_number" name="Association_Contact_Phone_number" value="';
						if(isset($Association_Contact_Phone_number)){
							echo $Association_Contact_Phone_number;
						};
					echo '">';
				echo '</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<td>';
					echo '<label for="Association_Contact_Email">Email adres:</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Association_Contact_Email" name="Association_Contact_Email" value="';
						if(isset($Association_Contact_Email)){
							echo $Association_Contact_Email;
						};
					echo '">';
				echo '</td>';
			echo '</tr>';
						echo '<tr>';
				echo '<td>';
					echo '<label for="Association_catering_nr">aantal deelnemers catering:</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="number" min="0" max="20" id="Association_catering_nr" name="Association_catering_nr" value="';
						if(isset($Association_catering_nr)){
							echo $Association_catering_nr;
						};
					echo '">';
				echo '</td>';
			echo '</tr>';
		echo '</table>';
		echo '<table>';
			foreach($PatrolList as $PatrolNr => $Patrol){
				echo '<tr>';
					echo '<td>';
						echo '<input type="text" id="Patrol_Name" placeholder="Patrouille Naam" name="Patrol['.$PatrolNr.'][Patrol_Name]" value="';
							if(isset($_POST['Patrol'][$PatrolNr]['Patrol_Name'])){
								echo $_POST['Patrol'][$PatrolNr]['Patrol_Name'];
							}elseif(isset($Patrol->Patrol_Name)){
								echo $Patrol->Patrol_Name;
							};
						echo '">';
					echo '</td>';
					echo '<td>';
						echo '<input type="checkbox" id="Patrol_youngest" placeholder="Patrouille Naam" name="Patrol['.$PatrolNr.'][Patrol_youngest]" value="1" ';
							if(isset($_POST['Patrol'][$PatrolNr]['Patrol_youngest']) && $_POST['Patrol'][$PatrolNr]['Patrol_youngest'] == 1 ){
								echo 'checked';
							}elseif(isset($Patrol->Patrol_youngest) && $Patrol->Patrol_youngest == 1 ){
								echo 'checked';
							};
						echo ' >';
					echo '</td>';
					echo '<td>';
						echo '<button type="submit" class="NoButton" form="RSWEditDeelnemerForm" name="RSWRemovePatrol" value="'.$Patrol->id.'"><span class="dashicons dashicons-remove"></span></button>';
					echo '</td>';
				echo '</tr>';
				
				$ScoutList = $wpdb->get_results("
					SELECT 
						id,
						Scout_first_name,
						Scout_last_name,
						Scout_birth_date,
						Scout_age,
						Scout_ScoutNL_number,
						Scout_PL,
						Scout_APL
					FROM {$RSW_Scout_table} 
					WHERE 
						Year = '$Year' AND 
						Association_Name = '{$Association_Name}' AND 
						Association_Sub_name = '{$Association_Sub_name}' AND 
						Patrol_Name = '{$Patrol->Patrol_Name}'
					ORDER BY Scout_PL DESC,Scout_APL DESC
				");
				
				foreach($ScoutList as $ScoutNR => $Scout){
					echo '<input type="hidden" name="Patrol['.$PatrolNr.'][Scouts]['.$ScoutNR.'][id]" value="'.$Scout->id.'">';
					echo '<tr>';
						echo '<td>';
							echo '<input type="text" id="Patrol_youngest" placeholder="Voornaam" name="Patrol['.$PatrolNr.'][Scouts]['.$ScoutNR.'][Scout_first_name]" value="';
								if(isset($_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_first_name'])){
									echo $_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_first_name'];
								}elseif(isset($Scout->Scout_first_name)){
									echo $Scout->Scout_first_name;
								};
						echo '">';
						echo '</td>';
						echo '<td>';
							echo '<input type="text" id="Patrol_youngest" placeholder="Achternaam" name="Patrol['.$PatrolNr.'][Scouts]['.$ScoutNR.'][Scout_last_name]" value="';
								if(isset($_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_last_name'])){
									echo $_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_last_name'];
								}elseif(isset($Scout->Scout_last_name)){
									echo $Scout->Scout_last_name;
								};
						echo '">';
						echo '</td>';
						echo '<td>';
							echo '<input type="date" id="Patrol_youngest" placeholder="Achternaam" name="Patrol['.$PatrolNr.'][Scouts]['.$ScoutNR.'][Scout_birth_date]" value="';
								if(isset($_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_birth_date'])){
									echo $_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_birth_date'];
								}elseif(isset($Scout->Scout_birth_date)){
									echo $Scout->Scout_birth_date;
								};
						echo '">';
						echo '</td>';
						echo '<td>';
							echo '<input type="text" id="Patrol_youngest" placeholder="Achternaam" name="Patrol['.$PatrolNr.'][Scouts]['.$ScoutNR.'][Scout_ScoutNL_number]" value="';
								if(isset($_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_ScoutNL_number'])){
									echo $_POST['Patrol'][$PatrolNr]['Scouts'][$ScoutNR]['Scout_ScoutNL_number'];
								}elseif(isset($Scout->Scout_ScoutNL_number)){
									echo $Scout->Scout_ScoutNL_number;
								};
						echo '">';
						echo '</td>';
						echo '<td>';
							echo '<input type="radio" name="Patrol['.$PatrolNr.'][PL]" value="';
								echo $Scout->id;
								echo '" ';
								if(isset($_POST['Patrol'][$PatrolNr]['PL']) && $_POST['Patrol'][$PatrolNr]['PL'] == $Scout->id){
									echo "checked";
								}elseif(isset($Scout->Scout_PL) && $Scout->Scout_PL == 1){
									echo "checked";
								};
						echo ' >';
						echo '</td>';
						echo '<td>';
							echo '<input type="radio" name="Patrol['.$PatrolNr.'][APL]" value="';
								echo $Scout->id;
								echo '" ';
								if(isset($_POST['Patrol'][$PatrolNr]['APL']) && $_POST['Patrol'][$PatrolNr]['APL'] == $Scout->id){
									echo "checked";
								}elseif(isset($Scout->Scout_APL) && $Scout->Scout_APL == 1){
									echo "checked";
								};
							echo ' >';
						echo '</td>';
						echo '<td>';
							echo '<button type="submit" class="NoButton" form="RSWEditDeelnemerForm" name="RSWRemoveRow" value="'.$Scout->id.'"><span class="dashicons dashicons-remove"></span></button>';
						echo '</td>';
						echo '<td>';
							echo '<button type="submit" class="NoButton" form="RSWEditDeelnemerForm" name="RSWAddRow" value="'.$Scout->id.'"><span class="dashicons dashicons-insert"></span></button>';
						echo '</td>';
					echo '</tr>';
				};
				echo '<tr>';
					echo '<td colspan="4">';
						echo '<textarea id="Patrol_remark" placeholder="Patrouille opmerking" name="Patrol['.$PatrolNr.'][Patrol_remark]" rows="4" cols="100">';
						if(isset($_POST['Patrol'][$PatrolNr]['Patrol_remark'])){
								echo $_POST['Patrol'][$PatrolNr]['Patrol_remark'];
							}elseif(isset($Patrol->Patrol_remark)){
								echo $Patrol->Patrol_remark;
							};
						echo '</textarea>';
					echo '</td>';
				echo '</tr>';
			};
		echo '</table>';
		echo '<input type="submit" id="Form-submit" class="button" name="RSWupdate" value="updaten"  />';
		echo '<input type="hidden" name="Year" value="'.$Year.'">';
	echo '</form>';
	
};
?>