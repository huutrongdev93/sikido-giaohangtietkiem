<?php
if ( !function_exists( 'ghtk_order_save_shipping_review' ) ) {

	function ghtk_order_save_shipping_review() {

        $ci =& get_instance();

		if( $ci->input->post() ) {

			$ghtk_config = giaohangtietkiem::confg();
			
			$shipping = $ci->input->post('show-form-shipping');
			
			if( !empty($shipping) && $shipping == 'on' ) {

				$citi 			= removeHtmlTags($ci->input->post('shipping_city'));

				$districts 		= removeHtmlTags($ci->input->post('shipping_districts'));
				
				$ward 		    = removeHtmlTags($ci->input->post('shipping_ward'));
			}
			else {

				$citi 			= removeHtmlTags($ci->input->post('billing_city'));

				$districts 		= removeHtmlTags($ci->input->post('billing_districts'));
				
				$ward 		    = removeHtmlTags($ci->input->post('billing_ward'));
			}
			
			if(empty($citi) || empty($districts)) return;

            $cart       = Scart::getItems();

            $weight = 0;

            foreach ($cart as $key => $item) {
                $weight += $item['weight']*$item['qty'];
            }

            $GHTK = new Ghtk();

            $shipping_price = $GHTK
                                ->setPick($ghtk_config['PickAddress'][0])
                                ->setWeight($weight)
                                ->setShipProvince($citi)
                                ->setShipDistrict($districts)
                                ->setShipWard($ward)
                                ->shipAmount();

			if( $shipping_price != false ) {

                $_SESSION['ghtk_ship_amount'] = $shipping_price;

				?>
				<tr class="ship">
					<td><?php echo $ghtk_config['title'];?></td>
					<td></td>
					<td><?= number_format($shipping_price)._price_currency(); ?></td>
				</tr>
				<?php
			}
			else {
				?>
				<tr class="ship">
					<td><?php echo $ghtk_config['title'];?></td>
					<td></td>
					<td><?php echo __('Liên hệ');?></td>
				</tr>
				<?php
			}
		}
    }
    
    add_action('order_add_total_review', 	'ghtk_order_save_shipping_review');
}

if ( ! function_exists( 'ghtk_order_save_total_shipping' ) ) {

	function ghtk_order_save_total_shipping( $total ) {

		$ci =& get_instance();
		
		$zone = array();

		if( $ci->input->post() && !empty($_SESSION['ghtk_ship_amount']) ) {

			$shipping_price = $_SESSION['ghtk_ship_amount'];

			unset($_SESSION['ghtk_ship_amount']);

			if( $shipping_price != false ) {

				$total = $total + $shipping_price;
			}
        }
        
        return $total;
	}

	add_action('order_add_total', 	'ghtk_order_save_total_shipping');
}

if ( ! function_exists( 'ghtk_order_save_customer_fields' ) ) {

	function ghtk_order_save_customer_fields( $fields, $customer, $order ) {

		$ci =& get_instance();

		if(isset($order) && have_posts($order)) {

			if(isset($order->billing_city)) {

				$fields['billing']['billing_city']['value'] = $order->billing_city;

				$fields['billing']['billing_districts']['options'] = Cart_Location::districts($order->billing_city);
				
				$fields['billing']['billing_districts']['value'] = $order->billing_districts;

				$fields['billing']['billing_ward']['options'] 	= Cart_Location::ward($order->billing_districts);

				if(isset($order->billing_ward)) {

					$fields['billing']['billing_ward']['value'] 	= $order->billing_ward;
				}
			}
		}
	
        
        return $fields;
	}

	add_action('admin_order_add_customer_fields', 	'ghtk_order_save_customer_fields', 10, 3);
}

if ( ! function_exists( 'ghtk_order_save_shipping_save' ) ) {

	add_action( 'admin_order_add_after_save', 'ghtk_order_save_shipping_save', 10, 1 );
	/**
	 * Update the order meta with field value
	 */
	function ghtk_order_save_shipping_save( $id ) {

		$ci =& get_instance();

        $order = Order::get( $id, false, true );
        
        if(!empty($_SESSION['ghtk_ship_amount'])) {
            $ship = $_SESSION['ghtk_ship_amount'];
        }
        else{
            $weight = 0;

            foreach ($order->items as $key => $val):
                $weight += (int)Order::getItemMeta($val->id, 'weight', true)/1000*$val->quantity;
            endforeach;

            $ghtk_config = ghtk_config();
            
            $GHTK = new Ghtk();

            $ship = $GHTK
                        ->setPick($ghtk_config['PickAddress'][0])
                        ->setWeight($weight)
                        ->setShipProvince($order->billing_city)
                        ->setShipDistrict($order->billing_districts)
                        ->setShipWard($order->billing_ward)
                        ->shipAmount();
        }

		Order::updateMeta($id, '_shipping_price', $ship );

		$model = get_model('plugins', 'backend');

		$model->settable('order');

		$model->update_where(array('total' => $order->total+$ship), array('id'=> $id));
	}
}