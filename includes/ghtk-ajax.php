<?php
if(!function_exists('admin_ajax_ghtk_connect')) {

    function admin_ajax_ghtk_connect($ci, $model) {

        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if( InputBuilder::post() ) {

            $user 	= trim(InputBuilder::post('ghtk_user_login'));

            $pass 	= trim(InputBuilder::post('ghtk_pass_login'));

            $mode 	= trim(InputBuilder::post('ghtk_mode'));

            $config         = GHTK::config();
            $config['mode'] = $mode;
            $shipping = shipping_gateways();
            $shipping[GHTK_KEY] = $config;
            Option::update('cart_shipping', $shipping);
            $response = GHTK()->connect($user, $pass, $mode);
            if(isset($response->success) && $response->success == true) {

                $config = shipping_gateways();

                $GHTK_config = GHTK::config();

                $GHTK_config['code'] = $response->data->code;

                $GHTK_config['token'] = $response->data->token;

                $config['ghtk'] = $GHTK_config;

                Option::update('cart_shipping', $config);

                Option::update('shipping_ghtk_default_html', true);

                $result['status']  = 'success';
            }
            else {

                $result['status']  = 'error';

                $result['message'] = $response->message;
            }
        }
        echo json_encode($result);
    }

    Ajax::admin('admin_ajax_ghtk_connect');
}

if(!function_exists('admin_ajax_ghtk_order_html')) {

	function admin_ajax_ghtk_order_html( $ci, $model ) {

		$result['status']  = 'error';

		$result['message'] = __('Lưu dữ liệu không thành công');
		
		if( InputBuilder::post() ) {

			$id = (int)InputBuilder::post('id');

			$order = Order::get( $id );

			if(have_posts($order)) {

                $waybill_code = Order::getMeta( $order->id, 'waybill_code', true );

                if(empty($waybill_code)) {

                    $config = GHTK::config();

                    $PickAddress = Branch::gets(['where' => ['ghtk_id <>' => 0, 'status' => 'working']]);
                    foreach ($PickAddress as $key => $pick) {
                        $PickAddress[$key]->area = @unserialize($pick->area);
                    }

                    ob_start();
                    if(!isset($order->billing_city)){
                        echo notice('error', 'Đơn hàng này không đủ thông tin để đăng lên giao hàng tiết kiệm.'); return;
                    }
                    include GHTK_PATH.'/admin/views/ghtk.order.add.php';
                    $result['html'] 	= ob_get_clean();
                    $result['status']  	= 'success';
                }
                else {
                    $result['status']  	= 'Đơn hàng đã đăng giao hàng tiết kiệm';
                }
			}
		}

		echo json_encode($result);

		return true;
	}

	Ajax::admin('admin_ajax_ghtk_order_html');
}

if(!function_exists('admin_ajax_ghtk_order_review')) {

    function admin_ajax_ghtk_order_review( $ci, $model ) {

        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if( InputBuilder::post() ) {

            $data = InputBuilder::post('ghtk');

            $id = (int)InputBuilder::post('id');

            $order = Order::get( $id );

            if(have_posts($order)) {

                $config = GHTK::config();

                $pick_id = trim(Str::clear($data['pick_id']));

                $transport = trim(Str::clear($data['transport']));

                $branch = Branch::get(['where' => ['ghtk_id' => $pick_id]]);

                if(!have_posts($branch)) {
                    $result['message'] = __('Kho hàng không tồn tại'); echo json_encode($result); return false;
                }

                $weight = 0;

                foreach ($order->items as $key => $val):
                    $weight += (int)Order::getItemMeta($val->id, 'weight', true)/1000*$val->quantity;
                endforeach;

                $shipping_price = GHTK()
                    ->setPick($branch)
                    ->setWeight($weight)
                    ->setTransport($transport)
                    ->setValue($order->total - $order->_shipping_price)
                    ->setShipProvince($order->billing_city)
                    ->setShipDistrict($order->billing_districts)
                    ->setShipWard($order->billing_ward)
                    ->shipAmount();

                if( $shipping_price != false ) {
                    $result['shipping_price']  = $shipping_price;
                    $result['status']  = 'success';
                    $result['message'] = __('Lưu dữ liệu thành công');
                }
            }
        }

        echo json_encode($result);

        return true;
    }

    Ajax::admin('admin_ajax_ghtk_order_review');
}

if(!function_exists('admin_ajax_ghtk_order_create')) {

	function admin_ajax_ghtk_order_create( $ci, $model ) {

		$result['status']  = 'error';

		$result['message'] = __('Lưu dữ liệu không thành công');
		
		if( InputBuilder::post() ) {

			$id 	= (int)InputBuilder::post('id');

			$data 	= InputBuilder::post('ghtk');

			$order = Order::get( $id );

			if(have_posts($order)) {

                $pick_id = trim(Str::clear($data['pick_id']));

                $branch = Branch::get(['where' => ['ghtk_id' => $pick_id]]);

                if(!have_posts($branch)) {
                    $result['message'] = __('Kho hàng không tồn tại'); echo json_encode($result); return false;
                }

				$pick_money  	= (int)Str::clear($data['pick_money']);

				$is_freeship 	= (int)Str::clear($data['is_freeship']);

				$note 		 	= Str::clear($data['note']);

				$transport 	 	= Str::clear($data['transport']);

				$response = GHTK()
								->setPick($branch)
								->setPickMoney($pick_money)
								->setIsFreeship($is_freeship)
								->setTransport($transport)
								->setOrder($order)
								->setNote($note)
								->addOrder();

				if($response->success == true) {

                    Order::updateMeta( $order->id, 'GHTK_fullinfo', (array)$response->order );
                    Order::updateMeta($order->id, 'GHTK_submited', GHTK()->GetOrder()->push_data());
                    Order::updateMeta( $order->id, 'waybill_code', $response->order->label );

                    $history = [
                        'order_id' => $id,
                        'action' => 'backend-ghtk-create-order',
                        'message'  => '<span class="hs-usname"><b>'.Auth::user()->username.'</b></span> đã tạo vận đơn giao hàng tiết kiệm <span class="hs-ghtkcode"><b>'.$response->order->label.'</b></span>',
                    ];

                    Order::insertHistory($history);

					$result['status']  = 'success';
					$result['info']    = $response->order;
					$result['message'] = $response->message;
				}
				else {
					if(!empty($response->error)) {
						if($response->error->code == 'ORDER_ID_EXIST') {
                            Order::updateMeta( $order->id, 'waybill_code', $response->error->ghtk_label );
						}
						$result['info'] 	= $response->error;
					}

					$result['status']  = 'error';
					$result['message'] 	= $response->message;
				}
					
			}
		}

		echo json_encode($result);
	}

	Ajax::admin('admin_ajax_ghtk_order_create');
}


