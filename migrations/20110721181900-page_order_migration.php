<?php

class PageOrderMigration extends Strategery_Migration {

    public function up() {
        if (get_current_blog_id() == 1)
            return;
        $pages = array('home', 'learn', 'schedule', 'office');
        foreach ($pages as $order => $name) {
            $page = $this->getPost(array('post_name' => $name, 'post_type' => 'page'));
            $page->menu_order = ($order + 1) * 10;
            $page->save();
        }
    }

    public function down() {
        // down code here
    }

}
