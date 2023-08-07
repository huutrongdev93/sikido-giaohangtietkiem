<div class="row">
	<div class="col-md-6">
		<div class="d-flex justify-content-between">
			<p>Mã vận đơn</p>
			<p><strong><?php echo $order->waybill_code;?></strong></p>
		</div>
		<div class="d-flex justify-content-between">
			<p>Nhà vận chuyển</p>
			<p><strong><?php echo $shipping['label'];?></strong></p>
		</div>
	</div>
	<div class="col-md-6">
		<div class="d-flex justify-content-between">
			<p>Trạng thái vận chuyển</p>
			<p><strong><?php echo GHTK::status($response->order->status);?></strong></p>
		</div>
		<div class="d-flex justify-content-between">
			<p>Kho lấy hàng</p>
			<p><strong><?php echo $order->_shipping_info['pickId'];?></strong></p>
		</div>
		<div class="d-flex justify-content-between">
			<p>Tổng khối lượng</p>
			<p><strong><?php echo $order->_shipping_info['weight'];?> kg</strong></p>
		</div>
	</div>
</div>