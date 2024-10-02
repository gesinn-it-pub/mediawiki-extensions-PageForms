<?php

use MediaWiki\MediaWikiServices;

/**
 * Integration tests for the PFMultiPageEdit class.
 *
 * @covers \PFMultiPageEdit
 *
 * @group Database
 *
 * @author gesinn-it-ilm
 */
class PFMultiPageEditTest extends SpecialPageTestBase {

	protected function setUp(): void {
		parent::setUp();
		// Ensure the user has the correct permissions for the test
		$this->setMwGlobals( 'wgGroupPermissions', [
			'*' => [ 'multipageedit' => true ],
			'user' => [ 'multipageedit' => true ]
		] );
	}

	/**
	 * Create an instance of the special page being tested.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		// Return an instance of PFMultiPageEdit
		return MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'MultiPageEdit' );
	}

	/**
	 * Test executing with template and form parameters.
	 */
	public function testExecuteWithTemplateAndForm() {
		// Create a FauxRequest to simulate passing 'template' and 'form' parameters.
		$params = [
			'template' => 'TestTemplate',
			'form' => 'TestForm'
		];
		$request = new FauxRequest( $params );

		// Set the request in the context using RequestContext.
		RequestContext::getMain()->setRequest( $request );

		// Create an instance of PFMultiPageEdit and call execute().
		$specialPage = $this->newSpecialPage();
		$specialPage->execute( null );

		// Fetch the OutputPage object from the context.
		$output = RequestContext::getMain()->getOutput();

		// Check assertions on the output.
		$this->assertNotEmpty( $output->getHTML(), 'The page output should not be empty' );
		$this->assertStringContainsString( 'TestTemplate', $output->getHTML(), 'The template name should appear in the output' );
	}

	/**
	 * Test the displaySpreadsheet function by mocking a template and form.
	 */
	public function testDisplaySpreadsheet() {
		// Create a FauxRequest to simulate passing 'template' and 'form' parameters.
		$params = [
			'template' => 'TestTemplate',
			'form' => 'TestForm'
		];
		$request = new FauxRequest( $params );
		// Set the request in the context using RequestContext.
		RequestContext::getMain()->setRequest( $request );

		// Mock the necessary template and form data.
		$this->editPage( 'Template:TestTemplate', 'Field1|Field2' );
		$this->editPage( 'Form:TestForm', '{{{for template|TestTemplate}}}' );

		// Create an instance of PFMultiPageEdit and call execute to trigger displaySpreadsheet().
		$specialPage = $this->newSpecialPage();
		$specialPage->execute( null );

		// Fetch the OutputPage object from the context.
		$output = RequestContext::getMain()->getOutput();

		// Instead of looking for 'Spreadsheet interface', let's look for part of the actual output.
		$this->assertStringContainsString( 'class="pfSpreadsheet"', $output->getHTML(), 'The spreadsheet interface should be displayed' );
		$this->assertStringContainsString( 'data-template-name="TestTemplate"', $output->getHTML(), 'The spreadsheet should include the template name' );
		$this->assertStringContainsString( 'data-form-name="TestForm"', $output->getHTML(), 'The spreadsheet should include the form name' );
	}
}
