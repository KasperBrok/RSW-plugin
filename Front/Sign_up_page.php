<?php

add_shortcode('RSW_Sign_up_Page', 'RSWSignUpPagina');

function RSWSignUpPagina(){
	ob_start();
	global $wpdb;

	global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	global $RSW_Settings_table;
	
	global $RSW_Sign_up_Page_Name;
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	
	$sign_up_code = $wpdb->get_var( "SELECT Sign_up_code FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	$Sign_up_date = $wpdb->get_var( "SELECT Sign_up_date FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	$Catering_date = $wpdb->get_var( "SELECT Catering_date FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	$Subfield_date = $wpdb->get_var( "SELECT Subfield_date FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	$Association_data = json_decode($wpdb->get_var("SELECT data FROM $RSW_Settings_table WHERE name='Association'"),true);
	
	global $RSW_passed_Sign_up_date;
	global $RSW_passed_Catering_date;
	global $RSW_passed_Subfield_date;
	$RSW_passed_Sign_up_date = true;
	$RSW_passed_Catering_date = true;
	$RSW_passed_Subfield_date = true;
	
	if(empty($Sign_up_date)){
		$RSW_passed_Sign_up_date = false;
	}else{
		if(strtotime(date('Y-m-d', strtotime($Sign_up_date) ) ) > strtotime(date('Y-m-d'))){
			$RSW_passed_Sign_up_date = false;
		};
	};
	
	if(empty($Catering_date)){
		$RSW_passed_Catering_date = false;
	}else{
		if(strtotime(date('Y-m-d', strtotime($Catering_date) ) ) > strtotime(date('Y-m-d'))){
			$RSW_passed_Catering_date = false;
		};
	};
	
	if(empty($Subfield_date)){
		$RSW_passed_Subfield_date = false;
	}else{
		if(strtotime(date('Y-m-d', strtotime($Subfield_date) ) ) > strtotime(date('Y-m-d'))){
			$RSW_passed_Subfield_date = false;
		};
	};
	
	$current_user = wp_get_current_user();
	if (user_can( $current_user, 'administrator' )) {
	  $RSW_passed_Sign_up_date = false;
	  $RSW_passed_Catering_date = false;
	  $RSW_passed_Subfield_date = false;
	}
	
	$Sign_up_data = array();
	
	if(isset($_POST['inschrijving'])){
		if(isset($_POST['inschrijving']['Association_Name']))$Sign_up_data['Association_Name'] = $_POST['inschrijving']['Association_Name'];
		if(isset($_POST['inschrijving']['Association_Sub_name']))$Sign_up_data['Association_Sub_name'] = $_POST['inschrijving']['Association_Sub_name'];
		if(isset($_POST['inschrijving']['Association_sign_op_code']))$Sign_up_data['Association_sign_op_code'] = $_POST['inschrijving']['Association_sign_op_code'];
	};
	if(isset($_GET['vereniging']))$Sign_up_data['Association_Name'] = $_GET['vereniging'];
	if(isset($_GET['groep']))$Sign_up_data['Association_Sub_name'] = $_GET['groep'];
	if(isset($_GET['groep-code']))$Sign_up_data['Association_sign_op_code'] = $_GET['groep-code'];
	
	if(isset($_GET['code'])){
		$given_sign_op_code = $_GET['code'];
	}elseif(isset($_POST['code'])){
		$given_sign_op_code = $_POST['code'];
	};
	
	
	global $wp;
	$page_url = home_url( $wp->request );
	
	
	if(isset($_POST['RSW_form_action']) && ($_POST['RSW_form_action'] == "Updaten" || $_POST['RSW_form_action'] == "Inschrijven")){
		echo'<div id="RSW_Sign_up_Dialog">';
		echo 'Weet je zeker dat alle gegevens kloppen?';
		echo'</div>';
	};
	
	
	echo'<form method="post" id="RSW_Sign_up_form">';
	echo'<input type="hidden" name="RSW_Active_url" value="'.$page_url.'">';
	if(isset($given_sign_op_code) && $given_sign_op_code == $sign_up_code){
		echo'<input type="hidden" name="code" value="'.$given_sign_op_code.'">';
		echo'<table>';
		echo'<col style="width: 20%;">';
        echo'<col style="width: 80%;">';
		echo'<tr>';
		echo'<td>';
		echo'<label for="inschrijving[Association_Name]">vereniging: </label>';
		echo'</td>';
		echo'<td>';
		echo'<select name="inschrijving[Association_Name]" onchange="this.form.submit()">';
		if(!isset($Sign_up_data['Association_Name']))echo'	<option value="empty" selected></option>';
		foreach($Association_data as $Association){
			echo'	<option value="'.$Association['Association_Name'].'" ';
			if(isset($Sign_up_data['Association_Name']) && $Sign_up_data['Association_Name'] == $Association['Association_Name'])echo'selected';
			echo'>'.$Association['Association_Name'].'</option>';
		};
		echo'</select>';
		echo'</td>';
		echo'</tr>';
		echo'</table>';
		
		if(isset($Sign_up_data['Association_Name']) && $Sign_up_data['Association_Name'] != "empty"){
			echo'<table>';
			echo'<col style="width: 20%;">';
			echo'<col style="width: 80%;">';
			echo'<tr>';
			echo'<td>';
			echo'<label for="inschrijving[Association_Sub_name]">Groep: </label>';
			echo'</td>';
			echo'<td>';
			echo'<select name="inschrijving[Association_Sub_name]" onchange="this.form.submit()">';
			foreach($Association_data as $Association){
				if($Association['Association_Name'] == $Sign_up_data['Association_Name']){
					if(is_array($Association['Association_Sub_name'])){
						if(!isset($Sign_up_data['Association_Sub_name']) || !in_array($Sign_up_data['Association_Sub_name'],$Association['Association_Sub_name'])){
							echo'	<option value="empty" selected></option>';
							unset($Sign_up_data['Association_Sub_name']);
						};
						
						foreach($Association['Association_Sub_name'] as $Sub_Association){
							echo'	<option value="'.$Sub_Association.'" ';
							if(isset($Sign_up_data['Association_Sub_name']) && $Sign_up_data['Association_Sub_name'] == $Sub_Association)echo'selected';
							echo'>'.$Sub_Association.'</option>';
						};
					}else{
						echo'	<option value="'.$Association['Association_Sub_name'].'" selected>'.$Association['Association_Sub_name'].'</option>';
						$Sign_up_data['Association_Sub_name'] = $Association['Association_Sub_name'];
					};
				};
			};
			echo'</select>';
			echo'</td>';
			echo'</tr>';
			echo'</table>';
		};
		
		if(isset($Sign_up_data['Association_Sub_name']) && isset($Sign_up_data['Association_Name'])){
			
			$Nr_Scouts = $wpdb->get_var("
				SELECT 
					COUNT(*) 
				FROM {$RSW_Scout_table} 
				WHERE 
					Year = '$Year' 
				AND 
					Association_Name = '{$Sign_up_data['Association_Name']}'
				AND 
					Association_Sub_name = '{$Sign_up_data['Association_Sub_name']}'
			");
			
			$Association_sign_op_code = $wpdb->get_var("
				SELECT 
					Association_sign_op_code 
				FROM {$RSW_Scout_table} 
				WHERE 
					Year = '$Year' 
				AND 
					Association_Name = '{$Sign_up_data['Association_Name']}'
				AND 
					Association_Sub_name = '{$Sign_up_data['Association_Sub_name']}'
			");
			
			if (user_can( $current_user, 'administrator' )) {
				$Sign_up_data['Association_sign_op_code'] = $Association_sign_op_code;
			}
			
			if($Nr_Scouts == 0){
				$Enable_Sign_up = true;
			}elseif(isset($Sign_up_data['Association_sign_op_code']) && $Association_sign_op_code == $Sign_up_data['Association_sign_op_code']){
				$Enable_Sign_up = true;
				echo'<input type="hidden" name="inschrijving[Association_sign_op_code]" value="'.$Sign_up_data['Association_sign_op_code'].'">';
			}else{
				echo'<br><br>';
				$Enable_Sign_up = false;
				echo'De '.$Sign_up_data['Association_Sub_name']. 'van de '.$Sign_up_data['Association_Name'].' zijn al ingeschreven!<br><br>';
				echo'Als je de inschrijving wilt wijzigen, geef dan de wijzigings code op:<br>';
				if(isset($Sign_up_data['Association_sign_op_code']))echo 'Deze Login code is onjuist!';
				echo'<br>';
				echo'<input type="text" name="inschrijving[Association_sign_op_code]"';
				if(isset($Sign_up_data['Association_sign_op_code']))echo ' value="'.$Sign_up_data['Association_sign_op_code'].'" ';
				echo'>';
				echo'<br><input type="submit" name="RSW_form_action" value="aanmelden">';
			};
			
			if($Enable_Sign_up == true){
				if(isset($_POST['inschrijving']["patrols"])){
					$Sign_up_data = $_POST['inschrijving'];
				}elseif($Nr_Scouts != 0){
					$Sign_up_data["patrols"] = json_decode(json_encode($wpdb->get_results("
						SELECT 
							Patrol_subfield,
							Patrol_Points_total,
							Patrol_Points_json,
							Patrol_Spelmiddag_json,
							Patrol_Position,
							Patrol_Number,
							Patrol_Name,
							Patrol_Count,
							Patrol_remark,
							Patrol_youngest,
							Patrol_Avarage_age
						FROM {$RSW_Scout_table} 
						WHERE 
							Year = '$Year' 
						AND 
							Association_Name = '{$Sign_up_data['Association_Name']}'
						AND 
							Association_Sub_name = '{$Sign_up_data['Association_Sub_name']}'
						GROUP BY
							Patrol_Name
					")), true);
					
					$association = json_decode(json_encode($wpdb->get_row("
						SELECT 
							Association_acronym,
							Association_Contact_name,
							Association_Contact_Phone_number,
							Association_Contact_Email,
							Association_catering_nr
						FROM {$RSW_Scout_table} 
						WHERE 
							Year = '$Year' 
						AND 
							Association_Name = '{$Sign_up_data['Association_Name']}'
						AND 
							Association_Sub_name = '{$Sign_up_data['Association_Sub_name']}'
						GROUP BY
							Association_Name,
							Association_Sub_name
					")), true);
					
					$Sign_up_data = array_merge($Sign_up_data,$association);
					
					$scouts = json_decode(json_encode($wpdb->get_results("
						SELECT 
							Patrol_Name,
							Scout_first_name,
							Scout_last_name,
							Scout_birth_date,
							Scout_age,
							Scout_ScoutNL_number,
							Scout_PL,
							Scout_APL
						FROM {$RSW_Scout_table} 
						WHERE 
							Year = '$Year' 
						AND 
							Association_Name = '{$Sign_up_data['Association_Name']}'
						AND 
							Association_Sub_name = '{$Sign_up_data['Association_Sub_name']}'
					")), true);
					
					foreach($scouts as $scout){
						foreach($Sign_up_data["patrols"] as $PatrolNr => $patrol){
							if($patrol['Patrol_Name'] == $scout['Patrol_Name']){
								if(!isset($Sign_up_data["patrols"][$PatrolNr]['scouts']))$Sign_up_data["patrols"][$PatrolNr]['scouts'] = array();
								array_push($Sign_up_data["patrols"][$PatrolNr]['scouts'],$scout);
							};
						};
					};
				}else{
					$Sign_up_data["patrols"] = array(
						array(
							//'Patrol_Name' => 'groepsnaam'
							'scouts' => array(
								array(),
								array(),
								array(),
								array(),
								array()
							)
						)
					);
				};
				
				echo'<table>';
				echo'<col style="width: 20%;">';
				echo'<col style="width: 80%;">';
				echo'<tr>';
				echo'<td>';
				echo'<label for="inschrijving[Association_Contact_name]">Contact persoon:</label>';
				echo'</td>';
				echo'<td>';
				echo'<input type="text" required name="inschrijving[Association_Contact_name]"';
				if(isset($Sign_up_data['Association_Contact_name']))echo'value="'.$Sign_up_data['Association_Contact_name'].'"';
				if($RSW_passed_Sign_up_date)echo' disabled ';
				echo'>';
				echo'</td>';
				echo'</tr>';
				
				echo'<tr>';
				echo'<td>';
				echo'<label for="inschrijving[Association_Contact_Phone_number]">contact telefoon nummer:</label>';
				echo'</td>';
				echo'<td>';
				echo'<input type="tel" name="inschrijving[Association_Contact_Phone_number]"';
				if(isset($Sign_up_data['Association_Contact_Phone_number']))echo'value="'.$Sign_up_data['Association_Contact_Phone_number'].'"';
				if($RSW_passed_Sign_up_date)echo' disabled ';
				echo'>';
				echo'</td>';
				echo'</tr>';
				
				echo'<tr>';
				echo'<td>';
				echo'<label for="inschrijving[Association_Contact_Email]">Email:</label>';
				echo'</td>';
				echo'<td>';
				echo'<input type="email" required name="inschrijving[Association_Contact_Email]"';
				if(isset($Sign_up_data['Association_Contact_Email']))echo'value="'.$Sign_up_data['Association_Contact_Email'].'"';
				if($RSW_passed_Sign_up_date)echo' disabled ';
				echo'>';
				echo'</td>';
				echo'</tr>';
				
				echo'<tr>';
				echo'<td>';
				echo'<label for="inschrijving[Association_catering_nr]">Catering:</label>';
				echo'</td>';
				echo'<td>';
				echo'<input type="number" name="inschrijving[Association_catering_nr]"';
				if(isset($Sign_up_data['Association_catering_nr'])){
					echo'value="'.$Sign_up_data['Association_catering_nr'].'"';
				}else{
					echo'value="0"';
				};
				if($RSW_passed_Sign_up_date || $RSW_passed_Catering_date)echo' disabled ';
				echo'>';
				echo'</td>';
				echo'</tr>';
				
				echo'<input type="hidden" name="inschrijving[Association_acronym]"';
				foreach($Association_data as $Association){
					if($Association['Association_Name'] == $Sign_up_data['Association_Name']){
						echo'value="'.$Association['Association_acronym'].'"';
					};
				};
				echo'>';
				
				echo'</table>';
				
				foreach($Sign_up_data['patrols'] as $patrol){
					RSW_Patrol_Row($patrol);
				};
				
				echo'<br><input type="submit" name="RSW_form_action" value="';
				if(isset($Sign_up_data['Association_sign_op_code']) && $Association_sign_op_code == $Sign_up_data['Association_sign_op_code']){
					echo'Updaten';
				}else{
					echo'Inschrijven';
				};
				echo '"';
				if($RSW_passed_Sign_up_date)echo' disabled ';
				echo '>';
			};
		};
	}else{
		echo'De aanmeld code otbreekt of is onjuist.<br>';
		echo'<input type="text" name="code"';
		if(isset($given_sign_op_code)){
			echo'value="'.$given_sign_op_code.'"';
		};
		echo'><br>';
		echo'<br><input type="submit" name="RSW_form_action" value="Aanmelden">';
	};
	
	echo'</form> ';
	
	$html_form = ob_get_clean();
    return $html_form;
};

function RSW_Scout_Row($scout = array(),$PatrolNr = ""){
	global $RSW_passed_Sign_up_date;
	global $RSW_passed_Catering_date;
	global $RSW_passed_Subfield_date;
	
	if(isset($_POST["Jquery_RSW_PatrolNr"]))$PatrolNr = $_POST["Jquery_RSW_PatrolNr"];
	
	echo'<tr ';
	if(!isset($_POST["Jquery_RSW_PatrolNr"]))echo'style="display:none;"';
	echo'>';
	
	if(isset($scout['TMPID'])){
		$scoutNr = $scout['TMPID'];
	}else{
		$scoutNr = rand();
	};
	
	echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][scouts]['.$scoutNr.'][TMPID]" value="'.$scoutNr.'">';
	
	echo'<td>';
	echo'<input type="text" name="inschrijving[patrols]['.$PatrolNr.'][scouts]['.$scoutNr.'][Scout_first_name]"';
	if(isset($scout['Scout_first_name']))echo'value="'.$scout['Scout_first_name'].'"';
	if($RSW_passed_Sign_up_date)echo' disabled ';
	echo'>';
	echo'</td>';
	
	echo'<td>';
	echo'<input type="text" name="inschrijving[patrols]['.$PatrolNr.'][scouts]['.$scoutNr.'][Scout_last_name]"';
	if(isset($scout['Scout_last_name']))echo'value="'.$scout['Scout_last_name'].'"';
	if($RSW_passed_Sign_up_date)echo' disabled ';
	echo'>';
	echo'</td>';
	
	echo'<td>';
	echo'<input type="date" name="inschrijving[patrols]['.$PatrolNr.'][scouts]['.$scoutNr.'][Scout_birth_date]"';
	if(isset($scout['Scout_birth_date']))echo'value="'.$scout['Scout_birth_date'].'"';
	if($RSW_passed_Sign_up_date)echo' disabled ';
	echo'>';
	echo'</td>';
	
	echo'<td>';
	echo'<input type="text" name="inschrijving[patrols]['.$PatrolNr.'][scouts]['.$scoutNr.'][Scout_ScoutNL_number]"';
	if(isset($scout['Scout_ScoutNL_number']))echo'value="'.$scout['Scout_ScoutNL_number'].'"';
	if($RSW_passed_Sign_up_date)echo' disabled ';
	echo'>';
	echo'</td>';
	
	echo'<td>';
	echo'<input type="radio" class="RSW_Radio_PL_'.$PatrolNr.'" name="inschrijving[patrols]['.$PatrolNr.'][scouts]['.$scoutNr.'][Scout_PL]"';
	if(isset($scout['Scout_PL']) && $scout['Scout_PL'] == 1){
		if(!(isset($scout['Scout_APL']) && $scout['Scout_APL'] == 1)){
			echo' checked ';
		};
	}else{
		if(isset($scout['Scout_APL']) && $scout['Scout_APL'] == 1){
			echo' disabled ';
		};
	};
	if($RSW_passed_Sign_up_date)echo' disabled ';
	echo' value="1">';
	echo'</td>';
	
	echo'<td>';
	echo'<input type="radio" class="RSW_Radio_APL_'.$PatrolNr.'" name="inschrijving[patrols]['.$PatrolNr.'][scouts]['.$scoutNr.'][Scout_APL]"';
	if(isset($scout['Scout_APL']) && $scout['Scout_APL'] == 1){
		if(!(isset($scout['Scout_PL']) && $scout['Scout_PL'] == 1)){
			echo' checked ';
		};
	}else{
		if(isset($scout['Scout_PL']) && $scout['Scout_PL'] == 1){
			echo' disabled ';
		};
	};
	if($RSW_passed_Sign_up_date)echo' disabled ';
	echo' value="1">';
	echo'</td>';
	
	echo'<td class="';
	if(!($RSW_passed_Sign_up_date))echo' RSW_add ';
	echo'RSW_action" data-item="scout" data-patrolnr="'.$PatrolNr.'" data-ScoutNr="'.$scoutNr.'">';
	echo'<span class="dashicons dashicons-plus-alt"> </span>';
	echo'</td>';
	
	echo'<td class="';
	if(!($RSW_passed_Sign_up_date))echo' RSW_remove ';
	echo'RSW_action" data-item="scout" data-patrolnr="'.$PatrolNr.'" data-ScoutNr="'.$scoutNr.'">';
	echo'<span class="dashicons dashicons-remove"> </span>';
	echo'</td>';
	
	echo'<td class="';
	if(!($RSW_passed_Sign_up_date))echo' RSW_move ';
	echo'RSW_action">';
	echo'<span class="dashicons dashicons-move"></span>';
	echo'</td>';
	
	echo'</tr>';
};

function RSW_Patrol_Row($patrol = array()){
	global $RSW_passed_Sign_up_date;
	global $RSW_passed_Catering_date;
	global $RSW_passed_Subfield_date;
	
	if(isset($patrol['TMPID'])){
		$PatrolNr = $patrol['TMPID'];
	}else{
		$PatrolNr = rand();
	};
	
	echo'<table class="RSW_Patrol_table" data-patrolnr="'.$PatrolNr.'">';
	
	echo'<col style="width:20%">';
	echo'<col style="width:20%">';
	echo'<col style="width:20%">';
	echo'<col style="width:20%">';
	echo'<col style="width:4%">';
	echo'<col style="width:4%">';
	echo'<col style="width:4%">';
	echo'<col style="width:4%">';
	echo'<col style="width:4%">';

	echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][TMPID]" value="'.$PatrolNr.'">';
	
	if(isset($patrol['Patrol_subfield'])){
		echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_subfield]" value="'.$patrol['Patrol_subfield'].'">';
	};
	
	if(isset($patrol['Patrol_Points_total'])){
		echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_Points_total]" value="'.$patrol['Patrol_Points_total'].'">';
	};
	
	if(isset($patrol['Patrol_Points_json'])){
		echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_Points_json]" value="'.htmlentities($patrol['Patrol_Points_json']).'">';
	};
	
	if(isset($patrol['Patrol_Spelmiddag_json'])){
		echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_Spelmiddag_json]" value="'.htmlentities($patrol['Patrol_Spelmiddag_json']).'">';
	};
	
	if(isset($patrol['Patrol_Position'])){
		echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_Position]" value="'.$patrol['Patrol_Position'].'">';
	};
	
	if(isset($patrol['Patrol_Number'])){
		echo'<input type="hidden" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_Number]" value="'.$patrol['Patrol_Number'].'">';
	};
	
	echo'<tr class="RSW_header">';
	
	echo'<th >';
	echo'<label for="inschrijving[patrols]['.$PatrolNr.'][Patrol_Name]">Patrouille:</label>';
	echo'</th>';
	
	echo'<th>';
	echo'<input type="text" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_Name]"';
	if(isset($patrol['Patrol_Name']))echo'value="'.$patrol['Patrol_Name'].'"';
	if($RSW_passed_Sign_up_date || $RSW_passed_Subfield_date)echo' disabled ';
	echo'>';
	echo'</th>';
	
	echo'<th colspan="2">';
	echo'</th>';
	
	echo'<th colspan="2">';
	echo'<label for="inschrijving[patrols]['.$PatrolNr.'][Patrol_youngest]">Jongste: </label>';
	echo'<input type="checkbox" name="inschrijving[patrols]['.$PatrolNr.'][Patrol_youngest]"';
	if(isset($patrol['Patrol_youngest']) && $patrol['Patrol_youngest'] == 1)echo' checked ';
	if($RSW_passed_Sign_up_date || $RSW_passed_Subfield_date)echo' disabled ';
	echo' value="1" >';
	echo'</th>';
	
	echo'<th rowspan="2" class="';
	if(!($RSW_passed_Sign_up_date || $RSW_passed_Subfield_date))echo' RSW_add ';
	echo'RSW_action" data-item="patrol" data-patrolnr="'.$PatrolNr.'">';
	echo'<span class="dashicons dashicons-plus-alt"> </span>';
	echo'</th>';
	
	echo'<th rowspan="2" class="';
	if(!($RSW_passed_Sign_up_date || $RSW_passed_Subfield_date))echo' RSW_remove ';
	echo'RSW_action" data-item="patrol" data-patrolnr="'.$PatrolNr.'">';
	echo'<span class="dashicons dashicons-remove"> </span>';
	echo'</th>';
	
	echo'<th rowspan="2" class="RSW_expand RSW_action">';
	echo'<span class="dashicons dashicons-arrow-down-alt2"></span>';
	echo'</th>';
	
	echo'</tr>';
	
	echo'<tr style="display:none;">';
	
	echo'<td>';
	echo'Voornaam';
	echo'</td>';
	
	echo'<td>';
	echo'Achternaam';
	echo'</td>';
	
	echo'<td>';
	echo'Geboortedatum';
	echo'</td>';
	
	echo'<td>';
	echo'Scout NL nummer';
	echo'</td>';
	
	echo'<td>';
	echo'PL';
	echo'</td>';
	
	echo'<td>';
	echo'APL';
	echo'</td>';
	
	echo'</tr>';
	
	echo'<tbody>';
	
	if(isset($patrol['scouts']) && count($patrol['scouts']) != 0){
		foreach($patrol['scouts'] as $scout){
			RSW_Scout_Row($scout,$PatrolNr);
		};
	}else{
		for ($x = 0; $x <= 5; $x++) {
			RSW_Scout_Row(array(),$PatrolNr);
		} 
	};
	
	echo'<tr style="display:none;">';
	
	echo'<td>';
	echo'Opmerking';
	echo'</td>';
	
	echo'<td colspan="8">';
	echo'<textarea name="inschrijving[patrols]['.$PatrolNr.'][Patrol_remark]" ';
	if($RSW_passed_Sign_up_date)echo' disabled ';
	echo'>';
	if(isset($patrol['Patrol_remark']))echo $patrol['Patrol_remark'];
	echo'</textarea>';
	echo'</td>';
	
	echo'</tr>';
	
	echo'</tbody>';
	echo'</table>';
};

Function RSW_handle_Sign_up(){
	if(isset($_POST["formdata"])){
		$FormData = array();
		parse_str($_POST["formdata"], $FormData);
	}
	
	global $wpdb;

	global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	global $RSW_Settings_table;
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	$LSW_date = $wpdb->get_var( "SELECT LSW_date FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	
	$scout_array = array();
	
	if(!isset($FormData['inschrijving']['Association_sign_op_code']) || empty($FormData['inschrijving']['Association_sign_op_code'])){
		$FormData['inschrijving']['Association_sign_op_code'] = wp_generate_password(20,false);
	};
	
	if(isset($FormData['inschrijving']['patrols'])){
		foreach($FormData['inschrijving']['patrols'] as $PatrolID => $Patrol){
			$HasScouts = false;
			
			$NrOfScouts = 0;
			$AgeTotal = 0;
			
			foreach($FormData['inschrijving']['patrols'][$PatrolID]['scouts'] as $ScoutID => $Scout){
				if(isset($Scout['Scout_first_name']) && !empty($Scout['Scout_first_name'])){
					$HasScouts = true;
					
					$diff = date_diff(date_create($Scout['Scout_birth_date']), date_create($LSW_date));
					$FormData['inschrijving']['patrols'][$PatrolID]['scouts'][$ScoutID]['Scout_age'] = $diff->format('%y');
					$AgeTotal = $AgeTotal + $diff->format('%y');
					$NrOfScouts++;
				}
			};
			
			$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_Count'] = $NrOfScouts;
			$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_Avarage_age'] = $AgeTotal / $NrOfScouts;
			
			$Patrol_Spelmiddag_json = str_replace("\\","",$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_Spelmiddag_json']);
			$Patrol_Points_json = str_replace("\\","",$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_Points_json']);
			
			//Print_r($Patrol_Spelmiddag_json);
			
			if(!$HasScouts){
				array_push($scout_array,array(
					'Year' => $Year,
					'Association_Sub_name' => $FormData['inschrijving']['Association_Sub_name'],
					'Association_Contact_name' => $FormData['inschrijving']['Association_Contact_name'],
					'Association_Contact_Phone_number' => $FormData['inschrijving']['Association_Contact_Phone_number'],
					'Association_Contact_Email' => $FormData['inschrijving']['Association_Contact_Email'],
					'Association_catering_nr' => $FormData['inschrijving']['Association_catering_nr'],
					'Association_acronym' => $FormData['inschrijving']['Association_acronym'],
					'Association_sign_op_code' => $FormData['inschrijving']['Association_sign_op_code'],
					'Patrol_subfield' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_subfield],
					'Patrol_Points_total' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Points_total],
					'Patrol_Points_json' => $Patrol_Points_json,
					'Patrol_Spelmiddag_json' => $Patrol_Spelmiddag_json,
					'Patrol_Position' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Position],
					'Patrol_Number' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Number],
					'Patrol_Name' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Name],
					//'Patrol_Count' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Count],
					'Patrol_remark' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_remark],
					'Patrol_youngest' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_youngest],
					//'Patrol_Avarage_age' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Avarage_age],
					//'Scout_first_name' => $Scout[Scout_first_name],
					//'Scout_last_name' => $Scout[Scout_last_name],
					//'Scout_birth_date' => $Scout[Scout_birth_date],
					//'Scout_age' => $Scout[Scout_age],
					//'Scout_ScoutNL_number' => $Scout[Scout_ScoutNL_number],
					//'Scout_PL' => $Scout[Scout_PL],
					//'Scout_APL' => $Scout[Scout_APL],
					'Association_Name' => $FormData['inschrijving']['Association_Name']
				));
			}else{
				foreach($FormData['inschrijving']['patrols'][$PatrolID]['scouts'] as $Scout){
					if(isset($Scout['Scout_first_name']) && !empty($Scout['Scout_first_name'])){
						
						array_push($scout_array,array(
							'Year' => $Year,
							'Association_Sub_name' => $FormData['inschrijving']['Association_Sub_name'],
							'Association_Contact_name' => $FormData['inschrijving']['Association_Contact_name'],
							'Association_Contact_Phone_number' => $FormData['inschrijving']['Association_Contact_Phone_number'],
							'Association_Contact_Email' => $FormData['inschrijving']['Association_Contact_Email'],
							'Association_catering_nr' => $FormData['inschrijving']['Association_catering_nr'],
							'Association_acronym' => $FormData['inschrijving']['Association_acronym'],
							'Association_sign_op_code' => $FormData['inschrijving']['Association_sign_op_code'],
							'Patrol_subfield' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_subfield],
							'Patrol_Points_total' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Points_total],
							'Patrol_Points_json' => $Patrol_Points_json,
							'Patrol_Spelmiddag_json' => $Patrol_Spelmiddag_json,
							'Patrol_Position' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Position],
							'Patrol_Number' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Number],
							'Patrol_Name' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Name],
							'Patrol_Count' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Count],
							'Patrol_remark' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_remark],
							'Patrol_youngest' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_youngest],
							'Patrol_Avarage_age' => $FormData['inschrijving']['patrols'][$PatrolID][Patrol_Avarage_age],
							'Scout_first_name' => $Scout[Scout_first_name],
							'Scout_last_name' => $Scout[Scout_last_name],
							'Scout_birth_date' => $Scout[Scout_birth_date],
							'Scout_age' => $Scout[Scout_age],
							'Scout_ScoutNL_number' => $Scout[Scout_ScoutNL_number],
							'Scout_PL' => $Scout[Scout_PL],
							'Scout_APL' => $Scout[Scout_APL],
							'Association_Name' => $FormData['inschrijving']['Association_Name']
						));
					};
				};
			};
		};
		$wpdb->delete( $RSW_Scout_table, array( 'Association_Name' => $FormData['inschrijving']['Association_Name'], 'Association_Sub_name' => $FormData['inschrijving']['Association_Sub_name'] ) );
		foreach($scout_array as $scout){
			$wpdb->insert( $RSW_Scout_table, $scout );
		};
		
		$wp_upload_dir = wp_upload_dir();
		$FileName = $Year.'_'.$FormData['inschrijving']['Association_Name'].'_'.$FormData['inschrijving']['Association_Sub_name'].'.pdf';
		$FileName = str_replace(" ","_",$FileName);
		$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
		$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
		
		$pdf = new GroepenlijstPDF();
		$pdf->AddPage();
		
		$pdf->SetFont('Arial','B',20);
		$pdf->Cell(150,20,'Bewijs van inschrijven',0,1);
		
		$pdf->SetFont('Arial','',12);
		
		$pdf->Cell(40,6,'Vereniging:');
		if($FormData['inschrijving']['Association_catering_nr'] != 0){
			$pdf->Cell(110,6,$FormData['inschrijving']['Association_Name']." ".$FormData['inschrijving']['Association_Sub_name']);
			$pdf->Cell(40,6,'Catering: '.$FormData['inschrijving']['Association_catering_nr'],0,1);
		}else{
			$pdf->Cell(150,6,$FormData['inschrijving']['Association_Name']." ".$FormData['inschrijving']['Association_Sub_name'],0,1);
		};
		$pdf->Cell(40,6,'Contactpersoon:');
		$pdf->Cell(150,6,$FormData['inschrijving']['Association_Contact_name'],0,1);
		$pdf->Cell(40,6,'Telefoon nmmer:');
		$pdf->Cell(150,6,$FormData['inschrijving']['Association_Contact_Phone_number'],0,1);
		$pdf->Cell(40,6,'Email adres:');
		$pdf->Cell(150,6,$FormData['inschrijving']['Association_Contact_Email'],0,1);
		
		$pdf->Cell(190,5,"","T",1);
		
		foreach($FormData['inschrijving']['patrols'] as $PatrolID => $Patrol){
			$pdf->Cell(50,6,'Naam: '.$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_Name'],1);
			$pdf->Cell(30,6,'Nummer: '.$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_Number'],1);
			$pdf->Cell(30,6,'Aantal: '.$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_Count'],1);
			if($FormData['inschrijving']['patrols'][$PatrolID]['Patrol_youngest'] == 1){
				$pdf->Cell(40,6,'Subkamp: '.$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_subfield'],1);
				$pdf->Cell(40,6,'Jongste',1,1);
			}else{
				$pdf->Cell(80,6,'Subkamp: '.$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_subfield'],1,1);
			};
			$pdf->Cell(190,1,'',1,1);
			
			$pdf->Cell(20,6,"",1);
			$pdf->Cell(60,6,"Naam",1);
			$pdf->Cell(40,6,"Geboortedatum",1);
			$pdf->Cell(30,6,"Leeftijd",1);
			$pdf->Cell(40,6,"ScoutNL Nr.",1,1);
			
			foreach($FormData['inschrijving']['patrols'][$PatrolID]['scouts'] as $Scout){
				if($Scout['Scout_PL'] == 1){
					$pdf->Cell(20,6,"PL/RL",1);
				}elseif($Scout['Scout_APL'] == 1){
					$pdf->Cell(20,6,"APL/ARL",1);
				}else{
					$pdf->Cell(20,6,"",1);
				};
				$pdf->Cell(60,6,$Scout['Scout_first_name'].' '.$Scout['Scout_last_name'],1);
				$pdf->Cell(40,6,$Scout['Scout_birth_date'],1);
				$pdf->Cell(30,6,$Scout['Scout_age'],1);
				$pdf->Cell(40,6,$Scout['Scout_ScoutNL_number'],1,1);
			};
			if($FormData['inschrijving']['patrols'][$PatrolID]['Patrol_remark'] != ""){
				$pdf->MultiCell(190,6,$FormData['inschrijving']['patrols'][$PatrolID]['Patrol_remark'],1,1);
			};
			$pdf->Cell(190,3,"",0,1);
		};
		
		$pdf->Output($uploadedfilePath, "F");
		
		$Group_url = $FormData['RSW_Active_url'];
		$Group_url .= '/?';
		$Group_url .= '&code=';
		$Group_url .= $FormData['code'];
		$Group_url .= '&vereniging=';
		$Group_url .= $FormData['inschrijving']['Association_Name'];
		$Group_url .= '&groep=';
		$Group_url .= $FormData['inschrijving']['Association_Sub_name'];
		$Group_url .= '&groep-code=';
		$Group_url .= $FormData['inschrijving']['Association_sign_op_code'];
		
		$current_user = wp_get_current_user();
		if (user_can( $current_user, 'administrator' )) {
			$to = get_bloginfo('admin_email');
		}else{
			$to = $FormData['inschrijving']['Association_Contact_Email'];
		};
		$RSW_email = json_decode($wpdb->get_var("SELECT data FROM $RSW_Settings_table WHERE name='Email'"),true);
		if(!empty($RSW_email)){
			$Bcc = $RSW_email;
		}else{
			$Bcc = get_bloginfo('admin_email');
		};
		$subject = 'RSW inschrijving '.$FormData['inschrijving']['Association_Sub_name'].' '.$FormData['inschrijving']['Association_acronym'];
		
		$message = 'Beste '.$FormData['inschrijving']['Association_Contact_name'].',<br>';
		$message .= '<br>';
		$message .= 'Bedankt voor het inschrijven.<br>';
		$message .= '<br>';
		$message .= 'In de bijlage vindt je het bewijs van inschrijving.<br>';
		$message .= 'Je kunt de inschrijving nog wijzigen met de onderstaande link:<br>';
		$message .= '<a href="'.$Group_url.'">'.$Group_url.'</a><br>';
		$message .= '<br>';
		$message .= 'Met vriendelijke groet,<br>';
		$message .= 'RSW organisatie<br>';
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: RSW Organisatie <'.$Bcc.'>' . "\r\n";
		$headers .= 'Bcc: '.$Bcc.'' . "\r\n";
		
		$attachments = array($uploadedfilePath);
		wp_mail( $to, $subject, $message , $headers , $attachments);
	};
};
?>