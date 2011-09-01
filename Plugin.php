<?php

/*
 * Plugin Name: Strategery Migrations
 * Plugin URI: http://usestrategery.com
 * Description: Allows for easier migrations to the database.
 * Author: Gabriel Somoza (me@gabrielsomoza.com) , Joseph Fitzgibbons (jfitzy87@gmail.com)
 * Version: 1.0
 * Author URI: http://gabrielsomoza.com
 * Compatibility: Wordpress 3.0+
 */

if (!defined('ST_MIGRATIONS_VERSION'))
    define('ST_MIGRATIONS_VERSION', '0.1');

if (!defined('ST_MIGRATIONS_BASE_PATH'))
    define('ST_MIGRATIONS_BASE_PATH', realpath(dirname(__FILE__)));

if (!defined('ST_MIGRATIONS_LIB'))
    define('ST_MIGRATIONS_LIB', ST_MIGRATIONS_BASE_PATH . '/lib');

if (!defined('ST_MIGRATIONS_TEMPLATES'))
    define('ST_MIGRATIONS_TEMPLATES', ST_MIGRATIONS_BASE_PATH . '/templates');

if (!defined('ST_MIGRATIONS_HELPERS_PATH'))
    define('ST_MIGRATIONS_HELPERS_PATH', ST_MIGRATIONS_LIB . '/Helpers');

if (!defined('ST_MIGRATIONS_MODELS_PATH'))
    define('ST_MIGRATIONS_MODELS_PATH', ST_MIGRATIONS_LIB . '/Models');

if (!defined('ST_MIGRATIONS_DIR'))
    define('ST_MIGRATIONS_DIR', PLUGINDIR . '/strategery-migrations/migrations');

if (!defined('ST_MIGRATIONS_URL'))
    define('ST_MIGRATIONS_URL', plugins_url('strategery-migrations'));

require_once ST_MIGRATIONS_LIB . '/Exception.php';
require_once ST_MIGRATIONS_LIB . '/Core.php';

class Strategery_Migrations_Plugin extends Strategery_Migrations_Core {
    const OPTION_MIGRATION_STATE = 'migration_state';
    const FILENAME_REGEX = '/^(\d{14})-([A-Za-z][A-Za-z0-9_]*)$/';
    const QUERY_TRIGGER = 'migrate';
    const QUERY_ACTION = 'action';
    const QUERY_METHOD = 'method';
    const QUERY_NAME = 'name';
	const QUERY_NEW_NAME = 'newname';
    const QUERY_TEMPLATE = 'template';
	const QUERY_BACKEND = 'backend';
	const QUERY_CALLING_URL = 'callingurl';
	const QUERY_FILE = 'file';
    const QUERY_ID = 'id';
    const METHOD_UP = 'up';
    const METHOD_DOWN = 'down';
	

    protected $migrateMethods;
    
    public function newAction() {
        $name = get_query_var(self::QUERY_NAME);
		$calling_url = $_GET[self::QUERY_CALLING_URL];
		$backend = $_GET[self::QUERY_BACKEND];
        if(empty($name)) {
            throw new Strategery_Migrations_Exception('No name specified.');
        }
        $template = get_query_var(self::QUERY_TEMPLATE);
        if(empty($template)) {
            $template = 'default';
        }
        $new_name = $this->getNewMigrationName($name);
        $new_class = $this->underscoreToClassName($name);
        $template = $this->getMigrationTemplate($new_class, $template);
        if(!$backend) $this->log('Creating migration ' . $new_name);
        $path = ST_MIGRATIONS_DIR . '/' . $new_name;
        if(file_put_contents($path, $template, LOCK_EX)) {
            if(!backend) $this->log('File created at: ' . $path);
			wp_redirect($calling_url);
        } else {
            $this->log('Unable to create file at: ' . $path);
        }
    }
    
    protected function getMigrationTemplate($class, $template = 'default') {
        $template = file_get_contents(ST_MIGRATIONS_TEMPLATES . '/' . $template . '.txt');
        return str_replace('{{class_name}}', $class, $template);
    }
    
    protected function getTimestamp() {
        return date('YmdHis');
    }
    
    public function getNewMigrationName($method) {
        return $this->getTimestamp() . '-' . $method . '.php';
    }

    public function migrateAction() {
        $old_blog_id = get_current_blog_id();
        $blog_id = get_query_var(self::QUERY_ID);
        if(empty($blog_id))
            $blog_id = $old_blog_id;
        $this->migrate($blog_id);
        if($old_blog_id != $blog_id)
            switch_to_blog($old_blog_id);
    }

    public function migrateAllAction() {
        $db = $this->db();
        $blogs = $db->get_col($db->prepare("SELECT blog_id FROM $db->blogs"));
        foreach ($blogs as $blog_id) {
            $this->migrate($blog_id);
        }
    }

    public function runAction() {
        if (!$id = intval(get_query_var('id'))) {
            throw new Strategery_Migrations_Exception('No migration ID specified.');
        }
        $method = get_query_var('method') ? get_query_var('method') : self::METHOD_UP;
        foreach (glob(ST_MIGRATIONS_DIR . '/*.php') as $file_name) {
            if (strpos($file_name, $id) != 0)
                continue;
            $this->run($file_name, $method);
        }
        $this->log('Finished' . "\n");
    }
	
	/**
	 *  migrate
	 *  Runs all migrations for a given blog
	 *  @param int $blog_id
	**/

    protected function migrate($blog_id) {
		if( $_GET['backend'] )
			$blog_id = get_current_blog_id();
		
        switch_to_blog($blog_id);
        $this->log('Migrating Blog ' . $blog_id . ' ', false);
        $old_latest = $latest = get_blog_option($blog_id, self::OPTION_MIGRATION_STATE, 0);
        $this->log('- Latest: ' . $latest);
        foreach (glob(ST_MIGRATIONS_DIR . '/*.php') as $file_name) {
            $id = $this->getMigrationID($file_name);
            if ( $this->compareState($id , $latest) )
                continue;
            $this->run($file_name);
            $latest = $id;
        }
        if ($latest != $old_latest) {
            update_blog_option($blog_id, self::OPTION_MIGRATION_STATE, $latest);
            $this->log('New Latest: ' . $latest . "\n");
        } else {
            $this->log('Already up to date' . "\n");
        }
    }
	
	public function migrateSingleAction(){
            $this->migrateSingle(get_current_blog_id());
	}
	
	/** 
	 *	migrateSingle
	 *  Migrates a single migration to a given blog
	**/
	
	protected function migrateSingle(){
		$file_name = $_GET[self::QUERY_FILE];
		$blog_id = $_GET[self::QUERY_ID];
        $this->log('Migrating Blog ' . $blog_id . ' ', false);
		$old_latest = $latest = get_blog_option($blog_id, self::OPTION_MIGRATION_STATE, 0);
        $this->log('- Latest: ' . $latest);
		$id = $this->getMigrationID($file_name);
		$this->log('migration ID = '. $id);
		 if ( $this->compareState($id , $latest)) {
			  $this->log('Already up to date' . "\n");
			 return;
		 }
		 $this->run(ST_MIGRATIONS_DIR . "/" . $file_name);
		 $latest = $id;
		if ($latest != $old_latest) {
		  update_blog_option($blog_id, self::OPTION_MIGRATION_STATE, $latest);
		  $this->log('New Latest: ' . $latest . "\n");
	  } else {
		  $this->log('Already up to date' . "\n");
	  }
	}
	
	public function deleteMigrationAction(){
		$backend = $_GET[self::QUERY_BACKEND];
		$calling_url = $_GET[self::QUERY_CALLING_URL];
		if( $this->deleteMigration(get_current_blog_id()) == FALSE ) { 
			if (!$backend) echo "Unable to Delete File";
		}
		else if(!$backend) $this->log( "File Deleted");
		wp_redirect($calling_url);
	}
	
	protected function deleteMigration(){
		$file_name = $_GET[self::QUERY_FILE];
		return unlink( ST_MIGRATIONS_DIR . "/" . $file_name );
	}
	
	public function renameMigrationAction(){
		$this->renameMigration();
	}
	
	/**
	 *  renameMigrations
	 *  Renames a migration file and updates its date
	 *  Replaces class name in file with new class name
	**/
	
	protected function renameMigration(){
		$file_name = $_GET[self::QUERY_FILE];
		$class_name = $this->getMigrationClassName( ST_MIGRATIONS_DIR . "/" . $file_name);
		$new_name = $_GET[self::QUERY_NEW_NAME];
		$new_class_name =  $this->underscoreToClassName($new_name);
		$new_name = $this->getNewMigrationName($new_name);
		
		$fhandle = fopen( ST_MIGRATIONS_DIR . "/" . $file_name,"r");
		$content = fread($fhandle,filesize(ST_MIGRATIONS_DIR . "/" . $file_name));
		$content = str_replace("class " . $class_name, "class " . $new_class_name, $content);
		$fhandle = fopen(ST_MIGRATIONS_DIR . "/" . $file_name,"w");
		fwrite($fhandle,$content);
		fclose($fhandle);
	
		
		if( rename( ST_MIGRATIONS_DIR . "/" . $file_name , ST_MIGRATIONS_DIR . "/" . $new_name))
			$this->log("Renamed $file_name to $new_name \n");
		else $this->log("Unable to rename file.");
	}

	/**
	 *  Run
	 *  Executes a migration file using a given method after checking if it needs to be run
	 *  @param string $file_name
	 *  @param  string $method contains the name of the method to br run
	**/
    protected function run($file_name, $method = self::METHOD_UP) {
        if (!in_array($method, $this->migrateMethods)) {
            throw new Strategery_Migrations_Exception('Method should be either "up" or "down"');
        }
        if ($class = $this->getMigrationClassName($file_name)) {
            try {
                $this->log('>>>> Executing "' . basename($file_name) . '"');
                require_once $file_name;
                $migration = new $class();
                $migration->$method();
                $this->log('>>>> Migration Finished');
            } catch (Strategery_Migrations_Exception $e) {
                $this->log('>> Error during migration: ' . $e->getMessage());
            }
        } else {
            throw new Strategery_Migrations_Exception('Could not infer classname from migration file "' . $file_name . '"');
        }
    }

    public function __construct() {
        $this->migrateMethods = array(self::METHOD_UP, self::METHOD_DOWN);

        $this->addFilter('query_vars');
        $this->addAction('parse_query');
		include 'admin/admin.php';
    }

    public function filterQueryVars($vars) {
        $vars[] = self::QUERY_TRIGGER;
        $vars[] = self::QUERY_ACTION;
        $vars[] = self::QUERY_ID;
        $vars[] = self::QUERY_METHOD;
        $vars[] = self::QUERY_NAME;
        $vars[] = self::QUERY_TEMPLATE;
        return $vars;
    }

	/**
	 *  actionParseQuery
	 *  gets and executes an action recieved from a url query string
	**/

    public function actionParseQuery() {
        if ($action = $this->getQueryActionName()) {
            @header('Content-Type: text-plain');
            try {
                require_once ST_MIGRATIONS_LIB . '/Migration.php';
               if( !$_GET[self::QUERY_BACKEND] )
				    $this->log('[Action "' . $this->getQueryAction() . '" | ' . date('Y-m-d H:i:s') . ']');
                	$this->$action();
			   
            } catch (Strategery_Migrations_Exception $e) {
                global $wp_query;
                $message = 'An error occured while executing action "' . $this->getQueryAction() . '":' . "\n\n";
                $message .= $e->getMessage() . "\n\n-------\n\n";
                $message .= 'Arguments = ';
                $message .= print_r($wp_query->query_vars, true);
                $this->log($message);
            }
            exit;
        }
    }

    protected function addAction($tag, $function = null, $priority = 10, $accepted_args = 1) {
        if (!$function)
            $function = $this->underscoreToCamelCase($tag, 'action');
        add_action($tag, array(&$this, $function), $priority, $accepted_args);
    }

    protected function addFilter($tag, $function = null, $priority = 10, $accepted_args = 1) {
        if (!$function)
            $function = $this->underscoreToCamelCase($tag, 'filter');
        add_filter($tag, array(&$this, $function), $priority, $accepted_args);
    }

    protected function getQueryAction() {
        return get_query_var(self::QUERY_ACTION);
    }
	
	/**
	 *  getQueryActionName
	 *  Gets the action to execute from the query string
	 *  Checks if the action corresponds to an action in te plugin
	 *  @return bool whether there is an action that can be executed
	**/
    protected function getQueryActionName() {
        if (intval(get_query_var(self::QUERY_TRIGGER)) == 1) {
            $action = $this->getQueryAction();
            $name = $this->underscoreToCamelCase($action);
            $name = lcfirst($name) . 'Action';
            return method_exists($this, $name) ? $name : FALSE;
        }
        return FALSE;
    }

    /**
     * Returns the class name inferred from the migration file name.
     * Format: ########-class_name.php  
     * 
     * @param string $path Path to the file including file name and extension.
     */
    protected function getMigrationClassName($path) {
        if ($matches = $this->parseMigrationFilename($path)) {
            return $this->underscoreToClassName($matches[2]);
        }
        return false;
    }
	
	// returns date in the migration file name
    protected function getMigrationID($path) {
        if ($matches = $this->parseMigrationFilename($path)) {
            return  $matches[1];
        }
        return false;
    }

    protected function parseMigrationFilename($path) {
        $base_name = basename($path, '.php');
        $matches = array();
        return preg_match(self::FILENAME_REGEX, $base_name, $matches) ? $matches : false;
    }
	
	/**
	 *  Returns true if file date is <= than blog state
	 *  @param string $file_date
	 *  @param string $blog_state
	**/
	
	public function compareState( $file_date , $blog_state ) {
		
		$file_time = strtotime($file_date);
		$blog_time = strtotime($blog_state);
		
		if( $file_time <= $blog_time ) return true;
		else return false;
	}

}


global $stMigrations;
$stMigrations = new Strategery_Migrations_Plugin();
