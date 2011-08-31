<?php
/**
 *  This file will set the page order of certain pages so that they appear in the order you want in the navigation bar
 *  Pay attention to how the menu_order property is set and then saved
**/
class PageOrderExample extends Strategery_Migration {

    public function up() {
        if (get_current_blog_id() == 1) //if The blog ID is 1 it is the main blog. In this implmentation we want to skip this blog
            return;
        $pages = array('home', 'learn', 'schedule', 'office'); //array of page names
        foreach ($pages as $order => $name) {
            $page = $this->getPost(array('post_name' => $name, 'post_type' => 'page')); //This gets a page with one of the names in the $pages array
			// you can set a scpecific post option as if it was property in a class. In this case we want the menu order in powers of 10
            $page->menu_order = ($order + 1) * 10;  
            $page->save();  // This line will save the page into the database
        }
    }

    public function down() {
        // down code here
    }

}
