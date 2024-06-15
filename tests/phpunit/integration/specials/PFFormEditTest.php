<?php

/**
 * @covers \PFFormEdit
 * @covers \PFAutoeditAPI
 * @group Database
 * @group medium
 *
 * @author gesinn-it-wam
 */
class PFFormEditTest extends MediaWikiIntegrationTestCase {

	use IntegrationTestHelpers;

	public function setUp(): void {
		parent::setUp();
		$this->requireLanguageCodeEn();
		$this->tablesUsed[] = 'page';
	}

	public function testEmptyQuery() {
		$formEdit = new PFFormEdit();

		$formEdit->execute( null );

		$output = $formEdit->getOutput();
		$this->assertStringStartsWith( '<div class="error"><p>No target page specified.', $output->mBodytext );
	}

	public function testInvalidForm() {
		$formEdit = new PFFormEdit();

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
		$formEdit = new PFFormEdit();

		$formEdit->execute( "Thing/Thing1" );

		$output = $formEdit->getOutput();
		$this->assertEquals( "Create Thing: Thing1", $output->getPageTitle() );
		$this->assertStringContainsString( '<legend>Thing</legend>', $output->mBodytext );
		$this->assertStringContainsString( 'Thing[Name]', $output->mBodytext );
	}
}
