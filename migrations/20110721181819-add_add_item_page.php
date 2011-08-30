<?php

class AddAddItemPage extends Strategery_Migration {

    public function up() {
        $parent = $this->getPost(array('post_name' => 'learn', 'post_type' => 'page'));
        $baseParams = array('post_type' => 'page', 'post_parent' => $parent->ID);
        
        $menuAdd = array_merge($baseParams, array('post_name' => 'menu-add', 'post_title' => 'Add Menu Item'));
        $menuAdd = $this->getPost($menuAdd)->save();
        
        $menuEdit = array_merge($baseParams, array('post_name' => 'menu-edit', 'post_title' => 'Edit Menu Item'));
        $menuEdit = $this->getPost($menuEdit)->save();
        
        $this->getHelper('plugins/exclude_from_nav')->exclude($menuAdd->ID);
        $this->getHelper('plugins/exclude_from_nav')->exclude($menuEdit->ID);
    }

    public function down() {
        // down code here
    }

}
