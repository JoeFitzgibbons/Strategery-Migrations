<?php

class EditFileMigration extends Strategery_Migration {

    public function up() {
        if (get_current_blog_id() == 1)
            return;
        $parent = $this->getPost(array('post_name' => 'office', 'post_type' => 'page'));
        $post = $this->getPost(array('post_type' => 'page', 'post_name' => 'edit-file', 'post_title' => 'Edit File', 'post_parent' => $parent->ID))
                     ->save();
        $this->getHelper('plugins/exclude_from_nav')->exclude($post->ID);
    }

    public function down() {
        // down code here
    }

}
