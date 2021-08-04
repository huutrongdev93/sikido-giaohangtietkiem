$(function () {

    let billing_city        = '#order_customer_infomation_result #billing_city';
    let billing_districts   = '#order_customer_infomation_result #billing_districts';
    let billing_ward        = '#order_customer_infomation_result #billing_ward';

    //Billing
    $(document).on('change', billing_city, function () {
        GHTK_load_districts(billing_city, billing_districts, billing_ward);
    });

    $(document).on('change', billing_districts, function () {
        GHTK_load_ward(billing_districts, billing_ward);
    });

    $(document).on('change', billing_ward, function () {});


    //Shipping
    $(document).on('change', '#order_customer_infomation_result #shipping_city', function () {
        let data = {
            province_id: $(this).val(),
            action: 'ghtk_ajax_load_districts'
        }

        $jqxhr = $.post(base + '/ajax', data, function () { }, 'json');

        $jqxhr.done(function (data) {
            if (data.type == 'success') {
                $('#order_customer_infomation_result #shipping_districts').html(data.data);
                $('#order_customer_infomation_result #shipping_ward').html('<option value="">Chọn phường xã</option>');
            }
        });
    });

    $(document).on('change', '#order_customer_infomation_result #shipping_districts', function () {

        let data = {
            district_id: $(this).val(),
            action: 'ghtk_ajax_load_ward'
        }

        $jqxhr = $.post(base + '/ajax', data, function () { }, 'json');

        $jqxhr.done(function (data) {
            if (data.type == 'success') {
                $('#order_customer_infomation_result #shipping_ward').html(data.data);
            }
        });

    });

    $(document).on('change', '#order_customer_infomation_result #shipping_districts', function () {});

    function GHTK_load_districts(city, districts, ward) {

        let data = {
            province_id: $(city).val(),
            action: 'ghtk_ajax_load_districts'
        };

        $jqxhr = $.post(base + '/ajax', data, function () { }, 'json');

        $jqxhr.done(function (data) {
            if (data.type == 'success') {
                $(districts).html(data.data);
                $(ward).html('<option value="">Chọn phường xã</option>');
            }
        });
    }

    function GHTK_load_ward(districts, ward) {

        var data = {
            district_id: $(districts).val(),
            action: 'ghtk_ajax_load_ward'
        };

        $jqxhr = $.post(base + '/ajax', data, function () { }, 'json');

        $jqxhr.done(function (data) {

            if (data.type == 'success') {

                $(ward).html(data.data);

                admin_order_add_review();
            }
        });
    }
})