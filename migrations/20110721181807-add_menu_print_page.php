<?php

class AddMenuPrintPage extends Strategery_Migration {

    public function up() {
        if (get_current_blog_id() == 1)
            return;
        $parent = $this->getPost(array('post_name' => 'learn', 'post_type' => 'page'));
        $post = $this->getPost(array('post_type' => 'page', 'post_name' => 'menu-print', 'post_title' => 'Menu Print View'))->save();
        $this->getHelper('plugins/exclude_from_nav')->exclude($post->ID);
    }

    public function down() {
        // down code here
    }

}
