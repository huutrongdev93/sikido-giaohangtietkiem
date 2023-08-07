<tr class="shipping-price">
    <td><?php echo __('Vận chuyển','discount_promotion');?></td>
    <td><?php echo $shippingPrice;?></td>
</tr>

<tr>
	<td colspan="2">
		<div class="ship-transport">
	        <?php foreach ($transports as $key => $transport) { ?>
			<div class="transport-item">
				<label style="padding:0;">
					<input type="radio" value="<?php echo $key;?>" <?php echo ($transportKey == $key) ? 'checked' : '';?> name="shipping_ghtk_transport"> <?php echo $transport['name'];?>
				</label>
				<strong><?php echo number_format($transport['fee'])._price_currency();?></strong>
			</div>
	        <?php } ?>
		</div>
	</td>
</tr>

<style>
	.ship-transport {
		display: flex; gap:10px;
	}
	.transport-item {
		border:1px solid #ccc;
		border-radius: 5px;
		padding:10px;
		cursor: pointer;
	}
</style>
