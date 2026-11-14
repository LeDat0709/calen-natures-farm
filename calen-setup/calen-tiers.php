<?php
/**
 * Combo pricing tiers, kept free of WordPress so it can be checked on its own (tests/tier-check.php).
 * Edit CALEN_TIERS to change combos: minimum packs => percent off.
 */

const CALEN_TIERS = array(
	6 => 12, // confirmed by the owner on 2026-11-14
	3 => 7,
);

/** Percent off for a quantity (0 when no tier applies). */
function calen_tier_pct( int $qty ): int {
	foreach ( CALEN_TIERS as $min => $pct ) {
		if ( $qty >= $min ) {
			return $pct;
		}
	}
	return 0;
}

/** Unit price after the tier discount, rounded to the nearest 1.000₫. */
function calen_tier_unit_price( float $base, int $qty ): float {
	$pct = calen_tier_pct( $qty );
	if ( 0 === $pct || $base <= 0 ) {
		return $base;
	}
	return (float) round( $base * ( 100 - $pct ) / 100, -3 );
}

/** Money saved buying $qty packs versus paying full price for each. */
function calen_tier_saving( float $base, int $qty ): float {
	return ( $base - calen_tier_unit_price( $base, $qty ) ) * $qty;
}
