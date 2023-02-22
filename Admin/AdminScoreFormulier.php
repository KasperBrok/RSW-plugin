<?php
function RSWScoreformulierenPagina(){
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
	
	$criterias = $wpdb->get_results("
		SELECT 
			Categorie,
			Sub_Categorie,
			Weeg_factor,
			SUM(Max_value) as Total_Max_value
		FROM $RSW_Criteria_table
		GROUP BY 
			Categorie,
			Sub_Categorie
	");
	
	$SubFields = $wpdb->get_results("SELECT * FROM $RSW_Subfield_table WHERE Year = '$Year'");
	$Categorys = array();
	$SubCategorys = array();
	$catagorie_list = array();
	
	foreach($criterias as $criteria){
		if(isset($_POST['Category']) && $_POST['Category'] != 'all'){
			if($_POST['Category'] == $criteria->Categorie){
				if(isset($_POST['SubCategory']) && $_POST['SubCategory'] != 'all'){
					if($_POST['SubCategory'] == $criteria->Sub_Categorie){
						if(!isset($catagorie_list[$criteria->Categorie]))$catagorie_list[$criteria->Categorie] = array();
						array_push($catagorie_list[$criteria->Categorie],$criteria->Sub_Categorie);
					};
				}else{
					if(!isset($catagorie_list[$criteria->Categorie]))$catagorie_list[$criteria->Categorie] = array();
					array_push($catagorie_list[$criteria->Categorie],$criteria->Sub_Categorie);
				};
			};
		}else{
			if(isset($_POST['SubCategory']) && $_POST['SubCategory'] != 'all'){
				if($_POST['SubCategory'] == $criteria->Sub_Categorie){
					if(!isset($catagorie_list[$criteria->Categorie]))$catagorie_list[$criteria->Categorie] = array();
					array_push($catagorie_list[$criteria->Categorie],$criteria->Sub_Categorie);
				};
			}else{
				if(!isset($catagorie_list[$criteria->Categorie]))$catagorie_list[$criteria->Categorie] = array();
				array_push($catagorie_list[$criteria->Categorie],$criteria->Sub_Categorie);
			};
		};
		
		if(!in_array($criteria->Categorie,$Categorys))array_push($Categorys,$criteria->Categorie);
		if(!in_array($criteria->Sub_Categorie,$SubCategorys))array_push($SubCategorys,$criteria->Sub_Categorie);
	};
	
	$Patrol_Score_list = array();
	
	if(isset($_POST['Patrol_score'])){
		$Patrol_Score_list = $_POST['Patrol_score'];
	}else{
		$Patrols = $wpdb->get_results("
			SELECT 
				id,
				Patrol_Points_total,
				Patrol_Points_json,
				Patrol_Position,
				Patrol_Number,
				Patrol_Name,
				Patrol_subfield,
				Association_Name,
				Association_Sub_name
			FROM $RSW_Scout_table 
			WHERE 
				Year = '$Year'
			GROUP BY 
				Association_Name,
				Association_Sub_name,
				Patrol_Name
			ORDER BY Patrol_Number
		");
		
		foreach($Patrols as $Patrol){
			$Patrol_Score_list[$Patrol->id]['id'] = $Patrol->id;
			$Patrol_Score_list[$Patrol->id]['Patrol_Name'] = $Patrol->Patrol_Name;
			$Patrol_Score_list[$Patrol->id]['Patrol_Number'] = $Patrol->Patrol_Number;
			$Patrol_Score_list[$Patrol->id]['Patrol_Points_total'] = $Patrol->Patrol_Points_total;
			$Patrol_Score_list[$Patrol->id]['Patrol_Position'] = $Patrol->Patrol_Position;
			$Patrol_Score_list[$Patrol->id]['Association_Name'] = $Patrol->Association_Name;
			$Patrol_Score_list[$Patrol->id]['Association_Sub_name'] = $Patrol->Association_Sub_name;
			$Patrol_Score_list[$Patrol->id]['Patrol_subfield'] = $Patrol->Patrol_subfield;
			$Patrol_Scores = json_decode($Patrol->Patrol_Points_json,true);
			foreach($criterias as $criteria){
				if(isset($Patrol_Scores[$criteria->Categorie][$criteria->Sub_Categorie])){
					$Patrol_Score_list[$Patrol->id]['Patrol_Points_json'][$criteria->Categorie][$criteria->Sub_Categorie] = $Patrol_Scores[$criteria->Categorie][$criteria->Sub_Categorie];
				}elseif(isset($Patrol_Scores[$criteria->Categorie.':'.$criteria->Sub_Categorie])){
					$Patrol_Score_list[$Patrol->id]['Patrol_Points_json'][$criteria->Categorie][$criteria->Sub_Categorie] = $Patrol_Scores[$criteria->Categorie.':'.$criteria->Sub_Categorie];
				}else{
					$Patrol_Score_list[$Patrol->id]['Patrol_Points_json'][$criteria->Categorie][$criteria->Sub_Categorie] = 0;
				};
			};
		};
	};
	
	if(isset($_POST['Action']) && $_POST['Action'] == "updaten"){
		
		$Highest_Score = array();
		$Max_score = array();
		$weeg_factor = array();
		
		$criterias_categorie = $wpdb->get_results("
			SELECT 
				Categorie,
				Weeg_factor,
				SUM(Max_value) as Total_Max_value
			FROM $RSW_Criteria_table
			GROUP BY 
				Categorie
		");
		
		foreach($criterias_categorie as $criteria){
			$Max_score[$criteria->Categorie] = $criteria->Total_Max_value;
			$weeg_factor[$criteria->Categorie] = $criteria->Weeg_factor/100;
		};
		
		foreach($Patrol_Score_list as $id => $Patrol){
			$Score_each_category = array();
			$Patrol_total_score = 0;
			
			foreach($Patrol['Patrol_Points_json'] as $Category => $SubCategory_list){
				$Category_total = 0;
				foreach($SubCategory_list as $SubCategory){
					$Category_total = $Category_total + $SubCategory;
				};
				$Score_each_category[$Category] = $Category_total;
				
			};
			
			foreach($Score_each_category as $category => $Score){
				
				$Patrol_total_score = $Patrol_total_score + round(((1000/$Max_score[$category])*$Score)*$weeg_factor[$category]);
			};
			$Patrol_Score_list[$id]['Patrol_Points_total'] = $Patrol_total_score;
			// echo $Patrol['Patrol_Name'].' - '.$Patrol['Patrol_Points_total'].'<br>';
		}
		usort($Patrol_Score_list, function ($a, $b) {
			return $b['Patrol_Points_total'] - $a['Patrol_Points_total'];
		});
		
		$New_Position = 1;
		foreach($Patrol_Score_list as $id => $Patrol){
			$Patrol_Score_list[$id]['Patrol_Position'] = $New_Position;
			$New_Position++;
		};
		
		foreach($Patrol_Score_list as $id => $Patrol){
			$updated = $wpdb->update( $RSW_Scout_table, 
				array(
					"Patrol_Position" => $Patrol['Patrol_Position'],
					"Patrol_Points_total" => $Patrol['Patrol_Points_total'],
					"Patrol_Points_json" => json_encode($Patrol['Patrol_Points_json'])
				), array(
					"Association_Name" => $Patrol['Association_Name'],
					"Association_Sub_name" => $Patrol['Association_Sub_name'],
					"Patrol_Name" => $Patrol['Patrol_Name']
				) );
				
			if ( false === $updated ) {
				echo 'update failed at id: '.$Scout->id;
			};
		};
	};
	
	// echo '<pre>';
	// print_r($Patrol_Score_list);
	// echo '</pre>';
	
	echo'<form method="post">';
		//echo'<input type="hidden" id="RSW_Page_Name" name="page" value="'.$_GET['page'].'">';
		
		echo'<select name="Category" id="RSW_Category" onchange="this.form.submit()">';
			echo'<option value="all" ';
				if(isset($_POST['Category']) && $_POST['Category'] == 'all'){
					echo'selected';
				};
			echo' >Alle Categorieën</option>';
			foreach($Categorys as $Category){
				echo'<option value="'.$Category.'" ';
				if(isset($_POST['Category']) && $_POST['Category'] == $Category){
					echo'selected';
				};
				echo' >'.$Category.'</option>';
			};
		echo'</select>';
		
		echo'<select name="SubCategory" id="RSW_Sub_Category" onchange="this.form.submit()">';
			echo'<option value="all" ';
				if(isset($_POST['SubCategory']) && $_POST['SubCategory'] == 'all'){
					echo'selected';
				};
			echo' >Alle Sub Categorieën</option>';
			foreach($SubCategorys as $SubCategory){
				echo'<option value="'.$SubCategory.'" ';
				if(isset($_POST['SubCategory']) && $_POST['SubCategory'] == $SubCategory){
					echo'selected';
				};
				echo' >'.$SubCategory.'</option>';
			};
		echo'</select>';
		
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
				foreach($catagorie_list as $Category => $SubCategory_list){
						echo '<th colspan="'.count($SubCategory_list).'" ';
						echo'>';
							echo $Category;
						echo '</th>';
				};
			echo '</tr>';
			echo '<tr>';
				foreach($catagorie_list as $Category => $SubCategory_list){
					foreach($SubCategory_list as $SubCategory){
						echo '<th>';
							echo $SubCategory;
						echo '</th>';
					};
				};
			echo '</tr>';
			usort($Patrol_Score_list, function ($a, $b) {
				return $a['Patrol_Number'] - $b['Patrol_Number'];
			});
			foreach($Patrol_Score_list as $Patrol){
				echo '<tr';
				if(isset($_POST['SubField']) && $_POST['SubField'] != 'all' && $_POST['SubField'] != $Patrol['Patrol_subfield'])echo' style="display: none;" ';
				echo'>';
					echo '<th>';
						echo $Patrol['Patrol_Number'].' - '.$Patrol['Patrol_Name'];
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][id]" value="'.$Patrol['id'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Patrol_Number]" value="'.$Patrol['Patrol_Number'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Patrol_Name]" value="'.$Patrol['Patrol_Name'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Patrol_Position]" value="'.$Patrol['Patrol_Position'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Patrol_Points_total]" value="'.$Patrol['Patrol_Points_total'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Association_Name]" value="'.$Patrol['Association_Name'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Association_Sub_name]" value="'.$Patrol['Association_Sub_name'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Patrol_Name]" value="'.$Patrol['Patrol_Name'].'">';
						echo'<input type="hidden" name="Patrol_score['.$Patrol['id'].'][Patrol_subfield]" value="'.$Patrol['Patrol_subfield'].'">';
					echo '</th>';
					$tabIndex = 1;
					foreach($criterias as $criteria){
						echo '<th';
						if(isset($catagorie_list[$criteria->Categorie])){
							if(!in_array($criteria->Sub_Categorie,$catagorie_list[$criteria->Categorie]))echo' style="display: none;" ';
						}else{
							echo' style="display: none;" ';
						};
						echo '>';
							echo'<input onchange="if(this.value > '.$criteria->Total_Max_value.') this.value = '.$criteria->Total_Max_value.';" tabindex="'.$tabIndex.'" type="number" name="Patrol_score['.$Patrol['id'].'][Patrol_Points_json]['.$criteria->Categorie.']['.$criteria->Sub_Categorie.']" min="0" max="'.$criteria->Total_Max_value.'" value="';
							if(isset($Patrol['Patrol_Points_json'][$criteria->Categorie][$criteria->Sub_Categorie])){
								echo $Patrol['Patrol_Points_json'][$criteria->Categorie][$criteria->Sub_Categorie];
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