<?php
/**
Plugin name     : Giao Hàng Tiết Kiệm
Plugin class    : giaohangtietkiem
Plugin uri      : http://sikido.vn
Description     : Giaohangtietkiem.vn cung cấp dịch vụ giao hàng giờ và thu tiền hộ (same-day delivery và cash on delivery) uy tín, đáng tin cậy.
Author          : Nguyễn Hữu Trọng
Version         : 1.2.1
*/
define( 'GHTK_NAME', 'giaohangtietkiem' );

define( 'GHTK_PATH', Path::plugin( GHTK_NAME ));

define( 'GHTK_KEY', 'ghtk');

class giaohangtietkiem {

    private $name = 'giaohangtietkiem';

    function __construct() {
        $this->loadDependencies();
        $this->loadAssets();
        new GHTK_Checkout();
        new GHTK_Order_Action();
        new GHTK_Branch_Custom();
        add_filter('shipping_gateways',array($this, 'register'), 10, 2);
    }

    public function active() {
        GHTK_Activator::activate();
	}

    public function deactivate() {
        $shipping 			= get_option('cart_shipping', []);
        foreach ($shipping as $key => $item) {
            if($key == 'ghtk' && !empty($item['enabled'])) {
                $shipping[$key]['enabled'] = false;
                break;
            }
        }
        update_option('cart_shipping', $shipping);
    }

    public function uninstall() {
        Option::delete('shipping_ghtk_default_html');
    }

    public function register($gateways, $config) {
        $gateways[GHTK_KEY] 	= [
            'label'         => 'Giao hàng tiết kiệm',
            'description'   => 'Giaohangtietkiem.vn cung cấp dịch vụ giao hàng giờ và thu tiền hộ',
            'class'         => 'GHTK_Shipping',
            'token'         => "",
            'weight'        => 100,
            'mode'          => "test",
            'b2cToken'      => 'e4c4659C04dd309628c0a830E9878d2B1Ad8fa4b',
            'icon'          => Url::base(GHTK_PATH.'/assets/images/logo-ghtk.png')
        ];
        return $gateways;
    }

    private function loadDependencies() {
        require_once GHTK_PATH.'/includes/ghtk-active.php';
        require_once GHTK_PATH.'/includes/ghtk-endpoint.php';
        require_once GHTK_PATH.'/includes/ghtk-shipping.php';
        require_once GHTK_PATH.'/includes/ghtk-order.php';
        require_once GHTK_PATH.'/includes/ghtk.php';
        require_once GHTK_PATH.'/includes/ghtk-api.php';
        require_once GHTK_PATH.'/includes/ghtk.order.class.php';
        require_once GHTK_PATH.'/includes/ghtk-ajax.php';
        require_once GHTK_PATH.'/includes/ghtk-webhook.php';
        require_once GHTK_PATH.'/includes/ghtk-branch.php';
    }

    private function loadAssets() {
        Admin::asset()->location('footer')->add(GHTK_KEY, GHTK_PATH.'/assets/js/admin/ghtk-order.js');
        Template::asset()->location('footer')->add(GHTK_KEY, GHTK_PATH.'/assets/js/ghtk-shipping.js');
    }
}

new giaohangtietkiem();