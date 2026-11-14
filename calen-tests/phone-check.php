<?php
// Run: php -d zend.assertions=1 -d assert.exception=1 phone-check.php  (no WordPress needed)
require __DIR__ . '/../calen-setup/calen-phone.php';

// Mobile numbers, written the ways customers type them.
assert( calen_is_vn_phone( '0988836910' ) );
assert( calen_is_vn_phone( '0988 836 910' ) );
assert( calen_is_vn_phone( '09888.36910' ) );
assert( calen_is_vn_phone( '+84988836910' ) );
assert( calen_is_vn_phone( '84 988 836 910' ) );
assert( calen_is_vn_phone( '0352-123-456' ) );

// Landline: 02 + area code, 11 digits.
assert( calen_is_vn_phone( '02438123456' ) );

// Rejected: too short, too long, wrong prefix, letters.
assert( ! calen_is_vn_phone( '12345' ) );
assert( ! calen_is_vn_phone( '098883691' ) );
assert( ! calen_is_vn_phone( '09888369100' ) );
assert( ! calen_is_vn_phone( '0188836910' ) );
assert( ! calen_is_vn_phone( '0988abc910' ) );
assert( ! calen_is_vn_phone( '' ) );

echo "phone-check: all assertions passed\n";
