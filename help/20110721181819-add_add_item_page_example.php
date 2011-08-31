<?php

/**
 *  This file highlights the ability to add a page as a child of another and also exclude it from the navigation menu.
 *  This exclude functionality requires having another plugin active which can be seen at http://wordpress.org/extend/plugins/exclude-pages/
**/
class AddAddItemPageExample extends Strategery_Migration {

    public function up() {
        $parent = $this->getPost(array('post_name' => 'learn', 'post_type' => 'page'));  //get a post object of a page that has the name learn
        $baseParams = array('post_type' => 'page', 'post_parent' => $parent->ID);  //standard prameters needed for both pages that will be added
        
        $menuAdd = array_merge($baseParams, array('post_name' => 'menu-add', 'post_title' => 'Add Menu Item')); // get full array of options needed
        $menuAdd = $this->getPost($menuAdd)->save();  // get post onject and save that post. $menuAdd is an object of the post
        
		// Do the same for the menu edit post
        $menuEdit = array_merge($baseParams, array('post_name' => 'menu-edit', 'post_title' => 'Edit Menu Item'));
        $menuEdit = $this->getPost($menuEdit)->save();
        
		/**
		 *  The following lines use the getHelper method to use methods that are not stnandard methods. 
		 *  These methods are included by using mini plugins that perform certain tasks that can be added in. These are found in the '/lib/Helper/Plugins' folder.
		 *  The parameter must be int the format of 'plugins/plugin_name' where plugin_name is the underscore separated name of the file that exists in 
		 *  '/lib/Helper/Plugins/'. the example below points to the ExcludeFromNav.php file.
		 *  Once you have the correct path parameter, getHelper() returns an instance of the class defined in that file so that you can 
		 *  use the methodes that are defined in the class. The exclude() method takes the ID of the page to be excluded and can be passed as shown.
        **/
		$this->getHelper('plugins/exclude_from_nav')->exclude($menuAdd->ID);
        $this->getHelper('plugins/exclude_from_nav')->exclude($menuEdit->ID);
    }

    public function down() {
        // down code here
    }

}
