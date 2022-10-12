<?php
/**
 * Plugin Name: Import Houses
 * Plugin URI: 
 * Description: 
 * Version: 1.0
 * Author: Raja Saqib
 * Author URI:
 */

if ( !class_exists('SMKImportHouses')){

	class SMKImportHouses{

		public $data	=	'';

		function __construct(){

			register_activation_hook( __FILE__, array(&$this, 'install') );
			register_deactivation_hook(__FILE__,  array(&$this,'my_deactivation'));

			add_action('admin_menu', array(&$this,'adminMenu'));
			add_action('admin_init', array(&$this,'activation_redirect'));
			add_action('admin_enqueue_scripts', array(&$this, 'AdminEnqueueScripts') );
			add_action('wp_enqueue_scripts', array(&$this, 'wpEnqueueScripts') );
			
			//Shortcode to view the data in the Pages/Posts
			add_shortcode('view_houses', array(&$this, 'frontEnd'));
		}

		function install(){
			//Add redirect option on plugin activation to Admin menu page
			add_option('redirect_after_activation_option', true);
		}

		// Function that activates for redirection
		function activation_redirect() {
			if (get_option('redirect_after_activation_option', false)) {
				delete_option('redirect_after_activation_option');
				exit(wp_redirect(admin_url( '/admin.php?page=Houses_import' )));
			}
		}

		function my_deactivation() {
			
		}

		//Function to enqueue scripts at admin side
		function AdminEnqueueScripts(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('js-bootstrap',plugins_url('js/bootstrap.min.js', __FILE__));
			wp_enqueue_style( 'css-bootstrap', plugins_url('css/bootstrap.min.css', __FILE__) );
		}

		//Function to enqueue custom script for frontend styles
		function wpEnqueueScripts(){
			wp_enqueue_style( 'custom-css', plugins_url('css/custom.css', __FILE__) );
		}
		
		function adminMenu(){
			add_menu_page('Houses', 'Houses', 'manage_options', 'Houses_import', array(&$this, 'backEnd'), 'dashicons-admin-home' );
		}
		
		// Main Function to get the data from the API
		function importHouses(){
			
			// API request URL
			$response = wp_remote_get( "https://anapioficeandfire.com/api/houses" );
			$body     = wp_remote_retrieve_body( $response );

			if (empty($body)) {
				
				$html = '
					<div class="table-wrap">
						<p>Response not found!</p>
					</div>';

				return $html;

			} else {

				$array = json_decode($body);
				$main_array = (array)$array;
				$this->data	=	$main_array;

				$html = '
				<div class="container table-wrap">
					<h2>List of items from an API</h2>
					<table class="table table-bordered table-striped">
						<thead class="thead-dark">
							<tr>
								<th scope="col">#</th>
								<th scope="col">Name</th>
								<th scope="col">Region</th>
								<th scope="col">Coat of Arms</th>
								<th scope="col">Words</th>
							</tr>
						</thead>
						<tbody>';
		
				foreach( $this->data as $key => $house ) : 
					$html .=	"
					<tr>
						<th scope='row'>$key</th>
						<td>$house->name</td>
						<td>$house->region</td>
						<td>$house->coatOfArms</td>
						<td>$house->words</td>
					</tr>";
				endforeach;
	
				$html .= '</tbody></table></div>';
				
			}

			return $html;
		
		}


		// Function to display the data using shortcode on the FrontEnd.
		function frontEnd(){
			return $this->importHouses();
		}

		// Function to display the data in the backend admin Menu Page ( Houses )
		function backEnd(){
			echo $this->importHouses();
		}

		
	}//end class
}//end main class
$SMKImport = new SMKImportHouses();