<div class="box-ghtk-order-created">
    <form id="ghtk_form_order__created">

        <input type="hidden" name="id" class="form-control" value="<?php echo $order->id;?>">
        
        <div class="header"><h2>ĐĂNG ĐƠN LÊN GIAO HÀNG TIẾT KIỆM</h2></div>

        <div class="ghtk-order-created__content">

            <div class="col-md-12" style="padding-top:10px;padding-bottom:10px;">
                <div class="row">
                    <div class="col-md-4">
                        <span>Chọn kho hàng:</span>
                    </div>
                    <div class="col-md-8">
                        <select name="ghtk[pick_id]" class="form-control ghtk_pick" required>
                            <?php foreach ($PickAddress as $key => $pick) {?>
                                <option value="<?php echo $pick->ghtk_id;?>" <?php echo ($pick->ghtk_id == $order->GHTK_info['PickID']) ? 'selected=selected' :'';?>> #<?php echo $pick->ghtk_id;?> -  <?php echo $pick->name;?> - <?php echo $pick->address;?>, <?php echo Cart_Location::cities($pick->city);?></option>
                            <?php } ?>
                        </select>
                        <p>Thay đổi chi nhanh có thể sẽ làm phí vận chuyển thay đổi</p>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <hr style="margin:0;">
            <div class="clearfix"></div>
            <div class="col-md-6 col-left">
                <h4>Thông tin người nhận hàng</h4>
                <div class="row info-tr">
                    <div class="col-md-4"><span>Số điện thoại:</span></div>
                    <div class="col-md-8"><?php echo $order->billing_phone;?></div>
                </div>

                <div class="row info-tr">
                    <div class="col-md-4"><span>Tên khách hàng:</span></div>
                    <div class="col-md-8"><?php echo $order->billing_fullname;?></div>
                </div>

                <div class="row info-tr">
                    <div class="col-md-4"><span>Địa chỉ:</span></div>
                    <div class="col-md-8"><?php echo $order->billing_address;?></div>
                </div>

                <div class="row info-tr">
                    <div class="col-md-4"><span>Quận huyện:</span></div>
                    <div class="col-md-8"><?php echo Cart_Location::districts($order->billing_city, $order->billing_districts);?></div>
                </div>

                <div class="row info-tr">
                    <div class="col-md-4"><span>Tỉnh thành:</span></div>
                    <div class="col-md-8"><?php echo Cart_Location::cities($order->billing_city);?></div>
                </div>

                <div class="row info-tr">
                    <div class="col-md-4"><span>Phường xã:</span></div>
                    <div class="col-md-8"><?php echo Cart_Location::ward($order->billing_districts, $order->billing_ward);?></div>
                </div>

                <h4>Thông tin hàng hóa</h4>
                <table class="table table-bordered" style="width:100%">
                    <?php foreach ($order->items as $key => $val): ?>
                        <tr class="item">
                            <td style="border:1px solid #ccc;">
                                <?php echo $val->title;?>
                                <?php $val->option = (is_serialized($val->option))?@unserialize($val->option):$val->option ;?>
                                <?php if(isset($val->option) && have_posts($val->option)) {
                                    $attributes = '';
                                    foreach ($val->option as $key => $attribute): $attributes .= $attribute.' / '; endforeach;
                                    $attributes = trim( trim($attributes), '/' );
                                    echo '<span class="variant-title" style="color:#999;">'.$attributes.'</span>';
                                } ?>
                            </td>
                            <td style="border:1px solid #ccc;"><b><?= $val->quantity;?></b></td>
                            <td style="border:1px solid #ccc;"><?= (int)Order::getItemMeta($val->id, 'weight', true);?> grams</td>
                        </tr>
                    <?php endforeach ?>
                </table>

            </div>
            <div class="col-md-6">
                <h4>Thông tin giao hàng</h4>

                <div class="form-group row">
                    <div class="col-md-4"><span>Hình thức gửi hàng:</span></div>
                    <div class="col-md-8">
                        <label class="pd-none"> <input name="ghtk[pick_option]" type="radio" value="cod" checked> Lấy tận nơi nhận hàng </label>
                        <label class=""> <input name="ghtk[pick_option]" type="radio" value="post"> Gửi hàng bưu cục</label>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-4">Hình thức vận chuyển</div>
                    <div class="col-md-8 checkbox">
                        <label class="pd-none"> <input name="ghtk[transport]" type="radio" value="road" class="ghtk_transport" checked> Đường bộ </label>
                        <label class=""> <input name="ghtk[transport]" type="radio" value="fly" class="ghtk_transport"> Đường bay </label>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-4">Phí ship :</div>
                    <div class="col-md-8 checkbox">
                        <b id="ghtk_ship_money"><?php echo number_format($order->_shipping_price);?>đ</b>
                        <label class=""> <input name="ghtk[is_freeship]" type="radio" value="1" checked class="ghtk_is_freeship"> Shop trả </label>
                        <label class=""> <input name="ghtk[is_freeship]" type="radio" value="0" class="ghtk_is_freeship"> Khách trả </label>
                    </div>

                </div>

                <div class="form-group row">
                    <div class="col-md-4">Giá trị đơn hàng : </div>
                    <div class="col-md-8">
                        <b><?php echo number_format($order->total - $order->_shipping_price);?>đ</b>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-4"><label for="input" class="control-label">Tiền thu hộ:</label></div>
                    <div class="col-md-8">
                        <input type="text" name="ghtk[pick_money]" class="form-control ghtk_pick_money" value="<?php echo $order->total - $order->_shipping_price;?>" required>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-4">Tổng tiền thu : </div>
                    <div class="col-md-8">
                        <b id="ghtk_total_money"><?php echo number_format($order->total - $order->_shipping_price);?>đ</b>
                    </div>
                </div>

                <div class="form-group">
                    <label for="input" class="control-label">Ghi chú cho GHTK:</label>
                    <textarea name="ghtk[note]" class="form-control" rows="3"><?php echo $order->order_note;?></textarea>
                </div>

                <div class="mg-b-10"></div>
            </div>
            
            <div class="clearfix"></div>
        </div>

        <div class="ghtk-order-created__footer">
            <div class="text-right"><button type="submit" class="btn btn-blue" id="ghtk_form_order__created_submit">Đăng ngay</button></div>
        </div>

        <style>
            .box-ghtk-order-created { min-width:1000px; padding:0;}
            .box-ghtk-order-created .header {
                background-color:#088A4B; color:#fff;
            }
            .box-ghtk-order-created .header h2{
                color:#fff;
            }
            .ghtk-order-created__content h4 {
                margin:20px 0;
            }

            .ghtk-order-created__content .col-left {
                border-right:1px solid #ccc;
            }

            .info-tr { margin-bottom:10px; font-size:12px; }
            .mg-b-10 { margin-bottom: 10px;}
            .pd-none { padding: 0px!important;}

            .ghtk-order-created__footer {
                padding:10px;
                border-top:1px solid #ccc;
            }
            .form-group { margin-bottom:15px; }
            .checkbox { margin:0px; }
        </style>

    </form>
</div>

<script>
    let pick_money = parseInt($('input.ghtk_pick_money').val());

    let ship_money = parseInt(<?php echo $order->_shipping_price;?>);

    let is_freeship = $('input.ghtk_is_freeship').val();

    let pick_id     = $('select.ghtk_pick').val();

    let total_money = 0;

    $('input.ghtk_is_freeship').change(function() {

        is_freeship = parseInt($(this).val());

        if(is_freeship === 1) {
            total_money = pick_money;
        }

        if(is_freeship === 0) {
            total_money = pick_money + ship_money;
        }

        $('#ghtk_total_money').html(FormatNumber(total_money+'')+'đ');
    });

    $('input.ghtk_pick_money').keyup(function() {

        is_freeship = parseInt($('input.ghtk_is_freeship:checked').val());

        pick_money = parseInt($(this).val());

        if(is_freeship === 1) {
            total_money = pick_money;
        }

        if(is_freeship === 0) {
            total_money = pick_money + ship_money;
        }

        $('#ghtk_total_money').html(FormatNumber(total_money+'')+'đ');
    });

    $(document).on('change', 'select.ghtk_pick', function() {

        let pick_id_change = $(this).val();

        if(pick_id_change === pick_id) return false;

        pick_id = pick_id_change;

        GHTK_order_review();
    });

    $(document).on('change', 'input.ghtk_transport', function() {

        GHTK_order_review();
    });

    $(document).on('click', '#ghtk_form_order__created_submit', function() {

        let data = $('#ghtk_form_order__created').serializeJSON();

        data.action = 'admin_ajax_ghtk_order_create';

        $jqxhr = $.post(base + '/ajax', data, function () { }, 'json');

        $jqxhr.done(function (data) {

            show_message(data.message, data.status);

            if (data.status === 'success') {
                $('#GHTK_modal_order_create').modal('hide');
                GHTK_SHOW = false;
            }
        });

        return false;
    });

    function GHTK_order_review() {

        $('#ghtk_loading').show();

        var data = $('#ghtk_form_order__created').serializeJSON();

        data.action = 'admin_ajax_ghtk_order_review';

        $jqxhr = $.post(base + '/ajax', data, function () { }, 'json');

        $jqxhr.done(function (data) {

            $('#ghtk_loading').hide();

            if (data.status === 'success') {

                ship_money = parseInt(data.shipping_price);

                if(is_freeship == 1) total_money = pick_money;

                if(is_freeship == 0) total_money = pick_money + ship_money;

                $('#ghtk_total_money').html(FormatNumber(total_money+'')+'đ');

                $('#ghtk_ship_money').html(FormatNumber(ship_money+'')+'đ');
            }
        });
    }
</script>