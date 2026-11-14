<?php
/**
 * Shop features: a richer single product page.
 * Per-product text comes from custom fields the owner can edit on the product:
 *   calen_benefit      one-line benefit under the title
 *   calen_taste        taste note, e.g. "Vị thanh, hương hoa dịu"
 *   calen_tags         comma list shown as badges, e.g. "Không caffeine, Không hương liệu"
 *   calen_ingredients  text for the "Thành phần & cách pha" tab
 *   calen_brew         brewing steps, one per line
 *   calen_composition  ingredient percentages, e.g. "Táo đỏ: 50, Kỷ tử: 20" (bar under the price)
 *   calen_temp         water temperature, e.g. "90°C"
 *   calen_steep        steeping time, e.g. "3–5 phút"
 *   calen_pack         pack size, e.g. "Túi 100g"
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/calen-composition.php';

const CALEN_COMPOSITION_COLORS = array( '#3f5d32', '#c8963e', '#7a8f5c', '#b5654a', '#5a6352', '#d9c38a' );

function calen_meta( string $key ): string {
	global $product;
	return $product ? trim( (string) $product->get_meta( $key ) ) : '';
}

/** Benefit line and taste note right under the product title. */
add_action(
	'woocommerce_single_product_summary',
	function () {
		$benefit = calen_meta( 'calen_benefit' );
		$taste   = calen_meta( 'calen_taste' );
		if ( $benefit ) {
			echo '<p class="cl-benefit">' . esc_html( $benefit ) . '</p>';
		}
		if ( $taste ) {
			echo '<p class="cl-taste">' . esc_html( $taste ) . '</p>';
		}
	},
	6
);

/** Quick facts under the price: ingredient bar and brewing chips. Each part shows only when its field is filled. */
add_action(
	'woocommerce_single_product_summary',
	function () {
		$parts = calen_parse_composition( calen_meta( 'calen_composition' ) );
		$chips = array_filter(
			array(
				'temp'  => array( 'Nước', calen_meta( 'calen_temp' ) ),
				'steep' => array( 'Hãm', calen_meta( 'calen_steep' ) ),
				'pack'  => array( 'Quy cách', calen_meta( 'calen_pack' ) ),
			),
			fn( $chip ) => '' !== $chip[1]
		);
		if ( ! $parts && ! $chips ) {
			return;
		}
		echo '<div class="cl-quick">';
		if ( $parts ) {
			$total = array_sum( array_column( $parts, 'pct' ) );
			echo '<p class="cl-quick__label">Thành phần</p><div class="cl-quick__bar" aria-hidden="true">';
			foreach ( $parts as $i => $part ) {
				printf(
					'<span style="width:%s%%;background:%s"></span>',
					esc_attr( round( $part['pct'] / $total * 100, 2 ) ),
					esc_attr( CALEN_COMPOSITION_COLORS[ $i % count( CALEN_COMPOSITION_COLORS ) ] )
				);
			}
			echo '</div><ul class="cl-quick__legend">';
			foreach ( $parts as $i => $part ) {
				printf(
					'<li><i style="background:%s"></i>%s <strong>%s%%</strong></li>',
					esc_attr( CALEN_COMPOSITION_COLORS[ $i % count( CALEN_COMPOSITION_COLORS ) ] ),
					esc_html( $part['name'] ),
					esc_html( rtrim( rtrim( number_format( $part['pct'], 1, ',', '' ), '0' ), ',' ) )
				);
			}
			echo '</ul>';
		}
		if ( $chips ) {
			echo '<ul class="cl-quick__chips">';
			foreach ( $chips as $key => list( $label, $value ) ) {
				printf( '<li class="is-%s"><span>%s</span><strong>%s</strong></li>', esc_attr( $key ), esc_html( $label ), esc_html( $value ) );
			}
			echo '</ul>';
		}
		echo '</div>';
	},
	25
);

/** Out of stock: offer Zalo instead of a dead button. */
add_action(
	'woocommerce_single_product_summary',
	function () {
		global $product;
		if ( ! $product || $product->is_in_stock() ) {
			return;
		}
		echo '<div class="cl-oos"><a class="cl-oos__btn" href="https://zalo.me/' . esc_attr( CALEN_PHONE ) . '" target="_blank" rel="noopener">Nhắn Zalo để giữ hàng</a>'
			. '<p>Sản phẩm tạm hết. Nhắn Calen để giữ một phần trong đợt hàng tới.</p></div>';
	},
	31
);

/** Mobile sticky buy bar, shown while the real buy button is off screen. */
add_action(
	'wp_footer',
	function () {
		global $product;
		if ( ! is_product() || ! $product instanceof WC_Product ) {
			return;
		}
		$in_stock = $product->is_in_stock() && $product->is_purchasable();
		$action   = $in_stock
			? '<button type="button" class="cl-stickybuy__btn" data-buy>Thêm vào giỏ</button>'
			: '<a class="cl-stickybuy__btn" href="https://zalo.me/' . esc_attr( CALEN_PHONE ) . '" target="_blank" rel="noopener">Nhắn Zalo giữ hàng</a>';
		echo '<div class="cl-stickybuy" aria-hidden="true"><div class="cl-stickybuy__info"><span class="cl-stickybuy__name">'
			. esc_html( $product->get_name() ) . '</span><span class="cl-stickybuy__price">' . wp_kses_post( $product->get_price_html() ) . '</span></div>'
			. $action . '</div>';
		?>
		<script>
		(function () {
			var bar = document.querySelector('.cl-stickybuy');
			var target = document.querySelector('form.cart .single_add_to_cart_button, .cl-oos__btn');
			if (!bar || !target || !('IntersectionObserver' in window)) return;
			new IntersectionObserver(function (entries) {
				var show = !entries[0].isIntersecting;
				bar.classList.toggle('is-on', show);
				bar.setAttribute('aria-hidden', show ? 'false' : 'true');
				document.body.classList.toggle('cl-stickybuy-on', show);
			}).observe(target);
			var buy = bar.querySelector('[data-buy]');
			if (buy) buy.addEventListener('click', function () { target.click(); });
		})();
		</script>
		<?php
	}
);

/** Badge row and trust list under the add-to-cart button. */
add_action(
	'woocommerce_single_product_summary',
	function () {
		$tags = array_filter( array_map( 'trim', explode( ',', calen_meta( 'calen_tags' ) ) ) );
		if ( $tags ) {
			echo '<ul class="cl-badges">';
			foreach ( $tags as $tag ) {
				echo '<li>' . esc_html( $tag ) . '</li>';
			}
			echo '</ul>';
		}
		echo '<ul class="cl-assure"><li>Nhận hàng rồi trả tiền (COD)</li><li>Giao hàng toàn quốc</li><li>Có giấy kiểm nghiệm</li>'
			. '<li><a href="https://zalo.me/' . esc_attr( CALEN_PHONE ) . '" target="_blank" rel="noopener">Chưa chắc chọn loại nào? Nhắn Zalo</a></li></ul>';
	},
	35
);

/** "Thành phần & cách pha" tab. */
add_filter(
	'woocommerce_product_tabs',
	function ( $tabs ) {
		if ( calen_meta( 'calen_ingredients' ) || calen_meta( 'calen_brew' ) ) {
			$tabs['calen_ingredients'] = array(
				'title'    => 'Thành phần & cách pha',
				'priority' => 15,
				'callback' => function () {
					$ingredients = calen_meta( 'calen_ingredients' );
					$steps       = array_filter( array_map( 'trim', explode( "\n", calen_meta( 'calen_brew' ) ) ) );
					if ( $ingredients ) {
						echo '<h3>Thành phần</h3>' . wp_kses_post( wpautop( $ingredients ) );
					}
					if ( $steps ) {
						echo '<h3>Cách pha</h3><ol>';
						foreach ( $steps as $step ) {
							echo '<li>' . esc_html( $step ) . '</li>';
						}
						echo '</ol>';
					}
				},
			);
		}
		return $tabs;
	}
);

/** Food-labelling note required for herbal products sold as food. */
add_action(
	'woocommerce_after_single_product_summary',
	function () {
		echo '<p class="cl-legal">Sản phẩm này là thực phẩm, không phải là thuốc, không có tác dụng thay thế thuốc chữa bệnh.</p>';
	},
	11
);
