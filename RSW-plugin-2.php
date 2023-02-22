<?php
/**
* Plugin Name: RSW-Plugin 2.0
* Plugin URI: https://www.familiebrok.com/
* Description: A plugin to manage RSW scores.
* Version: 2.0
* Author: Kasper Brok
* Author URI: https://www.familiebrok.com/
**/

global $wpdb;

global $Plugin_Prefix;
global $RSW_Edition_table;
global $RSW_Scout_table;
global $RSW_Criteria_table;
global $RSW_Subfield_table;
global $RSW_Settings_table;

global $RSW_Plugin_dir;

$Plugin_Prefix = "RSW_";
$RSW_Edition_table = $wpdb->prefix . $Plugin_Prefix . "Edition_table";
$RSW_Scout_table = $wpdb->prefix . $Plugin_Prefix . "Scout_table";
$RSW_Criteria_table = $wpdb->prefix . $Plugin_Prefix . "Criteria_table";
$RSW_Subfield_table = $wpdb->prefix . $Plugin_Prefix . "Subfield_table";
$RSW_Settings_table = $wpdb->prefix . $Plugin_Prefix . "Settings_table";

global $RSW_Score_Page_Name;
global $RSW_Sign_up_Page_Name;
$RSW_Score_Page_Name = $Plugin_Prefix . "Scoreformulier";
$RSW_Sign_up_Page_Name = $Plugin_Prefix . "Inschrijven";

$RSW_Plugin_dir = dirname( __FILE__ );

require_once( $RSW_Plugin_dir . '/PDF/FPDF/fpdf.php' );

require_once ( $RSW_Plugin_dir . '/install/install.php' );

require_once ( $RSW_Plugin_dir . '/Admin/DashboardPagina.php' );
require_once ( $RSW_Plugin_dir . '/Admin/AdminScoreFormulier.php' );
require_once ( $RSW_Plugin_dir . '/Admin/Subkampen.php' );
require_once ( $RSW_Plugin_dir . '/Admin/CriteriaPagina.php' );
require_once ( $RSW_Plugin_dir . '/Admin/EditCriteriaPagina.php' );
require_once ( $RSW_Plugin_dir . '/Admin/DeelnemersPagina.php' );
require_once ( $RSW_Plugin_dir . '/Admin/EditDeelnemersPagina.php' );
require_once ( $RSW_Plugin_dir . '/Admin/AdminSpelmiddagFormulier.php' );
require_once ( $RSW_Plugin_dir . '/Admin/PDFPagina.php' );
require_once ( $RSW_Plugin_dir . '/Front/Score_form_page.php' );
require_once ( $RSW_Plugin_dir . '/Front/Sign_up_page.php' );
require_once ( $RSW_Plugin_dir . '/KBE/KBE_Wordpress_functions.php' );

register_activation_hook( __FILE__, 'RSW_install' );

add_action('admin_enqueue_scripts','RSW_admin_scripts');
function RSW_admin_scripts() {
	wp_enqueue_style( 'RSW_Admin_style', plugins_url( '/css/admin.css', __FILE__ ) );
	wp_register_script( 'RSW_admin_script', plugins_url( '/js/admin.js', __FILE__ ), array('jquery','jquery-ui-dialog','jquery-ui-draggable','jquery-ui-droppable','jquery-ui-sortable'));
	wp_enqueue_script( 'RSW_admin_script' );
	wp_localize_script( 'RSW_admin_script', 'MyAjax', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'security' => wp_create_nonce( 'my-special-string' )
	));
};

add_action( 'wp_enqueue_scripts', 'RSW_front_scripts' );
function RSW_front_scripts() {
    wp_register_script( 'RSW_Front_script', plugins_url( '/js/front.js' , __FILE__ ),array('jquery','jquery-ui-dialog','jquery-ui-draggable','jquery-ui-droppable','jquery-ui-sortable') );

    wp_enqueue_script( 'RSW_Front_script' );
	
	$variables = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' )
    );
    wp_localize_script('RSW_Front_script', "RSW", $variables);
	
	wp_enqueue_style( 'RSW_Front_style', plugins_url( '/css/front.css', __FILE__ ) );
	wp_enqueue_style('jquery-style', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
	wp_enqueue_style( 'dashicons' );
}

add_action('admin_menu', 'RSW_Admin_Menu');
function RSW_Admin_Menu(){
    add_menu_page(
        'RSW Dashboard',// the page title
        'RSW',//menu title
        'manage_options',//capability 
        'RSW-Main',//menu slug
        'RSWDashboardPagina',//callback function
        '',//icon_url,
        '30'//position
    );
    add_submenu_page(
        'RSW-Main',//Main menu slug
        'Deelnemers', //page title
        'Deelnemers', //menu title
        'manage_options', //capability,
        'RSW-Deelnemers',//menu slug
        'RSWDeelnemersPagina' //callback function
    );
	add_submenu_page(
        'RSW-Deelnemers',//Main menu slug
        'Edit Deelnemers', //page title
        'Edit-Deelnemers', //menu title
        'manage_options', //capability,
        'RSW-Edit-Deelnemers',//menu slug
        'RSWEditDeelnemersPagina' //callback function
    );
	add_submenu_page(
        'RSW-Main',//Main menu slug
        'Subkampen', //page title
        'Subkampen', //menu title
        'manage_options', //capability,
        'RSW-Subkampen',//menu slug
        'RSWSubkampenPagina' //callback function
    );
	add_submenu_page(
        'RSW-Main',//Main menu slug
        'Scoreformulieren', //page title
        'Scoreformulieren', //menu title
        'manage_options', //capability,
        'RSW-Scoreformulieren',//menu slug
        'RSWScoreformulierenPagina' //callback function
    );
	add_submenu_page(
        'RSW-Main',//Main menu slug
        'Spelmiddagformulieren', //page title
        'Spelmiddagformulieren', //menu title
        'manage_options', //capability,
        'RSW-Spelmiddagformulieren',//menu slug
        'RSWSpelmiddagformulierenPagina' //callback function
    );
	add_submenu_page(
        'RSW-Main',//Main menu slug
        'Criteria', //page title
        'Criteria', //menu title
        'manage_options', //capability,
        'RSW-Criteria',//menu slug
        'RSWCriteriaPagina' //callback function
    );
	add_submenu_page(
        'RSW-Criteria',//Main menu slug
        'Edit Criteria', //page title
        'Edit-Criteria', //menu title
        'manage_options', //capability,
        'RSW-Edit-Criteria',//menu slug
        'RSWEditCriteriaPagina' //callback function
    );
	add_submenu_page(
        'RSW-Main',//Main menu slug
        'PDF', //page title
        'PDF', //menu title
        'manage_options', //capability,
        'RSW-PDF',//menu slug
        'RSWPDFPagina' //callback function
    );
};

add_action( 'wp_ajax_RSW_Patrol_list', 'RSW_Patrol_list' );
add_action( 'wp_ajax_RSW_Sub_Category_list', 'RSW_Sub_Category_list' );
add_action( 'wp_ajax_RSW_Patrol_score_overview', 'RSW_Patrol_score_overview' );
add_action( 'wp_ajax_RSW_patrol_score_submit', 'RSW_patrol_score_submit' );
add_action( 'wp_ajax_Create_PDF_Patrouille_Table', 'Create_PDF_Patrouille_Table' );
add_action( 'wp_ajax_Create_PDF_Patrouille_sheets', 'Create_PDF_Patrouille_sheets' );

add_action("wp_ajax_RSW_Scout_Row" , "RSW_Scout_Row");
add_action("wp_ajax_nopriv_RSW_Scout_Row" , "RSW_Scout_Row");

add_action("wp_ajax_RSW_Patrol_Row" , "RSW_Patrol_Row");
add_action("wp_ajax_nopriv_RSW_Patrol_Row" , "RSW_Patrol_Row");

add_action("wp_ajax_RSW_handle_Sign_up" , "RSW_handle_Sign_up");
add_action("wp_ajax_nopriv_RSW_handle_Sign_up" , "RSW_handle_Sign_up");

function RSWPageMeta(){
	global $wpdb;
	global $RSW_Edition_table;
	global $RSWYear;
	
	$RSW_Year_List = $wpdb->get_results("SELECT id,Year FROM {$RSW_Edition_table} ORDER BY Year");
	$Max_Year = $wpdb->get_var( "SELECT Year FROM {$RSW_Edition_table} WHERE Status_admin = 'Active'" );
	
	if(isset($_POST['RSWYears'])){
		$RSWYear = $_POST['RSWYears'];
		foreach($RSW_Year_List as $RSW_Year){
			if($RSW_Year->Year == $RSWYear){
				$updated = $wpdb->update( $RSW_Edition_table, array("Status_admin" => "Active"), array( 'id' => $RSW_Year->id) );
				if(false === $updated){
					echo 'Error in update id: '.$RSW_Year->id;
				};
			}else{
				$updated = $wpdb->update( $RSW_Edition_table, array("Status_admin" => "Inactive"), array( 'id' => $RSW_Year->id) );
				if(false === $updated){
					echo 'Error in update id: '.$RSW_Year->id;
				};
			};
		};
	}else{
		$RSWYear = $Max_Year;
	};
	
	echo '<fieldset class="metabox-prefs view-mode">';
		echo '<legend>RSW settings</legend>';
		echo '<label for="RSWYears">Selecteer een jaar:</label>';

		echo '<select name="RSWYears" id="RSWYears">';
			foreach( $RSW_Year_List as $RSW_Year ) {
				echo '<option value="'.$RSW_Year->Year.'" ';
				if($RSW_Year->Year == $RSWYear){
					echo "selected";
				};
				echo '>'.$RSW_Year->Year.'</option>';
			};
		echo '</select>';
	echo '</fieldset>';
	
};

?>