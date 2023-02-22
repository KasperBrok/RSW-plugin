<?php
function RSWDashboardPagina(){
	global $wpdb;
	global $RSW_Edition_table;
	global $RSW_Settings_table;
	global $RSW_Scout_table;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Admin_Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	$Front_Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status = 'Active'" );
	
	echo'<table>';
		echo'<tr>';
			echo'<td>';
				echo'Top 10 ';
				if($Admin_Year != $Front_Year)echo $Front_Year;
			echo'</td>';
			if($Admin_Year != $Front_Year){
				echo'<td>';
					echo'Top 10 '.$Admin_Year;
				echo'</td>';
			};
		echo'</tr>';
		echo'<tr>';
			echo'<td>';
				$Patrols = $wpdb->get_results("
					SELECT 
						*
					FROM {$RSW_Scout_table} 
					WHERE Year = '$Front_Year'
					GROUP BY
						Association_Name,
						Association_Sub_name,
						Patrol_Name
					ORDER BY
						Patrol_Points_total DESC,
						Patrol_Number ASC
				");
				RSW_small_patrol_list($Patrols,10);
			echo'</td>';
			if($Admin_Year != $Front_Year){
				echo'<td>';
					$Patrols = $wpdb->get_results("
						SELECT 
							*
						FROM {$RSW_Scout_table} 
						WHERE Year = '$Admin_Year'
						GROUP BY
							Association_Name,
							Association_Sub_name,
							Patrol_Name
						ORDER BY
							Patrol_Points_total DESC,
							Patrol_Number ASC
					");
					RSW_small_patrol_list($Patrols,10);
				echo'</td>';
			};
		echo'</tr>';
	echo'</table>';
};

function RSW_small_patrol_list($Patrols, $Max_Nr_row = 0){
	$Patrols = json_decode(json_encode($Patrols),true);
	if($Max_Nr_row == 0){
		foreach($Patrols as $Patrol){
			RSW_small_patrol_row($Patrol);
		};
	}else{
		if(count($Patrols) < $Max_Nr_row) $Max_Nr_row = count($Patrols);
		for ($x = 0; $x < $Max_Nr_row; $x++) {
			RSW_small_patrol_row($Patrols[$x]);
		}
	};
};

function RSW_small_patrol_row($Patrol){
	echo $Patrol['Patrol_Position'].' - '.$Patrol['Patrol_Name'].' '.$Patrol['Association_Sub_name'].' '.$Patrol['Association_acronym'].'<br>';
};
?>