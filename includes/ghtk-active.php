<?php
class GHTK_Activator {

    public static function activate() {
        self::createDatabase();
        self::createRouter();
        self::createOption();
    }

    public static function createDatabase() {
        $model = get_model();
        if($model->db_table_exists('branchs')) {
            $model->query("ALTER TABLE `".CLE_PREFIX."branchs` ADD `ghtk_id` INT NULL DEFAULT 0 AFTER `id`;");
            $model->query("ALTER TABLE `".CLE_PREFIX."branchs` ADD `ghtk_name` VARCHAR(255) NULL NULL DEFAULT '' AFTER `id`;");
        }
    }

    public static function createRouter() {
        $model = get_model();
        $model->settable('routes');
        $count = $model->count_where(array('slug' => 'ghtk-syscn', 'plugin' => 'giaohangtietkiem'));
        if($count == 0) {
            $model->add(array(
                'slug'        => 'ghtk-syscn',
                'controller'  => 'frontend_home/home/page/',
                'plugin'      => 'giaohangtietkiem',
                'object_type' => 'giaohangtietkiem',
                'directional' => 'ghtk_sync_order_status',
                'callback' 	  => 'ghtk_sync_order_status',
            ));
        }
    }

    public static function createOption() {
        Option::update('shipping_ghtk_default_html', false);
    }
}

class GHTK_Deactivator {
    public static function deactivate() {
    }
}