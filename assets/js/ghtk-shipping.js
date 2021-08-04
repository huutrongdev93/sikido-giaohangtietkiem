$(function () {
    $(document).on('change', 'input[name="shipping_ghtk_transport"]', function () {
        update_order_review();
    });
});