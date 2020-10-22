<?php

$filepath = './Czech.lang.php';
$file = file_get_contents( $filepath );
$pos = strpos( $file, "\$lang['4spaces']" );
$file = substr( $file, $pos );
$lines = explode( "\n", $file );
usort( $lines, 'mysql_simulator' );
$file = implode( "\n", $lines ) . "\n";
file_put_contents( $filepath . '.sort', $file );

/**
 * Explained at https://www.miqrogroove.com/blog/2020/php-sort-like-mysql/
 */
function mysql_simulator( $a, $b ) {
    return strcmp( strtoupper( $a ), strtoupper( $b ) );
}