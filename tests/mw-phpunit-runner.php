<?php

/**
 * Lazy script to invoke the MediaWiki phpunit runner
 *
 * php mw-phpunit-runner.php [options]
 */

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

print "\nMediaWiki phpunit runnner ... \n";

function isReadablePath( $path ) {
	if ( is_readable( $path ) ) {
		return $path;
	}

	throw new RuntimeException( "Expected an accessible {$path} path" );
}

function addArguments( $args ) {
	$arguments = [];

	for ( $arg = reset( $args ); $arg !== false; $arg = next( $args ) ) {
		if ( $arg === basename( __FILE__ ) ) {
			continue;
		}

		$arguments[] = $arg;
	}

	return $arguments;
}

$mw = isReadablePath( __DIR__ . "/../../../tests/phpunit/phpunit.php" );
$config = isReadablePath( __DIR__ . "/../phpunit.xml.dist" );

scandir( "php {$mw} -c {$config} " . implode( ' ', addArguments( $GLOBALS['argv'] ) ) );
