<?php

/**
 * @covers \PFCreateForm
 *
 * @author gesinn-it-wam
 */
class PFFormStartTest extends MediaWikiIntegrationTestCase {

	use IntegrationTestHelpers;

	public function setUp(): void {
		parent::setUp();
		$this->requireLanguageCodeEn();
	}

	public function testEmptyQuery() {
		$formStart = new PFFormStart();

		$formStart->execute( null );

		$output = $formStart->getOutput();
		$this->assertEquals( '<div class="error">Error: No forms have been defined on this site.</div>', $output->mBodytext );
	}

}
