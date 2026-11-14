<?php
/**
 * Shop features: combo pricing (1 / 3 / 6 packs) and a richer single product page.
 * Per-product text comes from custom fields the owner can edit on the product:
 *   calen_benefit      one-line benefit under the title
 *   calen_taste        taste note, e.g. "Vị thanh, hương hoa dịu"
 *   calen_tags         comma list shown as badges, e.g. "Không caffeine, Không hương liệu"
 *   calen_ingredients  text for the "Thành phần & cách pha" tab
 *   calen_brew         brewing steps, one per line
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/calen-tiers.php';

const CALEN_COMBO_PACKS = array( 1, 3, 6 );

/** Apply combo discounts to cart line prices (classic and block cart). */
add_action(
	'woocommerce_before_calculate_totals',
	function ( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		foreach ( $cart->get_cart() as $item ) {
			$fresh = wc_get_product( $item['data']->get_id() );
			if ( ! $fresh ) {
				continue;
			}
			$item['data']->set_price( calen_tier_unit_price( (float) $fresh->get_price(), (int) $item['quantity'] ) );
		}
	},
	20
);

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

/** Combo picker (1 / 3 / 6 packs) above the add-to-cart button. */
add_action(
	'woocommerce_before_add_to_cart_quantity',
	function () {
		global $product;
		if ( ! $product || ! $product->is_type( 'simple' ) || $product->is_sold_individually() ) {
			return;
		}
		$base = (float) $product->get_price();
		echo '<fieldset class="cl-combo"><legend>Chọn gói</legend><div class="cl-combo__opts">';
		foreach ( CALEN_COMBO_PACKS as $i => $qty ) {
			$label  = 1 === $qty ? '1 gói' : 'Combo ' . $qty . ' gói';
			$saving = calen_tier_saving( $base, $qty );
			$note   = $saving > 0
				? 'Tiết kiệm ' . wp_strip_all_tags( wc_price( $saving ) )
				: wp_strip_all_tags( wc_price( $base ) );
			printf(
				'<button type="button" class="cl-combo__opt%s" data-qty="%d" aria-pressed="%s"><strong>%s</strong><span>%s</span>%s</button>',
				0 === $i ? ' is-on' : '',
				$qty,
				0 === $i ? 'true' : 'false',
				esc_html( $label ),
				esc_html( $note ),
				calen_tier_pct( $qty ) ? '<em>-' . calen_tier_pct( $qty ) . '%</em>' : ''
			);
		}
		echo '</div></fieldset>';
		?>
		<script>
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.cl-combo__opt');
			if (!btn) return;
			var form = btn.closest('form.cart');
			var input = form && form.querySelector('input.qty');
			if (input) { input.value = btn.dataset.qty; input.dispatchEvent(new Event('change', { bubbles: true })); }
			btn.parentNode.querySelectorAll('.cl-combo__opt').forEach(function (b) {
				b.classList.toggle('is-on', b === btn);
				b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
			});
		});
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
