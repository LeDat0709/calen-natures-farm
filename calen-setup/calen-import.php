<?php
/**
 * One-time import of the Shopee teas listed in data/shopee-teas.json.
 * Runs through WP-Cron a few products at a time (each image is downloaded from Shopee), so no request times out.
 * Safe to re-run: a product whose SKU "SP-<shopee id>" already exists is skipped.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/calen-pricing.php'; // tier prices, so imported products get the website price, not the raw Shopee price

const CALEN_IMPORT_DONE     = 'calen_shopee_import_done_v2'; // v2: re-run after v1 skipped one product when two runs overlapped
const CALEN_IMPORT_NEXT     = 'calen_shopee_import_next_v2';
const CALEN_IMPORT_LOCK     = 'calen_shopee_import_lock';
const CALEN_IMPORT_LOCK_TTL = 300; // seconds; a lock older than this is treated as left behind by a crashed run
const CALEN_IMPORT_HOOK     = 'calen_shopee_import_batch';
const CALEN_IMPORT_BATCH    = 3; // products per cron run, 4 images each

add_action(
	'init',
	function () {
		if ( get_option( CALEN_IMPORT_DONE ) || get_option( CALEN_IMPORT_LOCK ) || wp_next_scheduled( CALEN_IMPORT_HOOK ) ) {
			return;
		}
		wp_schedule_single_event( time(), CALEN_IMPORT_HOOK );
	},
	30
);

/** add_option fails when the row exists, so only one run can hold the lock at a time. */
function calen_import_lock(): bool {
	if ( add_option( CALEN_IMPORT_LOCK, time(), '', 'no' ) ) {
		return true;
	}
	if ( time() - (int) get_option( CALEN_IMPORT_LOCK ) > CALEN_IMPORT_LOCK_TTL ) {
		delete_option( CALEN_IMPORT_LOCK );
		return add_option( CALEN_IMPORT_LOCK, time(), '', 'no' );
	}
	return false;
}

add_action( CALEN_IMPORT_HOOK, 'calen_import_batch' );

const CALEN_STOREFRONT_FLAG = 'calen_storefront_v4'; // v4: free shipping on every order
const CALEN_SHOPEE_SHOP_URL = 'https://shopee.vn/calennaturefarm';

/** Once the new categories exist, point the homepage and menu at them (runs once). */
add_action(
	'init',
	function () {
		if ( get_option( CALEN_STOREFRONT_FLAG ) || ! get_term_by( 'slug', 'phu-nu', 'product_cat' ) ) {
			return;
		}
		update_option( CALEN_STOREFRONT_FLAG, gmdate( 'c' ) );
		calen_import_menu();
		calen_import_homepage();
		calen_setup_shipping();
	},
	40
);

/** Shop order = Shopee bestseller rank ("rank" in the data file), applied once; the owner can re-order in WP Admin afterwards. */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_sort_v1' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
			return;
		}
		$data = calen_import_data();
		if ( ! $data ) {
			return;
		}
		update_option( 'calen_sort_v1', gmdate( 'c' ) );
		foreach ( $data['products'] as $item ) {
			$id = wc_get_product_id_by_sku( 'SP-' . $item['shopee_id'] );
			if ( $id && isset( $item['rank'] ) ) {
				wp_update_post( array( 'ID' => $id, 'menu_order' => (int) $item['rank'] ) );
			}
		}
	},
	60
);

/** 199k–210k prices by Shopee price level (calen-pricing.php), applied once; later prices set in WP Admin are kept. */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_price_tiers_v1' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
			return;
		}
		$data = calen_import_data();
		if ( ! $data ) {
			return;
		}
		update_option( 'calen_price_tiers_v1', gmdate( 'c' ) );
		foreach ( $data['products'] as $item ) {
			$product = wc_get_product( wc_get_product_id_by_sku( 'SP-' . $item['shopee_id'] ) );
			if ( ! $product ) {
				continue;
			}
			$product->set_regular_price( (string) calen_price_for_shopee( (int) $item['price'] ) );
			$product->set_sale_price( '' );
			$product->save();
		}
	},
	62
);

/** Shopee rating and sales shown next to prices, copied once from the data file (figures from 14/11/2026). */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_shopee_proof_v1' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
			return;
		}
		$data = calen_import_data();
		if ( ! $data ) {
			return;
		}
		update_option( 'calen_shopee_proof_v1', gmdate( 'c' ) );
		foreach ( $data['products'] as $item ) {
			$id = wc_get_product_id_by_sku( 'SP-' . $item['shopee_id'] );
			if ( $id ) {
				update_post_meta( $id, 'calen_shopee_rating', $item['rating'] );
				update_post_meta( $id, 'calen_shopee_sold', $item['sold'] );
			}
		}
	},
	61
);

/** v2: also copies the review count, so the Product schema can show an aggregate rating with its source. */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_shopee_proof_v2' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
			return;
		}
		$data = calen_import_data();
		if ( ! $data ) {
			return;
		}
		update_option( 'calen_shopee_proof_v2', gmdate( 'c' ) );
		foreach ( $data['products'] as $item ) {
			$id = wc_get_product_id_by_sku( 'SP-' . $item['shopee_id'] );
			if ( $id ) {
				update_post_meta( $id, 'calen_shopee_rating', $item['rating'] );
				update_post_meta( $id, 'calen_shopee_sold', $item['sold'] );
				update_post_meta( $id, 'calen_shopee_reviews', $item['reviews'] );
			}
		}
	},
	61
);

/** Swaps the old "Ngủ ngon" and "Quà biếu" menu links for the two biggest real categories. */
function calen_import_menu() {
	$menu = wp_get_nav_menu_object( 'Menu chính' );
	if ( ! $menu ) {
		error_log( 'Calen storefront: menu "Menu chính" not found.' );
		return;
	}
	$swap = array( 'ngu-ngon' => 'phu-nu', 'qua-bieu' => 'can-nang-detox' );
	foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
		$old = 'product_cat' === $item->object ? get_term( (int) $item->object_id, 'product_cat' ) : null;
		if ( ! $old || is_wp_error( $old ) || ! isset( $swap[ $old->slug ] ) ) {
			continue;
		}
		$new = get_term_by( 'slug', $swap[ $old->slug ], 'product_cat' );
		wp_update_nav_menu_item(
			$menu->term_id,
			$item->ID,
			array(
				'menu-item-title'     => $new->name,
				'menu-item-type'      => 'taxonomy',
				'menu-item-object'    => 'product_cat',
				'menu-item-object-id' => $new->term_id,
				'menu-item-position'  => $item->menu_order,
				'menu-item-status'    => 'publish',
			)
		);
	}
}

/** Replaces sample-era homepage blocks with real categories and real Shopee figures. Each block is changed only if found exactly once. */
function calen_import_homepage() {
	calen_home_apply(
		array(
		'hero proof' => array( '#<div class="cl-hero__proof">.*?</div>#s', calen_home_proof() ),
		'needs'      => array( '#<div class="cl-needs">.*?</div>#s', calen_home_needs() ),
		'reviews'    => array( '#<section class="cl-section"[^>]*>\s*<div class="cl-wrap">\s*<div class="cl-head">\s*<div>\s*<h2 class="cl-h2">Khách hàng nói gì về Calen</h2>.*?</section>#s', calen_home_reviews() ),
		'products'   => array( '#<div class="woocommerce columns-4\s*">\s*<ul class="products.*?</ul>\s*</div>#s', '[products limit="4" columns="4" visibility="featured"]' ),
		'combo text' => array( '#Mua combo tiết kiệm thêm 7.{1,3}12%#u', 'Mua từ 2 hộp là được miễn phí giao hàng' ),
		'freeship'   => array( '#<strong>Freeship từ 300\.000[^<]*</strong><span>[^<]*</span>#u', '<strong>Miễn phí giao hàng</strong><span>Cho mọi đơn, giao toàn quốc</span>' ),
		'gift link'  => array( '#<a([^>]*)href="/product-category/qua-bieu/"([^>]*)>Xem hộp quà</a>#', '<a$1href="https://zalo.me/' . CALEN_PHONE . '" target="_blank" rel="noopener"$2>Nhắn Zalo đặt hộp quà</a>' ),
		)
	);
}

/** Homepage hero: three real Calen boxes (cropped from the Shopee photos, without the claim text) instead of the AI teapot photo. */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_hero_v1' ) ) {
			return;
		}
		update_option( 'calen_hero_v1', gmdate( 'c' ) );
		calen_home_apply( array( 'hero media' => array( '#<div class="cl-hero__media">.*?<div class="cl-float">.*?</div>\s*</div>#s', calen_home_hero_media() ) ) );
	},
	63
);

/** Hero v2: swap to the clean Calen-branded boxes (Teatox, UG-UXO, Tròn Khe) the owner supplied. */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_hero_v2' ) ) {
			return;
		}
		update_option( 'calen_hero_v2', gmdate( 'c' ) );
		calen_home_apply( array( 'hero media v2' => array( '#<div class="cl-hero__media cl-hero__boxes">.*?<div class="cl-float">.*?</div>\s*</div>#s', calen_home_hero_media() ) ) );
	},
	65
);

/** Hero v3: background removed, four Calen boxes (Teatox, UG-UXO, Tròn Khe, Tophi) as a product lineup. */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_hero_v3' ) ) {
			return;
		}
		update_option( 'calen_hero_v3', gmdate( 'c' ) );
		calen_home_apply( array( 'hero media v3' => array( '#<div class="cl-hero__media cl-hero__boxes">.*?<div class="cl-float">.*?</div>\s*</div>#s', calen_home_hero_media() ) ) );
	},
	66
);

/**
 * Brand story from the owner (14/11/2026) in the "Câu chuyện" section, and brand age 7 -> 10 years where the page
 * speaks about the brand. "7 năm" stays where it is about Shopee tenure (badge, stats), which is verifiable.
 * "5 sao" in the owner's text is written as the real Shopee figure, 4.9/5, to match the rest of the site.
 */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_story_v1' ) ) {
			return;
		}
		update_option( 'calen_story_v1', gmdate( 'c' ) );
		$story = 'Calen Nature\'s Farm là thương hiệu trà thảo mộc uy tín, được hàng trăm nghìn người tin dùng trong 10 năm qua, '
			. 'với điểm đánh giá 4.9/5 và tỷ lệ khách hàng quay lại rất cao. Mong muốn của Calen là đưa tới tay từng người những sản phẩm '
			. 'trà thảo mộc thiên nhiên thuần tuý, không chất bảo quản, an toàn cho sức khoẻ. Hãy cùng mua sắm với Calen nhé!';
		calen_home_apply(
			array(
				'story heading' => array( '#<h2 class="cl-h2">Bảy năm làm trà<br\s*/?>theo cách chậm rãi</h2>#u', '<h2 class="cl-h2">Mười năm làm trà<br>theo cách chậm rãi</h2>' ),
				'story body'    => array( '#<p style="margin:0">Hơn 7 năm qua.*?</p>#su', '<p style="margin:0">' . esc_html( $story ) . '</p>' ),
				'hero pill'     => array( '#<span class="cl-pill">Thương hiệu trà thảo mộc · 7 năm</span>#u', '<span class="cl-pill">Thương hiệu trà thảo mộc · 10 năm</span>' ),
			)
		);
	},
	67
);

/** Homepage: replace the fake-video "Xem Calen làm trà" grid with the real production journey, and drop the now-duplicate story steps. */
add_action(
	'init',
	function () {
		if ( get_option( 'calen_journey_v1' ) ) {
			return;
		}
		update_option( 'calen_journey_v1', gmdate( 'c' ) );
		calen_home_apply(
			array(
				'story steps' => array( '#<ol class="cl-steps">.*?</ol>#s', '<p style="margin:16px 0 0"><a class="cl-link" href="#quy-trinh">Xem quy trình làm trà của Calen →</a></p>' ),
				'reels'       => array( '#<section class="cl-section"[^>]*>\s*<div class="cl-wrap">\s*<div class="cl-head">\s*<div>\s*<h2 class="cl-h2">Xem Calen làm trà</h2>.*?</section>#s', calen_home_journey() ),
			)
		);
	},
	64
);

/** The real farm-to-teabag journey (owner's brief), as a 7-step timeline with icons — no stock/AI photos. */
function calen_home_journey(): string {
	$icon = array(
		'sun'     => '<circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path>',
		'leaf'    => '<path d="M12 21c0-7 3-11 8-13-1 7-3 11-8 13Zm0 0C12 15 9 11 4 9c0 6 3 10 8 12Z"></path>',
		'truck'   => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"></path><circle cx="7" cy="17.5" r="1.8"></circle><circle cx="17" cy="17.5" r="1.8"></circle>',
		'drop'    => '<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z"></path>',
		'dry'     => '<path d="M8 3c1 2-1 3 0 5M12 3c1 2-1 3 0 5M16 3c1 2-1 3 0 5"></path><path d="M5 12h14l-1 7a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2l-1-7Z"></path>',
		'grind'   => '<path d="M5 10h14M6 10a6 6 0 0 0 12 0M12 16v4M9 20h6"></path>',
		'package' => '<path d="M4 8l8-4 8 4-8 4-8-4Z"></path><path d="M4 8v8l8 4 8-4V8M12 12v8"></path>',
	);
	$steps = array(
		array( 'sun', 'Nông trại đón nắng sớm', 'Mặt trời lên, cả nông trại Calen bừng sáng, bắt đầu một ngày mới.' ),
		array( 'leaf', 'Thu hái dược liệu', 'Bà con hái thảo mộc tươi ngay tại vườn, chọn đúng độ.' ),
		array( 'truck', 'Về xưởng trong ngày', 'Dược liệu được chuyển về nhà máy sản xuất ngay trong ngày.' ),
		array( 'drop', 'Rửa sạch', 'Thảo mộc được rửa kỹ, loại bỏ bụi đất.' ),
		array( 'dry', 'Sấy khô', 'Sấy ở nhiệt độ phù hợp để giữ màu và hương tự nhiên.' ),
		array( 'grind', 'Nghiền & phối trộn', 'Nghiền nhỏ và phối theo công thức của từng loại trà.' ),
		array( 'package', 'Đóng túi lọc & gửi đi', 'Đóng thành túi lọc, vào hộp và gửi đến khách khắp nơi.' ),
	);
	$html = '<section class="cl-section" style="padding-top:0" id="quy-trinh"><div class="cl-wrap">'
		. '<div class="cl-head"><div><h2 class="cl-h2">Hành trình từ nông trại đến tách trà</h2>'
		. '<p class="cl-sub">Mỗi gói trà Calen đi qua 7 bước, từ vườn thảo mộc đến tay bạn.</p></div>'
		. '<a class="cl-link" href="https://www.facebook.com/Calenmask/reels/" target="_blank" rel="noopener">Xem video trên Facebook Reels →</a></div>'
		. '<ol class="cl-journey">';
	foreach ( $steps as $i => list( $key, $title, $text ) ) {
		$html .= sprintf(
			'<li class="cl-journey__step"><span class="cl-journey__num">%02d</span><span class="cl-journey__icon"><svg viewBox="0 0 24 24" aria-hidden="true">%s</svg></span><strong>%s</strong><span class="cl-journey__text">%s</span></li>',
			$i + 1,
			$icon[ $key ],
			esc_html( $title ),
			esc_html( $text )
		);
	}
	$html .= '</ol><p class="cl-journey__note">Video quy trình đầy đủ đang được cập nhật. Trong lúc chờ, bạn xem các clip thật tại <a href="https://www.facebook.com/Calenmask/reels/" target="_blank" rel="noopener">Facebook Reels của Calen</a>.</p>';
	return $html . '</div></section>';
}

function calen_home_hero_media(): string {
	$dir   = plugins_url( 'assets/', __FILE__ );
	$boxes = array(
		'box-teatox.webp'  => 'Hộp Trà Teatox Calen Nature Farm',
		'box-uguxo.webp'   => 'Hộp Trà UG-UXO Calen Nature Farm',
		'box-tronkhe.webp' => 'Hộp Trà Tròn Khe Calen Nature Farm',
		'box-tophi.webp'   => 'Hộp Trà Tophi Calen Nature Farm',
	);
	$html = '<div class="cl-hero__media cl-hero__boxes">';
	foreach ( $boxes as $file => $alt ) {
		$html .= '<img class="cl-box" src="' . esc_url( $dir . $file ) . '" alt="' . esc_attr( $alt ) . '" width="440" height="845" fetchpriority="high">';
	}
	return $html . '<div class="cl-float"><strong>Shopee Mall chính hãng</strong>4.9★ · 22,4k đánh giá · 7 năm</div></div>';
}

/** Replaces homepage blocks; each one only when its pattern matches exactly once, so an unexpected layout is left alone. */
function calen_home_apply( array $edits ) {
	$page = get_page_by_path( 'trang-chu' );
	if ( ! $page ) {
		error_log( 'Calen storefront: page trang-chu not found.' );
		return;
	}
	$content = $page->post_content;
	foreach ( $edits as $label => list( $pattern, $replacement ) ) {
		$updated = preg_replace( $pattern, $replacement, $content, -1, $count );
		if ( 1 !== $count ) {
			error_log( "Calen storefront: homepage block '{$label}' matched {$count} times, left unchanged." );
			continue;
		}
		$content = $updated;
	}
	if ( $content === $page->post_content ) {
		return;
	}
	kses_remove_filters(); // the page holds inline SVG icons that kses would strip for a visitor request
	$result = wp_update_post( array( 'ID' => $page->ID, 'post_content' => $content ), true );
	kses_init();
	if ( is_wp_error( $result ) ) {
		error_log( 'Calen storefront: homepage not saved: ' . $result->get_error_message() );
	}
}

function calen_home_proof(): string {
	return '<div class="cl-hero__proof"><strong>4.9/5</strong><span>từ 22,4k đánh giá<br>trên Shopee Mall</span></div>';
}

function calen_home_needs(): string {
	$leaf  = '<path d="M12 21c0-7 3-11 8-13-1 7-3 11-8 13Zm0 0C12 15 9 11 4 9c0 6 3 10 8 12Z"></path>';
	$moon  = '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z"></path>';
	$heart = '<path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.5-7 10-7 10Z"></path>';
	$drop  = '<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z"></path>';
	$needs = array(
		array( 'phu-nu', $heart, 'Chăm sóc sức khoẻ phụ nữ', 'Trinh Nữ Hoàng Cung, Mâm Xôi' ),
		array( 'can-nang-detox', $leaf, 'Muốn giữ dáng, nhẹ bụng', 'Lá Nam 10 Vị, Detox 14 Ngày' ),
		array( 'tieu-hoa', $leaf, 'Hay đầy bụng, khó tiêu', 'Rau Mương – Nghệ Vàng, Kiện Tỳ' ),
		array( 'ngu-ngon', $moon, 'Khó ngủ, hay chóng mặt', 'Dưỡng Tâm An, Tiền Đình' ),
		array( 'xuong-khop', $leaf, 'Hay mỏi xương khớp', 'Trà Xương Khớp, Tía Tô' ),
		array( 'gan-than', $drop, 'Quan tâm gan, thận', 'Cà Gai Leo, Kim Tiền Thảo' ),
		array( 'mo-mau-duong-huyet', $drop, 'Quan tâm mỡ máu, đường huyết', 'Lá Ổi – Lá Sen, Dây Thìa Canh' ),
		array( 'dep-da', $heart, 'Muốn da, tóc đẹp hơn', 'Mâm Xôi, Diệp Hạ Châu' ),
	);
	$html = '<div class="cl-needs">';
	foreach ( $needs as list( $slug, $icon, $title, $hint ) ) {
		$html .= '<a class="cl-need" href="/product-category/' . $slug . '/"><span class="cl-need__icon"><svg viewBox="0 0 24 24" aria-hidden="true">' . $icon . '</svg></span>'
			. '<span><strong>' . esc_html( $title ) . '</strong><small>Gợi ý: ' . esc_html( $hint ) . '</small></span></a>';
	}
	return $html . '</div>';
}

function calen_home_reviews(): string {
	$stats = array(
		array( '4.9/5', 'Điểm trung bình từ 22,4k đánh giá của khách trên Shopee Mall.' ),
		array( '22,2k', 'Người theo dõi gian hàng Calen Nature Farm trên Shopee.' ),
		array( '7 năm', 'Bán hàng trên Shopee, trả lời chat 100% trong vài phút.' ),
	);
	$html = '<section class="cl-section" style="padding-top:0"><div class="cl-wrap"><div class="cl-head"><div><h2 class="cl-h2">Khách hàng nói gì về Calen</h2>'
		. '<p class="cl-sub">Số liệu thật từ gian hàng Shopee Mall của Calen (ngày 14/11/2026).</p></div>'
		. '<a class="cl-link" href="' . CALEN_SHOPEE_SHOP_URL . '" target="_blank" rel="noopener">Đọc đánh giá trên Shopee →</a></div><div class="cl-reviews">';
	foreach ( $stats as list( $number, $text ) ) {
		$html .= '<figure class="cl-review"><strong class="cl-stat">' . esc_html( $number ) . '</strong><figcaption>' . esc_html( $text ) . '</figcaption></figure>';
	}
	return $html . '</div></div></section>';
}

function calen_import_data(): ?array {
	$raw  = file_get_contents( __DIR__ . '/data/shopee-teas.json' );
	$data = $raw ? json_decode( $raw, true ) : null;
	if ( ! is_array( $data ) || empty( $data['products'] ) || empty( $data['cats'] ) ) {
		error_log( 'Calen import: data/shopee-teas.json is missing or invalid.' );
		return null;
	}
	return $data;
}

function calen_import_batch() {
	$data = calen_import_data();
	if ( ! $data ) {
		update_option( CALEN_IMPORT_DONE, 'error: invalid data file' );
		return;
	}
	if ( ! calen_import_lock() ) {
		return; // another run is busy; it schedules the next batch itself
	}
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( CALEN_IMPORT_LOCK_TTL );
	}
	$cat_ids = calen_import_categories( $data['cats'] );
	$start   = (int) get_option( CALEN_IMPORT_NEXT, 0 );
	$slice   = array_slice( $data['products'], $start, CALEN_IMPORT_BATCH );
	foreach ( $slice as $item ) {
		calen_import_product( $item, $cat_ids, $data['image_base'], $data['shopee_shop_id'] );
	}
	$next = $start + count( $slice );
	update_option( CALEN_IMPORT_NEXT, $next );
	delete_option( CALEN_IMPORT_LOCK );
	if ( $next >= count( $data['products'] ) ) {
		update_option( CALEN_IMPORT_DONE, gmdate( 'c' ) );
		return;
	}
	wp_schedule_single_event( time() + 5, CALEN_IMPORT_HOOK );
}

/** Creates missing categories and renames existing ones to the names in the data file. @return array<string,int> slug => term id */
function calen_import_categories( array $cats ): array {
	$ids = array();
	foreach ( $cats as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term ) {
			if ( $term->name !== $name ) {
				wp_update_term( $term->term_id, 'product_cat', array( 'name' => $name ) );
			}
			$ids[ $slug ] = (int) $term->term_id;
			continue;
		}
		$made = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		if ( is_wp_error( $made ) ) {
			error_log( "Calen import: category {$slug} not created: " . $made->get_error_message() );
			continue;
		}
		$ids[ $slug ] = (int) $made['term_id'];
	}
	return $ids;
}

function calen_import_product( array $item, array $cat_ids, string $image_base, string $shop_id ) {
	$sku = 'SP-' . $item['shopee_id'];
	if ( wc_get_product_id_by_sku( $sku ) ) {
		return;
	}
	$image_ids = array_values(
		array_filter(
			array_map(
				fn( $hash ) => calen_import_image( $image_base . $hash, $hash, $item['name'] ),
				$item['images']
			)
		)
	);
	$shopee_url = "https://shopee.vn/product/{$shop_id}/{$item['shopee_id']}";

	$product = new WC_Product_Simple();
	$product->set_name( $item['name'] );
	$product->set_sku( $sku );
	$product->set_regular_price( (string) calen_price_for_shopee( (int) $item['price'] ) ); // website tier price, not the raw Shopee price
	$product->set_status( 'publish' );
	$product->set_featured( ! empty( $item['featured'] ) );
	$product->set_category_ids( isset( $cat_ids[ $item['cat'] ] ) ? array( $cat_ids[ $item['cat'] ] ) : array() );
	$product->set_description( calen_import_description( $item, $shopee_url ) );
	if ( $image_ids ) {
		$product->set_image_id( $image_ids[0] );
		$product->set_gallery_image_ids( array_slice( $image_ids, 1 ) );
	}
	$product->update_meta_data( 'calen_pack', $item['pack'] );
	$product->update_meta_data( 'calen_shopee_url', $shopee_url );
	$product->update_meta_data( 'calen_shopee_rating', $item['rating'] ?? '' );
	$product->update_meta_data( 'calen_shopee_sold', $item['sold'] ?? '' );
	$product->update_meta_data( 'calen_shopee_reviews', $item['reviews'] ?? '' );
	if ( ! $product->save() ) {
		error_log( "Calen import: product {$sku} not saved." );
	}
}

/** Downloads one Shopee image into the media library. Returns the attachment id, or 0 on failure. */
function calen_import_image( string $url, string $hash, string $alt ): int {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		error_log( "Calen import: image {$hash} not downloaded: " . $tmp->get_error_message() );
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => $hash . '.jpg', 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		error_log( "Calen import: image {$hash} not saved: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

function calen_import_description( array $item, string $shopee_url ): string {
	$rows = array_filter(
		array(
			'Quy cách'    => $item['pack'],
			'Khối lượng'  => $item['weight'],
			'Hạn sử dụng' => $item['hsd'],
		)
	);
	$html = '<ul>';
	foreach ( $rows as $label => $value ) {
		$html .= '<li><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value ) . '</li>';
	}
	$html .= '</ul>';
	$html .= sprintf(
		'<p>Trên Shopee Mall, sản phẩm được đánh giá <strong>%s/5</strong> từ %s lượt, đã bán %s (số liệu ngày 14/11/2026). <a href="%s" target="_blank" rel="noopener">Xem đánh giá trên Shopee</a></p>',
		esc_html( $item['rating'] ),
		esc_html( $item['reviews'] ),
		esc_html( $item['sold'] ),
		esc_url( $shopee_url )
	);
	return $html;
}
