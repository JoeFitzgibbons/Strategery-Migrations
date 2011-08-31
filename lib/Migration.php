<?php

class Strategery_Migration extends Strategery_Migrations_Core {

    public function up() {
        // override this method
    }

    public function down() {
        // override this method
    }

    protected function log($message, $nl = true) {
        parent::log('>> ' . $message, $nl);
    }

    /**
     * Loops through each blog calling $callback.
     * @param Closure $callback 
     */
    protected function blogs() {
        $db = $this->db();
        return $db->get_col($db->prepare('SELECT blog_id FROM wp_blogs'));
    }

    /**
     * Returns a list of user IDs.
     * @param Closure $callback 
     */
    protected function users() {
        $db = $this->db();
        return $db->get_results($db->prepare("SELECT ID, user_login, user_email, display_name FROM $db->users ORDER BY ID;"));
    }

}
