<?php

class BaseMigration extends Strategery_Migration {

    public function up() {
        $this->log('Dummy UP code. This migration does nothing to the database.');
    }

    public function down() {
        $this->log('Dummy DOWN code. This migration does nothing to the database.');
    }

}
