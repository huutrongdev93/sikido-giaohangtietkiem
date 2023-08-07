<?php
Class GHTK_Order_Action {
    function __construct() {
        add_action('shipping_online_table_column', array($this, 'tableColumn'), 10, 1);
        add_action('order_detail_header_action', array($this, 'buttonPrint'), 10);
        add_action('template_redirect', array($this, 'printOrder'));
        add_action('admin_order_status_wc-cancelled_save', array($this, 'cancelOrder'), 20 );
    }
    public function tableColumn( $order ) {
        if(isset($order->_shipping_type) && $order->_shipping_type == GHTK_KEY) {
            if(!empty($order->waybill_code)) {
                if(empty($order->GHTK['info']) || empty($order->GHTK['status'])) {
                    $response = GHTK_Api()->getOrderStatus($order->waybill_code);
                    if(!empty($response) && $response->success == 1) {
                        $response = $response->order;
                        if(empty($order->GHTK['info'])) {
                            $order->GHTK['info'] = [
                                "partner_id" => $response->partner_id,
                                "label" => $response->label_id,
                                "fee" => $response->ship_money,
                                "insurance_fee" => $response->insurance,
                                "estimated_pick_time" => $response->pick_date,
                                "estimated_deliver_time" => $response->deliver_date,
                                "status_id" => $response->status,
                                'products' => $response->products
                            ];
                        }
                        if(empty($order->GHTK['status'])) {
                            $order->GHTK['status'] = $response->status;
                        }
                        Order::updateMeta($order->id, 'GHTK', $order->GHTK);
                    }
                }
                echo '<p class="mb-0" style="margin-top:5px;background-color:#05934D; border-radius:20px; padding:3px 15px; font-size:12px;color:#fff;">'.$order->waybill_code.'</p>';
                if(!empty($order->GHTK['status'])) echo '<p class="mb-0">'.GHTK::status($order->GHTK['status']).'</p>';
            }
            else {
                echo '<p style="color: var(--red);">Chưa đăng giao hàng</p>';
            }
        }
    }
    public function buttonPrint($order): void
    {
        if(empty($order->_shipping_type) || $order->_shipping_type != GHTK_KEY) return;
        $waybill_code = Order::getMeta( $order->id, 'waybill_code', true );
        if(!empty($waybill_code)) {
            ?>
            <a href="<?php echo Url::admin('plugins?page=ghtk_order_print&id='.$order->id);?>" class="btn btn-default btn-ghtk-order__print"><?php get_img('https://dev.ghtk.vn/img/ico/favicon.png', '', array('style' => 'width:20px;'));?> In hóa đơn GHTK</a>
        <?php }
    }
    public function printOrder( $order ): void
    {

        if(!Admin::is()) return;

        if( !Template::isPage('plugins_index') ) return;

        if(Request::Get('page') != 'ghtk_order_print' ) return;

        $id = (int)Request::Get('id');

        $ghtk_id = Order::getMeta( $id,'waybill_code', true);

        GHTK_API()->printOrder($ghtk_id);

        die;
    }
    public function cancelOrder($order): void
    {
        if(!empty($order->waybill_code) && !empty($order->_shipping_type) && $order->_shipping_type == GHTK_KEY) {
            $response = GHTK_API()->cancelOrder($order->waybill_code);
            if($response) {
                Order::updateMeta($order->id, 'GHTK_status', -1);
            }
        }
    }
}

function ghtk_order_print() {}