<?php
/*
Plugin Name: Discount BY Qty for WooCommerce
Plugin URI: http://mintcreative.github.io/discount-qty/
Description: Add Vocuher Codes to the basket based on the total number of products in the basket
Author: David Wellock
Author URI: http://mintcreative.github.io
Version: 1.0
Usage: First create a voucher coupon for each type of discount you want to apply, e.g. 10% off for 10 products. 
       Then enter the coupon code into the settings page and select the quantity
       The plugin will automatically remove any discount by qty coupons that have been entered but do not match the criteria
Thanks: settings code by http://wpsettingsapi.jeroensormani.com/    
*/

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // only run if we have WooCommerce installed

		add_action( 'woocommerce_before_cart', 'apply_matched_coupons' );
		 
		function apply_matched_coupons() {
		    global $woocommerce;

		    // get the values stored in the settings page
		    $options = get_option( 'discount_qty_settings' );

		    // determine which is the smaller qty - to be used in the IF statement later, brah
		    if ($options['discount_qty_text_field_1'] < $options['discount_qty_text_field_3']) {
	    		$lowQty = $options['discount_qty_text_field_1'];
	    		$lowCode = $options['discount_qty_text_field_0'];

	    		$highQty = $options['discount_qty_text_field_3'];
	    		$highCode = $options['discount_qty_text_field_2'];
		    } else {
				$lowQty = $options['discount_qty_text_field_3'];
	    		$lowCode = $options['discount_qty_text_field_2'];

	    		$highQty = $options['discount_qty_text_field_0'];
	    		$highCode = $options['discount_qty_text_field_1'];
		    }

		    $totalQty = $woocommerce->cart->cart_contents_count;

				$woocommerce->cart->remove_coupon( $lowCode );
				$woocommerce->cart->remove_coupon( $highCode );

			if ($totalQty >= $lowQty && $totalQty < $highQty ) {
			    $woocommerce->cart->add_discount( $lowCode );
			} elseif ($totalQty >= $highQty) {
				$woocommerce->cart->add_discount( $highCode );
			}
		}

		add_action( 'admin_menu', 'discount_qty_add_admin_menu' );
		add_action( 'admin_init', 'discount_qty_settings_init' );


			$args = array(
			    'posts_per_page'   => -1,
			    'orderby'          => 'title',
			    'order'            => 'asc',
			    'post_type'        => 'shop_coupon',
			    'post_status'      => 'publish',
			);
		    
			$coupons = get_posts( $args );


		function discount_qty_add_admin_menu(  ) { 

			add_submenu_page( 'options-general.php', 'Discount Qty', 'Discount Qty', 'manage_options', 'discount_qty', 'discount_qty_options_page' );

		}

		function discount_qty_settings_init(  ) { 

			register_setting( 'pluginPage', 'discount_qty_settings' );

			add_settings_section(
				'discount_qty_pluginPage_section', 
				__( 'Apply a discount based on the total number of items in the basket', 'discount-qty' ), 
				'discount_qty_settings_section_callback', 
				'pluginPage'
			);

			add_settings_field( 
				'discount_qty_text_field_0', 
				__( 'Voucher code', 'wordpress' ), 
				'discount_qty_text_field_0_render', 
				'pluginPage', 
				'discount_qty_pluginPage_section' 
			);

			add_settings_field( 
				'discount_qty_text_field_1', 
				__( 'Basket Total Quantity', 'wordpress' ), 
				'discount_qty_text_field_1_render', 
				'pluginPage', 
				'discount_qty_pluginPage_section' 
			);

			add_settings_field( 
				'discount_qty_text_field_2', 
				__( 'Voucher code', 'wordpress' ), 
				'discount_qty_text_field_2_render', 
				'pluginPage', 
				'discount_qty_pluginPage_section' 
			);

			add_settings_field( 
				'discount_qty_text_field_3', 
				__( 'Basket Total Quantity', 'wordpress' ), 
				'discount_qty_text_field_3_render', 
				'pluginPage', 
				'discount_qty_pluginPage_section' 
			);	
		}


		function discount_qty_text_field_0_render(  ) { 
			global $coupons;

			if (count($coupons) < 1) {
				echo "<h3>You must create a voucher code to apply the discount</h3>";
			} else {

				$options = get_option( 'discount_qty_settings' );
				$codeUsed = $options['discount_qty_text_field_0'];

				echo "<select name='discount_qty_settings[discount_qty_text_field_0]'>";

				foreach ( $coupons as $coupon ) {
				    // Get the name for each coupon post
				    $coupon_name = $coupon->post_title;
				    $checked = "";
				    if ($codeUsed == $coupon_name) {
				    	$checked = "selected='selected'";	
				    }
				    echo "<option $checked value='$coupon_name'>$coupon_name</option>";
				}

				echo "</select>";
			}
		}


		function discount_qty_text_field_1_render(  ) { 
			global $coupons;

			if (count($coupons) < 1) {
				echo "<h3>You must create a voucher code to apply the discount</h3>";
			} else {
				$options = get_option( 'discount_qty_settings' );
				?>
				<input type='text' name='discount_qty_settings[discount_qty_text_field_1]' value='<?php echo $options['discount_qty_text_field_1']; ?>'>
				<?php
			}
		}

		/// start of coupon 2
		function discount_qty_text_field_2_render(  ) { 
			global $coupons;
			if (count($coupons) < 1) {
				echo "<h3>You must create a voucher code to apply the discount</h3>";
			} else {
				$options = get_option( 'discount_qty_settings' );
				$codeUsed2 = $options['discount_qty_text_field_2'];

				echo "<select name='discount_qty_settings[discount_qty_text_field_2]'>";

				foreach ( $coupons as $coupon ) {
				    // Get the name for each coupon post
				    $coupon_name = $coupon->post_title;
				    $checked = "";
				    if ($codeUsed2 == $coupon_name) {
				    	$checked = "selected='selected'";	
				    }
				    echo "<option $checked value='$coupon_name'>$coupon_name</option>";
				}

				echo "</select>";
			}
		}


		function discount_qty_text_field_3_render(  ) { 
			global $coupons;

			if (count($coupons) < 1) {
					echo "<h3>You must create a voucher code to apply the discount</h3>";
			} else {
				$options = get_option( 'discount_qty_settings' );
				?>
				<input type='text' name='discount_qty_settings[discount_qty_text_field_3]' value='<?php echo $options['discount_qty_text_field_3']; ?>'>
				<?php
			}
		}
		///

		function discount_qty_settings_section_callback(  ) { 

			echo __( 'Select the Voucher Coupon code to use and enter the total number of items in the basket', 'discount-qty' );

		}


		function discount_qty_options_page(  ) { 

			?>
			<form action='options.php' method='post'>
				
				<h2>Discount Based on Basket Total Quantity</h2>
				
				<?php
				settings_fields( 'pluginPage' );
				do_settings_sections( 'pluginPage' );
				submit_button();
				?>
				
			</form>
			<?php

		}
}
?>