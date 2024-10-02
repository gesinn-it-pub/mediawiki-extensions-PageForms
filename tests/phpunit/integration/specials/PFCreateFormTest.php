<?php

/**
 * @covers \PFCreateForm
 *
 * @author gesinn-it-wam
 */
class PFCreateFormTest extends SpecialPageTestBase {

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
		// Return an instance of PFCreateForm
		return new PFCreateForm();
	}

	public function testGet() {
		$createForm = $this->newSpecialPage();

		$createForm->execute( null );

		$output = $createForm->getOutput();
		$this->assertStringStartsWith( "Create a form", $output->getPageTitle() );
	}

}
