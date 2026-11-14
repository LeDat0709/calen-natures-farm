<?php
/**
 * Plugin Name: Calen Setup
 * Description: Calen Nature's Farm store setup (Vietnamese, VND, COD, shipping, Kadence colors) and floating Zalo/call buttons. Keep active for the buttons.
 * Version: 1.4.0
 */

defined( 'ABSPATH' ) || exit;

// Legal details printed in the footer (required for Vietnamese e-commerce sites). Empty fields are not shown.
const CALEN_LEGAL = array(
	'owner'   => '', // e.g. "Hộ kinh doanh Calen Nature's Farm" or the company name
	'license' => '', // e.g. "GCN ĐKKD số 0123456789 do UBND huyện ... cấp ngày 01/01/2020"
	'address' => '', // registered business address
	'email'   => '',
);
// Link from online.gov.vn after notifying Bộ Công Thương. The badge stays hidden until this is set.
const CALEN_BCT_URL = '';

const CALEN_SETUP_FLAG = 'calen_setup_done_v7';

if ( class_exists( 'WooCommerce' ) || in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins' ), true ) ) {
	require_once __DIR__ . '/calen-shop.php';
	require_once __DIR__ . '/calen-import.php';
	require_once __DIR__ . '/calen-checkout.php';
	require_once __DIR__ . '/calen-seo.php';
}

// HTML was going out with max-age=604800, so browsers kept prices and cart counts for a week.
// Browsers now re-check every page; the CDN may still keep a copy for 10 minutes (cleared on every deploy).
add_action(
	'send_headers',
	function () {
		if ( ! is_admin() ) {
			header( 'Cache-Control: public, max-age=0, s-maxage=600, must-revalidate' );
		}
	}
);

// Keep the admin username private: no author pages, no ?author=N redirect, no public user list, no users sitemap.
add_action(
	'template_redirect',
	function () {
		if ( is_author() || isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
);
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

// Kadence strings that its Vietnamese translation leaves in English (breadcrumb, 404 page).
add_filter(
	'gettext_kadence',
	function ( $translation, $text ) {
		$vi = array(
			'Home'                                                                 => 'Trang chủ',
			'Oops! That page can&rsquo;t be found.'                                => 'Không tìm thấy trang này',
			'It looks like nothing was found at this location. Maybe try a search?' => 'Trang bạn tìm không tồn tại hoặc đã được chuyển. Thử tìm loại trà bạn cần bên dưới nhé.',
		);
		return $vi[ $text ] ?? $translation;
	},
	10,
	2
);

// Fonts + site styles from the Claude Design canvas.
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
			wp_enqueue_script( 'wc-cart-fragments' ); // pages come from the CDN cache, so the header cart count must refresh in the browser
		}
		wp_enqueue_style( 'calen-fonts', 'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap', array(), null );
		wp_enqueue_style( 'calen-site', plugin_dir_url( __FILE__ ) . 'calen-site.css', array(), '2.7.0' ); // priority 20: prints after Kadence's styles
	},
	20
);

// Add Cart SVG icon and dynamic badge counter to the cart menu item
add_filter(
	'nav_menu_item_title',
	function ( $title, $item, $args, $depth ) {
		$is_cart = false;
		if ( function_exists( 'wc_get_page_id' ) && (int) $item->object_id === (int) wc_get_page_id( 'cart' ) ) {
			$is_cart = true;
		} elseif ( ! empty( $item->url ) && false !== stripos( $item->url, '/cart' ) ) {
			$is_cart = true;
		} elseif ( 'giỏ hàng' === mb_strtolower( trim( wp_strip_all_tags( $title ) ), 'UTF-8' ) ) {
			$is_cart = true;
		}

		if ( $is_cart ) {
			$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
			$svg = '<svg class="cl-menu-cart-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 7h14l-1.2 11.2a2 2 0 0 1-2 1.8H8.2a2 2 0 0 1-2-1.8L5 7Z"></path><path d="M9 7a3 3 0 0 1 6 0"></path></svg>';
			$badge = '<span class="cl-cart-count' . ( $count > 0 ? ' has-items' : '' ) . '">' . esc_html( $count ) . '</span>';
			return '<span class="cl-cart-menu-wrap">' . $svg . '<span class="cl-cart-text">' . esc_html( $title ) . '</span>' . $badge . '</span>';
		}

		return $title;
	},
	10,
	4
);

// Dynamic update of cart count badge on AJAX add to cart
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
		$fragments['.cl-cart-count'] = '<span class="cl-cart-count' . ( $count > 0 ? ' has-items' : '' ) . '">' . esc_html( $count ) . '</span>';
		return $fragments;
	}
);

// Announcement bar above the header on every page.
add_action(
	'wp_body_open',
	function () {
		echo '<div class="cl-topbar"><div class="cl-wrap"><span>Giao hàng toàn quốc</span><span>Miễn phí giao hàng mọi đơn</span><span>Nhận hàng rồi trả tiền (COD)</span>'
			. '<span>Hotline <a href="tel:' . esc_attr( CALEN_PHONE ) . '">09888.36910</a></span></div></div>';
	}
);
const CALEN_PHONE      = '0988836910';
const CALEN_PHONE_SHOW = '09888.36910'; // how the hotline is written on the site
const CALEN_PALETTE    = array( '#3f5d32', '#2f4325', '#1f2a1b', '#2f3a2a', '#5a6352', '#8a917f', '#e7e3d4', '#f6f2e7', '#fffdf8' );

// Floating Zalo + call buttons on every page (mobile-first, most traffic comes from Facebook).
add_action(
	'wp_footer',
	function () {
		if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() ) ) {
			return; // on small screens they cover the form fields
		}
		$phone = esc_attr( CALEN_PHONE );
		echo '<style>.calen-fab{position:fixed;right:16px;bottom:16px;z-index:9999;display:flex;flex-direction:column;gap:10px}'
			. '.calen-fab a{display:flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:50%;'
			. 'color:#fff;font:600 13px/1 sans-serif;text-decoration:none;box-shadow:0 4px 12px rgba(0,0,0,.2)}'
			. '.calen-fab .zalo{background:#0068ff}.calen-fab .call{background:#3f5d32}</style>'
			. '<div class="calen-fab"><a class="zalo" href="https://zalo.me/' . $phone . '" target="_blank" rel="noopener" aria-label="Nhắn Zalo cho Calen">Zalo</a>'
			. '<a class="call" href="tel:' . $phone . '" aria-label="Gọi hotline Calen">Gọi</a></div>';
	}
);

// Runs once on the first request after deploy; activation hooks are skipped by silent activations.
add_action(
	'init',
	function () {
		if ( get_option( CALEN_SETUP_FLAG ) ) {
			return;
		}
		update_option( CALEN_SETUP_FLAG, gmdate( 'c' ) );
		calen_setup_run();
	},
	20
);

function calen_setup_run() {
	calen_setup_language();
	update_option( 'timezone_string', 'Asia/Ho_Chi_Minh' );
	calen_setup_front_page();
	calen_setup_palette();
	calen_setup_header_footer();

	if ( ! class_exists( 'WooCommerce' ) ) {
		error_log( 'Calen Setup: WooCommerce not active, store settings skipped.' );
		return;
	}
	calen_setup_store();
	calen_setup_cod();
	calen_setup_shipping();
	calen_setup_menu();
}

function calen_setup_language() {
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$installed = wp_download_language_pack( 'vi' );
	if ( ! $installed ) {
		error_log( 'Calen Setup: could not download the Vietnamese language pack.' );
		return;
	}
	update_option( 'WPLANG', $installed );
}

function calen_setup_front_page() {
	$home = get_page_by_path( 'trang-chu' );
	if ( ! $home ) {
		error_log( 'Calen Setup: page trang-chu not found, front page unchanged.' );
		return;
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home->ID );
	// Kadence per-page layout: full width, no title bar, no extra padding.
	update_post_meta( $home->ID, '_kad_post_title', 'hide' );
	update_post_meta( $home->ID, '_kad_post_layout', 'fullwidth' );
	update_post_meta( $home->ID, '_kad_post_content_style', 'unboxed' );
	update_post_meta( $home->ID, '_kad_post_vertical_padding', 'hide' );
}

function calen_setup_palette() {
	$colors = array();
	foreach ( CALEN_PALETTE as $i => $hex ) {
		$n        = $i + 1;
		$colors[] = array( 'color' => $hex, 'slug' => "palette{$n}", 'name' => "Palette Color {$n}" );
	}
	update_option(
		'kadence_global_palette',
		wp_json_encode( array( 'palette' => $colors, 'second-palette' => $colors, 'third-palette' => $colors, 'active' => 'palette' ) )
	);
}

function calen_setup_header_footer() {
	$everywhere = array( 'desktop' => 'logo_title', 'tablet' => 'logo_title', 'mobile' => 'logo_title' );
	set_theme_mod(
		'logo_layout',
		array( 'include' => $everywhere, 'layout' => array( 'desktop' => 'standard', 'tablet' => 'standard', 'mobile' => 'standard' ) )
	);
	set_theme_mod( 'footer_html_content', '{copyright} {year} Calen Nature\'s Farm · Hotline 09888.36910' );
}

// Brand logo (assets/calen-logo.webp, from the owner) next to the site name in the header; added once to the media library.
add_action(
	'init',
	function () {
		if ( get_option( 'calen_logo_v1' ) ) {
			return;
		}
		update_option( 'calen_logo_v1', gmdate( 'c' ) );
		calen_setup_logo();
	},
	70
);

// The logo shows at 56px, so let the browser pick the 100/150px file instead of the 1418px original.
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr ) {
		if ( isset( $attr['class'] ) && false !== strpos( $attr['class'], 'custom-logo' ) ) {
			$attr['sizes'] = '56px'; // Kadence builds the logo with wp_get_attachment_image, not get_custom_logo
		}
		return $attr;
	}
);

function calen_setup_logo() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( 'calen-logo.webp' );
	if ( ! copy( __DIR__ . '/assets/calen-logo.webp', $tmp ) ) {
		error_log( 'Calen Setup: logo file could not be copied.' );
		return;
	}
	$id = media_handle_sideload( array( 'name' => 'calen-logo.webp', 'tmp_name' => $tmp ), 0, "Logo Calen Nature's Farm" );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		error_log( 'Calen Setup: logo not added: ' . $id->get_error_message() );
		return;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', "Logo Calen Nature's Farm" );
	set_theme_mod( 'custom_logo', $id ); // Kadence shows logo + site title (logo_layout "logo_title")
}

function calen_setup_menu() {
	$menu = wp_get_nav_menu_object( 'Menu chính' );
	$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Menu chính' );
	if ( is_wp_error( $menu_id ) ) {
		error_log( 'Calen Setup: menu not created: ' . $menu_id->get_error_message() );
		return;
	}
	if ( ! wp_get_nav_menu_items( $menu_id ) ) {
		$items = array(
			array( 'Trang chủ', 'post_type', 'page', get_option( 'page_on_front' ) ),
			array( 'Cửa hàng', 'post_type', 'page', wc_get_page_id( 'shop' ) ),
			array( 'Ngủ ngon', 'taxonomy', 'product_cat', get_term_by( 'slug', 'ngu-ngon', 'product_cat' )->term_id ?? 0 ),
			array( 'Quà biếu', 'taxonomy', 'product_cat', get_term_by( 'slug', 'qua-bieu', 'product_cat' )->term_id ?? 0 ),
			array( 'Giỏ hàng', 'post_type', 'page', wc_get_page_id( 'cart' ) ),
		);
		foreach ( $items as $position => list( $title, $type, $object, $object_id ) ) {
			if ( ! $object_id ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $title,
					'menu-item-type'      => $type,
					'menu-item-object'    => $object,
					'menu-item-object-id' => (int) $object_id,
					'menu-item-position'  => $position + 1,
					'menu-item-status'    => 'publish',
				)
			);
		}
	}
	set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id, 'mobile' => $menu_id ) );
}

function calen_setup_store() {
	$options = array(
		'woocommerce_default_country'            => 'VN',
		'woocommerce_allowed_countries'          => 'specific',
		'woocommerce_specific_allowed_countries' => array( 'VN' ),
		'woocommerce_ship_to_countries'          => '',
		'woocommerce_currency'                   => 'VND',
		'woocommerce_currency_pos'               => 'right_space',
		'woocommerce_price_thousand_sep'         => '.',
		'woocommerce_price_decimal_sep'          => ',',
		'woocommerce_price_num_decimals'         => 0,
		'woocommerce_calc_taxes'                 => 'no',
		'woocommerce_checkout_phone_field'       => 'required',
		'woocommerce_enable_guest_checkout'      => 'yes',
		'woocommerce_coming_soon'                => 'no',
	);
	foreach ( $options as $name => $value ) {
		update_option( $name, $value );
	}
}

function calen_setup_cod() {
	update_option(
		'woocommerce_cod_settings',
		array(
			'enabled'            => 'yes',
			'title'              => 'Thanh toán khi nhận hàng (COD)',
			'description'        => 'Bạn trả tiền mặt cho shipper khi nhận hàng.',
			'instructions'       => 'Calen sẽ gọi xác nhận đơn trong ngày. Bạn trả tiền khi nhận hàng.',
			'enable_for_methods' => array(),
			'enable_for_virtual' => 'yes',
		)
	);
}

/** Every order ships free: removes paid flat rates and keeps one free-shipping method with no minimum. */
function calen_setup_shipping() {
	$vn_zone = null;
	foreach ( WC_Shipping_Zones::get_zones() as $zone_data ) {
		if ( 'Việt Nam' === $zone_data['zone_name'] ) {
			$vn_zone = new WC_Shipping_Zone( $zone_data['zone_id'] );
			break;
		}
	}
	if ( ! $vn_zone ) {
		$vn_zone = new WC_Shipping_Zone();
		$vn_zone->set_zone_name( 'Việt Nam' );
		$vn_zone->add_location( 'VN', 'country' );
		$vn_zone->save();
	}

	$free_id = 0;
	foreach ( $vn_zone->get_shipping_methods() as $instance_id => $method ) {
		if ( 'flat_rate' === $method->id ) {
			$vn_zone->delete_shipping_method( $instance_id );
		} elseif ( 'free_shipping' === $method->id && ! $free_id ) {
			$free_id = $instance_id;
		}
	}
	if ( ! $free_id ) {
		$free_id = $vn_zone->add_shipping_method( 'free_shipping' );
	}
	update_option(
		"woocommerce_free_shipping_{$free_id}_settings",
		array(
			'title'            => 'Miễn phí giao hàng',
			'requires'         => '',
			'min_amount'       => '0',
			'ignore_discounts' => 'no',
		)
	);
	WC_Cache_Helper::get_transient_version( 'shipping', true );
}

// 4-column branded footer from Claude Design
add_action(
	'wp_footer',
	function () {
		?>
		<footer class="cl-footer">
			<div class="cl-wrap cl-footer__grid">
				<div class="cl-footer__col cl-footer__col--brand">
					<span class="cl-footer__logo">CALEN</span>
					<p class="cl-footer__tagline">Trà thảo mộc sạch từ nông trại.<br>Thương hiệu uy tín 10 năm.</p>
					<?php if ( CALEN_BCT_URL ) : ?>
					<a class="cl-footer__bct" href="<?php echo esc_url( CALEN_BCT_URL ); ?>" target="_blank" rel="noopener">
						<span>ĐÃ THÔNG BÁO</span>
						<strong>BỘ CÔNG THƯƠNG</strong>
					</a>
					<?php endif; ?>
				</div>
				<div class="cl-footer__col">
					<h4>Sản phẩm</h4>
					<ul class="cl-footer__links">
						<li><a href="/shop/">Tất cả sản phẩm</a></li>
						<li><a href="/product-category/phu-nu/">Dành cho phụ nữ</a></li>
						<li><a href="/product-category/can-nang-detox/">Cân nặng & detox</a></li>
						<li><a href="/product-category/tieu-hoa/">Dạ dày & tiêu hoá</a></li>
						<li><a href="/product-category/xuong-khop/">Xương khớp</a></li>
						<li><a href="/product-category/gan-than/">Gan & thận</a></li>
						<li><a href="https://shopee.vn/calennaturefarm" target="_blank" rel="noopener">Gian hàng Shopee Mall</a></li>
					</ul>
				</div>
				<div class="cl-footer__col">
					<h4>Chính sách & Hỗ trợ</h4>
					<ul class="cl-footer__links">
						<li><a href="#chinh-sach">Đổi trả trong 7 ngày nếu lỗi sấy</a></li>
						<li><a href="#chinh-sach">Đồng kiểm tra hàng khi nhận (COD)</a></li>
						<li><a href="#chinh-sach">Giao hàng toàn quốc (2–4 ngày)</a></li>
						<li><a href="#chinh-sach">Miễn phí giao hàng mọi đơn</a></li>
						<li><a href="#chinh-sach">Bảo mật thông tin khách hàng</a></li>
					</ul>
				</div>
				<div class="cl-footer__col">
					<h4>Liên hệ</h4>
					<p class="cl-footer__info">
						<strong>Hotline / Zalo:</strong> <a href="tel:0988836910">09888.36910</a><br>
						<strong>Nông trại & Đóng gói:</strong> Xã Lạc Dương, Tỉnh Lâm Đồng<br>
						<strong>Giờ làm việc:</strong> 8:00 – 18:00 (T2 – CN)
					</p>
					<div class="cl-footer__social">
						<a href="https://www.facebook.com/Calenmask" target="_blank" rel="noopener">Facebook Calen</a>
						<span>·</span>
						<a href="https://zalo.me/0988836910" target="_blank" rel="noopener">Zalo tư vấn</a>
					</div>
				</div>
			</div>
			<?php
			$legal = array_filter( CALEN_LEGAL );
			if ( $legal ) :
				?>
			<div class="cl-wrap cl-footer__legal">
				<?php foreach ( $legal as $key => $value ) : ?>
					<span><?php echo 'email' === $key ? 'Email: ' : ''; ?><?php echo esc_html( $value ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<div class="cl-footer__bot">
				<div class="cl-wrap cl-footer__bot-wrap">
					<span>© 2026 Calen Nature's Farm · Bản quyền thuộc về Calen</span>
					<span>Sản phẩm là thực phẩm, không phải là thuốc</span>
				</div>
			</div>
		</footer>
		<?php
	},
	5
);
