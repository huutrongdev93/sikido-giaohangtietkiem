<?php
$Form = new FormBuilder();
$Form
    ->add('enabled', 'checkbox', [
        'label' => 'Bật /Tắt shipping GHTK',
        'after' => '<div class="col-md-6 form-group">', 'before' => '</div>',
    ], (!empty($shipping['enabled'])) ? 'enabled' : '')
    ->add('default', 'checkbox', [
        'options' => 'ghtk',
        'label' => 'Phí ship mặc định',
        'after' => '<div class="col-md-6 form-group">', 'before' => '</div><div class="clearfix"></div>',
    ], (!empty($shipping['default'])) ? 'ghtk' : '')
    ->add('ghtk[mode]', 'radio', [
        'single' => true, 'label' => 'Chế độ',
        'options' => [
            'test' => 'Sandbox (chạy thử nghiệm)',
            'prod' => 'Production (chạy thực)'
        ]],  (!empty($shipping['mode'])) ? $shipping['mode'] : 'test')
    ->add('img', 'image', [
        'label' => 'Icon',
        'after' => '<div class="col-md-6 form-group">', 'before' => '</div>',
    ], $shipping['img'])
    ->add('title', 'text', [
        'label' => 'Tiêu đề',
        'after' => '<div class="col-md-6 form-group">', 'before' => '</div><div class="clearfix"></div>',
    ], $shipping['title'])
    ->add('price_default', 'text', [
        'label' => 'Giá mặc định',
        'after' => '<div class="col-md-6 form-group">', 'before' => '</div>',
    ], (int)$shipping['price_default'])
    ->add('ghtk[weight]', 'number', [
        'label' => 'Khối lượng sản mặc định (Gram)',
        'after' => '<div class="col-md-6 form-group">', 'before' => '</div><div class="clearfix"></div>',
    ], $shipping['weight']);
$Form = apply_filters('admin_payment_'.$key_shipping.'_input_fields', $Form, $shipping);
?>
<div class="row">
    <div class="col-md-8">
        <?php echo $Form->html(false);?>
    </div>
    <div class="col-md-4">
        <?php echo notice('warning', '<p style="margin-top: 5px;">Cấu hình liên kết chi nhánh GHTK và Website</p> <p><b>Sản phẩn > chi nhánh</b></p>');?>
    </div>
</div>
<div class="clearfix"></div>

<style>
    table.table tr { border: 1px solid #ccc; }
    .radio label, .checkbox label {
        padding-left:0;
    }
    .select2-container { width: 100%!important; }
    .form-group { overflow: hidden; margin-bottom: 10px;}
</style>