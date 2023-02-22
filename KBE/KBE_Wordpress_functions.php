<?php
function KBEAdminTable($TableData){
	
	global $wpdb;
	
	foreach($TableData['Columns'] as $key => $Column){
		$TableData['Columns'][$key]['Displayed'] = false;
		if(isset($Column['Primary']) && $Column['Primary'] == true){
			$TableData['Columns'][$key]['Displayed'] = true;
		};
		if(isset($_POST['KBE-hide'])){
			if(in_array($Column['Slug'],$_POST['KBE-hide'])){
				$TableData['Columns'][$key]['Displayed'] = true;
			};
		}else{
			$TableData['Columns'][$key]['Displayed'] = true;
		};
	};
	
	if((isset($_GET['action2']) && $_GET['action2'] != "-1") || (isset($_GET['action1']) && $_GET['action1'] != "-1")){
		foreach ($TableData['BulkActions'] as $BulkAction){
			if($BulkAction['Slug'] == $_GET['action2'] || $BulkAction['Slug'] == $_GET['action1']){
				if(function_exists($BulkAction['function'])){
					call_user_func($BulkAction['function']);
				};
			};
		};
	};
	
	if(isset($_GET['rowaction'])){
		foreach($TableData['Columns'] as $Column){
			if(isset($Column['RowActions'])){
				foreach($TableData[$Column['RowActions']] as $key => $RowAction){
					if($RowAction['Slug'] == $_GET['rowaction']){
						if(isset($RowAction['function']) && function_exists($RowAction['function'])){
							call_user_func($RowAction['function']);
						};
					};
				};
			};
		};
	};
	
	$Filter_SQL = "";
	$Search_SQL = "";
	$Sort_SQL = "";
	$SQL_Data = "";
	
	$FilterArray = array();
	foreach($_GET as $key => $value){
		if(substr($key,0,7) == "filter-"){
			if($value != 'all'){
				$Column_name = ltrim($key,"filter-");
				$FilterArray[$Column_name] = $value;
			};
		};
	};
	
	if(!empty($FilterArray)){
		foreach($FilterArray as $key => $value){
			$Filter_SQL = $Filter_SQL." ".$key." = '".$value."'";
			if ($key !== array_key_last($FilterArray)) {
				$Filter_SQL = $Filter_SQL . "AND ";
			}
		};
	};
	
	if(isset($_GET['s']) && $_GET['s'] != ""){
		$existing_columns = $wpdb->get_col("DESC {$TableData['Table']}", 0);
		foreach($existing_columns as $key => $existing_column){
			$Search_SQL = $Search_SQL . $existing_column;
			$Search_SQL = $Search_SQL . " LIKE ";
			$Search_SQL = $Search_SQL . "'%";
			$Search_SQL = $Search_SQL . $_GET['s'];
			$Search_SQL = $Search_SQL . "%' ";
			if ($key !== array_key_last($existing_columns)) {
				$Search_SQL = $Search_SQL . "OR ";
			}
		};
	};
	
	if(isset($_GET['orderby']) && isset($_GET['order'])){
		$Sort_SQL = $Sort_SQL.' ORDER BY ';
		$Sort_SQL = $Sort_SQL.$_GET['orderby'];
		
		if($_GET['order'] == "asc"){
			$Sort_SQL = $Sort_SQL." ASC";
		}else{
			$Sort_SQL = $Sort_SQL." DESC";
		};
	};
	
	if($Filter_SQL != "" || $Search_SQL != "" ||(isset($TableData['DefaultFilter']) && !empty($TableData['DefaultFilter']))){
		$SQL_Data = $SQL_Data . "WHERE ";
	};
	
	if($Filter_SQL != "" && $Search_SQL != ""){
		$SQL_Data = $SQL_Data . " ( ";
		$SQL_Data = $SQL_Data . $Search_SQL;
		$SQL_Data = $SQL_Data . " ) AND ";
		$SQL_Data = $SQL_Data . $Filter_SQL;
	}elseif($Search_SQL != ""){
		$SQL_Data = $SQL_Data . " ( ";
		$SQL_Data = $SQL_Data . $Search_SQL;
		$SQL_Data = $SQL_Data . " ) ";
	}elseif($Filter_SQL != ""){
		$SQL_Data = $SQL_Data . $Filter_SQL;
	};
	
	if(isset($TableData['DefaultFilter']) && !empty($TableData['DefaultFilter'])){
		
		if($Filter_SQL != "" || $Search_SQL != ""){
			$SQL_Data = $SQL_Data . " AND ";
		};
		
		foreach($TableData['DefaultFilter'] as $key => $value){
			$SQL_Data = $SQL_Data . " ".$key." = ".$value;
			if ($key !== array_key_last($TableData['DefaultFilter'])) {
				$SQL_Data = $SQL_Data . " AND ";
			}
		};
	};
		
	if($Sort_SQL != ""){
		$SQL_Data = $SQL_Data . $Sort_SQL;
	};
	
	$RSW_Criteria_List_count = $wpdb->get_var("SELECT COUNT(*) FROM {$TableData['Table']} $SQL_Data");
	
	if(isset($_POST['Items-per-Page']) && $RSW_Criteria_List_count < $_POST['Items-per-Page']){
		$NrPage = 1;
	}elseif($RSW_Criteria_List_count < 20){
		$NrPage = 1;
	}else{
		if(isset($_POST['Items-per-Page'])){
			$NrPage = ceil($RSW_Criteria_List_count / $_POST['Items-per-Page']);
		}else{
			$NrPage = ceil($RSW_Criteria_List_count / 20);
		};
		if(isset($_GET['paged'])){
			$curr_page = $_GET['paged'];
		}else{
			$curr_page = "1";
		};
	};
	
	$Limit_SQL = "";
	
	if($NrPage != 1){
		$Limit_SQL = $Limit_SQL . "LIMIT ";
		if(isset($_POST['Items-per-Page'])){
			$Limit_SQL = $Limit_SQL . $_POST['Items-per-Page'];
		}else{
			$Limit_SQL = $Limit_SQL . 20;
		};
		if(isset($_GET['paged']) && $_GET['paged'] != 1){
			$Limit_SQL = $Limit_SQL . " OFFSET ";
			if(isset($_POST['Items-per-Page'])){
				$Offset_Nr = (($_GET['paged'] - 1 ) * $_POST['Items-per-Page']) + 1;
			}else{
				$Offset_Nr = (($_GET['paged'] - 1 ) * 20) + 1;
			};
			$Limit_SQL = $Limit_SQL . $Offset_Nr;
		};
	};
	$RSW_Criteria_List = $wpdb->get_results("SELECT * FROM {$TableData['Table']} $SQL_Data $Limit_SQL");
	$DBColumns = $wpdb->get_col("DESC {$TableData['Table']}", 0);
	
	if(isset($TableData['ScreenMeta'])){
		$ScreenMeta = $TableData['ScreenMeta'];
		if(isset($ScreenMeta['table']) && $ScreenMeta['table'] == true){
			$ScreenMeta['Columns'] = $TableData['Columns'];
		};
		KBRScreenMeta($ScreenMeta);
	};
	
	echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">'.get_admin_page_title().'</h1>';
		if(isset($TableData["NewButton"])){
			$TMP_URL = admin_url().'admin.php?page=';
			$TMP_URL = $TMP_URL.$TableData["NewButton"];
			$TMP_URL = $TMP_URL."&KBRItemList=new";
			foreach($_GET as $key => $value){
				if(substr($key,0,7) == "filter-"){
					$TMP_URL = $TMP_URL . "&" . $key ."=". $value;
				};
			};
			echo '<a href="'.$TMP_URL.'" class="page-title-action">Add New</a>';
		};
		echo '<hr class="wp-header-end">';
		echo '<form id="'.get_admin_page_title().'-filter" method="get">';
			
			echo '<p class="search-box">';
				echo '<label class="screen-reader-text" for="get_admin_page_title()-search-input">Search '.get_admin_page_title().':</label>';
				echo '<input type="search" id="post-search-input" name="s" value="';
				if(isset($_GET['s'])){
					echo $_GET['s'];
				};
				echo '" />';
				echo '<input type="submit" id="search-submit" class="button" value="Search '.get_admin_page_title().'"  />';
			echo '</p>';
			
			echo '<input type="hidden" name="page" value="'.$_GET['page'].'" />';
			if(isset($_GET['order'])){
				echo '<input type="hidden" name="order" value="'.$_GET['order'].'" />';
			};
			if(isset($_GET['orderby'])){
				echo '<input type="hidden" name="orderby" value="'.$_GET['orderby'].'" />';
			};
			
			echo '<div class="tablenav top">';
				
				if(isset($TableData['BulkActions'])){
					KBEBulkActions($TableData['BulkActions']);
				};
				
				echo '<div class="alignleft actions">';
					foreach($TableData['Columns'] as $Column){
						if(isset($Column['Filterable']) && $Column['Filterable'] == true){
							echo '<select name="filter-'.$Column['DBName'].'" id="filter-by-'.$Column['Slug'].'" onchange="this.form.submit()">';
								echo '<option value="all">All '.$Column['Name'].'</option>';
								$Coll_name = $Column['DBName'];
								$Filters = $wpdb->get_results("SELECT DISTINCT $Coll_name FROM {$TableData['Table']} $SQL_Data");
								foreach($Filters as $Filter){
									echo '<option value="'.$Filter->$Coll_name.'" ';
									if(isset($_GET['filter-'.$Column['DBName']]) && $_GET['filter-'.$Column['DBName']] == $Filter->$Coll_name){
										echo "selected";
									};
									echo '>'.$Filter->$Coll_name.'</option>';
								};
							echo '</select>';
						};
					};
					echo '<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter"  />';
				echo '</div>';

				KBEPageNav($NrPage,$RSW_Criteria_List_count);
			
			echo '</div>';
			
			echo '<h2 class="screen-reader-text">Users '.get_admin_page_title().'</h2>';
			
			echo '<table class="wp-list-table widefat fixed striped table-view-list">';
			
				echo '<thead>';
					KBETableHeadFoot($TableData['Columns']);
				echo '</thead>';

				echo '<tfoot>';
					KBETableHeadFoot($TableData['Columns'],true);
				echo '</tfoot>';
			
				echo '<tbody>';
			
				$Row_Nr =0;
				foreach( $RSW_Criteria_List as $RSW_Criteria ) {
					echo '<tr  valign="top">';
					   echo '<th class="check-column" scope="row"><input name="KBRItemList[]" value="'.$RSW_Criteria->id.'" type="checkbox"';
					   if(isset($_GET['KBRItemList']) && in_array($RSW_Criteria->id,$_GET['KBRItemList'])){
						   echo ' checked ';
					   };
					   echo '></th>';
						foreach($TableData['Columns'] as $Column){
							if($Column['Displayed'] == true){
								echo '<td class="column-'.$Column['Slug'].'">';
								
								//echo '<strong>';
									if(isset($Column['DisplayName'])){
										$TMPName = $Column['DisplayName'];
										foreach($DBColumns as $DBColumn){
											$TMPName = str_replace($DBColumn,$RSW_Criteria->{$DBColumn},$TMPName);
										};
										echo $TMPName;
									}elseif(isset($Column['DBName'])){
										echo $RSW_Criteria->{$Column['DBName']};
									}elseif(isset($Column['Name'])){
										echo $RSW_Criteria->{$Column['Name']};
									};
								//echo '</strong>';
								if(isset($Column['RowActions'])){
									echo '<div class="row-actions">';
										foreach($TableData[$Column['RowActions']] as $RowActionNr => $RowAction){
											
											$TMPurl = "";
											
											$TMPurl = $TMPurl . admin_url(). 'admin.php?';
											
											$TMP_GET_Array = $_GET;
											if(isset($TMP_GET_Array['action1'])){
												unset($TMP_GET_Array['action1']);
											};
											if(isset($TMP_GET_Array['action2'])){
												unset($TMP_GET_Array['action2']);
											};
											
											if(isset($RowAction['link'])){
												$TMP_GET_Array['page'] = $RowAction['link'];
											};
											
											$TMP_GET_Array['KBRItemList'] = array($RSW_Criteria->id);
											
											$TMP_GET_Array['rowaction'] = $RowAction['Slug'];
											
											foreach($TMP_GET_Array as $key => $value){
												if(is_array($value)){
													foreach($value as $key_value => $value_value){
														$TMPurl = $TMPurl . $key.'%5B'.$key_value.'%5D='.$value_value;
														if ($key_value !== array_key_last($value)) {
															$TMPurl = $TMPurl . "&";
														}
													};
												}else{
													$TMPurl = $TMPurl . $key.'='.$value;
												};
												if ($key !== array_key_last($TMP_GET_Array)) {
													$TMPurl = $TMPurl . "&";
												};
											};
											
											echo '<span class="'.$RowAction['Slug'].'"><a href="';
											echo $TMPurl;
											echo'" >'.$RowAction['Name'].'</a>';
											if ($RowActionNr !== array_key_last($TableData[$Column['RowActions']])) {
												echo ' | ';
											}
											echo '</span>';
										};
									echo '</div>';
								};
								echo '</td>';
							};
						};
					echo '</tr>';
					$Row_Nr++;
				};
				echo '</tbody>';
			echo '</table>';
			
			echo '<div class="tablenav bottom">';
			
			if(isset($TableData['BulkActions'])){
				KBEBulkActions($TableData['BulkActions'],true);
			};

			KBEPageNav($NrPage,$RSW_Criteria_List_count,true);
			
			echo '</div>';
			
		echo '</form>';
		echo '<div class="clear"></div>';
	echo '</div>';


};

function KBEBulkActions($BulkActions,$bottom = false){

		echo '<div class="alignleft actions bulkactions">';
			echo '<label for="bulk-action-selector-';
			if($bottom == false){
				echo 'top';
			}else{
				echo 'bottom';
			};
			echo '" class="screen-reader-text">Select bulk action</label>';
			echo '<select name="action';
			if($bottom == false){
				echo '1';
			}else{
				echo '2';
			};
			echo '" id="bulk-action-selector-';
			if($bottom == false){
				echo 'top';
			}else{
				echo 'bottom';
			};
			echo '">';
				echo '<option value="-1" selected>Bulk actions</option>';
				foreach($BulkActions as $BulkAction){
					echo '<option value="'.$BulkAction['Slug'].'">'.$BulkAction['Name'].'</option>';
				};
			echo '</select>';
			echo '<input type="submit" id="doaction';
			if($bottom == false){
				echo '1';
			}else{
				echo '2';
			};
			echo '" class="button action" value="Apply"  />';
		echo '</div>';

};

function KBEPageNav($NrPage,$NrItems,$bottom = false){
	echo '<div class="tablenav-pages ';
		if($NrPage == 1){
			echo 'one-page';
		};
		echo '">';
		
		if($NrItems == 1){
			echo '<span class="displaying-num">1 item</span>';
		}else{
			echo '<span class="displaying-num">'.$NrItems.' items</span>';
		};
		
		if($NrPage != 1){
			echo '<span class="pagination-links">';
				if(isset($_GET['paged']) && $_GET['paged'] > 1){
					
					echo '<a class="first-page button" href="'.admin_url(). 'admin.php?';
					$TMP_GET_Array = $_GET;
					
					$TMP_GET_Array['paged'] = '1';
					if(isset($TMP_GET_Array['action1'])){
						unset($TMP_GET_Array['action1']);
					};
					if(isset($TMP_GET_Array['action2'])){
						unset($TMP_GET_Array['action2']);
					};
					if(isset($TMP_GET_Array['rowaction'])){
						unset($TMP_GET_Array['rowaction']);
					};
					
					foreach($TMP_GET_Array as $key => $value){
						if(is_array($value)){
							foreach($value as $key_value => $value_value){
								echo $key.'%5B'.$key_value.'%5D='.$value_value;
								if ($key_value !== array_key_last($value)) {
									echo "&";
								}
							};
						}else{
							echo $key.'='.$value;
						};
						if ($key !== array_key_last($TMP_GET_Array)) {
							echo "&";
						};
					};
					echo '">&laquo;</a>';
					
					echo '<a class="first-page button" href="'.admin_url(). 'admin.php?';
					$TMP_GET_Array = $_GET;
					
					if(isset($_GET['paged'])){
						$TMP_GET_Array['paged'] = $_GET['paged'] - 1;
					}else{
						$TMP_GET_Array['paged'] = '1';
					};
					
					if(isset($TMP_GET_Array['action1'])){
						unset($TMP_GET_Array['action1']);
					};
					if(isset($TMP_GET_Array['action2'])){
						unset($TMP_GET_Array['action2']);
					};
					if(isset($TMP_GET_Array['rowaction'])){
						unset($TMP_GET_Array['rowaction']);
					};
					
					foreach($TMP_GET_Array as $key => $value){
						if(is_array($value)){
							foreach($value as $key_value => $value_value){
								echo $key.'%5B'.$key_value.'%5D='.$value_value;
								if ($key_value !== array_key_last($value)) {
									echo "&";
								}
							};
						}else{
							echo $key.'='.$value;
						};
						if ($key !== array_key_last($TMP_GET_Array)) {
							echo "&";
						};
					};
					echo '">&lsaquo;</a>';
					
				}else{
					echo '<span class="tablenav-pages-navspan button disabled" >&laquo;</span>';
					echo '<span class="tablenav-pages-navspan button disabled" >&lsaquo;</span>';
				};
				
				echo '<span class="paging-input">';
					echo '<label for="current-page-selector" class="screen-reader-text">Current Page</label>';
					if($bottom == true){
						if(isset($_GET['paged'])){
							echo $_GET['paged'];
						}else{
							echo "1";
						};
					}else{
						echo '<input class="current-page" id="current-page-selector" type="text:" name="paged" value="';
						if(isset($_GET['paged'])){
							echo $_GET['paged'];
						}else{
							echo "1";
						};
						echo '" size="1" aria-describedby="table-paging" />';
					};
					
					echo '<span class="tablenav-paging-text"> of <span class="total-pages">'.$NrPage.'</span>';
				echo '</span>';
				echo '</span>';
					
				if((isset($_GET['paged']) && $_GET['paged'] < $NrPage)||(!isset($_GET['paged']) && $NrPage > 1)){
					
					echo '<a class="first-page button" href="'.admin_url(). 'admin.php?';
					$TMP_GET_Array = $_GET;
					
					if(isset($_GET['paged'])){
						$TMP_GET_Array['paged'] = $_GET['paged'] + 1;
					}else{
						$TMP_GET_Array['paged'] = '2';
					};
					
					if(isset($TMP_GET_Array['action1'])){
						unset($TMP_GET_Array['action1']);
					};
					if(isset($TMP_GET_Array['action2'])){
						unset($TMP_GET_Array['action2']);
					};
					if(isset($TMP_GET_Array['rowaction'])){
						unset($TMP_GET_Array['rowaction']);
					};
					
					foreach($TMP_GET_Array as $key => $value){
						if(is_array($value)){
							foreach($value as $key_value => $value_value){
								echo $key.'%5B'.$key_value.'%5D='.$value_value;
								if ($key_value !== array_key_last($value)) {
									echo "&";
								}
							};
						}else{
							echo $key.'='.$value;
						};
						if ($key !== array_key_last($TMP_GET_Array)) {
							echo "&";
						};
					};
					echo '">&rsaquo;</a>';
					
					echo '<a class="first-page button" href="'.admin_url(). 'admin.php?';
					$TMP_GET_Array = $_GET;
					
					$TMP_GET_Array['paged'] = $NrPage;
					
					if(isset($TMP_GET_Array['action1'])){
						unset($TMP_GET_Array['action1']);
					};
					if(isset($TMP_GET_Array['action2'])){
						unset($TMP_GET_Array['action2']);
					};
					if(isset($TMP_GET_Array['rowaction'])){
						unset($TMP_GET_Array['rowaction']);
					};
					
					foreach($TMP_GET_Array as $key => $value){
						if(is_array($value)){
							foreach($value as $key_value => $value_value){
								echo $key.'%5B'.$key_value.'%5D='.$value_value;
								if ($key_value !== array_key_last($value)) {
									echo "&";
								}
							};
						}else{
							echo $key.'='.$value;
						};
						if ($key !== array_key_last($TMP_GET_Array)) {
							echo "&";
						};
					};
					echo '">&raquo;</a>';
				}else{
					echo '<span class="tablenav-pages-navspan button disabled" >&rsaquo;</span>';
					echo '<span class="tablenav-pages-navspan button disabled" >&raquo;</span>';
				};
			echo '</span>';
		};
	echo '</div>';
	echo '<br class="clear" />';
};

function KBETableHeadFoot($Columns,$bottom = false){
		echo '<tr>';

		echo '<td id="cb" class="manage-column column-cb check-column">';
			echo '<label class="screen-reader-text" for="cb-select-all-';
			if($bottom = false){
				echo "2";
			}else{
				echo "1";
			};
			echo '">Select All</label>';
			echo '<input id="cb-select-all-';
			if($bottom = false){
				echo "2";
			}else{
				echo "1";
			};
			echo '" type="checkbox">';
		echo '</td>';
		foreach($Columns as $Column){
			if($Column['Displayed'] == true){
				echo '<th scope="col" id="'.$Column['Slug'].'" class="manage-column column-'.$Column['Slug'];
				
				if(isset($Column['Primary']) && $Column['Primary'] == true){
					echo ' column-primary ';
				};
				if(isset($Column['Sortable']) && $Column['Sortable'] == true){
					if(isset($_GET['orderby']) && $_GET['orderby'] == $Column['DBName']){
						echo ' sorted ';
					}else{
						echo ' sortable ';
					};
					
					if(isset($_GET['order']) && $_GET['order'] == "asc"){
						echo "asc";
					}else{
						echo "desc";
					};
				};
				echo '" scope="col">';
				
				if(isset($Column['Sortable']) && $Column['Sortable'] == true){
					echo '<a href="'.admin_url(). 'admin.php?';
					$TMP_GET_Array = $_GET;
					
					if(isset($Column['DBName'])){
						$TMP_GET_Array['orderby'] = $Column['DBName'];
					}elseif(isset($Column['Name'])){
						$TMP_GET_Array['orderby'] = $Column['Name'];
					};

					if(isset($TMP_GET_Array['order']) && $TMP_GET_Array['order'] == "asc"){
						$TMP_GET_Array['order'] = "desc";
					}else{
						$TMP_GET_Array['order'] = "asc";
					};
					
					if(isset($TMP_GET_Array['action1'])){
						unset($TMP_GET_Array['action1']);
					};
					if(isset($TMP_GET_Array['action2'])){
						unset($TMP_GET_Array['action2']);
					};
					if(isset($TMP_GET_Array['rowaction'])){
						unset($TMP_GET_Array['rowaction']);
					};
					
					foreach($TMP_GET_Array as $key => $value){
						if(is_array($value)){
							foreach($value as $key_value => $value_value){
								echo $key.'%5B'.$key_value.'%5D='.$value_value;
								if ($key_value !== array_key_last($value)) {
									echo "&";
								}
							};
						}else{
							echo $key.'='.$value;
						};
						if ($key !== array_key_last($TMP_GET_Array)) {
							echo "&";
						};
					};
					
					echo '"><span>';
				};
				
				echo $Column['Name'];
				if(isset($Column['Sortable']) && $Column['Sortable'] == true){
					echo '</span><span class="sorting-indicator">';
				};
				echo '</th>';
			};
		};

	echo '</tr>';
};

function KBRScreenMeta($Settings){
	
	if(isset($Settings['Columns'])){
		$TableData['Columns'] = $Settings['Columns'];
	};
	if(isset($Settings['FunctionData'])){
		$FunctionData = $Settings['FunctionData'];
	};
	if(isset($Settings['FunctionName'])){
		$FunctionName = $Settings['FunctionName'];
	};
	
	echo '<div id="screen-meta" class="metabox-prefs" style="display: none";">';
		echo '<div id="screen-options-wrap" class="hidden">';
			echo '<form method="post">';
				if(isset($TableData['Columns'])){
					echo '<fieldset>';
						echo '<legend>Columns</legend>';
						foreach($TableData['Columns'] as $Column){
							if(!isset($Column['Primary']) || $Column['Primary'] == false){
								echo '<label><input class="hide-column-tog" name="KBE-hide[]" type="checkbox" id="'.$Column['Slug'].'-hide" value="'.$Column['Slug'].'" ';
								if($Column['Displayed'] == true){
									echo 'checked="checked"';
								};
								echo ' />'.$Column['Name'].'</label>';						
							};
						};
					echo '</fieldset>';
					echo '<fieldset class="screen-options">';
						echo '<legend>Pagination</legend>';
						echo '<label for="edit_page_per_page">Number of items per page:</label>';
						echo '<input type="number" step="1" min="1" max="999" class="screen-per-page" name="Items-per-Page" id="editItems-per-Page" maxlength="3"value="';
						if(isset($_POST['Items-per-Page'])){
							echo $_POST['Items-per-Page'];
						}else{
							echo '20';
						};
						echo '" />';
					echo '</fieldset>';
				};
				if(isset($FunctionName) && function_exists($FunctionName)){
					if(isset($FunctionData)){
						call_user_func($FunctionName,$FunctionData);
					}else{
						call_user_func($FunctionName);
					};
				};
				echo '<p class="submit"><input type="submit" name="screen-options-apply" id="screen-options-apply" class="button button-primary" value="Apply"  /></p>';
			echo '</form>';
		echo '</div>';
	echo '</div>';
	
	echo '<div id="screen-meta-links">';
		
		echo '<div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">';
			echo '<button type="button" id="show-settings-link" class="button show-settings" aria-controls="screen-options-wrap" aria-expanded="false">Screen Options</button>';
		echo '</div>';
		
		echo '<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle">';
			echo '<button type="button" id="contextual-help-link" class="button show-settings" aria-controls="contextual-help-wrap" aria-expanded="false">Help</button>';
		echo '</div>';
		
	echo '</div>';
};

?>