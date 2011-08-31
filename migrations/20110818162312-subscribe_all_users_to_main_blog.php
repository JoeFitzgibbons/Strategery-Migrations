<?php 

class SubscribeAllUsersToMainBlog extends Strategery_Migration {

    public function up() {
        $users = get_users(); //gets users for current blog
        foreach($users as $user) {
            add_user_to_blog(1, $user->ID, 'subscriber');
        }
    }

    public function down() {
        // down code here
    }

}
