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

	public function testVisitSomeLinesOfCodeOnly() {
		self::expectNotToPerformAssertions();

		PFHooks::registerExtension();
		PFHooks::initialize();

		$resourceLoader = $this->createStub( ResourceLoader::class );
		PFHooks::registerModules( $resourceLoader );

		$list = [];
		PFHooks::registerNamespaces( $list );

		$parser = $this->createStub( Parser::class );
		PFHooks::registerFunctions( $parser );
	}
}
