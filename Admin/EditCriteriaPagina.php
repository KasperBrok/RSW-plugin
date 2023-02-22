<?php
function RSWEditCriteriaPagina(){
	global $wpdb;
	global $RSWYear;
	global $RSW_Criteria_table;
	global $RSW_Edition_table;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	if(is_array($_GET['KBRItemList'])){
		if(count($_GET['KBRItemList']) == 1){
			$CriteriaId = $_GET['KBRItemList'][0];
			
			$initialCriteriaData = $wpdb->get_row("
				SELECT 
					* 
				FROM {$RSW_Criteria_table} 
				WHERE id = '$CriteriaId' 
			");
			$Year = $initialCriteriaData->Year;
		};
	}else{
		$Year = $RSWYear;
		if(isset($_GET['filter-Categorie']) && $_GET['filter-Categorie'] != 'all' && !isset($_POST['Categorie'])){
			$_POST['Categorie'] = $_GET['filter-Categorie'];
		};
		if(isset($_GET['filter-Sub_Categorie']) && $_GET['filter-Sub_Categorie'] != 'all' && !isset($_POST['Sub_Categorie'])){
			$_POST['Sub_Categorie'] = $_GET['filter-Sub_Categorie'];
		};
	};
	
	if(isset($_POST['Action']) && $_POST['Action'] == 'updaten'){
		if(isset($initialCriteriaData)){
			$updated = $wpdb->update( $RSW_Criteria_table, array(
				"Categorie" => $_POST['Categorie'],
				"Sub_Categorie" => $_POST['Sub_Categorie'],
				"Name" => $_POST['RSWName'],
				"Description" => $_POST['Description'],
				"Max_value" => $_POST['Max_value']
			), array( 'id' => $initialCriteriaData->id) );
			if(false === $updated){
				echo 'Error in update id: '.$Scout['id'];
			};
		}else{
			$inserted = $wpdb->insert( $RSW_Criteria_table, array(
				"Year" => $Year,
				"Categorie" => $_POST['Categorie'],
				"Sub_Categorie" => $_POST['Sub_Categorie'],
				"Name" => $_POST['RSWName'],
				"Description" => $_POST['Description'],
				"Max_value" => $_POST['Max_value'],
				"Status" => "Active"
			) );
			if(false === $inserted){
				echo 'Error in insert';
			};
		};
	};
	
	$Categorys = $wpdb->get_results("
		SELECT 
			Categorie
		FROM {$RSW_Criteria_table} 
		GROUP BY
			Categorie
	");
	
	$Sub_Categories = $wpdb->get_results("
		SELECT 
			Sub_Categorie
		FROM {$RSW_Criteria_table} 
		GROUP BY
			Sub_Categorie
	");
	
		// Year
        // Categorie
		// Weeg_factor
		// Sub_Categorie
		// Description
        // Max_value
		// Status_admin
	
	echo'<form method="post">';
		echo '<table>';
		
			echo '<tr>';
				echo '<td>';
					echo '<label for="Categorie">Categorie</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Categorie" name="Categorie" list="CategorieList" value="';
						if(isset($_POST['Categorie'])){
							echo $_POST['Categorie'];
						}elseif(isset($initialCriteriaData)){
							echo $initialCriteriaData->Categorie;
						};
					echo '" onchange="this.form.submit()">';
					echo '<datalist id="CategorieList">';
						foreach($Categorys as $Category){
							echo '<option value="'.$Category->Categorie.'">';
						};
					echo '</datalist>';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<label for="Sub_Categorie">Sub categorie</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Sub_Categorie" name="Sub_Categorie" list="Sub_CategorieList" value="';
						if(isset($_POST['Sub_Categorie'])){
							echo $_POST['Sub_Categorie'];
						}elseif(isset($initialCriteriaData)){
							echo $initialCriteriaData->Sub_Categorie;
						};
					echo '" onchange="this.form.submit()">';
					echo '<datalist id="Sub_CategorieList">';
						foreach($Sub_Categories as $Sub_Categorie){
							echo '<option value="'.$Sub_Categorie->Sub_Categorie.'">';
						};
					echo '</datalist>';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<label for="Name">Naam</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" id="Name" name="RSWName" list="NameList" value="';
						if(isset($_POST['RSWName'])){
							echo $_POST['RSWName'];
						}elseif(isset($initialCriteriaData)){
							echo $initialCriteriaData->Name;
						};
					echo '" onchange="this.form.submit()">';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<label for="Description">Omschrijving</label>';
				echo '</td>';
				echo '<td>';
					echo '<textarea  id="Description" name="Description" rows="4" cols="50">';
						if(isset($_POST['Description'])){
							echo $_POST['Description'];
						}elseif(isset($initialCriteriaData)){
							echo $initialCriteriaData->Description;
						};
					echo '</textarea>';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<label for="Max_value">Maximale punten</label>';
				echo '</td>';
				echo '<td>';
					echo '<input type="number" id="Max_value" name="Max_value" value="';
						if(isset($_POST['Max_value'])){
							echo $_POST['Max_value'];
						}elseif(isset($initialCriteriaData)){
							echo $initialCriteriaData->Max_value;
						};
					echo '">';
				echo '</td>';
			echo '</tr>';
		echo '<table>';
		echo '<input type="submit" id="Form-submit" class="button" name="Action" value="updaten" />';
	echo '</post>';
};
?>