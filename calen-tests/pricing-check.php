<?php
// Run: php -d zend.assertions=1 -d assert.exception=1 pricing-check.php  (no WordPress needed)
require __DIR__ . '/../calen-setup/calen-pricing.php';

// Three website prices, chosen by the tea's current Shopee price level.
assert( calen_price_for_shopee( 92624 ) === 199000 );
assert( calen_price_for_shopee( 129999 ) === 199000 );
assert( calen_price_for_shopee( 130000 ) === 205000 );
assert( calen_price_for_shopee( 169999 ) === 205000 );
assert( calen_price_for_shopee( 170000 ) === 210000 );
assert( calen_price_for_shopee( 220000 ) === 210000 );

// Every result stays inside the owner's range.
foreach ( array( 0, 1, 50000, 150000, 1000000 ) as $p ) {
	$web = calen_price_for_shopee( $p );
	assert( $web >= 199000 && $web <= 210000 );
}

echo "pricing-check: all assertions passed\n";
