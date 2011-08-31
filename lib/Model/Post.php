<?php

class Strategery_Migrations_Model_Post extends Strategery_Migrations_Model_Abstract {

    protected $modelName = 'Post';

    protected function _save($force = false) {
        $db = $this->db();
        $defaults = array(
            'post_status' => 'publish',
            'post_author' => 1,
        );
        $post = array_merge($defaults, $this->getData());
        
        if (!$force && isset($post['ID']) && $post == $this->originalData) {
            return false;
        } else {
            $post['ID'] = wp_insert_post($post);
            if (!is_wp_error($post['ID'])) {
                $this->setData($post);
                return $post['ID'];
            } else {
                throw new Strategery_Migrations_Exception($post['ID']->get_error_message());
            }
        }
    }

    protected function _find($data, $single = true) {
        if (!is_array($data) || empty($data)) {
            throw new Strategery_Migrations_Exception("Invalid data specified.");
        }
        $db = $this->db();
        $where = $this->_where($data);
        $limit = $single ? 'LIMIT 1' : '';
        $select = "SELECT * FROM $db->posts WHERE $where $limit";
        $this->log('{' . $select . '} ', false);
        $results = $db->get_results($select);
        if ($results) {
            return $single ? $results[0] : $results;
        } else {
            throw new Strategery_Migrations_Exception("No results found.");
        }
    }

}