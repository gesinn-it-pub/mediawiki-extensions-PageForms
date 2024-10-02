<?php

/**
 * @covers \PFCreateProperty
 *
 * @author gesinn-it-ilm
 */
class PFCreatePropertyTest extends SpecialPageTestBase {

	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Create an instance of the special page being tested.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		// Return an instance of PFCreateProperty
		return new PFCreateProperty();
	}

	/**
	 * Tests the execute method of the special page
	 */
	public function testExecute() {
		// Set up the special page object
		$specialPage = $this->newSpecialPage();

		// Create a simulated request
		$request = new FauxRequest( [] );
		$this->setMwGlobals( 'wgRequest', $request );

		// Execute the special page and capture the output
		$context = new RequestContext();
		$context->setRequest( $request );
		$context->setTitle( Title::newFromText( 'Special:CreateProperty' ) );
		$output = $context->getOutput();
		$specialPage->setContext( $context );

		$specialPage->run( '' );
		$htmlOutput = $output->getHTML();

		// Assert that the output contains expected form elements
		$this->assertStringContainsString( '<form action="" method="post">', $htmlOutput );
		$this->assertStringContainsString( 'name="property_name"', $htmlOutput );
		$this->assertStringContainsString( 'name="property_type"', $htmlOutput );
		$this->assertStringContainsString( 'name="values"', $htmlOutput );
		$this->assertStringContainsString( 'id="wpSave"', $htmlOutput );
		$this->assertStringContainsString( 'id="wpPreview"', $htmlOutput );
	}

	/**
	 * Tests form submission with missing CSRF token
	 */
	public function testFormSubmissionMissingCSRF() {
		// Create a test user and request with missing CSRF token
		$user = $this->getTestUser()->getUser();
		$request = new FauxRequest( [
			'property_name' => 'TestProperty',
			'property_type' => 'String',
			'values' => 'Value1, Value2',
			'wpSave' => 'Save',
		] );
		$this->setMwGlobals( 'wgRequest', $request );

		// Set up the special page object and context
		$specialPage = $this->newSpecialPage();
		$context = new RequestContext();
		$context->setRequest( $request );
		$context->setTitle( Title::newFromText( 'Special:CreateProperty' ) );
		$output = $context->getOutput();
		$specialPage->setContext( $context );

		// Run the page with the request
		$specialPage->run( '' );

		// Assert that the page detects the missing CSRF token
		$htmlOutput = $output->getHTML();
		$this->assertStringContainsString( 'cross-site request forgery', $htmlOutput );
	}
}
