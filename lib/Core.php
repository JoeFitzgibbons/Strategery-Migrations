<?php

class Strategery_Migrations_Core {

    static protected $cache = array();

    const NS_BASE = 'Strategery_Migrations';
    const NS_MODEL = 'Strategery_Migrations_Model';
    const NS_HELPER = 'Strategery_Migrations_Helper';

    /**
     * Returns $wpdb
     * @return wpdb $wpdb
     */
    public function db() {
        global $wpdb;
        return $wpdb;
    }

    protected function log($message, $nl = true) {
        echo $message;
        if ($nl)
            echo "\n";
    }

    protected function debug($object) {
        die('<pre>' . print_r($object, true));
    }

    protected function underscoreToCamelCase($underscore, $prepend = '') {
        $result = str_replace('_', ' ', $underscore);
        $result = ucwords($result);
        $result = str_replace(' ', '', $result);
        if (empty($prepend))
            $result = lcfirst($result);
        return $prepend . $result;
    }

    protected function underscoreToClassName($underscore) {
        $result = $this->underscoreToCamelCase($underscore);
        return ucfirst($result);
    }

    /**
     * Receives a type and turns it into a class name under the given namespace
     * @param string $type The class type (e.g., 'plugins/exclude_from_nav')
     * @param string $ns Namespace, for example self::NS_MODEL
     * @return string The full class name
     */
    protected function getClassNameFromType($type, $ns = '') {
        //$type = 'plugins/foo_bar/baz_post'
        //$ns = 'Strategery_Migrations_Helper'
        $parts = explode('/', $type); //array('plugins', 'foo_bar', 'baz_post')
        $cnParts = array_map(array($this, 'underscoreToClassName'), $parts); //array('Plugins', 'FooBar', 'BazPost')
        if ($ns)
            array_unshift($cnParts, $ns); //array('Strategery_Migrations_Helper', 'Plugins', 'FooBar', 'BazPost')
        return implode('_', $cnParts); //'Strategery_Migrations_Helper_Plugins_FooBar_BazPost'
    }

    /**
     * Returns the full path for the given class
     * @param string $class The class name without namespace
     * @param string $basePath The base path for the class type (path equivalent to namespace)
     * @param string $suffix A suffix (e.g. file extension) to append to the path.
     * @return string The full path
     */
    protected function getPathFromClass($class, $basePath = '', $suffix = '.php') {
        $path = str_replace('_', '/', $class);
        if ($basePath)
            $path = rtrim($basePath, '/') . '/' . $path;
        return $path . $suffix;
    }

    /**
     * Autoloads and returns a class instance from a given type
     * @param string $type The class type
     * @param array $args Optional arguments that will be passed as array
     * @return object The class instance 
     */
    protected function getClassInstance($type, $args = array()) {
        $className = $this->getClassNameFromType($type);
        $fullClassName = self::NS_BASE . '_' . $className;
        if (!class_exists($fullClassName)) {
            require_once $this->getPathFromClass($className, ST_MIGRATIONS_LIB);
        }
        return new $fullClassName($args);
    }
    
    /**
     * Returns or creates a cached instance for a given class type
     * @param string $type The class type
     * @return object The class instance 
     */
    protected function getClassSingleton($type) {
        if(!isset($this->cache[$type])) {
            $this->cache[$type] = $this->getClassInstance($type);
        }
        return $this->cache[$type];
    }

    /**
     * Returns a helper from the given type helper. Helpers behave as singletons.
     * @param string $type The helper type
     * @param array $args Optional arguments that will be passsed as an array
     * @return Strategery_Migrations_Helper
     */
    protected function getHelper($type) {
        require_once ST_MIGRATIONS_LIB . '/Helper.php';
        $type = 'helper/' . $type;
        return $this->getClassSingleton($type);
    }

    /**
     * Returns an instance of the specified model. Wrapper for getClassInstance().
     * @param string $type The model type
     * @param mixed $data Optional data to be passed an argument
     * @return Strategery_Migrations_Model 
     */
    protected function getModel($type, $data = array()) {
        require_once ST_MIGRATIONS_LIB . '/Model.php';
        return $this->getClassInstance('model/'.$type, $data);
    }

    /**
     * Post wrapper for getModel()
     * @param mixed $data Optional data to be passed an argument
     * @return Strategery_Migrations_Model_Post
     */
    protected function getPost($data = array()) {
        return $this->getModel('post', $data);
    }

}