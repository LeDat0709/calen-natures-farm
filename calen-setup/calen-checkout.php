<?php
/**
 * Shorter path to an order: a "Mua ngay" button that goes straight to checkout,
 * and a classic checkout that only asks for phone, name, province and street address.
 * Vietnam has two administrative levels since 01/07/2025 (34 provinces/cities, then wards/communes), so there is no district field.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/calen-phone.php';

const CALEN_CHECKOUT_FLAG = 'calen_checkout_v1';

/** WooCommerce only checks that a phone looks like digits; a wrong number means an undeliverable COD order. */
add_action(
	'woocommerce_after_checkout_validation',
	function ( $data, $errors ) {
		$phone = (string) ( $data['billing_phone'] ?? '' );
		if ( '' !== $phone && ! calen_is_vn_phone( $phone ) ) {
			$errors->add( 'validation', 'Số điện thoại chưa đúng. Vui lòng nhập số Việt Nam 10 chữ số, ví dụ 0988 836 910.' );
		}
	},
	10,
	2
);

/** The 34 provinces and cities after the 2025 merger. Keys are stored on orders. */
const CALEN_VN_PROVINCES = array(
	'HN'  => 'Hà Nội',
	'HCM' => 'TP. Hồ Chí Minh',
	'HP'  => 'Hải Phòng',
	'DN'  => 'Đà Nẵng',
	'CT'  => 'Cần Thơ',
	'HUE' => 'Huế',
	'AG'  => 'An Giang',
	'BN'  => 'Bắc Ninh',
	'CM'  => 'Cà Mau',
	'CB'  => 'Cao Bằng',
	'DL'  => 'Đắk Lắk',
	'DB'  => 'Điện Biên',
	'DNA' => 'Đồng Nai',
	'DT'  => 'Đồng Tháp',
	'GL'  => 'Gia Lai',
	'HT'  => 'Hà Tĩnh',
	'HY'  => 'Hưng Yên',
	'KH'  => 'Khánh Hòa',
	'LCH' => 'Lai Châu',
	'LD'  => 'Lâm Đồng',
	'LS'  => 'Lạng Sơn',
	'LC'  => 'Lào Cai',
	'NA'  => 'Nghệ An',
	'NB'  => 'Ninh Bình',
	'PT'  => 'Phú Thọ',
	'QNG' => 'Quảng Ngãi',
	'QN'  => 'Quảng Ninh',
	'QT'  => 'Quảng Trị',
	'SL'  => 'Sơn La',
	'TN'  => 'Tây Ninh',
	'TNG' => 'Thái Nguyên',
	'TH'  => 'Thanh Hóa',
	'TQ'  => 'Tuyên Quang',
	'VL'  => 'Vĩnh Long',
);

/** "Mua ngay": submits the same form to a URL flagged for an immediate redirect to checkout. */
add_action(
	'woocommerce_after_add_to_cart_button',
	function () {
		global $product;
		if ( ! $product || ! $product->is_type( 'simple' ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return;
		}
		printf(
			'<button type="submit" name="add-to-cart" value="%d" formaction="%s" class="button alt cl-buynow">Mua ngay</button>',
			(int) $product->get_id(),
			esc_url( add_query_arg( 'calen_buy_now', '1', $product->get_permalink() ) )
		);
	}
);

/** Hotline under the buy buttons, for customers who would rather order by phone. */
add_action(
	'woocommerce_after_add_to_cart_button',
	function () {
		printf(
			'<p class="cl-callorder">Hoặc gọi đặt hàng: <a href="tel:%s">%s</a></p>',
			esc_attr( CALEN_PHONE ),
			esc_html( CALEN_PHONE_SHOW )
		);
	},
	20
);

/** Help line at the top of checkout; the floating call/Zalo buttons are hidden there so they do not cover the form. */
add_action(
	'woocommerce_before_checkout_form',
	function () {
		printf(
			'<div class="cl-help">Cần hỗ trợ đặt hàng? <a href="tel:%1$s">Gọi %2$s</a><span aria-hidden="true">·</span><a href="https://zalo.me/%1$s" target="_blank" rel="noopener">Nhắn Zalo</a></div>',
			esc_attr( CALEN_PHONE ),
			esc_html( CALEN_PHONE_SHOW )
		);
	},
	5
);

add_filter(
	'woocommerce_add_to_cart_redirect',
	function ( $url ) {
		return isset( $_GET['calen_buy_now'] ) ? wc_get_checkout_url() : $url; // phpcs:ignore WordPress.Security.NonceVerification -- only picks the redirect target
	}
);

/** After "Mua ngay" the customer is already at checkout, so the "added to cart" notice is noise. */
add_filter(
	'wc_add_to_cart_message_html',
	function ( $message ) {
		return isset( $_GET['calen_buy_now'] ) ? '' : $message; // phpcs:ignore WordPress.Security.NonceVerification
	}
);

// The shop runs no discount codes; the coupon prompt only adds a step to read past.
add_action(
	'wp',
	function () {
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	}
);

/** The default privacy line under the order button is English. */
add_filter(
	'woocommerce_get_privacy_policy_text',
	function ( $text, $type ) {
		return 'checkout' === $type ? 'Thông tin của bạn chỉ dùng để giao hàng và liên hệ về đơn hàng. Xem [privacy_policy].' : $text;
	},
	10,
	2
);

add_filter(
	'woocommerce_states',
	function ( $states ) {
		$states['VN'] = CALEN_VN_PROVINCES;
		return $states;
	}
);

/** Address fields for Vietnam; the checkout script re-orders and re-labels address fields from this locale. */
add_filter(
	'woocommerce_get_country_locale',
	function ( $locale ) {
		$locale['VN'] = array(
			'country'    => array( 'priority' => 1 ),
			'phone'      => array( 'label' => 'Số điện thoại', 'required' => true, 'priority' => 10 ), // WooCommerce treats phone as an address field, so its order comes from here
			'first_name' => array( 'label' => 'Họ và tên', 'priority' => 20 ),
			'last_name'  => array( 'required' => false, 'hidden' => true ),
			'company'    => array( 'required' => false, 'hidden' => true ),
			'state'      => array( 'label' => 'Tỉnh/Thành phố', 'required' => true, 'priority' => 30 ),
			'address_1'  => array( 'label' => 'Địa chỉ nhận hàng', 'placeholder' => 'Số nhà, tên đường, phường/xã', 'priority' => 40 ),
			'address_2'  => array( 'required' => false, 'hidden' => true ),
			'city'       => array( 'required' => false, 'hidden' => true ),
			'postcode'   => array( 'required' => false, 'hidden' => true ),
		);
		return $locale;
	}
);

/** Classic checkout: phone first, one name field, optional email, nothing else. */
add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		foreach ( array( 'billing_last_name', 'billing_company', 'billing_address_2', 'billing_city', 'billing_postcode' ) as $key ) {
			unset( $fields['billing'][ $key ] );
		}
		$fields['billing']['billing_phone']['priority']      = 10;
		$fields['billing']['billing_phone']['label']         = 'Số điện thoại';
		$fields['billing']['billing_first_name']['label']    = 'Họ và tên';
		$fields['billing']['billing_first_name']['priority'] = 20;
		$fields['billing']['billing_first_name']['class']    = array( 'form-row-wide' );
		$fields['billing']['billing_email']['required']      = false;
		$fields['billing']['billing_email']['label']         = 'Email'; // WooCommerce appends "(tuỳ chọn)" to optional fields
		$fields['billing']['billing_email']['priority']      = 90;
		$fields['billing']['billing_country']['class'][]     = 'cl-hidden';
		if ( isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['label']       = 'Ghi chú';
			$fields['order']['order_comments']['placeholder'] = 'Ví dụ: gọi trước khi giao, giao giờ hành chính';
		}
		return $fields;
	}
);

add_action(
	'init',
	function () {
		if ( get_option( CALEN_CHECKOUT_FLAG ) ) {
			return;
		}
		update_option( CALEN_CHECKOUT_FLAG, gmdate( 'c' ) );
		calen_setup_checkout();
	},
	50
);

function calen_setup_checkout() {
	$checkout_id = wc_get_page_id( 'checkout' );
	if ( $checkout_id > 0 ) {
		wp_update_post( array( 'ID' => $checkout_id, 'post_content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->' ) );
	}
	foreach ( array( $checkout_id, wc_get_page_id( 'cart' ) ) as $page_id ) {
		if ( $page_id > 0 ) {
			update_post_meta( $page_id, '_kad_post_title', 'hide' ); // the big page-title band pushes the form below the fold
		}
	}
	update_option( 'woocommerce_ship_to_destination', 'billing_only' ); // ship to the address above, no second address form
	update_option( 'woocommerce_gateway_order', array( 'cod' => 0, 'bacs' => 1 ) ); // COD listed and selected first

	$bacs = get_option( 'woocommerce_bacs_settings', array() );
	if ( 'yes' === ( $bacs['enabled'] ?? '' ) && empty( get_option( 'woocommerce_bacs_accounts' ) ) ) {
		$bacs['enabled'] = 'no'; // bank transfer with no account number listed leaves the customer stuck
		update_option( 'woocommerce_bacs_settings', $bacs );
		error_log( 'Calen checkout: bank transfer disabled because no bank account is configured.' );
	}
}
