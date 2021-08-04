<?php
Class GHTK_Order_Action {
    function __construct() {
        add_action('shipping_online_table_column', array($this, 'tableColumn'), 10, 1);
        add_action('order_detail_header_action', array($this, 'buttonSync'), 10);
        add_action('order_detail_header_action', array($this, 'buttonPrint'), 10);
        add_action('order_detail_sections_secondary', array($this, 'statusGHTK'), 5, 1);
        add_action('template_redirect', array($this, 'printOrder'));
        add_action( 'admin_order_status_wc-cancelled_save', array($this, 'cancelOrder'), 20 );
    }
    public function tableColumn( $order ) {
        if(isset($order->_shipping_type) && $order->_shipping_type == GHTK_KEY) {
            if(!empty($order->waybill_code)) {
                if(empty($order->GHTK_fullinfo) || empty($order->GHTK_status)) {
                    $response = GHTK_Api()->getOrderStatus($order->waybill_code);
                    if(!empty($response) && $response->success == 1) {
                        $response = $response->order;
                        if(empty($order->GHTK_fullinfo)) {
                            $fullinfo = [
                                "partner_id" => $response->partner_id,
                                "label" => $response->label_id,
                                "fee" => $response->ship_money,
                                "insurance_fee" => $response->insurance,
                                "estimated_pick_time" => $response->pick_date,
                                "estimated_deliver_time" => $response->deliver_date,
                                "status_id" => $response->status,
                                'products' => $response->products
                            ];
                            Order::updateMeta($order->id, 'GHTK_fullinfo', $fullinfo);
                            $order->GHTK_fullinfo = $fullinfo;
                        }
                        if(empty($order->GHTK_status)) {
                            Order::updateMeta($order->id, 'GHTK_status', $response->status);
                            $order->GHTK_status = $response->status;
                        }
                    }
                }
                echo '<p style="margin-top:5px;background-color:#05934D; border-radius:20px; padding:3px 15px; font-size:12px;color:#fff;">'.$order->waybill_code.'</p>';
                if(!empty($order->GHTK_fullinfo)) echo '<p><b>Phí ship: </b>'.number_format($order->GHTK_fullinfo['fee']).'</p>';
                if(!empty($order->GHTK_status)) echo '<p>'.GHTK::status($order->GHTK_status).'</p>';
            }
            else {
                echo '<p style="color: var(--red);">Chưa đăng giao hàng</p>';
            }
        }
    }
    public function buttonSync( $order ) {
        if(empty($order->_shipping_type) || $order->_shipping_type != GHTK_KEY) return;
        $waybill_code = Order::getMeta( $order->id, 'waybill_code', true );
        if(empty($waybill_code)) {
            $text = 'Tạo vận đơn GHTK';
            ?>
            <button type="button" class="btn btn-default btn_ghtk_order__create" data-id="<?php echo $order->id; ?>">
                <?php Template::img(Url::base(GHTK_PATH).'/assets/images/logo-ghtk.png', '', array('style' => 'width:20px;')); ?><?php echo $text; ?>
            </button>
            <div class="modal fade" id="GHTK_modal_order_create">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body" style="padding:0;">
                            <div id="GHTK_modal_order_html"></div>
                            <?php echo Admin::loading('ghtk_loading'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .modal-lg { max-width: 100%; width: 1100px;  }
            </style>
            <script>
                $(function () {
                    GHTK_SHOW = false;
                    $('.btn_ghtk_order__create').click(function (e) {

                        e.preventDefault();

                        $('#GHTK_modal_order_create').modal('show');

                        if (GHTK_SHOW === false) {

                            let id = $(this).attr('data-id');

                            let data = {
                                action: 'admin_ajax_ghtk_order_html',
                                id: id
                            };

                            $.post(ajax, data, function () {}, 'json').done(function (data) {
                                if (data.status === 'success') {
                                    $('#GHTK_modal_order_html').html(data.html);
                                    GHTK_SHOW = true;
                                }
                            });
                        }
                    });
                })
            </script>
            <?php
        }
    }
    public function buttonPrint( $order ) {
        if(empty($order->_shipping_type) || $order->_shipping_type != GHTK_KEY) return;
        $waybill_code = Order::getMeta( $order->id, 'waybill_code', true );
        if(!empty($waybill_code)) {
            ?>
            <a href="<?php echo Url::admin('plugins?page=ghtk_order_print&id='.$order->id);?>" class="btn btn-default btn-ghtk-order__print"><?php get_img('https://dev.ghtk.vn/img/ico/favicon.png', '', array('style' => 'width:20px;'));?> In hóa đơn GHTK</a>
        <?php }
    }
    public function statusGHTK( $order ) {
        if(isset($order->_shipping_type) && $order->_shipping_type == GHTK_KEY) {
            $waybill_code = Order::getMeta( $order->id, 'waybill_code', true );
            if(!empty($waybill_code)) {
                $response = GHTK_Api()->getOrderStatus($waybill_code);
                include GHTK_PATH.'/admin/views/ghtk.order.info.php';
            }
        }
    }
    public function printOrder( $order ) {

        if(!Admin::is()) return;

        if( !Template::isPage('plugins_index') ) return;

        if(InputBuilder::Get('page') != 'ghtk_order_print' ) return;

        $id = (int)InputBuilder::Get('id');

        $ghtk_id = Order::getMeta( $id,'waybill_code', true);

        GHTK_API()->printOrder($ghtk_id);

        die;
    }
    public function cancelOrder($order) {
        if(!empty($order->waybill_code) && !empty($order->_shipping_type) && $order->_shipping_type == GHTK_KEY) {
            $response = GHTK_API()->cancelOrder($order->waybill_code);
            if($response == true) {
                Order::updateMeta($order->id, 'GHTK_status', -1);
            }
        }
    }
}

function ghtk_order_print() {}