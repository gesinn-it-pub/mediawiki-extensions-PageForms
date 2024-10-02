<?php

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
		return new PFFormEdit();
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
		if ( version_compare( MW_VERSION, '1.38', '>' ) ) {
			$this->markTestSkipped( 'Check how to properly register special page inside tests for MW 1.39 and higher!' );
		}
		// Sample input data
		$altForms = [ 'Form1', 'Form2' ];
		$targetName = 'TargetPage';

		// Mocking the PFUtils::getSpecialPage method
		$mockSpecialPage = $this->createMock( SpecialPage::class );
		$mockTitle = $this->createMock( Title::class );

		// Set expectations on the mocked objects
		$mockTitle->expects( $this->once() )
			->method( 'getFullURL' )
			->willReturn( 'https://example.com/index.php/Special:FormEdit' );

		$mockSpecialPage->expects( $this->once() )
			->method( 'getPageTitle' )
			->willReturn( $mockTitle );

		// Replace the static method call in PFUtils with the mock
		$this->setMwGlobals( 'wgSpecialPages', [ 'FormEdit' => $mockSpecialPage ] );

		// Create an instance of the class that contains printAltFormsList
		$pfFormEdit = $this->newSpecialPage();

		// Run the method with the mocked objects and inputs
		$output = $pfFormEdit->printAltFormsList( $altForms, $targetName );

		// Check the expected HTML output
		$expectedOutput = '<a href="https://example.com/index.php/Special:FormEdit/Form1/TargetPage">Form1</a>, ' .
						  '<a href="https://example.com/index.php/Special:FormEdit/Form2/TargetPage">Form2</a>';

		// Assert the output matches the expected result
		$this->assertEquals( $expectedOutput, $output );
	}
}
