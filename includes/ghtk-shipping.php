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

        $GHTK = Request::post('ghtk');

        $config = GHTK::config();

        $config['enabled']        = (empty(Request::post('enabled'))) ? 0 : 1;

        $config['title']          = Request::post('title');

        $config['img']            = FileHandler::handlingUrl(Request::post('img'));

        $config['price_default']  = Request::post('price_default');

        $config['weight']         = $GHTK['weight'];

        $config['mode']           = $GHTK['mode'];

        $shipping = shipping_gateways();

        $shipping[GHTK_KEY] = $config;

        Option::update('cart_shipping', $shipping);

        return $result;
    }

    static public function service($ship) {

        $shipping = Request::Post('shipping_type');

        if($shipping == GHTK_KEY) {

            $package = Request::Post();

            if(!empty($package['show-form-shipping']) && $package['show-form-shipping'] == 'on') {
                $citi 		= Request::Post('shipping_city');
                $districts 	= Request::Post('shipping_districts');
                $ward 		= Request::Post('shipping_ward');
            }
            else {
                $citi 		= Request::Post('billing_city');
                $districts 	= Request::Post('billing_districts');
                $ward 		= Request::Post('billing_ward');
            }

            if(!empty($citi) && !empty($districts)) {

                $weight = 0;

                foreach (Scart::getItems() as $key => $item) {
                    if($item['weight'] == 0) $item['weight'] = 100;
                    $weight += $item['weight']*$item['qty'];
                }

                $transport = Request::Post('shipping_ghtk_transport');

                $transport = (!empty($transport)) ? $transport : 'road';

                $shipping_price_road = GHTK()->setWeight($weight)->setTransport('road')->setValue(Scart::total())->shipAmount($citi, $districts, $ward);

                $shipping_price_fly = GHTK()->setWeight($weight)->setTransport('fly')->setValue(Scart::total())->shipAmount($citi, $districts, $ward);
                ?>
                <tr class="ship">
                    <td>
                        <div class="checkbox" style="margin:0 0 0 20px;">
                            <label style="padding:0;">
                                <input type="radio" value="road" <?php echo ($transport == 'road') ? 'checked' : '';?> name="shipping_ghtk_transport"> ???????ng b???
                            </label>
                        </div>
                    </td>
                    <td>
                        <strong id="ship-road"><?php echo number_format($shipping_price_road)._price_currency();?></strong>
                    </td>
                </tr>
                <tr class="ship">
                    <td>
                        <div class="checkbox" style="margin:0 0 0 20px;">
                            <label style="padding:0;">
                                <input type="radio" value="fly" <?php echo ($transport == 'fly') ? 'checked' : '';?> name="shipping_ghtk_transport"> ???????ng bay
                            </label>
                        </div>
                    </td>
                    <td>
                        <strong id="ship-fly"><?php echo number_format($shipping_price_fly)._price_currency();?></strong>
                    </td>
                </tr>
                <?php
            }
        }
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

        $transport = (!empty($package['shipping_ghtk_transport'])) ? $package['shipping_ghtk_transport'] : 'road';

        if(empty($citi) || empty($districts)) return $shipping_price;

        $weight = 0;

        foreach ($cart as $key => $item) {
            if($item['weight'] == 0) $item['weight'] = (int)$config['weight'];
            $weight += $item['weight']*$item['qty'];
        }

        $shipping_price = GHTK()->setWeight($weight)->setTransport($transport)->setValue(Scart::total())->shipAmount($citi, $districts, $ward);

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

            $fee = GHTK()->setWeight($weight)->setTransport('road')->setValue($order->total)->shipAmount($citi, $districts, $ward);
            if($fee > 0) {
                $itemList['road'] = [
                    'label'     => $package['label'].' - ???????ng b???',
                    'fee'       => number_format($fee),
                    'expected_delivery_time' => 'N/A',
                    'value'     => GHTK_KEY.'__road'
                ];
            }
            $fee = GHTK()->setWeight($weight)->setTransport('fly')->setValue($order->total)->shipAmount($citi, $districts, $ward);
            if($fee > 0) {
                $itemList['fly'] = [
                    'label'     => $package['label'].' - ???????ng bay',
                    'fee'       => number_format($fee),
                    'expected_delivery_time' => 'N/A',
                    'value'     => GHTK_KEY.'__fly'
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

        $fee = GHTK()->setWeight($weight)->setTransport($value[1])->setValue($order->total)->shipAmount($citi, $districts, $ward);

        $pick = GHTK()->getPickArea($citi);

        Order::updateMeta($order->id, '_shipping_type', GHTK_KEY);

        Order::updateMeta($order->id, '_shipping_price', $fee);

        Order::updateMeta($order->id, '_shipping_label', $package['label']);

        if(have_posts($pick)) {
            Order::updateMeta($order->id, 'GHTK_info', ['PickID' => $pick->ghtk_id, 'transport' => $value[1]]);
        }
    }
}

add_action('checkout_shipping_'.GHTK_KEY.'_template', 'GHTK_Shipping::service', 10, 1);

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

            $shipping   = Str::clear(Request::post('show-form-shipping'));

            if( !empty($shipping) && $shipping == 'on' ) {
                $citi = Request::post('shipping_city');
            }
            else {
                $citi  = Request::post('billing_city');
            }

            $pick = GHTK()->getPickArea($citi);

            $transport = (!empty(Request::post('shipping_ghtk_transport'))) ? Request::post('shipping_ghtk_transport') : 'road';

            if(have_posts($pick)) {
                $metadata_order['GHTK_info'] = [
                    'PickID' => $pick->ghtk_id,
                    'transport' => $transport,
                ];
            }
        }

        return $metadata_order;
    }
}