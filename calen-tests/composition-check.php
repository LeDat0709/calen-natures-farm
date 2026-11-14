<?php
// Run: php -d zend.assertions=1 -d assert.exception=1 composition-check.php  (no WordPress needed)
require __DIR__ . '/../calen-setup/calen-composition.php';

assert( calen_parse_composition( '' ) === array() );

assert(
	calen_parse_composition( 'Táo đỏ: 50, Kỷ tử: 20%' ) === array(
		array( 'name' => 'Táo đỏ', 'pct' => 50.0 ),
		array( 'name' => 'Kỷ tử', 'pct' => 20.0 ),
	)
);

// Newlines and semicolons separate entries; decimals use a dot.
assert(
	calen_parse_composition( "Hoa nhài: 12.5\nCỏ ngọt: 7.5; Gừng: 80" ) === array(
		array( 'name' => 'Hoa nhài', 'pct' => 12.5 ),
		array( 'name' => 'Cỏ ngọt', 'pct' => 7.5 ),
		array( 'name' => 'Gừng', 'pct' => 80.0 ),
	)
);

// Bad entries are skipped: no name, no number, zero, above 100.
assert( calen_parse_composition( ': 40, Gừng, Sả: 0, Quế: 120' ) === array() );

echo "composition-check: all assertions passed\n";
