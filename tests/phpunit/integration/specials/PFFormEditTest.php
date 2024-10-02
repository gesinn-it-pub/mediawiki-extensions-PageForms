<?php

use MediaWiki\MediaWikiServices;

/**
 * @covers \PFFormEdit
 * @covers \PFAutoeditAPI
 * @group Database
 * @group medium
 *
 * @author gesinn-it-wam
 */
class PFFormEditTest extends SpecialPageTestBase {

	use IntegrationTestHelpers;

	public function setUp(): void {
		parent::setUp();
		$this->requireLanguageCodeEn();
		$this->tablesUsed[] = 'page';
	}

	/**
	 * Create an instance of the special page being tested.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		// Return an instance of PFFormEdit
		return MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'FormEdit' );
	}

	public function testEmptyQuery() {
		$formEdit = $this->newSpecialPage();

		$formEdit->execute( null );

		$output = $formEdit->getOutput();
		$this->assertStringStartsWith( '<div class="error"><p>No target page specified.', $output->mBodytext );
	}

	public function testInvalidForm() {
		$formEdit = $this->newSpecialPage();

		$formEdit->execute( "InvalidForm/X" );

		$output = $formEdit->getOutput();

		$this->assertEquals( "Create InvalidForm: X", $output->getPageTitle() );
		$this->assertStringContainsString( '<div class="error"><p><b>InvalidForm</b> is not a valid form.', $output->mBodytext );
	}

	public function testValidForm() {
		$formText = <<<EOF
			{{{for template|Thing|label=Thing}}}
			{| class="formtable"
			! Name: 
			| {{{field|Name|input type=text}}}
			|}
			{{{end template}}}
		EOF;
		$this->insertPage( 'Form:Thing', $formText );
		$formEdit = $this->newSpecialPage();

		$formEdit->execute( "Thing/Thing1" );

		$output = $formEdit->getOutput();
		$this->assertEquals( "Create Thing: Thing1", $output->getPageTitle() );
		$this->assertStringContainsString( '<legend>Thing</legend>', $output->mBodytext );
		$this->assertStringContainsString( 'Thing[Name]', $output->mBodytext );
	}

	public function testPrintAltFormsList() {
        // Create an instance of PFFormEdit
        $formEdit = $this->newSpecialPage();

        // Prepare input for the test
        $alt_forms = [ 'FormA', 'FormB', 'FormC' ];
        $target_name = 'SampleTarget';

        // Expected URL for the special page
        $fe_url = SpecialPage::getTitleFor( 'FormEdit' )->getFullURL();
        
        // Manually construct the expected output
        $expected_output = '<a href="' . $fe_url . '/FormA/SampleTarget">FormA</a>, ' .
                           '<a href="' . $fe_url . '/FormB/SampleTarget">FormB</a>, ' .
                           '<a href="' . $fe_url . '/FormC/SampleTarget">FormC</a>';
        
        // Call the method being tested
        $result = $formEdit->printAltFormsList( $alt_forms, $target_name );

        // Assert that the result matches the expected output
        $this->assertEquals( $expected_output, $result );
    }
}
