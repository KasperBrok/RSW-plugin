<?php
function RSWSubkampenPagina_(){
	global $wpdb;

	global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	global $RSW_Settings_table;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	$Subfield_date = $wpdb->get_var( "SELECT Subfield_date FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	$RSW_Subfield_list = $wpdb->get_results("SELECT * FROM {$RSW_Subfield_table}");
	$RSW_Patrol_List = $wpdb->get_results("SELECT * FROM {$RSW_Scout_table} WHERE Year = '$Year' GROUP BY Association_Name,Association_Sub_name,Patrol_Name ORDER BY Patrol_Number");
	
	$Subfield_date_passed = false;
	if(strtotime(date('Y-m-d', strtotime($Subfield_date) ) ) < strtotime(date('Y-m-d'))){
		//$Subfield_date_passed = true;
	}
	
	echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">Subkampen</h1>';
	
	$SubfieldList = array();
	if(isset($_POST['SubfieldList'])){
		$SubfieldList = $_POST['SubfieldList'];
	}else{
		foreach($RSW_Subfield_list as $subfield){
			$SubfieldList[$subfield->color]["color_code"] = $subfield->color_code;
			$SubfieldList[$subfield->color]["color"] = $subfield->color;
			$SubfieldList[$subfield->color]["name"] = $subfield->name;
			$SubfieldList[$subfield->color]["patrol"] = array();
			foreach( $RSW_Patrol_List as $RSW_Patrol ) {
				if(isset($RSW_Patrol->Patrol_subfield) && $RSW_Patrol->Patrol_subfield == $subfield->color){
					$SubfieldList[$subfield->color]["patrol"][$RSW_Patrol->id]["Patrol_Number"] = $RSW_Patrol->Patrol_Number;
					$SubfieldList[$subfield->color]["patrol"][$RSW_Patrol->id]["id"] = $RSW_Patrol->id;
					$SubfieldList[$subfield->color]["patrol"][$RSW_Patrol->id]["Patrol_Name"] = $RSW_Patrol->Patrol_Name;
					$SubfieldList[$subfield->color]["patrol"][$RSW_Patrol->id]["Association_Sub_name"] = $RSW_Patrol->Association_Sub_name;
					$SubfieldList[$subfield->color]["patrol"][$RSW_Patrol->id]["Association_Name"] = $RSW_Patrol->Association_Name;
					$SubfieldList[$subfield->color]["patrol"][$RSW_Patrol->id]["Association_acronym"] = $RSW_Patrol->Association_acronym;
					$SubfieldList[$subfield->color]["patrol"][$RSW_Patrol->id]["Patrol_youngest"] = $RSW_Patrol->Patrol_youngest;
				}
			};
		};
		foreach( $RSW_Patrol_List as $RSW_Patrol ) {
			if(!isset($RSW_Patrol->Patrol_subfield)){
				if(isset($RSW_Patrol->Patrol_Number)){
					$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Patrol_Number"] = $RSW_Patrol->Patrol_Number;
				}
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["id"] = $RSW_Patrol->id;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Patrol_Name"] = $RSW_Patrol->Patrol_Name;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Association_Sub_name"] = $RSW_Patrol->Association_Sub_name;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Association_Name"] = $RSW_Patrol->Association_Name;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Association_acronym"] = $RSW_Patrol->Association_acronym;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Patrol_youngest"] = $RSW_Patrol->Patrol_youngest;
			}
		}
	};
	
	
	
	if(isset($_POST['RSW_devide_sub_divisions']) && $_POST['RSW_devide_sub_divisions'] == "Subkampen Indelen"){
		$Input_allowed = true;
		
		foreach($_POST['SubfieldList'] as $color => $subfield){
			if($color == "undefined"){
				$Input_allowed = false;
				echo'<div class="notice notice-warning is-dismissible">
						<p>Niet alle groepen zijn ingedeeld.</p>
					</div>'; 
			}
		}
		
		if($Input_allowed == true){
			foreach($_POST['SubfieldList'] as $color => $Subfield){
				foreach($Subfield["patrol"] as $patrol){
					
					$update = array(
						"Patrol_subfield"=>$Subfield["color"]
					);
					
					if(isset($patrol["TMP_Patrol_Number"])){
						$update["Patrol_Number"] = $patrol["TMP_Patrol_Number"];
					};
					
					$where = array(
						"Association_Name"=>$patrol["Association_Name"],
						"Association_Sub_name"=>$patrol["Association_Sub_name"],
						"Patrol_Name"=>$patrol["Patrol_Name"]
					);
					
					$updated = $wpdb->update( 
						$RSW_Scout_table,
						$update,
						$where);

					if ( false === $updated ) {
						echo'<div class="notice notice-warning is-dismissible">
								<p>error tijdens updaten van '.$patrol["Patrol_Name"].'.</p>
							</div>'; 
					} else {
						// No error. You can check updated to see how many rows were changed.
					}
				}
			}
		}
		
	};
	
	$Renumber = false;
	if(isset($_POST['RSW_renumber_sub_divisions']) && $_POST['RSW_renumber_sub_divisions'] == "Opnieuw nummeren"){
		$Renumber = true;
	}
	
	$Check_Position = false;
	if(isset($_POST['RSW_check_sub_divisions']) && $_POST['RSW_check_sub_divisions'] == "check posities"){
		
		$Check_Position = true;
		foreach($SubfieldList as $Subfieldcolor => $subfield){
			if($Subfieldcolor != "undefined"){
				if(isset($subfield["patrol"])){
					foreach($subfield["patrol"] as $patrol){
						foreach($subfield["patrol"] as $patrol_check){
							if($patrol["id"] != $patrol_check["id"]){
								if($patrol["Association_Name"] == $patrol_check["Association_Name"] && $patrol["Association_Sub_name"] == $patrol_check["Association_Sub_name"]){
									$SubfieldList[$subfield["color"]]["patrol"][$patrol["id"]]["Patrol_position_check"] = 3;
								}elseif($patrol["Association_Name"] == $patrol_check["Association_Name"]){
									if(!(isset($SubfieldList[$subfield["color"]]["patrol"][$patrol["id"]]["Patrol_position_check"]) && $SubfieldList[$subfield["color"]]["patrol"][$patrol["id"]]["Patrol_position_check"] == 3)){
										$SubfieldList[$subfield["color"]]["patrol"][$patrol["id"]]["Patrol_position_check"] = 2;
									};
								};
							};
						};
						if(!isset($SubfieldList[$subfield["color"]]["patrol"][$patrol["id"]]["Patrol_position_check"])){
							$SubfieldList[$subfield["color"]]["patrol"][$patrol["id"]]["Patrol_position_check"] = 1;
						};
					};
				};
			};
		};
	};
	
	// echo '<pre>';
	// print_r($SubfieldList);
	// echo '</pre>';
	
		echo'<form method="post" id="RSW_Subfield_form">';
		echo '<div class="subfield-setup-container">';
		
		if($Renumber)$RenumberCount = 1;
		foreach($SubfieldList as $Subfieldcolor => $Subfield){
			if($Subfieldcolor != "undefined"){
				echo "<div class='subfield-setup' style='background-color: ".$Subfield["color_code"]."20;'>";
				echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][color_code]" value="'.$Subfield["color_code"].'">';
				echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][color]" value="'.$Subfield["color"].'">';
				echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][name]" value="'.$Subfield["name"].'">';
				echo "<h4>";
				echo $Subfield["color"] . " - " . $Subfield["name"];
				echo "</h4>";
				echo '<ul id="RSW_Subfield_list_'.$Subfield["color"].'" data-color="'.$Subfield["color"].'" class="';
				if(!$Subfield_date_passed){
					echo 'Empty_list sortable_list connectedSortable';
				}
				echo '">';
				if(isset($Subfield["patrol"])){
					foreach($Subfield["patrol"] as $patrol){
						echo '<li class="';
						if(isset($patrol["Patrol_Number"])){
							//echo ' ui-state-disabled';
						}
						echo '">';
						if($Renumber){
							echo'('.$RenumberCount.')';
							echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][TMP_Patrol_Number]" value="'.$RenumberCount.'">';
							$RenumberCount++;
							
						};
						if(isset($patrol["Patrol_Number"])){
							echo $patrol["Patrol_Number"];
							echo " ";
							echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][Patrol_Number]" value="'.$patrol["Patrol_Number"].'">';
						};
						echo $patrol["Patrol_Name"];
						echo " - ";
						echo $patrol["Association_Sub_name"];
						echo ", ";
						echo $patrol["Association_acronym"];
						if($patrol["Patrol_youngest"] == 1)echo' *';
						echo " ";
						if($Check_Position){
							switch ($patrol["Patrol_position_check"]) {
								case 1:
									echo'<span class="dot_green"></span>';
									break;
								case 2:
									echo'<span class="dot_orange"></span>';
									break;
								case 3:
									echo'<span class="dot_red"></span>';
									break;
							}
						}
						echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][id]" value="'.$patrol["id"].'">';
						echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][Patrol_Name]" value="'.$patrol["Patrol_Name"].'">';
						echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][Association_Sub_name]" value="'.$patrol["Association_Sub_name"].'">';
						echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][Association_Name]" value="'.$patrol["Association_Name"].'">';
						echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][Association_acronym]" value="'.$patrol["Association_acronym"].'">';
						echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["id"].'][Patrol_youngest]" value="'.$patrol["Patrol_youngest"].'">';
						echo '</li>';
					};
				};
				echo '</ul>';
				echo "</div>";
			}else{
				echo '<div id="subfield-unsorted-container">';
				echo '<ul id="RSW_unsorted_list" data-color="undefined" class="';
				if(!$Subfield_date_passed){
					echo 'sortable_list connectedSortable';
				}
				echo '">';
				foreach($Subfield["patrol"] as $patrol){
					echo '<li>';
						echo $patrol["Patrol_Name"];
						echo " - ";
						echo $patrol["Association_Sub_name"];
						echo ", ";
						echo $patrol["Association_acronym"];
						if($patrol["Patrol_youngest"] == 1)echo' *';
						echo'<input type="hidden" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][id]" value="'.$patrol["id"].'">';
						echo'<input type="hidden" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Patrol_Name]" value="'.$patrol["Patrol_Name"].'">';
						echo'<input type="hidden" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Association_Sub_name]" value="'.$patrol["Association_Sub_name"].'">';
						echo'<input type="hidden" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Association_Name]" value="'.$patrol["Association_Name"].'">';
						echo'<input type="hidden" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Association_acronym]" value="'.$patrol["Association_acronym"].'">';
						echo'<input type="hidden" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Patrol_youngest]" value="'.$patrol["Patrol_youngest"].'">';
					echo '</li>';
				};
				echo '</ul>';
				echo "</div>";
			};
		};
		
		
		echo "</div>";	
		
		echo "<div>";	
		if(!$Subfield_date_passed){
			echo' <input type="submit" name="RSW_devide_sub_divisions" value="Subkampen Indelen">';
			echo'hernummeren';
			echo' <label class="switch">';
			echo'   <input type="checkbox" name="RSW_renumber_sub_divisions" value="Opnieuw nummeren" onchange="this.form.submit()" ';
			if(isset($_POST["RSW_renumber_sub_divisions"]))echo'checked';
			echo'>';
			echo'   <span class="slider round"></span>';
			echo' </label>';
			echo'controleer positie';
			echo' <label class="switch">';
			echo'   <input type="checkbox" name="RSW_check_sub_divisions" value="check posities" onchange="this.form.submit()" ';
			if(isset($_POST["RSW_check_sub_divisions"]))echo'checked';
			echo'>';
			echo'   <span class="slider round"></span>';
			echo' </label>';
			echo'auto update';
			echo' <label class="switch">';
			echo'   <input type="checkbox" id="RSW_Auto_update_sub_divisions" name="RSW_Auto_update_sub_divisions" value="AutoUpdate" onchange="this.form.submit()" ';
			if(isset($_POST["RSW_Auto_update_sub_divisions"]))echo'checked';
			echo'>';
			echo'   <span class="slider round"></span>';
			echo' </label>';
		}
		echo "</div>";	
		
		echo'</form>';
	echo '</div>';
};

function RSWSubkampenPagina(){
	global $wpdb;

	global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	global $RSW_Settings_table;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	$Subfield_date = $wpdb->get_var( "SELECT Subfield_date FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	$RSW_Subfield_list = $wpdb->get_results("SELECT * FROM {$RSW_Subfield_table}");
	$RSW_Patrol_List = $wpdb->get_results("SELECT * FROM {$RSW_Scout_table} WHERE Year = '$Year' GROUP BY Association_Name,Association_Sub_name,Patrol_Name ORDER BY Patrol_Number");
	$Patrol_position_value_matrix = json_decode($wpdb->get_var("SELECT data FROM $RSW_Settings_table WHERE name='Subfield_Position_value'"),true);
	
	$Subfield_date_passed = false;
	if(strtotime(date('Y-m-d', strtotime($Subfield_date) ) ) < strtotime(date('Y-m-d'))){
		//$Subfield_date_passed = true;
	}
	
	echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">Subkampen</h1>';
	
	$SubfieldList = array();
	if(isset($_POST['SubfieldList'])){
		$SubfieldList = $_POST['SubfieldList'];
		
		$Subfield_patrol_position_Nr = 1;
		
		foreach($SubfieldList as $Subfieldcolor => $subfield){
			if($Subfieldcolor != "undefined"){
				for ($x = 1; $x <= $subfield["max_patrol"]; $x++) {
					$SubfieldList[$subfield["color"]]["patrol"][$Subfield_patrol_position_Nr]["Patrol_subfield_position"] = $Subfield_patrol_position_Nr;
					$Subfield_patrol_position_Nr++;
				};
				ksort($SubfieldList[$subfield["color"]]["patrol"]);
			};
		};
	}else{
		$Subfield_patrol_position_Nr = 1;
		foreach($RSW_Subfield_list as $subfield){
			$SubfieldList[$subfield->color]["color_code"] = $subfield->color_code;
			$SubfieldList[$subfield->color]["color"] = $subfield->color;
			$SubfieldList[$subfield->color]["name"] = $subfield->name;
			$SubfieldList[$subfield->color]["patrol"] = array();
			$SubfieldList[$subfield->color]["max_patrol"] = $subfield->max_patrol;
			
			$Has_Subfield_ID = array();
			$No_Subfield_ID = array();
			foreach( $RSW_Patrol_List as $RSW_Patrol ) {
				if(isset($RSW_Patrol->Patrol_subfield) && $RSW_Patrol->Patrol_subfield == $subfield->color){
					if(isset($RSW_Patrol->Patrol_subfield_position) && $RSW_Patrol->Patrol_subfield_position != 0 && $RSW_Patrol->Patrol_subfield_position > $Subfield_patrol_position_Nr && $RSW_Patrol->Patrol_subfield_position < ($Subfield_patrol_position_Nr + $subfield->max_patrol)){
						$Has_Subfield_ID[$RSW_Patrol->Patrol_subfield_position] = $RSW_Patrol;
					}else{
						array_push($No_Subfield_ID,$RSW_Patrol);
					};
				};
			};
			
			for ($x = 1; $x <= $subfield->max_patrol; $x++) {
				$filled = false;
				
				foreach($Has_Subfield_ID as $RSW_Patrol){
					if($Subfield_patrol_position_Nr == $RSW_Patrol->Patrol_subfield_position){
						$filled = true;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_Number"] = $RSW_Patrol->Patrol_Number;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_subfield_position"] = $Subfield_patrol_position_Nr;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["id"] = $RSW_Patrol->id;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_Name"] = $RSW_Patrol->Patrol_Name;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Association_Sub_name"] = $RSW_Patrol->Association_Sub_name;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Association_Name"] = $RSW_Patrol->Association_Name;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Association_acronym"] = $RSW_Patrol->Association_acronym;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_youngest"] = $RSW_Patrol->Patrol_youngest;
					};
				};
				
				if(!$filled){
					if(isset($No_Subfield_ID[0])){
						$filled = true;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_Number"] = $No_Subfield_ID[0]->Patrol_Number;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_subfield_position"] = $Subfield_patrol_position_Nr;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["id"] = $No_Subfield_ID[0]->id;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_Name"] = $No_Subfield_ID[0]->Patrol_Name;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Association_Sub_name"] = $No_Subfield_ID[0]->Association_Sub_name;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Association_Name"] = $No_Subfield_ID[0]->Association_Name;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Association_acronym"] = $No_Subfield_ID[0]->Association_acronym;
						$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_youngest"] = $No_Subfield_ID[0]->Patrol_youngest;

						unset($No_Subfield_ID[0]);
						$No_Subfield_ID = array_values($No_Subfield_ID);
					};
				};
				
				if(!$filled){
					$SubfieldList[$subfield->color]["patrol"][$Subfield_patrol_position_Nr]["Patrol_subfield_position"] = $Subfield_patrol_position_Nr;
				};
				
				$Subfield_patrol_position_Nr++;
			};
		};
		foreach( $RSW_Patrol_List as $RSW_Patrol ) {
			if(!isset($RSW_Patrol->Patrol_subfield)){
				if(isset($RSW_Patrol->Patrol_Number)){
					$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Patrol_Number"] = $RSW_Patrol->Patrol_Number;
				}
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["id"] = $RSW_Patrol->id;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Patrol_Name"] = $RSW_Patrol->Patrol_Name;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Association_Sub_name"] = $RSW_Patrol->Association_Sub_name;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Association_Name"] = $RSW_Patrol->Association_Name;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Association_acronym"] = $RSW_Patrol->Association_acronym;
				$SubfieldList["undefined"]["patrol"][$RSW_Patrol->id]["Patrol_youngest"] = $RSW_Patrol->Patrol_youngest;
			}
		}
	};
	
	if(isset($_POST["RSW_renumber_sub_divisions"])){
		$TMP_Patrol_Number_Nr = 1;
		foreach($SubfieldList as $Subfieldcolor => $subfield){
			if($Subfieldcolor != "undefined"){
				foreach($subfield["patrol"] as $patrol){
					if(isset($patrol["Patrol_Name"])){
						$SubfieldList[$subfield["color"]]["patrol"][$patrol["Patrol_subfield_position"]]["TMP_Patrol_Number"] = $TMP_Patrol_Number_Nr;
						$TMP_Patrol_Number_Nr++;
					};
				};
			};
		};
	};
	
	if(isset($_POST['RSW_reposition_sub_divisions'])){
		
		
		$Position_matrix_value_max = 0;
		foreach($SubfieldList as $Subfieldcolor => $subfield){
			if($Subfieldcolor != "undefined"){
				foreach($subfield["patrol"] as $patrol){
					if(isset($patrol["id"])){
						$PatrolPositionValue = 0;
						foreach($SubfieldList as $SubfieldcolorLoop => $subfieldLoop){
							if($SubfieldcolorLoop != "undefined"){
								foreach($subfieldLoop["patrol"] as $patrolLoop){
									if(isset($patrolLoop["id"]) && $patrolLoop["id"] != $patrol["id"]){
										if($patrolLoop["Association_Name"] == $patrol["Association_Name"]){
											$patrol_position = $patrol["Patrol_subfield_position"] - 1;
											$patrolLoop_position = $patrolLoop["Patrol_subfield_position"] - 1;
											$Position_matrix_value = $Patrol_position_value_matrix[$patrol_position][$patrolLoop_position];
											
											if($patrolLoop["Association_Sub_name"] == $patrol["Association_Sub_name"]){
												$Position_matrix_value = $Position_matrix_value * 2;
											};
											
											if($Position_matrix_value > $PatrolPositionValue) $PatrolPositionValue = $Position_matrix_value;
											
											if($Position_matrix_value_max < $Position_matrix_value)$Position_matrix_value_max = $Position_matrix_value;
											
										};
									};
									
								};
							};
						};
						$SubfieldList[$subfield["color"]]["patrol"][$patrol["Patrol_subfield_position"]]["TMP_Position_Value"] = $PatrolPositionValue;
					};
				};
			};
		};
		
		echo $Position_matrix_value_max;
		
		$Position_color = array();
		array_push($Position_color,sprintf("#%02x%02x%02x", 0, 0, 0));
		$red = 0;
		$green = 255;
		$stepsize = round((255+255)/($Position_matrix_value_max + 1));
		while($red < 255){
			$red = $red + $stepsize;
			if($red > 255)$red = 255;
			array_push($Position_color,sprintf("#%02x%02x%02x", $red, $green, 0));
		};
		while($green > 0){
			$green = $green - $stepsize;
			if($green < 0)$green = 0;
			array_push($Position_color,sprintf("#%02x%02x%02x", $red, $green, 0));
		};
	};
	
	if(isset($_POST["RSW_devide_sub_divisions"])){
		foreach($SubfieldList as $Subfieldcolor => $subfield){
			if($Subfieldcolor != "undefined"){
				foreach($subfield["patrol"] as $patrol){
					if(isset($patrol["Patrol_Name"])){
						$update = array(
							"Patrol_subfield"=>$subfield["color"]
						);
						
						if(isset($patrol["Patrol_subfield_position"])){
							$update["Patrol_subfield_position"] = $patrol["Patrol_subfield_position"];
						};
						
						if(isset($patrol["TMP_Patrol_Number"])){
							$update["Patrol_Number"] = $patrol["TMP_Patrol_Number"];
						};
						
						$where = array(
							"Association_Name"=>$patrol["Association_Name"],
							"Association_Sub_name"=>$patrol["Association_Sub_name"],
							"Patrol_Name"=>$patrol["Patrol_Name"]
						);
						
						$updated = $wpdb->update( 
							$RSW_Scout_table,
							$update,
							$where);

						if ( false === $updated ) {
							echo'<div class="notice notice-warning is-dismissible">
									<p>error tijdens updaten van '.$patrol["Patrol_Name"].'.</p>
								</div>'; 
						} else {
							// No error. You can check updated to see how many rows were changed.
						};
					};
				};
			};
		};
	};
	
	// echo"<pre>";
	// print_r($SubfieldList);
	// echo"</pre>";
	
	echo'<form method="post" id="RSW_Subfield_form">';
	echo '<div id="subfield-setup-container">';
	
	foreach($SubfieldList as $Subfieldcolor => $Subfield){
		if($Subfieldcolor != "undefined"){
			echo "<div class='subfield-setup' data-color='".$Subfield["color"]."' style='background-color: ".$Subfield["color_code"]."20;'>";
			echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][color_code]" value="'.$Subfield["color_code"].'">';
			echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][color]" value="'.$Subfield["color"].'">';
			echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][name]" value="'.$Subfield["name"].'">';
			echo'<input type="hidden" name="SubfieldList['.$Subfield["color"].'][max_patrol]" value="'.$Subfield["max_patrol"].'">';
			echo "<h4>";
			echo $Subfield["color"] . " - " . $Subfield["name"];
			echo "</h4>";
			foreach($Subfield["patrol"] as $patrol){
				echo '<div class="subfield-setup-dropable" data-position="'.$patrol["Patrol_subfield_position"].'"><p>'.$patrol["Patrol_subfield_position"].'</p>';
				if(isset($patrol["Patrol_Name"])){
					echo '<div class="subfield-setup-dragable" data-position="'.$patrol["Patrol_subfield_position"].'">';
						if(isset($_POST["RSW_renumber_sub_divisions"])){
							echo "<span class='Temp_ID'>(".$patrol["TMP_Patrol_Number"].")</span> ";
							echo'<input type="hidden" data-name="TMP_Patrol_Number" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][TMP_Patrol_Number]" value="'.$patrol["TMP_Patrol_Number"].'">';
						};
						
						if(isset($patrol["Patrol_Number"])){
							echo $patrol["Patrol_Number"];
							echo " ";
							echo'<input type="hidden" data-name="TMP_Patrol_Number" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][Patrol_Number]" value="'.$patrol["Patrol_Number"].'">';
						};
						
						echo $patrol["Patrol_Name"];
						echo " - ";
						echo $patrol["Association_Sub_name"];
						echo ", ";
						echo $patrol["Association_acronym"];
						if($patrol["Patrol_youngest"] == 1)echo' *';
						echo " ";
						
						if(isset($_POST['RSW_reposition_sub_divisions'])){
							echo'<span class="dot" style="background-color: '.$Position_color[$patrol["TMP_Position_Value"]].';"></span> ';
							echo $patrol["TMP_Position_Value"];
						};
						
						echo'<input type="hidden" data-name="id" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][id]" value="'.$patrol["id"].'">';
						echo'<input type="hidden" data-name="Patrol_Name" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][Patrol_Name]" value="'.$patrol["Patrol_Name"].'">';
						echo'<input type="hidden" data-name="Association_Sub_name" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][Association_Sub_name]" value="'.$patrol["Association_Sub_name"].'">';
						echo'<input type="hidden" data-name="Association_Name" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][Association_Name]" value="'.$patrol["Association_Name"].'">';
						echo'<input type="hidden" data-name="Association_acronym" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][Association_acronym]" value="'.$patrol["Association_acronym"].'">';
						echo'<input type="hidden" data-name="Patrol_youngest" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][Patrol_youngest]" value="'.$patrol["Patrol_youngest"].'">';
						echo'<input type="hidden" data-name="Patrol_subfield_position" name="SubfieldList['.$Subfield["color"].'][patrol]['.$patrol["Patrol_subfield_position"].'][Patrol_subfield_position]" value="'.$patrol["Patrol_subfield_position"].'">';
					echo '</div>';
				};
				echo '</div>';
			};
			echo'</div>';
		}else{
			echo '<div id="subfield-unsorted-container">';
			//echo '<div class="subfield-setup-dropable" data-position="'.$patrol["Patrol_subfield_position"].'"><p>'.$patrol["Patrol_subfield_position"].'</p>';
			foreach($SubfieldList as $Subfieldcolor => $subfield){
				if($Subfieldcolor == "undefined"){
					foreach($subfield["patrol"] as $patrol){
						echo '<div class="subfield-setup-dragable" data-position="'.$patrol["id"].'">';
						if(isset($_POST["RSW_renumber_sub_divisions"]) && isset($patrol["TMP_Patrol_Number"])){
							echo "<span class='Temp_ID'>(".$patrol["TMP_Patrol_Number"].")</span> ";
							echo'<input type="hidden" data-name="TMP_Patrol_Number" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][TMP_Patrol_Number]" value="'.$patrol["TMP_Patrol_Number"].'">';
						};
						
						if(isset($patrol["Patrol_Number"])){
							echo $patrol["Patrol_Number"];
							echo " ";
							echo'<input type="hidden" data-name="TMP_Patrol_Number" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Patrol_Number]" value="'.$patrol["Patrol_Number"].'">';
						};
						
						echo $patrol["Patrol_Name"];
						echo " - ";
						echo $patrol["Association_Sub_name"];
						echo ", ";
						echo $patrol["Association_acronym"];
						if($patrol["Patrol_youngest"] == 1)echo' *';
						echo " ";
						
						if(isset($_POST['RSW_reposition_sub_divisions']) && isset($patrol["TMP_Position_Value"])){
							echo'<span class="dot" style="background-color: '.$Position_color[$patrol["TMP_Position_Value"]].';"></span> ';
							echo $patrol["TMP_Position_Value"];
						};
						
						echo'<input type="hidden" data-name="id" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][id]" value="'.$patrol["id"].'">';
						echo'<input type="hidden" data-name="Patrol_Name" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Patrol_Name]" value="'.$patrol["Patrol_Name"].'">';
						echo'<input type="hidden" data-name="Association_Sub_name" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Association_Sub_name]" value="'.$patrol["Association_Sub_name"].'">';
						echo'<input type="hidden" data-name="Association_Name" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Association_Name]" value="'.$patrol["Association_Name"].'">';
						echo'<input type="hidden" data-name="Association_acronym" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Association_acronym]" value="'.$patrol["Association_acronym"].'">';
						echo'<input type="hidden" data-name="Patrol_youngest" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Patrol_youngest]" value="'.$patrol["Patrol_youngest"].'">';
						echo'<input type="hidden" data-name="Patrol_subfield_position" name="SubfieldList[undefined][patrol]['.$patrol["id"].'][Patrol_subfield_position]" value="'.$patrol["id"].'">';
						echo '</div>';
					};
				};
			};
			//echo "</div>";
			echo "</div>";
		};
	}
	echo'</div>';
	echo' <input type="submit" name="RSW_devide_sub_divisions" value="Subkampen Indelen">';
	echo' Hernummeren';
		echo' <label class="switch">';
		echo'   <input type="checkbox" name="RSW_renumber_sub_divisions" id="RSW_renumber_sub_divisions" value="Opnieuw nummeren" onchange="this.form.submit()" ';
		if(isset($_POST["RSW_renumber_sub_divisions"]))echo'checked';
		echo'>';
		echo'   <span class="slider round"></span>';
		echo' </label>';
	echo' Controleer positie';
		echo' <label class="switch">';
		echo'   <input type="checkbox" name="RSW_reposition_sub_divisions" id=""RSW_reposition_sub_divisions" value="Opnieuw nummeren" onchange="this.form.submit()" ';
		if(isset($_POST["RSW_reposition_sub_divisions"]))echo'checked';
		echo'>';
		echo'   <span class="slider round"></span>';
		echo' </label>';
	echo'</form>';
	echo'</div>';
};
?>