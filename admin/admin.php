<?php


class Strategery_Migrations_Admin {

	public function _init_migrations_menu_admin() {
		add_menu_page( 'Migrations', 'Migrations', 'manage_options', 'migrations-handle', array($this , 'migrations_list' ) );
		add_submenu_page( 'migrations-handle', 'Add New', 'Add New' , 'manage_options', 'add-new-handle', array(&$this , 'add_new'));
		
	}
	
	public function _init_migrations_menu_network_admin() {
		add_menu_page( 'Migrations', 'Migrations', 'manage_options', 'migrations-handle', array($this , 'migrations_list_network' ) );
		add_submenu_page( 'migrations-handle', 'Add New', 'Add New' , 'manage_options', 'add-new-handle', array(&$this , 'add_new'));
		add_submenu_page( 'migrations-handle', 'Blog List', 'Blog List' , 'manage_options', 'blog-list-handle', array(&$this , 'blog_list'));
		
	}
	
	/**
	 *  init_migrations_list
	 *  gets $migrations and asociated values and returns associative array
	 *  @param bool $network tests if it is for the network or site admin
	 *  @return array $migrations contains associative array of migration files and thier values
	 *  @see get_migrations
	**/
	
	public function init_migrations_list($network = false){
		$migrations = array();
		//get list of migration files
		$migrations = $this->get_migrations($network);
		$i = 0;
		//get migrations names from migrations file timestamps and inserts into migrations array
		foreach( $migrations as $migration => $file){
			preg_match ("/^\d{14}/" , $file['file'] ,$matches);
			if(empty($matches)) unset($migrations[$i]);
			else{
				$date = $this->format_date($matches[0]);
				$migrations[$i]['formatted_date'] = $date;
				$migrations[$i]['date'] = (string)$matches[0];
			}
			$i++;
		}//end foreach
		
		return $migrations;
	}
	
	public function migrations_list() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		$migrations = $this->init_migrations_list();
		
		include "migrations_list.php";
	}
	
	public function migrations_list_network() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		$migrations = $this->init_migrations_list(true);
		include "migrations_list.php";
	}
	
	public function add_new(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		include "add_new.php";
	}
	
	public function blog_list(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		include "blog_list.php";	
	}
	
	/**
	 *  format_date
	 *  Formats the date in YYYY/MM/DD and returns that string
	 *  @param string $date
	 *  @return string $date (formatted)
	**/
	 
	public function format_date($date){
		
		preg_match( '/^\d{8}/' ,$date , $ymd );
		preg_match( '/\d{2}$/' , $ymd[0] , $d);
		preg_match( '/^\d{6}/' , $date , $ym );
		preg_match( '/\d{2}$/' , $ym[0] , $m );
		preg_match( '/^\d{4}/' , $ym[0] ,$yr );
		
		$date = $m[0] . "/" . $d[0] . "/" . $yr[0];
		
		
		return $date;	
	}
	
	/**
	 *  get_migrations
	 *  gets an array of migration files and returns it
	 *  @param bool $network checks if it is the network or site admin
	 *  @return array $migrations
	**/
	
	public function get_migrations($network = false){
		$migrations = array();
		if( $network) {$handle = opendir('../../wp-content/plugins/strategery-migrations/migrations');}
		else $handle = opendir('../wp-content/plugins/strategery-migrations/migrations');
		if ($handle) {
		
			while (false !== ($file = readdir($handle))) {
				if( $file != '.' && $file != '..' && $file != '.DS_Store'  ) 
				$file_name = explode('-' , $file);
				$migrations[] = array( 'file' => $file , 'name' => $file_name[1]);
				
			}
			closedir($handle);
		}
	sort($migrations);
	return $migrations;
	}


}

// Actions to initiate admin menu in the backend
add_action('admin_menu', 'migrations_menu_admin_action');	
add_action('network_admin_menu', 'migrations_menu_admin_network_action');	

function migrations_menu_admin_action(){
	global $rr_migrations_admin;
	$rr_migrations_admin = new Strategery_Migrations_Admin();	
	$rr_migrations_admin->_init_migrations_menu_admin();
}

function migrations_menu_admin_network_action(){
	global $rr_migrations_admin;
	$rr_migrations_admin = new Strategery_Migrations_Admin();	
	$rr_migrations_admin->_init_migrations_menu_network_admin();
}