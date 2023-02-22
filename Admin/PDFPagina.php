<?php
function RSWPDFPagina(){
	global $wpdb;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	global $RSW_Score_Page_Name;
	
	$ScreenMeta = array(
		"FunctionName" => "RSWPageMeta"
	);

	KBRScreenMeta($ScreenMeta);
	
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$Categorys = $wpdb->get_results("
		SELECT 
			Categorie
		FROM {$RSW_Criteria_table} 
		GROUP BY
			Categorie
	");
	
	// echo '<h4>QR Codes (werkt nog niet)</h4>';
	
	// foreach($Categorys as $Category){
		// echo '<a href="'.PDFCategorieQR($Category->Categorie).'" target="_blank" class="button">Jury '.$Category->Categorie.' lijst.pdf</a>';
	// };
	
	echo '<br><hr><h4>PDF Score lijsten</h4>';
	
	echo '<a href="'.PDFScoreLijstTotaal().'" target="_blank" class="button">ScoreLijstTotaal.pdf</a>';
	echo '<a href="'.PDFScoreLijstPatroille().'" target="_blank" class="button">_ScoreLijstPatrouille.pdf</a>';
	
	echo '<br><hr><h4>PDF lijsten van groepen</h4>';
	
	echo '<a href="'.PDFGroepenlijst().'" target="_blank" class="button">Groepenlijst.pdf</a>';
	echo '<a href="'.PDFSubkamplijst().'" target="_blank" class="button">Sunbkamplijst.pdf</a>';
	
	echo '<br><hr><h4>PDF Jury lijsten met criteria</h4>';
	
	foreach($Categorys as $Category){
		echo '<a href="'.PDFJurylijst($Category->Categorie).'" target="_blank" class="button">Jury '.$Category->Categorie.' lijst.pdf</a>';
	};
	
	echo '<br><hr><h4>PDF Jury lijsten alleen sub categorie</h4>';
	
	foreach($Categorys as $Category){
		echo '<a href="'.PDFJuryCategorielijst($Category->Categorie).'" target="_blank" class="button">Jury '.$Category->Categorie.' lijst.pdf</a>';
	};
};

class GroepenlijstPDF extends FPDF{
	function Footer(){
		global $wpdb;
		global $RSW_Edition_table;
		$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
		// Go to 1.5 cm from bottom
		$this->SetY(-15);
		// Select Arial italic 8
		$this->SetFont('Arial','I',8);
		// Print centered page number
		$this->Cell(0,10,'RSW '.$Year.'   Groepenlijst   Page '.$this->PageNo(),0,0,'C');
	}
};

class JuryPDF extends FPDF{
	function Footer(){
		global $wpdb;
		global $RSW_Edition_table;
		$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
		// Go to 1.5 cm from bottom
		$this->SetY(-15);
		// Select Arial italic 8
		$this->SetFont('Arial','I',8);
		// Print centered page number
		$this->Cell(0,10,'RSW '.$Year.'   Jury lijst   Page '.$this->PageNo(),0,0,'C');
	}
	
	function WordWrap(&$text, $maxwidth)
	{
		$text = trim($text);
		if ($text==='')
			return 0;
		$space = $this->GetStringWidth(' ');
		$lines = explode("\n", $text);
		$text = '';
		$count = 0;

		foreach ($lines as $line)
		{
			$words = preg_split('/ +/', $line);
			$width = 0;

			foreach ($words as $word)
			{
				$wordwidth = $this->GetStringWidth($word);
				if ($wordwidth > $maxwidth)
				{
					// Word is too long, we cut it
					for($i=0; $i<strlen($word); $i++)
					{
						$wordwidth = $this->GetStringWidth(substr($word, $i, 1));
						if($width + $wordwidth <= $maxwidth)
						{
							$width += $wordwidth;
							$text .= substr($word, $i, 1);
						}
						else
						{
							$width = $wordwidth;
							$text = rtrim($text)."\n".substr($word, $i, 1);
							$count++;
						}
					}
				}
				elseif($width + $wordwidth <= $maxwidth)
				{
					$width += $wordwidth + $space;
					$text .= $word.' ';
				}
				else
				{
					$width = $wordwidth + $space;
					$text = rtrim($text)."\n".$word.' ';
					$count++;
				}
			}
			$text = rtrim($text)."\n";
			$count++;
		}
		$text = rtrim($text);
		return $count;
	}
};

class SubfieldPDF extends FPDF{
	function Footer(){
		global $wpdb;
		global $RSW_Edition_table;
		$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
		// Go to 1.5 cm from bottom
		$this->SetY(-15);
		// Select Arial italic 8
		$this->SetFont('Arial','I',8);
		// Print centered page number
		$this->Cell(0,10,'RSW '.$Year.'   Subkamp lijst   Page '.$this->PageNo(),0,0,'C');
	}
};

function PDFGroepenlijst(){
	global $wpdb;
	global $RSW_Scout_table;
	global $wpdb;
	global $RSW_Edition_table;
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$AssociationList = $wpdb->get_results("
		SELECT 
			Association_Name,
			Association_acronym,
			Association_Sub_name,
			Association_Contact_name,
			Association_Contact_Phone_number,
			Association_Contact_Email,
			Association_catering_nr 
		FROM {$RSW_Scout_table} 
		WHERE Year = '$Year' 
		GROUP BY Association_Name,Association_Sub_name 
	");
	
	$wp_upload_dir = wp_upload_dir();
	$FileName = 'PatrouilleLijst.pdf';
	$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
	$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
	
	$pdf = new GroepenlijstPDF();
	$pdf->AddPage();
	foreach($AssociationList as $KeyAssociation => $Association){
		$pdf->SetFont('Arial','',12);
		
		$pdf->Cell(40,6,'Vereniging:');
		if($Association->Association_catering_nr != 0){
			$pdf->Cell(110,6,$Association->Association_Sub_name." ".$Association->Association_Name);
			$pdf->Cell(40,6,'Catering: '.$Association->Association_catering_nr,0,1);
		}else{
			$pdf->Cell(150,6,$Association->Association_Sub_name." ".$Association->Association_Name,0,1);
		};
		$pdf->Cell(40,6,'Contactpersoon:');
		$pdf->Cell(150,6,$Association->Association_Contact_name,0,1);
		$pdf->Cell(40,6,'Telefoon nmmer:');
		$pdf->Cell(150,6,$Association->Association_Contact_Phone_number,0,1);
		$pdf->Cell(40,6,'Email adres:');
		$pdf->Cell(150,6,$Association->Association_Contact_Email,0,1);
		
		$pdf->Cell(190,5,"","T",1);
		
		$PatrolList = $wpdb->get_results("
			SELECT 
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
			WHERE Year = '$Year' AND Association_Name = '{$Association->Association_Name}' AND Association_Sub_name = '{$Association->Association_Sub_name}'
			GROUP BY Patrol_Name
		");
		
		foreach($PatrolList as $Patrol){
			$pdf->Cell(50,6,'Naam: '.$Patrol->Patrol_Name,1);
			$pdf->Cell(30,6,'Nummer: '.$Patrol->Patrol_Number,1);
			$pdf->Cell(30,6,'Aantal: '.$Patrol->Patrol_Count,1);
			if($Patrol->Patrol_youngest == 1){
				$pdf->Cell(40,6,'Subkamp: '.$Patrol->Patrol_subfield,1);
				$pdf->Cell(40,6,'Jongste',1,1);
			}else{
				$pdf->Cell(80,6,'Subkamp: '.$Patrol->Patrol_subfield,1,1);
			};
			$pdf->Cell(190,1,'',1,1);
			
			$ScoutList = $wpdb->get_results("
				SELECT 
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
					Association_Name = '{$Association->Association_Name}' AND 
					Association_Sub_name = '{$Association->Association_Sub_name}' AND 
					Patrol_Name = '{$Patrol->Patrol_Name}'
				ORDER BY Scout_PL DESC,Scout_APL DESC
			");
			
			$pdf->Cell(20,6,"",1);
			$pdf->Cell(60,6,"Naam",1);
			$pdf->Cell(40,6,"Geboortedatum",1);
			$pdf->Cell(30,6,"Leeftijd",1);
			$pdf->Cell(40,6,"ScoutNL Nr.",1,1);
			foreach($ScoutList as $Scout){
				if($Scout->Scout_PL == 1){
					$pdf->Cell(20,6,"PL/RL",1);
				}elseif($Scout->Scout_APL == 1){
					$pdf->Cell(20,6,"APL/ARL",1);
				}else{
					$pdf->Cell(20,6,"",1);
				};
				$pdf->Cell(60,6,$Scout->Scout_first_name.' '.$Scout->Scout_last_name,1);
				$pdf->Cell(40,6,$Scout->Scout_birth_date,1);
				$pdf->Cell(30,6,$Scout->Scout_age,1);
				$pdf->Cell(40,6,$Scout->Scout_ScoutNL_number,1,1);
			};
			if($Patrol->Patrol_remark != ""){
				$pdf->MultiCell(190,6,$Patrol->Patrol_remark,1,1);
			};
			$pdf->Cell(190,3,"",0,1);
		};
		
		if ($KeyAssociation !== array_key_last($AssociationList)) {
			$pdf->AddPage();
		}
	};
	
	
	$pdf->Output($uploadedfilePath, "F");
	
	return $uploadedfileURL;
};

function PDFSubkamplijst(){
	global $wpdb;
	global $RSW_Scout_table;
	global $RSW_Subfield_table;
	global $wpdb;
	global $RSW_Edition_table;
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$Subfields = $wpdb->get_results("
		SELECT 
			color,
			color_code,
			name
		FROM {$RSW_Subfield_table} 
		WHERE Year = '$Year' 
	");
	
	$wp_upload_dir = wp_upload_dir();
	$FileName = 'SubkampLijst.pdf';
	$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
	$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
	
	$pdf = new SubfieldPDF();
	$pdf->AddPage();
	foreach($Subfields as $KeySubfield => $Subfield){
		
		$pdf->SetFont('Arial','',12);
		
		$pdf->Cell(190,6,'Subkamp kleur: '.$Subfield->color,0,1);
		$pdf->Cell(40,6,'Subkamp Naam: '.$Subfield->name,0,1);
		
		$pdf->Cell(190,5,"","T",1);
		
		$PatrolList = $wpdb->get_results("
			SELECT 
				Patrol_subfield,
				Patrol_Points_total,
				Patrol_Points_json,
				Patrol_Position,
				Patrol_Number,
				Patrol_Name,
				Patrol_Count,
				Patrol_remark,
				Patrol_youngest,
				Patrol_Avarage_age,
				Association_Name,
				Association_Sub_name
			FROM {$RSW_Scout_table} 
			WHERE Year = '$Year' AND 
			Patrol_subfield = '{$Subfield->color}' 
			GROUP BY 
				Patrol_Name,
				Association_Name,
				Association_Sub_name
		");
		
		foreach($PatrolList as $Patrol){
			
			$ScoutList = $wpdb->get_results("
				SELECT 
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
					Association_Name = '{$Patrol->Association_Name}' AND 
					Association_Sub_name = '{$Patrol->Association_Sub_name}' AND 
					Patrol_Name = '{$Patrol->Patrol_Name}'
				ORDER BY Scout_PL DESC,Scout_APL DESC
			");
			
			$NrScouts = count($ScoutList);
			$TableHeight = 13 + $NrScouts * 6;
			
			if($TableHeight + $pdf->GetY() > $pdf->GetPageHeight()){
				$pdf->AddPage();
			};
			
			$pdf->Cell(50,6,'Naam: '.$Patrol->Patrol_Name,1);
			$pdf->Cell(30,6,'Nummer: '.$Patrol->Patrol_Number,1);
			$pdf->Cell(30,6,'Aantal: '.$Patrol->Patrol_Count,1);
			if($Patrol->Patrol_youngest == 1){
				$pdf->Cell(40,6,'Subkamp: '.$Patrol->Patrol_subfield,1);
				$pdf->Cell(40,6,'Jongste',1,1);
			}else{
				$pdf->Cell(80,6,'Subkamp: '.$Patrol->Patrol_subfield,1,1);
			};
			$pdf->Cell(190,1,'',1,1);
			
			$pdf->Cell(20,6,"",1);
			$pdf->Cell(60,6,"Naam",1);
			$pdf->Cell(40,6,"Geboortedatum",1);
			$pdf->Cell(30,6,"Leeftijd",1);
			$pdf->Cell(40,6,"ScoutNL Nr.",1,1);
			foreach($ScoutList as $Scout){
				if($Scout->Scout_PL == 1){
					$pdf->Cell(20,6,"PL/RL",1);
				}elseif($Scout->Scout_APL == 1){
					$pdf->Cell(20,6,"APL/ARL",1);
				}else{
					$pdf->Cell(20,6,"",1);
				};
				$pdf->Cell(60,6,$Scout->Scout_first_name.' '.$Scout->Scout_last_name,1);
				$pdf->Cell(40,6,$Scout->Scout_birth_date,1);
				$pdf->Cell(30,6,$Scout->Scout_age,1);
				$pdf->Cell(40,6,$Scout->Scout_ScoutNL_number,1,1);
			};
			if($Patrol->Patrol_remark != ""){
				$pdf->MultiCell(190,6,$Patrol->Patrol_remark,1,1);
			};
			$pdf->Cell(190,3,"",0,1);
		};
		
		if ($KeySubfield !== array_key_last($Subfields)) {
			$pdf->AddPage();
		}
	};
	
	
	$pdf->Output($uploadedfilePath, "F");
	
	return $uploadedfileURL;
};

function PDFJurylijst($Categorie){
	global $wpdb;
	global $RSW_Scout_table;
	global $RSW_Subfield_table;
	global $RSW_Criteria_table;
	global $wpdb;
	global $RSW_Edition_table;
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$Subfields = $wpdb->get_results("
		SELECT 
			color,
			color_code,
			name
		FROM {$RSW_Subfield_table} 
		WHERE Year = '$Year' 
	");
	
	$wp_upload_dir = wp_upload_dir();
	$FileName = 'Jury'.$Categorie.'lijst.pdf';
	$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
	$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
	
	$pdf = new JuryPDF();
	$pdf->AddPage("L");
	foreach($Subfields as $KeySubfield => $Subfield){
		
		$Sub_Categories = $wpdb->get_results("
			SELECT 
				Sub_Categorie
			FROM {$RSW_Criteria_table} 
			WHERE 
				Categorie = '$Categorie' AND
				status = 'Active'
			GROUP BY
				Sub_Categorie
		");
		
		$pdf->SetFont('Arial','',12);
		
		$pdf->Cell(40,8,'Subkamp: ',1);
		$pdf->Cell(50,8,$Subfield->color." - ".$Subfield->name,1);
		$pdf->Cell(8,8,"");
		$pdf->Cell(30,8,"Jury 1",1);
		$pdf->Cell(50,8,"",1,1);
		$pdf->Cell(40,8,'Categorie: ',1);
		$pdf->Cell(50,8,$Categorie,1);
		$pdf->Cell(8,8,"");
		$pdf->Cell(30,8,"Jury 2",1);
		$pdf->Cell(50,8,"",1,1);
		
		$pdf->Cell(200,5,"",0,1);
		
		$PatrolList = $wpdb->get_results("
			SELECT 
				Patrol_subfield,
				Patrol_Points_total,
				Patrol_Points_json,
				Patrol_Position,
				Patrol_Number,
				Patrol_Name,
				Patrol_Count,
				Patrol_remark,
				Patrol_youngest,
				Patrol_Avarage_age,
				Association_Name,
				Association_Sub_name
			FROM {$RSW_Scout_table} 
			WHERE Year = '$Year' AND 
			Patrol_subfield = '{$Subfield->color}' 
			GROUP BY 
				Patrol_Name,
				Association_Name,
				Association_Sub_name
			ORDER BY
				Patrol_Number
		");
		foreach($Sub_Categories as $Sub_Categorie){
			
			$criterias = $wpdb->get_results("
				SELECT 
					Description,
					Max_value
				FROM {$RSW_Criteria_table} 
				WHERE 
					Categorie = '$Categorie' AND
					Sub_Categorie = '{$Sub_Categorie->Sub_Categorie}' AND
					status = 'Active'
			");
			
			$pdf->Cell(170,8,$Sub_Categorie->Sub_Categorie,1);
			foreach($PatrolList as $Patrol){
				$pdf->Cell(8,8,$Patrol->Patrol_Number,1,0,"C");
			};
			$pdf->Cell(15,8,"Max",1,1,"C");
			
			$Total_Max_Value = 0;
			
			foreach($criterias as $criteria){
				$LineHight = $pdf->WordWrap($criteria->Description, 170);
				if($LineHight > 1){
					$current_y = $pdf->GetY();
					$current_x = $pdf->GetX();
					$pdf->MultiCell(170,8,$criteria->Description,1);
					$current_x+=170;
					$pdf->SetXY($current_x, $current_y);
					foreach($PatrolList as $Patrol){
						$pdf->Cell(8,8*$LineHight,"",1,0,"C");
					};
					$pdf->Cell(15,8*$LineHight,$criteria->Max_value,1,1,"C");
				}else{
					$pdf->Cell(170,8,$criteria->Description,1);
					foreach($PatrolList as $Patrol){
						$pdf->Cell(8,8,"",1,0,"C");
					};
					$pdf->Cell(15,8,$criteria->Max_value,1,1,"C");
				};
				$Total_Max_Value = $Total_Max_Value + $criteria->Max_value;
			};
			
			$pdf->Cell(170,8,"Totaal",1);
			foreach($PatrolList as $Patrol){
				$pdf->Cell(8,8,"",1,0,"C");
			};
			$pdf->Cell(15,8,$Total_Max_Value,1,1,"C");
			
			$pdf->Cell(250,5,"",0,1);
		};
		
		
		if ($KeySubfield !== array_key_last($Subfields)) {
			$pdf->AddPage("L");
		}
	};
	
	
	$pdf->Output($uploadedfilePath, "F");
	
	return $uploadedfileURL;
};

function PDFJuryCategorielijst($Categorie){
	global $wpdb;
	global $RSW_Scout_table;
	global $RSW_Subfield_table;
	global $RSW_Criteria_table;
	global $wpdb;
	global $RSW_Edition_table;
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$Subfields = $wpdb->get_results("
		SELECT 
			color,
			color_code,
			name
		FROM {$RSW_Subfield_table} 
		WHERE Year = '$Year' 
	");
	
	$wp_upload_dir = wp_upload_dir();
	$FileName = 'Jury'.$Categorie.'lijstCategorie.pdf';
	$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
	$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
	
	$pdf = new JuryPDF();
	$pdf->AddPage("L");
	foreach($Subfields as $KeySubfield => $Subfield){
		
		$Sub_Categories = $wpdb->get_results("
			SELECT 
				Sub_Categorie
			FROM {$RSW_Criteria_table} 
			WHERE 
				Categorie = '$Categorie' AND
				Status = 'Active'
			GROUP BY
				Sub_Categorie
		");
		
		$pdf->SetFont('Arial','',12);
		
		$pdf->Cell(40,8,'Subkamp: ',1);
		$pdf->Cell(50,8,$Subfield->color." - ".$Subfield->name,1);
		$pdf->Cell(8,8,"");
		$pdf->Cell(30,8,"Jury 1",1);
		$pdf->Cell(50,8,"",1,1);
		$pdf->Cell(40,8,'Categorie: ',1);
		$pdf->Cell(50,8,$Categorie,1);
		$pdf->Cell(8,8,"");
		$pdf->Cell(30,8,"Jury 2",1);
		$pdf->Cell(50,8,"",1,1);
		
		$pdf->Cell(200,5,"",0,1);
		
		$PatrolList = $wpdb->get_results("
			SELECT 
				Association_acronym,
				Patrol_subfield,
				Patrol_Points_total,
				Patrol_Points_json,
				Patrol_Position,
				Patrol_Number,
				Patrol_Name,
				Patrol_Count,
				Patrol_remark,
				Patrol_youngest,
				Patrol_Avarage_age,
				Association_Name,
				Association_Sub_name
			FROM {$RSW_Scout_table} 
			WHERE Year = '$Year' AND 
			Patrol_subfield = '{$Subfield->color}' 
			GROUP BY 
				Patrol_Name,
				Association_Name,
				Association_Sub_name
			ORDER BY
				Patrol_Number
		");
		
		$pdf->Cell(80,10,"Patrouille",1);
		foreach($Sub_Categories as $Sub_Categorie){
			$pdf->Cell(50,10,$Sub_Categorie->Sub_Categorie,1);
		};
		$pdf->Cell(5,10,"",0,1,1);
		
		foreach($PatrolList as $Patrol){
			$pdf->Cell(80,10,$Patrol->Patrol_Number." - ".$Patrol->Patrol_Name." ".$Patrol->Association_acronym,1);
			foreach($Sub_Categories as $Sub_Categorie){
				$pdf->Cell(50,10,"",1);
			};
			$pdf->Cell(5,10,"",0,1,1);
		};
	
		$pdf->Cell(80,10,"Maximale waarde",1);
		foreach($Sub_Categories as $Sub_Categorie){
			
			$criterias = $wpdb->get_results("
				SELECT 
					Description,
					Max_value
				FROM {$RSW_Criteria_table} 
				WHERE 
					Categorie = '$Categorie' AND
					Sub_Categorie = '{$Sub_Categorie->Sub_Categorie}' AND
					Status = 'Active'
			");
			
			$Total_Max_Value = 0;
			foreach($criterias as $criteria){
				$Total_Max_Value = $Total_Max_Value + $criteria->Max_value;
			};
			
			$pdf->Cell(50,10,$Total_Max_Value,1);
		};
		
		$pdf->Cell(5,8,"",0,1,1);
		
		$pdf->Cell(250,5,"",0,1);
		if ($KeySubfield !== array_key_last($Subfields)) {
			$pdf->AddPage("L");
		}
	};
	
	
	$pdf->Output($uploadedfilePath, "F");
	
	return $uploadedfileURL;
};

function PDFCategorieQR($Categorie){
	global $wpdb;
	global $RSW_Scout_table;
	global $RSW_Subfield_table;
	global $RSW_Criteria_table;
	global $RSW_Score_Page_Name;
	global $wpdb;
	global $RSW_Edition_table;
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$Subfields = $wpdb->get_results("
		SELECT 
			color,
			color_code,
			name
		FROM {$RSW_Subfield_table} 
		WHERE Year = '$Year' 
	");
	
	$wp_upload_dir = wp_upload_dir();
	$FileName = 'PDF'.$Categorie.'QR.pdf';
	$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
	$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
	
	$pdf = new JuryPDF();
	$pdf->AddPage("L");
	foreach($Subfields as $KeySubfield => $Subfield){
		
		$pdf->SetFont('Arial','',12);
		
		$pdf->Cell(40,8,'Subkamp: ',1);
		$pdf->Cell(50,8,$Subfield->color." - ".$Subfield->name,1,1);
		$pdf->Cell(40,8,'Categorie: ',1);
		$pdf->Cell(50,8,$Categorie,1,1);
		
		$pdf->Cell(200,5,"",0,1);
		
		$pdf->Cell(200,5,get_page_link(get_page_by_title($RSW_Score_Page_Name)).'?Categorie='.$Categorie.'&Subkamp='.$Subfield->color.'&choe=UTF-8',0,1);
		
		$pdf->Cell(200,5,"",0,1);
		
		$ImgUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=';
		$ImgUrl = $ImgUrl . urlencode(get_page_link(get_page_by_title($RSW_Score_Page_Name)));
		$ImgUrl = $ImgUrl . urlencode('?Categorie='.$Categorie.'&Subkamp='.$Subfield->color);
		$ImgUrl = $ImgUrl . '&choe=UTF-8';
		
		$pdf->Image($ImgUrl, 50, 40, 150, 150, "png");
		
		if ($KeySubfield !== array_key_last($Subfields)) {
			$pdf->AddPage("L");
		}
	};
	
	
	$pdf->Output($uploadedfilePath, "F");
	
	return $uploadedfileURL;
};

function PDFScoreLijstTotaal(){
	global $wpdb;
	global $RSW_Scout_table;
	global $RSW_Subfield_table;
	global $RSW_Criteria_table;
	global $RSW_Score_Page_Name;
	global $wpdb;
	global $RSW_Edition_table;
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$Patrols = $wpdb->get_results("
		SELECT 
			*
		FROM {$RSW_Scout_table} 
		WHERE Year = '$Year'
		GROUP BY
			Association_Name,
			Association_Sub_name,
			Patrol_Name
		ORDER BY
			Patrol_Points_total DESC,
			Patrol_Number ASC
	");
	
	$wp_upload_dir = wp_upload_dir();
	$FileName = 'PDFScoreLijstTotaal.pdf';
	$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
	$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
	
	$pdf = new JuryPDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial','',10);
	
	$pdf->Cell(15,7,"Plaats",1);
	$pdf->Cell(140,7,"Patrouille",1);
	$pdf->Cell(20,7,"Punten",1,1);
	
	foreach($Patrols as $Patrol){
		$pdf->Cell(15,7,$Patrol->Patrol_Position,1,0,"C");
		$pdf->Cell(140,7,$Patrol->Patrol_Name." (".$Patrol->Patrol_Number.") - ".$Patrol->Association_Name." ".$Patrol->Association_Sub_name,1);
		$pdf->Cell(20,7,$Patrol->Patrol_Points_total,1,1);
	};
	
	
	$pdf->Output($uploadedfilePath, "F");
	
	return $uploadedfileURL;
};

function PDFScoreLijstPatroille(){
	global $wpdb;
	global $RSW_Scout_table;
	global $RSW_Subfield_table;
	global $RSW_Criteria_table;
	global $RSW_Score_Page_Name;
	global $RSW_Edition_table;
	$Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	$Categorys = $wpdb->get_results("
		SELECT 
			*,
			SUM(Max_value) as Total_Max_Value
		FROM {$RSW_Criteria_table} 
		GROUP BY
			Categorie
	");
	
	$Max_score = array();
	$weeg_factor = array();
	
	foreach($Categorys as $criteria){
		$Max_score[$criteria->Categorie] = $criteria->Total_Max_Value;
		$weeg_factor[$criteria->Categorie] = $criteria->Weeg_factor/100;
	};
	
	$Patrols = $wpdb->get_results("
		SELECT 
			*
		FROM {$RSW_Scout_table} 
		WHERE Year = '$Year'
		GROUP BY
			Association_Name,
			Association_Sub_name,
			Patrol_Name
		ORDER BY
			Patrol_Points_total DESC,
			Patrol_Number ASC
	");
	
	$Highest_Score = array();
	$category_rankings = array();
	foreach($Patrols as $KeyPatrol => $Patrol){
		$Patrol_Points_json = json_decode($Patrol->Patrol_Points_json);
		if(!empty($Patrol_Points_json)){
			foreach($Patrol_Points_json as $Category => $SubCategory_list){
				$Category_total = 0;
				foreach($SubCategory_list as $SubCategory){
					$Category_total = $Category_total + $SubCategory;
				};
				$category_rankings[$Category][$Patrol->Patrol_Number]['score'] = $Category_total;
				$category_rankings[$Category][$Patrol->Patrol_Number]['id'] = $Patrol->Patrol_Number;
				if(!isset($Highest_Score[$Category]) || $Highest_Score[$Category] < $Category_total)$Highest_Score[$Category] = $Category_total;
			};
		};
	};
	//print_r($category_rankings);
	foreach($category_rankings as $category => $category_ranking){
		uasort($category_rankings[$category], function ($a, $b) {
			return $b['score'] - $a['score'];
		});
	};
	
	foreach($category_rankings as $category => $category_ranking){
		$category_position = 0;
		$category_value = 1000;
		foreach($category_ranking as $Patrol_id => $Patrol){
			if($category_value > $Patrol['score']){
				$category_value = $Patrol['score'];
				$category_position++;
			};
			$category_rankings[$category][$Patrol_id]['position'] = $category_position;
		};
	};
	
	$Patrol_list = array();
	
	foreach($Patrols as $KeyPatrol => $Patrol){
		if(!empty($Patrol->Patrol_Points_json)){
			$Patrol_list[$Patrol->Patrol_Number]['Patrol_Position'] = $Patrol->Patrol_Position;
			$Patrol_list[$Patrol->Patrol_Number]['Patrol_Number'] = $Patrol->Patrol_Number;
			$Patrol_list[$Patrol->Patrol_Number]['Patrol_Name'] = $Patrol->Patrol_Name;
			$Patrol_list[$Patrol->Patrol_Number]['Association_acronym'] = $Patrol->Association_acronym;
			$Patrol_list[$Patrol->Patrol_Number]['Association_Name'] = $Patrol->Association_Name;
			$Patrol_list[$Patrol->Patrol_Number]['Patrol_Points_total'] = $Patrol->Patrol_Points_total;
			foreach($Categorys as $criteria){
				$Patrol_list[$Patrol->Patrol_Number]['categorie'][$criteria->Categorie]['weeg_factor'] = $weeg_factor[$criteria->Categorie];
				$Patrol_list[$Patrol->Patrol_Number]['categorie'][$criteria->Categorie]['score'] = round((1000/$Max_score[$criteria->Categorie])*$category_rankings[$criteria->Categorie][$Patrol->Patrol_Number]['score']);
				$Patrol_list[$Patrol->Patrol_Number]['categorie'][$criteria->Categorie]['position'] = $category_rankings[$criteria->Categorie][$Patrol->Patrol_Number]['position'];
			};
		};
	};
	
	// echo'<pre>';
	// print_r($Patrol_list);
	// echo'</pre>';
	
	$wp_upload_dir = wp_upload_dir();
	$FileName = 'PDFScoreLijstPatrouille.pdf';
	$uploadedfilePath = trailingslashit ( $wp_upload_dir['path'] ) . $FileName;
	$uploadedfileURL = trailingslashit ( $wp_upload_dir['url'] ) . $FileName;
	
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial','',12);
	
	foreach($Patrol_list as $KeyPatrol => $Patrol){
		
		$pdf->SetFont('Arial','B',20);
		$pdf->Cell(190,20,"RSW ".$Year." Score lijst van de ".$Patrol['Patrol_Name'],0,1);
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(40,10,"Positie",1);
		$pdf->Cell(80,10,$Patrol['Patrol_Position'],1,1);
		$pdf->Cell(40,10,"Groepsnummer",1);
		$pdf->Cell(80,10,$Patrol['Patrol_Number'],1,1);
		$pdf->Cell(40,10,"Patrouille",1);
		$pdf->Cell(80,10,$Patrol['Patrol_Name'],1,1);
		$pdf->Cell(40,10,"Vereniging",1);
		$pdf->Cell(80,10,$Patrol['Association_acronym']." ".$Patrol['Association_Name'],1,1);
		$pdf->Cell(40,10,"Totaal score",1);
		$pdf->Cell(80,10,$Patrol['Patrol_Points_total'],1,1);
		
		$pdf->Cell(190,5,"",0,1);
		
		$pdf->Cell(40,10,"Categorie",1);
		$pdf->Cell(40,10,"Behaalde punten",1);
		$pdf->Cell(40,10,"Maximale punten",1);
		$pdf->Cell(30,10,"Positie",1);
		$pdf->Cell(30,10,"Weegfactor",1,1);
		
		foreach($Patrol['categorie'] as $Category => $Category_data){
			
			$pdf->Cell(40,10,$Category,1);
			$pdf->Cell(40,10,$Category_data['score'],1);
			$pdf->Cell(40,10,'1000',1);
			$pdf->Cell(30,10,$Category_data['position'],1);
			$pdf->Cell(30,10,$Category_data['weeg_factor'].'%',1,1);
			
			// $pdf->Cell(45,10,$Category->Categorie,1);
			// $pdf->Cell(45,10,$PatrolCategoryPoints*$Category->Weeg_factor,1);
			// $pdf->Cell(45,10,$Category->Total_Max_Value*$Category->Weeg_factor,1);
			// $pdf->Cell(45,10,$Category->Weeg_factor.'%',1,1);
		};
		
		$pdf->Cell(185,5,"",0,1);
		$pdf->SetFont('Arial','',10);
		$comments = '* om alle categorrieen gelijk te houden worden ze naar 1000 punten omgerekend.';
		$comments .= '- De positie geeft aan hoe goed je gescoord hebt in die categorie (ten opzichte van andere groepen)';
		$comments .= '- Er kunnen meerdere groepen de zelfde positie hebben (bijv. er zijn er 5 met de hoogste score, jullie groep heeft een 2 positie behaald, dan ben je de op 5 na beste)';
		$pdf->MultiCell(190,5,$comments,0,1);
		
		if ($KeyPatrol !== array_key_last($Patrols)) {
			$pdf->AddPage();
		}
		
	};
	
	$pdf->Output($uploadedfilePath, "F");
	
	return $uploadedfileURL;
};

?>