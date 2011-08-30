<?php

abstract class Strategery_Migrations_Model_Abstract extends Strategery_Migrations_Core {

    protected $data = array();
    protected $modelName;
    protected $originalData;

    public function __construct($data = array()) {
        if (!empty($data)) {
            $this->data = wp_parse_args($data, array());
            $this->load();
        }
    }

    public function __get($name) {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return FALSE;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function getData($key = null) {
        if ($key)
            return $this->data[$key];
        else
            return $this->data;
    }

    public function setData($key, $value = null) {
        if (is_array($key)) {
            $this->data = $key;
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function save($reload = false) {
        $args = count(func_get_args()) > 1 ? array_shift(func_get_args()) : array();
        $this->log('[' . $this->_name() . '::save] ', false);
        try {
            $saved = call_user_method_array('_save', $this, $args);
            if($saved === false) {
                $this->log('NOT SAVED: There were no changes.');
                return $this;
            } else {
                $this->log('SAVED: ' . $saved);
                return $reload ? $this->load($saved) : $this;
            }
        } catch (Strategery_Migrations_Exception $e) {
            $this->log('error - ' . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        // override this function
    }

    public function find($data, $single = true) {
        try {
            $this->log('[' . $this->_name() . '::find] ', false);
            $results = $this->_find($data, $single);
            $this->log('- FOUND');
            return $results;
        } catch (Strategery_Migrations_Exception $e) {
            $this->logError($e);
            return false;
        }
    }

    public function load($id_or_data = null) {
        if(is_numeric($id_or_data)) {
            $data = array('ID' => (int) $id_or_data);
        } elseif(is_array($id_or_data) || is_object($id_or_data)) {
            $data = wp_parse_args($id_or_data);
        } else {
            $data = $this->getData();
        }
        // try to find the row
        $result = get_object_vars($this->find($data));
        $this->setData($result);
        $this->originalData = $result;
        return $this;
    }
    
    protected function _where($data) {
        $keys = array();
        $values = array();
        foreach($data as $key => $value) {
            if(is_array($value) && $op = $value['op']) {
                $op = $value['op'];
                $value = $value['value'];
            } else {
                $op = '=';
            }
            $type = is_numeric($value) ? '%d' : '%s';
            $keys[] = "$key $op $type";
            $values[] = $value;
        }
        $keys = implode(' AND ', $keys);
        $where = $this->db()->prepare($keys, $values);
        return $where;
    }

    protected function logError(Exception $e) {
        $this->log('- ERROR: ' . $e->getMessage());
    }
    
    protected function _name() {
        if(!isset($this->modelName)) {
            $this->modelName = get_class($this);
        }
        return $this->modelName;
    }

}