<hr />
<div class="row">
	<div class="col-md-3">
		<div class="ui-title-bar__group pt-0">
			<h3 class="ui-title-bar__title" style="font-size: 20px;">Kho hàng</h3>
			<p style="margin-top: 10px; margin-left: 1px; color: #8c8c8c">Cấu hình liên kết kho hàng website và kho hàng giao hàng tiết kiệm</p>
		</div>
	</div>
	<div class="col-md-9">
		<div class="box p-3">
			<?php if(have_posts($branches)) {?>
				<div class="shipping-table">
					<table class="display table table-striped media-table ">
						<thead>
						<tr>
							<th class="manage-column">Tên địa điểm</th>
							<th class="manage-column">Website</th>
							<th class="manage-column">Giao hàng tiết kiệm</th>
						</tr>
						</thead>
						<tbody>
							<?php foreach ($branches as $branch) { ?>
								<tr>
									<td class="manage-column"><?php echo $branch->name;?></td>
									<td class="manage-column"><?php echo $branch->address;?></td>
									<td class="manage-column">
										<select name="branchConnect[<?php echo $branch->id;?>]" class="form-control">
											<?php foreach ($picks as $pickKey => $pickName) { ?>
												<option value="<?php echo $pickKey;?>" <?php echo (!empty($branchConnect[$branch->id]) && $branchConnect[$branch->id] == $pickKey) ? 'selected' : '';?>><?php echo $pickName;?></option>
											<?php } ?>
										</select>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else {
				echo notice('error', 'Website chưa có kho hàng vui lòng tạo kho hàng cho website');
			} ?>
		</div>
	</div>
</div>

<script>
	$('.js_shipping_btn__save').remove();
	$('button[form="system_form"]').attr('type', 'button').removeAttr('form').addClass('js_shipping_btn__save');
</script>