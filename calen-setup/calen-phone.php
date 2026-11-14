<?php
/**
 * Vietnamese phone check, kept free of WordPress so tests/phone-check.php can run it.
 * Accepts mobiles (03/05/07/08/09 + 8 digits) and landlines (02 + 9 digits); +84 or 84 may replace the leading 0.
 * Spaces, dots, dashes and brackets are ignored.
 */

function calen_is_vn_phone( string $raw ): bool {
	$digits = preg_replace( '/[\s.\-()]/', '', $raw );
	$digits = preg_replace( '/^(\+84|84)(?=\d{9,10}$)/', '0', $digits );
	return 1 === preg_match( '/^(0[35789]\d{8}|02\d{9})$/', $digits );
}
