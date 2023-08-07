<?php
Class GHTK_Shipping {
    static function form($key_shipping, $shipping): void
    {
        if(!empty($shipping['token']) && GHTK()->TestConnect()) {

            $Form = new FormBuilder();

            $Form
                ->add('', 'html', '<div class="row">')
                ->add('enabled', 'checkbox', [
                    'label' => 'Bật /Tắt hình nhà vận chuyển Giaohangtietkiem.vn',
                    'start' => '12',
                ], (!empty($shipping['enabled'])) ? 'enabled' : '')
                ->add('default', 'checkbox', [
                    'options' => SHIP_KEY,
                    'label' => 'Đặt làm phí ship mặc định',
                    'start' => '12',
                ], (!empty($shipping['default'])) ? SHIP_KEY : '')
                ->add('img', 'image', [
                    'label' => 'Icon',
                    'start' => '4',
                ], $shipping['img'])
                ->add('title', 'text', [
                    'label' => 'Tiêu đề',
                    'start' => '8',
                ], $shipping['title'])
                ->add('description', 'textarea', [
                    'label' => 'Mô tả',
                    'start' => '12',
                ], $shipping['description'])
                ->add('', 'html', '</div>');

            $Form->html(false);

        } else {
            Option::update('shipping_ghtk_default_html', false);
            include GHTK_PATH.'/admin/views/ghtk-login.php';
        }
    }
    static function setting($key_shipping, $shipping): void
    {
        if(!empty($shipping['token']) && GHTK()->TestConnect()) {

            $branches = (class_exists('Branch')) ? Branch::gets() : [];

            if(have_posts($branches)) {

                $picks = CacheHandler::get('GHTK_Branch');

                if(!have_posts($picks)) {

                    $picksResponse = GHTK()->getsPick();

                    $picks = [0 => 'Chọn chi nhánh GHTK'];

                    if(!empty($picksResponse->success) && !empty($picksResponse->data)) {
                        foreach ($picksResponse->data as $datum) {
                            $picks[$datum->pick_address_id] = '#'.$datum->pick_address_id.'-'.$datum->pick_name;
                        }
                    }

                    CacheHandler::save('GHTK_Branch', $picks, 30*24*60*60);
                }
            }

            $branchConnect = GHTK::config('branchConnect');

            include GHTK_PATH.'/admin/views/ghtk-setting.php';

        }
    }
    static function config($result) {

        $GHTK   = Request::post('ghtk');

        $config = GHTK::config();

        $config['enabled']        = (empty(Request::post('enabled'))) ? 0 : 1;

        if(empty(Request::post('title'))) {
            $result['status'] = 'error';
            $result['message'] = 'Không được để trống tên loại vận chuyển';
            return $result;
        }

        $config['title']          = Request::post('title');

        $config['description']    = Request::post('description');

        $config['img']            = FileHandler::handlingUrl(Request::post('img'));

        $branchConnect            = Request::post('branchConnect');

        if(!have_posts($branchConnect)) {
            $result['status'] = 'error';
            $result['message'] = 'Chi nhánh website và chi nhánh GHTK chưa liên kết';
            return $result;
        }

        foreach ($branchConnect as $item) {
            if($item == 0) {
                $result['status'] = 'error';
                $result['message'] = 'Bạn chưa chọn chi nhánh GHTK liên kết';
                return $result;
            }
        }

        $config['branchConnect']  = Request::post('branchConnect');

        $shipping = shipping_gateways();

        $shipping[GHTK_KEY] = $config;

        Option::update('cart_shipping', $shipping);

        return $result;
    }
    static function calculate($package): float|bool
    {

        $config     = GHTK::config();

        $cart       = Scart::getItems();

        if(!empty($package['show-form-shipping']) && $package['show-form-shipping'] == 'on') {
            if(empty($package['shipping_city'])) return false;
            if(empty($package['shipping_districts'])) return false;
            $citi 			= $package['shipping_city'];
            $districts 		= $package['shipping_districts'];
            $ward           = $package['shipping_ward'];
        }
        else {
            if(empty($package['billing_city'])) return false;
            if(empty($package['billing_districts'])) return false;
            $citi 			= $package['billing_city'];
            $districts 		= $package['billing_districts'];
            $ward           = $package['billing_ward'];
        }

        $transport = (!empty($package['shipping_ghtk_transport'])) ? $package['shipping_ghtk_transport'] : 'road';

        $weight = 0;

        foreach ($cart as $item) {
            if($item['weight'] == 0) $item['weight'] = (int)$config['weight'];
            $weight += $item['weight']*$item['qty'];
        }

        $fee = GHTK()->setWeight($weight)->setTransport($transport)->setValue(Scart::total())->shipAmount($citi, $districts, $ward);

		if(is_numeric($fee)) return ceil($fee);

        return false;
    }
    static function listService($shipping, $order): void
    {
        $config         = GHTK::config();

        $pickAddress    = Branch::gets(Qr::set()->where('status', 'working'));

        foreach ($pickAddress as $key => $pick) {
            if(empty($config['branchConnect'][$pick->id])) continue;
            $pickAddress[$key]->area = @unserialize($pick->area);
            $pickAddress[$key]->ghtkId = $config['branchConnect'][$pick->id];
        }

        if(!isset($order->billing_city)){
            echo notice('error', 'Đơn hàng này không đủ thông tin để đăng lên giao hàng tiết kiệm.');
        }
        else {
            $orderGHTK = [
                'pickId'    => '',
                'transport' => 'road',
                'option'    => 'cod',
                'weight'    => 0
            ];
            if(isset($order->_shipping_info['pickId'])) {
                $orderGHTK['pickId'] = $order->_shipping_info['pickId'];
            }
            if(isset($order->_shipping_info['transport'])) {
                $orderGHTK['transport'] = $order->_shipping_info['transport'];
            }
            if(!empty($order->_shipping_info['weight'])) {
                $orderGHTK['weight'] = $order->_shipping_info['weight'];
            }
            else {
                foreach ($order->items as $item) {
                    $weightItem = (int)Order::getItemMeta($item->id, 'weight', true);
                    if($weightItem == 0) $weightItem = 100;
                    $orderGHTK['weight'] += $weightItem*$item->quantity;
                }
            }

            $orderGHTK['weight'] = $orderGHTK['weight']/1000;

            Plugin::partial(GHTK_NAME, 'admin/order-detail/shipping', [
                'order'         => $order,
                'shipping'      => $shipping,
                'pickAddress'   => $pickAddress,
                'orderGHTK'     => $orderGHTK
            ]);
        }
    }
    static function change($shipping, $order): SKD_Error|array
    {
        $shippingData = Request::post('shipping');

        if(empty($shippingData['pick_id'])) {
            return new SKD_Error('error', 'Kho (chi nhánh) xuất hàng không được để trống');
        }

        $pickId = (int)$shippingData['pick_id'];

        if(empty($shippingData['transport'])) {
            return new SKD_Error('error', 'Bạn chưa chọn hình thức vận chuyển');
        }

        $transport = trim($shippingData['transport']);

        $config = GHTK::config();

        if(empty($config['branchConnect'][$pickId])) {
            return new SKD_Error('error', 'Kho (chi nhánh) xuất hàng chưa được liên kết với giao hàng tiết kiệm');
        }

        $branch = Branch::get(Qr::set($pickId));

        if(!have_posts($branch)) {
            return new SKD_Error('error', 'Kho (chi nhánh) xuất hàng không tồn tại');
        }

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

        if(empty($shippingData['weight'])) {
            return new SKD_Error('error', 'Khối lượng đơn hàng không được để trống');
        }

        $weight = (float)$shippingData['weight'];

        if(!isset($order->_shipping_info['weight'])) {
            $order->_shipping_info['weight'] = $weight;
        }

        $fee = GHTK()->setWeight($weight)->setTransport($transport)->setValue($order->total)->shipAmount($citi, $districts, $ward);

        if(!is_numeric($fee)) {
            return new SKD_Error('error', 'Không lấy được phí vận chuyển từ GHTK');
        }

        $money = $order->total;

        if(!empty($order->_shipping_price)) {
            $money = $order->total - $order->_shipping_price;
        }

        $isFreeShip = 0;

        if(isset($shippingData['isFreeShip'])) {
            $isFreeShip = 1;
        }

        $note = '';

        if(isset($shippingData['note'])) {
            $note = Str::clear($shippingData['note']);
        }

        $response = GHTK()
            ->setPick($branch)
            ->setPickMoney($money)
            ->setIsFreeship($isFreeShip)
            ->setTransport($transport)
            ->setOrder($order)
            ->setNote($note)
            ->addOrder();

        if(isset($response->success) && $response->success) {

            $orderMeta = [
                '_shipping_type'    => GHTK_KEY,
                '_shipping_price'   => $fee,
                '_shipping_label'   => $shipping['label'],
                '_shipping_info'    => [
                    'pickId'    => $shippingData['pick_id'],
                    'transport' => $shippingData['transport'],
                    'weight'    => $weight,
                    'isFreeShip'=> $isFreeShip,
                    'note'      => $note,
                ],
                'GHTK' => [
                    'info'      => (array)$response->order,
                    'pickId'    => $config['branchConnect'][$pickId],
                    'submitted' => GHTK()->GetOrder()->push_data(),
                ],
                'waybill_code' => $response->order->label
            ];

            $history = [
                'order_id'  => $order->id,
                'action'    => 'backend-ghtk-create-order',
                'message'   => '<span class="hs-usname"><b>'.Auth::user()->username.'</b></span> đã tạo vận đơn giao hàng tiết kiệm <span class="hs-ghtkcode"><b>'.$response->order->label.'</b></span>',
            ];

            OrderHistory::insert($history);

            return [
                'order' => $order,
                'orderMeta' => $orderMeta
            ];
        }

        if(!empty($response->error)) {
            if($response->error->code == 'ORDER_ID_EXIST') {
                Order::updateMeta($order->id, 'waybill_code', $response->error->ghtk_label);
            }
            return new SKD_Error('error', $response->message);
        }

        if(!empty($response->error_code)) {
            return new SKD_Error('error', $response->message);
        }

        return new SKD_Error('error', 'Không kết nối được GHTK');
    }
    static function info($shipping, $order): void
    {

        $waybill_code = Order::getMeta($order->id, 'waybill_code', true);

        if(!empty($waybill_code)) {

            $response = GHTK_Api()->getOrderStatus($waybill_code);

            Plugin::partial(GHTK_NAME, 'admin/order-detail/info', [
                'order' => $order,
                'shipping' => $shipping,
                'response' => $response,
            ]);
        }
    }
}

add_action('checkout_shipping_'.GHTK_KEY.'_template', 'GHTK_Shipping::service', 10, 1);

Class GHTK_Checkout {

    function __construct() {
        add_filter('checkout_order_before_save',array($this, 'setWeightDefaultToProduct'), 10, 2);
        add_filter('checkout_order_metadata_before_save', array($this, 'setPickIDToOrder'), 10, 2);
        add_action('checkout_review_order', array($this, 'feeReview'), 50);
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

            if(!isset($metadata_order['_shipping_info'])) $metadata_order['_shipping_info'] = [];

            if(empty($metadata_order['_shipping_info']['pickId'])) {
                $citi  = Request::post('billing_city');
                $pick = GHTK()->getPickArea($citi);
                if(have_posts($pick)) {
                    $metadata_order['_shipping_info']['pickId'] = $pick->id;
                }
            }

            $metadata_order['_shipping_info']['transport'] = (!empty(Request::post('shipping_ghtk_transport'))) ? Request::post('shipping_ghtk_transport') : 'road';
        }

        return $metadata_order;
    }

    function feeReview(): void
    {
        $checkout       = Cms::getData('checkout');

        $shippingPrice  = (isset($checkout['shipping_price_ghtk'])) ? $checkout['shipping_price_ghtk'] : false;

        if($shippingPrice !== false) {

            if($shippingPrice === 0) $shippingPrice = 'Miễn phí';

            if(!empty($shippingPrice) && is_numeric($shippingPrice)) $shippingPrice = number_format($shippingPrice)._price_currency();

            $transports = [];

            $transportKey = Request::post('shipping_ghtk_transport');

            $transportKey = (!empty($transportKey)) ? $transportKey : 'road';

            if(Request::Post('shipping_type') == GHTK_KEY) {

                $citi 		= Request::Post('billing_city');
                $districts 	= Request::Post('billing_districts');
                $ward 		= Request::Post('billing_ward');

                if(!empty($citi) && !empty($districts)) {

                    $weight = 0;

                    foreach (Scart::getItems() as $key => $item) {
                        if($item['weight'] == 0) $item['weight'] = 100;
                        $weight += $item['weight']*$item['qty'];
                    }

                    $transports['road'] = [
						'name' => 'Đường bộ',
	                    'fee'  => GHTK()->setWeight($weight)->setTransport('road')->setValue(Scart::total())->shipAmount($citi, $districts, $ward)
                    ];

                    $transports['fly'] = [
                        'name' => 'Đường bay',
                        'fee'  => GHTK()->setWeight($weight)->setTransport('fly')->setValue(Scart::total())->shipAmount($citi, $districts, $ward)
                    ];
                }
            }

            Plugin::partial(GHTK_NAME, 'checkout/review', [
                'shippingPrice' => $shippingPrice,
	            'transportKey'  => $transportKey,
	            'transports'    => $transports,
            ]);
        }

    }
}