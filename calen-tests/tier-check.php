<?php
// Run: php -d zend.assertions=1 -d assert.exception=1 tier-check.php  (no WordPress needed)
require __DIR__ . '/../calen-setup/calen-tiers.php';

assert( calen_tier_pct( 1 ) === 0 );
assert( calen_tier_pct( 2 ) === 0 );
assert( calen_tier_pct( 3 ) === 7 );
assert( calen_tier_pct( 5 ) === 7 );
assert( calen_tier_pct( 6 ) === 12 );
assert( calen_tier_pct( 20 ) === 12 );

assert( calen_tier_unit_price( 120000, 1 ) === 120000.0 );
assert( calen_tier_unit_price( 120000, 3 ) === 112000.0 ); // 111.600 rounded to 112.000
assert( calen_tier_unit_price( 120000, 6 ) === 106000.0 ); // 105.600 rounded to 106.000
assert( calen_tier_unit_price( 0, 6 ) === 0.0 );

assert( calen_tier_saving( 120000, 3 ) === 24000.0 );
assert( calen_tier_saving( 120000, 6 ) === 84000.0 );
assert( calen_tier_saving( 120000, 1 ) === 0.0 );

echo "tier-check: all assertions passed\n";
