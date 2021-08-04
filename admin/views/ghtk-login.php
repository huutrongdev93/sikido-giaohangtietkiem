<?php
    $Form = new FormBuilder();
    $Form
        ->add('ghtk_user_login', 'email', [
            'label' => 'Tài khoản',
        ], '')
        ->add('ghtk_pass_login', 'password', [
            'label' => 'Mật khẩu',
        ], '')
        ->add('ghtk_mode', 'radio', [
            'single' => true, 'label' => 'Chế độ',
            'options' => [
                'test' => 'Sandbox (chạy thử nghiệm)',
                'prod' => 'Production (chạy thực)'
            ]], GHTK::config('mode'))->html(false);
?>

<div class="col-md-12">
    <div class="form-group">
        <button type="button" class="btn btn-green js_ghtk_btn__connect">Kết Nối</button>
    </div>
</div>

<style>
    .form-group {
        overflow: hidden; margin-bottom:20px;
    }
    .shipping-button.footer { display: none; }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        $('.js_ghtk_btn__connect').click(function() {
            let box = $(this).closest('.shipping-form');
            let data = $(':input', box).serializeJSON();
            let loading = box.find('.loading');
            loading.show();
            data.action = 'admin_ajax_ghtk_connect';
            $jqxhr   = $.post(ajax, data, function() {}, 'json');
            $jqxhr.done(function( data ) {
                loading.hide();
                show_message(data.message, data.status);
                if(data.status === 'success') {
                    location.reload(true);
                }
            });

            return false;

        });
    });
</script>
