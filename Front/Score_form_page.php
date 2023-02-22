<?php

add_shortcode('RSW_Score_Page', 'RSWScoreFormulierPagina');

function RSWScoreFormulierPagina(){
	ob_start();
	global $wpdb;

	global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	
	global $RSW_Score_Page_Name;
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	
	if(isset($_GET['Categorie']) && isset($_GET['Subkamp'])){
		
		$SubCategorys = $wpdb->get_results( "SELECT * FROM {$RSW_Criteria_table} WHERE Categorie = '{$_GET['Categorie']}' GROUP BY Sub_Categorie" );
		
		foreach($SubCategorys as $SubCategory){
			echo '<table is="ScoreFormTableFront">';
				echo '<tr>';
					echo '<td>';
						echo $SubCategory->Sub_Categorie;
					echo '</td>';
				echo '</tr>';
				$Criterias = $wpdb->get_results( "SELECT * FROM {$RSW_Criteria_table} WHERE Categorie = '{$_GET['Categorie']}' AND Sub_Categorie = '{$SubCategory->Sub_Categorie}'" );
				foreach($Criterias as $Criteria){
					echo '<tr>';
						echo '<td>';
							echo $Criteria->Description;
						echo '</td>';
					echo '</tr>';
				};
			echo '</table>';
		};
		
	}else{
		echo "Pagina kan niet gevonden worden";
	};
	
	$html_form = ob_get_clean();
    return $html_form;
};

?>