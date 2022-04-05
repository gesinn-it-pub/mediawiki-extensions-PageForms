<?php

/**
 * @covers \PFHooks
 *
 * @author gesinn-it-wam
 */
class PFHooksTest extends MediaWikiIntegrationTestCase {

	/**
	 * Set up environment
	 */
	public function setUp(): void {
		parent::setUp();
	}

	public function testRunThroughOnly() {
		PFHooks::registerExtension();
		PFHooks::initialize();
		$resourceLoader = new ResourceLoader();
		PFHooks::registerModules( $resourceLoader );
		$list = [];
		PFHooks::registerNamespaces( $list );
		$parser = new Parser();
		PFHooks::registerFunctions( $parser );
		$this->assertTrue( true );
	}
}
