<?php

use MediaWiki\MediaWikiServices;

/**
 * @covers \PFFormStart
 *
 * @author gesinn-it-wam
 */
class PFFormStartTest extends SpecialPageTestBase {

	use IntegrationTestHelpers;

	public function setUp(): void {
		parent::setUp();
		$this->requireLanguageCodeEn();
	}

	/**
	 * Create an instance of the special page being tested.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		// Return an instance of PFFormStart
		return MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'FormStart' );
	}

	public function testEmptyQuery() {
		$formStart = $this->newSpecialPage();

		$formStart->execute( null );

		$output = $formStart->getOutput();
		$this->assertEquals( '<div class="error">Error: No forms have been defined on this site.</div>', $output->mBodytext );
	}

}
