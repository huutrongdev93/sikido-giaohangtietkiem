<?php
class GHTK_Activator {

    public static function activate() {
        self::createDatabase();
        self::createRouter();
        self::createOption();
    }

    public static function createDatabase() {
        $model = model();
        if($model::schema()->hasTable('branchs')) {
            if(!$model::schema()->hasColumns('branchs', ['ghtk_id', 'ghtk_name'])) {
                $model::schema()->table('branchs', function ($table) {
                    $table->integer('ghtk_id')->default(0)->after('id');
                    $table->string('ghtk_name', 255)->nullable()->after('id');
                });
            }
        }
    }

    public static function createRouter() {
        $count = Routes::count(Qr::set('slug', 'ghtk-syscn')->where('plugin', 'giaohangtietkiem'));
        if($count == 0) {
            Routes::insert([
                'slug'        => 'ghtk-syscn',
                'controller'  => 'frontend/home/page/',
                'plugin'      => 'giaohangtietkiem',
                'object_type' => 'giaohangtietkiem',
                'directional' => 'ghtk_sync_order_status',
                'callback' 	  => 'ghtk_sync_order_status',
            ]);
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