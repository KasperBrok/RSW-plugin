<?php
function RSW_install(){
    global $wpdb;

    global $Plugin_Prefix;
	global $RSW_Edition_table;
	global $RSW_Scout_table;
	global $RSW_Criteria_table;
	global $RSW_Subfield_table;
	global $RSW_Settings_table;
	
	global $RSW_Score_Page_Name;
	global $RSW_Sign_up_Page_Name;
	
	$charset_collate = $wpdb->get_charset_collate();
	
	$RSW_Edition_sql = "CREATE TABLE $RSW_Edition_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		Year text,
		start_date date,
		LSW_date date,
		Sign_up_code text,
		Sign_up_date date,
		Catering_date date,
		Subfield_date date,
		Status_admin text,
		Status text,
        PRIMARY KEY  (id)
    ) $charset_collate;";
	
	$RSW_Scout_sql = "CREATE TABLE $RSW_Scout_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		Year int,
		Association_Name text,
        Association_acronym text,
		Association_Sub_name text,
        Association_Contact_name text,
        Association_Contact_Phone_number text,
		Association_Contact_Email text,
        Association_catering_nr int,
		Association_sign_op_code text,
		Patrol_subfield text,
		Patrol_subfield_position int,
		Patrol_Points_total int,
		Patrol_Points_json JSON,
		Patrol_Spelmiddag_json JSON,
		Patrol_Position int,
        Patrol_Number int,
        Patrol_Name text,
		Patrol_Count int,
        Patrol_remark text,
        Patrol_youngest int,
		Patrol_Avarage_age DECIMAL(5,2),
        Scout_first_name text,
        Scout_last_name text,
        Scout_birth_date date,
		Scout_age int,
        Scout_ScoutNL_number text,
		Scout_PL TINYINT,
		Scout_APL TINYINT,
		PRIMARY KEY  (id)
	) $charset_collate;";
	
	$RSW_Criteria_sql = "CREATE TABLE $RSW_Criteria_table (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		Year int,
        Categorie text,
		Weeg_factor int,
		Sub_Categorie text,
		Description text,
		Name text,
        Max_value int,
		status text,
		PRIMARY KEY  (id)
	) $charset_collate;";
	
	$RSW_Subfield_sql = "CREATE TABLE $RSW_Subfield_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		Year int,
		color text,
		color_code text,
        name text,
        PRIMARY KEY  (id)
    ) $charset_collate;";
	
	$RSW_Settings_sql = "CREATE TABLE $RSW_Settings_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name text,
		data JSON,
        PRIMARY KEY  (id)
    ) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $RSW_Edition_sql );
	dbDelta( $RSW_Scout_sql );
	dbDelta( $RSW_Criteria_sql );
	dbDelta( $RSW_Subfield_sql );
	dbDelta( $RSW_Settings_sql );
	
	$page = get_page_by_title( $RSW_Score_Page_Name );

	if ( !isset($page) ){
		$PageGuid = site_url() . "/ScoreFormulieren";
		$my_post  = array( 'post_title'     => $RSW_Score_Page_Name,
						   'post_type'      => 'page',
						   'post_name'      => 'RSWScoreFormulieren',
						   'post_content'   => '[RSW_Score_Page]',
						   'post_status'    => 'publish',
						   'comment_status' => 'closed',
						   'ping_status'    => 'closed',
						   'post_author'    => 1,
						   'menu_order'     => 0,
						   'guid'           => $PageGuid );

		$PageID = wp_insert_post( $my_post, FALSE ); // Get Post ID - FALSE to return 0 instead of wp_error.
	}
	
	$page = get_page_by_title( $RSW_Sign_up_Page_Name );
	if ( !isset($page) ){
		$PageGuid = site_url() . "/inschrijven";
		$my_post  = array( 'post_title'     => $RSW_Sign_up_Page_Name,
						   'post_type'      => 'page',
						   'post_name'      => 'RSWScoreFormulieren',
						   'post_content'   => '[RSW_Sign_up_Page]',
						   'post_status'    => 'publish',
						   'comment_status' => 'closed',
						   'ping_status'    => 'closed',
						   'post_author'    => 1,
						   'menu_order'     => 0,
						   'guid'           => $PageGuid );

		$PageID = wp_insert_post( $my_post, FALSE ); // Get Post ID - FALSE to return 0 instead of wp_error.
	}

};
?>