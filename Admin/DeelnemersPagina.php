<?php
function RSWDeelnemersPagina(){
	global $wpdb;

	global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Association_table;
	global $RSW_Patrol_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $Plugin_Prefix;
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$TableData = array(
		"Columns" => array(
			array(
				"Slug" => "Scout-Surame",
				"Name" => "Voor naam",
				"Primary" => true,
				"Sortable" => true,
				"DBName" => "Scout_first_name",
				//"DisplayName" => "Scout_first_name Scout_last_name",
				"RowActions" => "RowActionsScout"
			),
			array(
				"Slug" => "Scout-Lastname",
				"Name" => "achter naam",
				"Sortable" => true,
				"DBName" => "Scout_last_name",
			),
			array(
				"Slug" => "Patol-name",
				"Name" => "Patrouille",
				"Sortable" => true,
				"Filterable" => true,
				"DBName" => "Patrol_Name",
				"DisplayName" => "Patrol_Name (Patrol_Number)",
			),
			array(
				"Slug" => "Subfield",
				"Name" => "Subkamp",
				"Sortable" => true,
				"DBName" => "Patrol_subfield",
				"Filterable" => true
			),
			array(
				"Slug" => "Association",
				"Name" => "Vereniging",
				"Sortable" => true,
				"DBName" => "Association_acronym",
				"DisplayName" => "Association_Name",
				"Filterable" => true
			),
			array(
				"Slug" => "SubAssociation",
				"Name" => "groep",
				"Sortable" => true,
				"DBName" => "Association_Sub_name",
				"Filterable" => true
			),
		),
		
		"NewButton" => "RSW-Edit-Deelnemers",
		
		"Table" => $RSW_Scout_table,
		
		"DefaultFilter" => array(
			'Year' => $Year,
		),
		
		"BulkActions" => array(
			array(
				"Slug" => "Delete",
				"Name" => "Delete",
				"function" => "RSWScoutDelete"
			)
		),
		
		"RowActionsScout" => array(
			array(
				"Slug" => "EditScout",
				"Name" => "Wijzigen",
				"link" => "RSW-Edit-Deelnemers"
			),
			
			array(
				"Slug" => "Delete",
				"Name" => "verwijderen",
				"function" => "RSWScoutDelete"
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
?>