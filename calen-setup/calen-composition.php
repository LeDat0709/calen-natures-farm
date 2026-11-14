<?php
/**
 * Parses the calen_composition product field, kept free of WordPress so tests/composition-check.php can run it.
 * Format: "Táo đỏ: 50, Kỷ tử: 20" — name, colon, percent; entries split by comma, semicolon or new line.
 */

/** @return array<int, array{name: string, pct: float}> valid entries only, in the order written. */
function calen_parse_composition( string $raw ): array {
	$out = array();
	foreach ( preg_split( '/[,;\n]+/u', $raw ) as $entry ) {
		$parts = explode( ':', $entry, 2 );
		if ( 2 !== count( $parts ) ) {
			continue;
		}
		$name = trim( $parts[0] );
		$num  = trim( str_replace( '%', '', $parts[1] ) );
		if ( '' === $name || ! is_numeric( $num ) ) {
			continue;
		}
		$pct = (float) $num;
		if ( $pct <= 0 || $pct > 100 ) {
			continue;
		}
		$out[] = array( 'name' => $name, 'pct' => $pct );
	}
	return $out;
}
