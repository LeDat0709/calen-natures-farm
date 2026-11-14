<?php
/**
 * SEO without an SEO plugin: meta description, Open Graph / Twitter tags, canonical on shop and category pages,
 * store schema on the homepage, and the pack size in product titles.
 * Text is built from live product data (name, pack, price), so it follows price changes.
 * Per-product override: custom field calen_meta_description. Category text: the category description in WP Admin.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/calen-seo-text.php';

const CALEN_SITE_NAME     = "Calen Nature's Farm";
const CALEN_FACEBOOK_URL  = 'https://www.facebook.com/Calenmask';
const CALEN_SEO_PROMISE   = 'Miễn phí giao hàng toàn quốc, nhận hàng rồi trả tiền (COD).';
const CALEN_HOME_DESC     = "Trà thảo mộc túi lọc Calen Nature's Farm: 7 năm trên Shopee Mall, 4.9★ từ 22,4k đánh giá. " . CALEN_SEO_PROMISE;
const CALEN_SHOP_DESC     = "Tất cả trà thảo mộc túi lọc Calen Nature's Farm, xếp theo mức bán chạy trên Shopee. " . CALEN_SEO_PROMISE;

/** Image of the first product in shop order (Shopee bestseller rank), optionally within one category. */
function calen_seo_first_image( string $category_slug = '' ): string {
	$args = array( 'limit' => 1, 'status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC' );
	if ( $category_slug ) {
		$args['category'] = array( $category_slug );
	}
	$first = wc_get_products( $args );
	return $first ? (string) wp_get_attachment_image_url( $first[0]->get_image_id(), 'large' ) : '';
}

/** What to describe for the current request, or null for pages left to WordPress (cart, checkout, account). */
function calen_seo_context(): ?array {
	if ( is_front_page() ) {
		return array( 'title' => wp_get_document_title(), 'desc' => CALEN_HOME_DESC, 'image' => calen_seo_first_image(), 'url' => home_url( '/' ), 'type' => 'website' );
	}
	if ( is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( ! $product ) {
			return null;
		}
		$pack   = trim( (string) $product->get_meta( 'calen_pack' ) );
		$price  = calen_seo_trim( wc_price( $product->get_price() ) );
		$custom = trim( (string) $product->get_meta( 'calen_meta_description' ) );
		$name   = $product->get_name();
		$label  = ( $pack && false === mb_stripos( $name, $pack ) ) ? "{$name} ({$pack})" : $name;
		$desc   = $custom ? $custom : "{$label} – {$price}. " . CALEN_SEO_PROMISE . ' Calen Nature\'s Farm, 4.9★ trên Shopee Mall.';
		return array(
			'title' => wp_get_document_title(),
			'desc'  => $desc,
			'image' => (string) wp_get_attachment_image_url( $product->get_image_id(), 'large' ),
			'url'   => get_permalink( $product->get_id() ),
			'type'  => 'product',
			'price' => (string) $product->get_price(),
		);
	}
	if ( is_product_category() ) {
		$term = get_queried_object();
		$desc = $term->description ? $term->description : $term->name . ": trà thảo mộc túi lọc Calen Nature's Farm. " . CALEN_SEO_PROMISE;
		return array( 'title' => wp_get_document_title(), 'desc' => $desc, 'image' => calen_seo_first_image( $term->slug ), 'url' => calen_seo_paged_url( get_term_link( $term ) ), 'type' => 'website' );
	}
	if ( is_shop() ) {
		return array( 'title' => wp_get_document_title(), 'desc' => CALEN_SHOP_DESC, 'image' => calen_seo_first_image(), 'url' => calen_seo_paged_url( wc_get_page_permalink( 'shop' ) ), 'type' => 'website' );
	}
	return null;
}

function calen_seo_paged_url( string $base ): string {
	$paged = (int) get_query_var( 'paged' );
	return $paged > 1 ? strtok( get_pagenum_link( $paged ), '?' ) : $base; // drop ?fbclid= and other tracking from the canonical
}

add_action(
	'wp_head',
	function () {
		$c = calen_seo_context();
		if ( ! $c ) {
			return;
		}
		$desc = calen_seo_trim( $c['desc'] );
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		if ( ! is_singular() ) {
			echo '<link rel="canonical" href="' . esc_url( $c['url'] ) . '">' . "\n"; // WordPress already prints it on single pages
		}
		$tags = array(
			'og:locale'      => 'vi_VN',
			'og:site_name'   => CALEN_SITE_NAME,
			'og:type'        => $c['type'],
			'og:title'       => $c['title'],
			'og:description' => $desc,
			'og:url'         => $c['url'],
			'og:image'       => $c['image'],
		);
		if ( isset( $c['price'] ) ) {
			$tags['product:price:amount']   = $c['price'];
			$tags['product:price:currency'] = 'VND';
		}
		foreach ( array_filter( $tags ) as $property => $content ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		if ( is_front_page() ) {
			$store = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'OnlineStore',
				'name'       => CALEN_SITE_NAME,
				'url'        => home_url( '/' ),
				'telephone'  => '+84' . ltrim( CALEN_PHONE, '0' ),
				'image'      => $c['image'],
				'areaServed' => 'VN',
				'sameAs'     => array( CALEN_FACEBOOK_URL, CALEN_SHOPEE_SHOP_URL ),
			);
			echo '<script type="application/ld+json">' . wp_json_encode( $store, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}
	},
	1
);

/** Product titles carry the pack size, which shoppers search for ("hộp 30 túi lọc"). */
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( function_exists( 'is_product' ) && is_product() ) {
			$pack = trim( (string) get_post_meta( get_queried_object_id(), 'calen_pack', true ) );
			if ( $pack && false === mb_stripos( $parts['title'], $pack ) ) {
				$parts['title'] .= ' – ' . $pack;
			}
		}
		return $parts;
	}
);
