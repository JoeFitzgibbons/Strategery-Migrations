<?php

class Strategery_Migrations_Helper_Plugins_ExcludeFromNav extends Strategery_Migrations_Helper {

    public function exclude($pageId) {
        $this->log('[ExcludeFromNav::exclude] Excluding ' . $pageId . ' from nav ', false);
        $option = get_option('ep_exclude_pages', FALSE);
        $exclude = $option ? explode(',', $option) : array();
        if(!is_array($exclude)) $exclude = array($exclude);
        if (!in_array($pageId, $exclude)) {
            $exclude[] = $pageId;
            $exclude = array_unique($exclude);
            $exclude = implode(',', $exclude);
            update_option('ep_exclude_pages', $exclude);
            $this->log('- EXCLUDED {' . $exclude . '}');
        } else {
            $this->log('- ALREADY EXCLUDED {' . implode(',', $exclude) . '}');
        }
    }

}
