<?php

/**
 * Integration test class for the PFCreateCategory special page.
 * This uses MediaWikiIntegrationTestCase, designed for testing in the MediaWiki framework.
 *
 * @covers \PFCreateCategory
 *
 * @author gesinn-it-ilm
 */
class PFCreateCategoryTest extends MediaWikiIntegrationTestCase {

	/**
	 * Set up the test environment.
	 * This is called before each test method.
	 */
	protected function setUp(): void {
		parent::setUp();
	}

	public function testGet() {
		// Instantiate the PFCreateCategory special page
		$createCategoryPage = new PFCreateCategory();

		// Set up the context manually with a simulated request
		$context = RequestContext::getMain();
		$request = new FauxRequest();
		$context->setRequest( $request );

		// Set the context and execute the special page
		$createCategoryPage->setContext( $context );
		$createCategoryPage->execute( null );

		// Get the output of the page
		$output = $createCategoryPage->getOutput();

		// Assert that the title contains "Create a category"
		$this->assertStringContainsString( "Create a category", $output->getPageTitle() );
	}

	/**
	 * Test the createCategoryText method.
	 */
	public function testCreateCategoryText() {
		$defaultForm = 'TestForm';
		$categoryName = 'TestCategory';
		$parentCategory = 'ParentCategory';

		// Call the method and get the result
		$categoryText = PFCreateCategory::createCategoryText( $defaultForm, $categoryName, $parentCategory );

		// Check that the default form is inserted correctly
		$this->assertStringContainsString( '{{#default_form:TestForm}}', $categoryText );

		// Check that the parent category is added correctly
		$this->assertStringContainsString( '[[Category:ParentCategory]]', $categoryText );
	}

	/**
	 * Test for CSRF token validation in execute method.
	 */
	public function testCSRFProtection() {
		$request = new FauxRequest( [
			'wpSave' => true,
			'csrf' => 'invalid-token',
		] );

		// Set up the context manually
		$context = RequestContext::getMain();
		$context->setRequest( $request );

		// Execute the special page
		$specialPage = new PFCreateCategory();
		$specialPage->setContext( $context );
		$specialPage->execute( null );

		// Get the HTML output from the context
		$outputText = $context->getOutput()->getHTML();

		// Check that the CSRF protection message is shown
		$this->assertStringContainsString( 'cross-site request forgery', $outputText );
	}
}
