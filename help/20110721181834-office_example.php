<?php 
/**
 * This file shows an example of adding a page "or a post" to whatever blog its applied.
 * getPost() takes an associative array containing keys and values that correspond to columns in a given blog's post table
 * getPost() will return a model object which allows you to perform certain operations.
 * getPost()->save(); will insert the post into the database
**/

class OfficeExample extends Strategery_Migration {

    public function up() {
       $this->getPost(array('post_type' => 'page' , 'post_name' => 'office' , 'post_title' => 'Office'))
            ->save();
    }

    public function down() {
        // down code here
    }

}