<div class="box" id="order_action">
    <div class="box-content" style="padding-top:0">
        <header class="order__title" style="background-color: green; color:#fff">
            <div class="order__title_wrap"> <h2 style="padding:5px 0;">Thông tin GHTK</h2> </div>
        </header>
        <div class="order_cart__section">
            <div class="ghtk-order-created__content">
                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-4">Mã vận đơn</div>
                    <div class="col-md-8 text-right text-bold"><b><?php echo $order->waybill_code;?></b></div>
                </div>
                <?php if(isset($response->success) && $response->success == 1) { ?>
                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-6">Trạng thái</div>
                    <div class="col-md-6 text-right text-bold"><?php echo GHTK::status($response->order->status);?></div>
                </div>

                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-6">Dự kiến lấy hàng</div>
                    <div class="col-md-6 text-right text-bold"><?php echo date('d/m/Y', strtotime($response->order->pick_date));?></div>
                </div>

                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-6">Dự kiến giao hàng</div>
                    <div class="col-md-6 text-right text-bold"><?php echo date('d/m/Y', strtotime($response->order->deliver_date));?></div>
                </div>
                <?php } ?>
                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-6">Phí ship</div>
                    <div class="col-md-6 text-right text-bold"><b><?php echo number_format($order->GHTK_fullinfo['fee']);?>đ</b></div>
                </div>

                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>