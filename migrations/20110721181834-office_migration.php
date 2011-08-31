<?php 

class OfficeMigration extends Strategery_Migration {

    public function up() {
       $this->getPost(array('post_type' => 'page' , 'post_name' => 'office' , 'post_title' => 'Office'))
            ->save();
    }

    public function down() {
        // down code here
    }

}