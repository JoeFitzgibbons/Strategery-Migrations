<?php

class AddBlogBannerUrl extends Strategery_Migration {

    public function up() {
        add_option('blog_banner_url'); //adds only if option doesn't already exist
    }

    public function down() {
        // down code here
    }

}
