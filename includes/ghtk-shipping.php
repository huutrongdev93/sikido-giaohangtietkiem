<?php
Class GHTK_Shipping {

    static public function form($key_shipping, $shipping) {
        if(!empty($shipping['token']) && GHTK()->TestConnect() == true) {
            $provinces = Cart_Location::cities();
            Option::update('shipping_ghtk_default_html', true);
            include GHTK_PATH.'/admin/views/ghtk-setting.php';
        } else {
            Option::update('shipping_ghtk_default_html', false);
            include GHTK_PATH.'/admin/views/ghtk-login.php';
        }
    }

    static public function config($result) {
        $GHTK = InputBuilder::post('ghtk');

        $config = GHTK::config();

        $config['enabled']        = (empty(InputBuilder::post('enabled'))) ? 0 : 1;

        $config['title']          = InputBuilder::post('title');

        $config['img']            = FileHandler::handlingUrl(InputBuilder::post('img'));

        $config['price_default']  = InputBuilder::post('price_default');

        $config['weight']         = $GHTK['weight'];

        $config['mode']           = $GHTK['mode'];

        $shipping = shipping_gateways();

        $shipping[GHTK_KEY] = $config;

        Option::update('cart_shipping', $shipping);

        return $result;
    }

    static public function calculate($package) {

        $shipping_price = GHTK::config('price_default');

        $config     = GHTK::config();

        $cart       = Scart::getItems();

        $ward = '';

        if(!empty($package['show-form-shipping']) && $package['show-form-shipping'] == 'on') {
            if(empty($package['shipping_city'])) return $shipping_price;
            if(empty($package['shipping_districts'])) return $shipping_price;
            $citi 			= $package['shipping_city'];
            $districts 		= $package['shipping_districts'];
            $ward           = $package['shipping_ward'];
        }
        else {
            if(empty($package['billing_city'])) return $shipping_price;
            if(empty($package['billing_districts'])) return $shipping_price;
            $citi 			= $package['billing_city'];
            $districts 		= $package['billing_districts'];
            $ward           = $package['billing_ward'];
        }

        if(empty($citi) || empty($districts)) return $shipping_price;

        $weight = 0;

        foreach ($cart as $key => $item) {
            if($item['weight'] == 0) $item['weight'] = (int)$config['weight'];
            $weight += $item['weight']*$item['qty'];
        }

        $shipping_price = GHTK()->setWeight($weight)->setValue(Scart::total())->shipAmount($citi, $districts, $ward);

        return $shipping_price;
    }

    static public function listService($package, $order) {

        $itemList = [];

        if(!empty($order->other_delivery_address)) {
            $citi 			= $order->shipping_city;
            $districts 		= $order->shipping_districts;
            $ward 		    = $order->shipping_ward;
        }
        else {
            $citi 			= $order->billing_city;
            $districts 		= $order->billing_districts;
            $ward 		    = $order->billing_ward;
        }

        if(!empty($citi) && !empty($districts)) {

            $weight = 0;

            foreach ($order->items as $key => $item) {
                $weight_item = Order::getItemMeta($item->id, 'weight', true);
                if($weight_item == 0) $weight_item = 100;
                $weight += $weight_item*$item->quantity;
            }

            $service = GHTK()->setWeight($weight)->setValue($order->total)->shipAmount($citi, $districts, $ward, true);

            if(have_posts($service)) {
                $itemList[$service->cost_id] = [
                    'label'     => $package['label'].' - '.$service->name,
                    'fee'       => $service->fee,
                    'expected_delivery_time' => 'N/A',
                    'value'     => GHTK_KEY.'__'.$service->cost_id
                ];
            }
        }

        return $itemList;
    }

    static public function change($package, $order, $value) {

        if(!empty($order->other_delivery_address)) {
            $citi 			= $order->shipping_city;
            $districts 		= $order->shipping_districts;
            $ward 		    = $order->shipping_ward;
        }
        else {
            $citi 			= $order->billing_city;
            $districts 		= $order->billing_districts;
            $ward 		    = $order->billing_ward;
        }

        $weight = 0;

        foreach ($order->items as $key => $item) {
            $weight_item = Order::getItemMeta($item->id, 'weight', true);
            if($weight_item == 0) $weight_item = 100;
            $weight += $weight_item*$item->quantity;
        }

        $fee = GHTK()->setWeight($weight)->setValue($order->total)->shipAmount($citi, $districts, $ward);

        $pick = GHTK()->getPickArea($citi);

        Order::updateMeta($order->id, '_shipping_type', GHTK_KEY);

        Order::updateMeta($order->id, '_shipping_price', $fee);

        Order::updateMeta($order->id, '_shipping_label', $package['label']);

        if(have_posts($pick)) {
            Order::updateMeta($order->id, 'GHTK_info', ['PickID' => $pick->ghtk_id]);
        }
    }
}

Class GHTK_Checkout {

    function __construct() {
        add_filter('checkout_order_before_save',array($this, 'setWeightDefaultToProduct'), 10, 2);
        add_filter('checkout_order_metadata_before_save',array($this, 'setPickIDToOrder'), 10, 2);
    }

    function setWeightDefaultToProduct($order, $metadata_order) {
        if(!empty($metadata_order['_shipping_type']) && $metadata_order['_shipping_type'] == GHTK_KEY) {
            $config = GHTK::config();
            foreach ($order['items'] as $key => &$item) {
                if(empty($item['metadata']['weight'])) {
                    $item['metadata']['weight'] = $config['weight'];
                }
            }
        }
        return $order;
    }

    function setPickIDToOrder($metadata_order, $order) {

        if(!empty($metadata_order['_shipping_type']) && $metadata_order['_shipping_type'] == GHTK_KEY) {

            $shipping   = Str::clear(InputBuilder::post('show-form-shipping'));

            if( !empty($shipping) && $shipping == 'on' ) {
                $citi = InputBuilder::post('shipping_city');
            }
            else {
                $citi  = InputBuilder::post('billing_city');
            }

            $pick = GHTK()->getPickArea($citi);

            if(have_posts($pick)) {
                $metadata_order['GHTK_info'] = [
                    'PickID' => $pick->ghtk_id,
                ];
            }
        }

        return $metadata_order;
    }
}