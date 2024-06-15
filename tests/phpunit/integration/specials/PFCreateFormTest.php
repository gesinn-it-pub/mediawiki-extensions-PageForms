<?php

/**
 * @covers \PFCreateForm
 *
 * @author gesinn-it-wam
 */
class PFCreateFormTest extends MediaWikiIntegrationTestCase {

	use IntegrationTestHelpers;

	public function setUp(): void {
		parent::setUp();
		$this->requireLanguageCodeEn();
	}

	public function testGet() {
		$createForm = new PFCreateForm();

		$createForm->execute( null );

		$output = $createForm->getOutput();
		$this->assertStringStartsWith( "Create a form", $output->getPageTitle() );
	}

}
