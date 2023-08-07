<div class="form-group">
	<label>Kho (chi nhánh) xuất hàng</label>
	<select name="shipping[pick_id]" class="form-control" required>
	    <?php foreach ($pickAddress as $key => $pick) {?>
			<option value="<?php echo $pick->id;?>" <?php echo ($pick->id == $orderGHTK['pickId']) ? 'selected=selected' :'';?>> #<?php echo $pick->ghtkId;?> -  <?php echo $pick->name;?> - <?php echo $pick->address;?>, <?php echo Cart_Location::cities($pick->city);?></option>
	    <?php } ?>
	</select>
	<p style="font-style: italic">Thay đổi chi nhanh có thể sẽ làm phí vận chuyển thay đổi</p>
</div>

<div class="row">
	<div class="form-group col-md-4">
		<label>Tổng khối lượng (kg)</label>
		<input name="shipping[weight]" type="text" value="<?php echo $orderGHTK['weight'];?>" min="0" class="form-control">
	</div>
	<div class="form-group col-md-4">
		<label>Hình thức vận chuyển</label>
		<select name="shipping[transport]" class="form-control" data-id="<?php echo $order->id;?>">
			<option value="road" <?php echo ($orderGHTK['transport'] == 'road') ? 'selected' : '';?>>Đường bộ</option>
			<option value="fly" <?php echo ($orderGHTK['transport'] == 'fly') ? 'selected' : '';?>>Đường bay</option>
		</select>
	</div>
	<div class="form-group col-md-4">
		<label>Hình thức gửi hàng</label>
		<select name="shipping[option]" class="form-control" data-id="<?php echo $order->id;?>">
			<option value="cod" <?php echo ($orderGHTK['option'] == 'cod') ? 'selected' : '';?>>Lấy tận nơi nhận hàng</option>
			<option value="post" <?php echo ($orderGHTK['option'] == 'post') ? 'selected' : '';?>>Gửi hàng bưu cục</option>
		</select>
	</div>
	<div class="form-group col-md-12">
		<label class="">
			<input name="shipping[freeship]" type="checkbox" value="1" class="icheck"> Shop trả phí
		</label>
	</div>
	<div class="form-group col-md-12">
		<label>Ghi chú</label>
		<textarea name="shipping[note]" class="form-control"></textarea>
	</div>
</div>