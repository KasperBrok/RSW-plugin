<?php
function RSWCriteriaPagina(){
	global $wpdb;

	global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Association_table;
	global $RSW_Patrol_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $Plugin_Prefix;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$TableData = array(
		"Columns" => array(
			array(
				"Slug" => "Sub-category-Name",
				"Name" => "Naam",
				"Primary" => true,
				"Sortable" => true,
				"DBName" => "Description",
				"RowActions" => "RowActions"
			),
			array(
				"Slug" => "Category-name",
				"Name" => "Categorie",
				"Sortable" => true,
				"DBName" => "Categorie",
				"Filterable" => true
			),
			array(
				"Slug" => "Sub-Category-name",
				"Name" => "Sub Categorie",
				"Sortable" => true,
				"DBName" => "Sub_Categorie",
				"Filterable" => true
			),
			array(
				"Slug" => "max-points",
				"Name" => "max punten",
				"DBName" => "Max_value"
			)
		),
		
		"Table" => $RSW_Criteria_table,
		
		"NewButton" => "RSW-Edit-Criteria",
		
		"DefaultFilter" => array(
			'Status' => "'Active'",
		),
		
		"BulkActions" => array(
			array(
				"Slug" => "Trash",
				"Name" => "move to trash",
				"function" => "RSWCriteriaTrash"
			),
			array(
				"Slug" => "Copy_last",
				"Name" => "Copy from last Year",
				"function" => "RSWCriteriaCopyLastYear"
			)
		),
		
		"RowActions" => array(
			array(
				"Slug" => "Edit",
				"Name" => "Edit",
				"link" => "RSW-Edit-Criteria"
			)
		),
		"ScreenMeta" => array(
			"table" => true,
			"FunctionName" => "RSWPageMeta",
			//"FunctionData" => $TableData
		)
	);

	KBEAdminTable($TableData);
};

function RSWCriteriaTrash(){
	global $wpdb;
	global $RSW_Criteria_table;
	if(isset($_GET['KBRItemList'])){
		foreach($_GET['KBRItemList'] as $ItemID){
			$updated = $wpdb->update( $RSW_Criteria_table, array("Status" => "Inactive"), array("id" => $ItemID) );
			if ( false === $updated ) {
				echo 'update failed at id: '.$Scout->id;
			};
		};
	};
};
?>