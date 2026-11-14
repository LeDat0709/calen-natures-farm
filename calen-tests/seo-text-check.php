<?php
// Run: php -d zend.assertions=1 -d assert.exception=1 seo-text-check.php  (no WordPress needed)
require __DIR__ . '/../calen-setup/calen-seo-text.php';

// Short text: tags stripped, entities decoded, spaces collapsed, nothing cut.
assert( calen_seo_trim( "<p>Trà  Cà Gai Leo &amp; Diệp Hạ Châu</p>\n" ) === 'Trà Cà Gai Leo & Diệp Hạ Châu' );

// Long Vietnamese text: cut on a word boundary, ends with an ellipsis, never longer than the limit, still valid UTF-8.
$long = str_repeat( 'Trà thảo mộc túi lọc thơm dịu dễ uống ', 10 );
$cut  = calen_seo_trim( $long, 60 );
assert( mb_strlen( $cut ) <= 60 );
assert( str_ends_with( $cut, '…' ) );
assert( ! str_ends_with( $cut, ' …' ) );
assert( mb_check_encoding( $cut, 'UTF-8' ) );
assert( str_starts_with( $long, rtrim( $cut, '…' ) ) );

// Exactly at the limit: unchanged.
assert( calen_seo_trim( 'abc def', 7 ) === 'abc def' );

echo "seo-text-check: all assertions passed\n";
