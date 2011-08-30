<?php

class AddPageHeading extends Strategery_Migration {

    public function up() {
        if (get_current_blog_id() == 1)
            return;
        
        $pages = array('lunch', 'brunch', 'dinner', 'dessert', 'drinks', 'staff', 'schedule');
        foreach ($pages as $page_slug) {
            $page = $this->getPost(array('post_name' => $page_slug, 'post_type' => 'page'));
            if(strpos($page->post_content, '<h2>') !== false) continue;
            $header = '<h2>' . ucfirst($page_slug) . '</h2>';
            switch ($page_slug) {
                case 'lunch':
                case 'brunch':
                case 'dinner':
                case 'dessert':
                case 'drinks':
                    $header .= '<h3>Click to expand each item and START LEARNING!</h3>';
                    break;
                case 'staff':
                    $header = "<h2>Your Co-workers</h2>";
                    break;
            }
            $page->post_content = $header . $page->post_content;
            $page->save();
        }
    }

    public function down() {
        // down code here
    }

}
