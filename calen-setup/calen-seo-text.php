<?php
/**
 * Plain-text helper for meta descriptions, kept free of WordPress so tests/seo-text-check.php can run it.
 */

/** Strips tags and entities, collapses whitespace, and cuts on a word boundary to at most $max characters (… included). */
function calen_seo_trim( string $text, int $max = 160 ): string {
	$text = html_entity_decode( strip_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}
	$cut   = mb_substr( $text, 0, $max - 1 );
	$space = mb_strrpos( $cut, ' ' );
	if ( false !== $space ) {
		$cut = mb_substr( $cut, 0, $space );
	}
	return rtrim( $cut, " ,.;:–-" ) . '…';
}
