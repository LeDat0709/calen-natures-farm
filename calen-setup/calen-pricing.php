<?php
/**
 * Website prices between 199.000₫ and 210.000₫, picked by the tea's current Shopee price level,
 * so the order of prices matches Shopee. Kept free of WordPress so tests/pricing-check.php can run it.
 * Owner's request 2026-11-14: replace the flat 200.000₫ with a 199k–210k spread.
 */

/** Shopee price below the key => website price. Checked top to bottom; the last entry catches everything else. */
const CALEN_PRICE_TIERS = array(
	130000      => 199000,
	170000      => 205000,
	PHP_INT_MAX => 210000,
);

function calen_price_for_shopee( int $shopee_price ): int {
	foreach ( CALEN_PRICE_TIERS as $below => $price ) {
		if ( $shopee_price < $below ) {
			return $price;
		}
	}
	return end( CALEN_PRICE_TIERS );
}
