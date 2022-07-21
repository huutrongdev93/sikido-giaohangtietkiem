<?php
function ghtk_sync_order_status() {

    $hash       = xss_clean(Request::get('hash'));

    $code       = (int)xss_clean(Request::get('code'));

    $status     = (int)xss_clean(Request::get('status'));

    $mavandon   = xss_clean(Request::get('label'));

    $config = GHTK::config();

    if($hash == md5($config['code'].'sikido_connect_ghtk')) {

        $order = Order::get(Qr::set('code', $code));

        if(have_posts($order) && isset($order->waybill_code)) {

            if($order->waybill_code == $mavandon) {

                if(empty($order->GHTK_status) || $order->GHTK_status != $status) {

                    if($status == 4) {

                        $order_update = [
                            'id'     => $order->id,
                            'status' => 'wc-ship',
                        ];

                        $errors = Order::insert( $order_update );

                        if(!is_skd_error($errors)) {
                            do_action( 'admin_order_status_wc-ship_action',  $order, 'wc-ship');
                        }
                    }
                    $history = [
                        'order_id' => $order->id,
                        'action' => 'backend-ghtk-update-order',
                        'message' => '<span class="hs-usname"><b>GHTK</b></span> đã cập nhật vận đơn thành <span class="hs-ghtkcode"><b>' . GHTK::status($status) . '</b></span>',
                    ];

                    OrderHistory::insert($history);

                    Order::updateMeta($order->id, 'GHTK_status', $status);
                }
            }
        }
    }
}