<?php

use MediaWiki\MediaWikiServices;

/**
 * Integration test class for the PFCreateCategory special page.
 * This uses SpecialPageTestBase, designed for testing in the MediaWiki framework.
 * SpecialPageTestBase extends MediaWikiIntegrationTestCase
 *
 * @covers \PFCreateCategory
 *
 * @author gesinn-it-ilm
 */
class PFCreateCategoryTest extends SpecialPageTestBase {

	/**
	 * Set up the test environment.
	 * This is called before each test method.
	 */
	protected function setUp(): void {
		parent::setUp();
	}

	/**
	 * Create an instance of the special page being tested.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		// Return an instance of PFCreateCategory
		return MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'CreateCategory' );
	}

	public function testGet() {
		// Instantiate the PFCreateCategory special page
		$createCategoryPage = $this->newSpecialPage();

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
		$specialPage = $this->newSpecialPage();
		$specialPage->setContext( $context );
		$specialPage->execute( null );

		// Get the HTML output from the context
		$outputText = $context->getOutput()->getHTML();

		// Check that the CSRF protection message is shown
		$this->assertStringContainsString( 'cross-site request forgery', $outputText );
	}

	public function testForm() {
		// Create new special page
		$createCategory = $this->newSpecialPage();
		$context = new RequestContext();
		$createCategory->setContext( $context );

		// Set the test values
		$values = [
			"title" => "Special:CreateCategory",
			"category_name" => "NewCategory",
			"default_form" => "ExampleForm",
			"parent_category" => "ParentCategory",
			"csrf" => $context->getUser()->getEditToken( 'CreateCategory' ),
			"wpSave" => ""
		];

		// Set the values inside the page context
		foreach ( $values as $k => $v ) {
			$context->getRequest()->setVal( $k, $v );
		}

		// Execute the page
		$createCategory->execute( null );

		// Get the page output
		$output = $createCategory->getOutput();

		// Additional DOMDocument checks
		$dom = new DomDocument;
		@$dom->loadHTML( $output->mBodytext );
		$xpath = new DomXPath( $dom );

		// Query for the form element
		$form = $xpath->query( '//form[contains(@action,"Category:NewCategory")]' )->item( 0 );
		$this->assertNotNull( $form, 'Category creation form not found' );

		$this->assertEquals( 'editform', $form->getAttribute( 'name' ) );
		$this->assertEquals( 'post', $form->getAttribute( 'method' ) );
		$this->assertEquals( '/index.php?title=Category:NewCategory&action=submit', $form->getAttribute( 'action' ), 'Form action does not match expected value' );

		// Check for the presence of "Category:NewCategory" in the HTML body
		$this->assertNotFalse( $xpath->query( ' //text()[contains(., "Category:NewCategory")]' )->length, 'Category:NewCategory text not found' );

		// Check the default form value in the wpTextbox1 input
		$inputDefaultForm = $xpath->query( '//input[@name="wpTextbox1"]' )->item( 0 );
		$this->assertNotNull( $inputDefaultForm, 'Special:CreateCategory: <input name="wpTextbox1"/> not found' );
		$this->assertStringContainsString( '{{#default_form:ExampleForm}}', $inputDefaultForm->getAttribute( 'value' ), 'Default form value does not match' );

		// Assert that wpSave is present
		$this->assertNotNull(
			$xpath->query( '//input[@name="wpSave"]' )->item( 0 ),
			'wpSave input not found'
		);
	}
}
