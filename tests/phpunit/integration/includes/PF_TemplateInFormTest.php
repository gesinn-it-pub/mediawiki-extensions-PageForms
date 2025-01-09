<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers PFTemplateInForm
 *
 * @author gesinn-it-ilm
 */
class PFTemplateInFormTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		$this->backupGlobals = [ 'wgRequest' ];
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	public function testCreateWithFormFields() {
		// Mock form fields
		$mockField1 = $this->createMock( PFFormField::class );
		$mockField2 = $this->createMock( PFFormField::class );
		$formFields = [ $mockField1, $mockField2 ];

		// Call the create method
		$template = PFTemplateInForm::create(
			'TemplateName',
			'Template Label',
			true,
			5,
			$formFields
		);

		// Assertions
		$this->assertInstanceOf( PFTemplateInForm::class, $template );
		$this->assertEquals( 'TemplateName', $template->getTemplateName() );
		$this->assertEquals( 'Template Label', $template->getLabel() );
		$this->assertTrue( $template->allowsMultiple() );
		$this->assertEquals( 5, $template->getMaxInstancesAllowed() );
		$this->assertCount( 2, $template->getFields() );
		$this->assertSame( $mockField1, $template->getFields()[0] );
		$this->assertSame( $mockField2, $template->getFields()[1] );
	}

	public function testCreateWithoutFormFields() {
		// Create a mock for PFTemplate and its methods
		$mockTemplate = $this->createMock( PFTemplate::class );
		$mockTemplate->method( 'getTemplateFields' )->willReturn( [
			'field1',
			'field2'
		] );

		// Use ReflectionClass to override the static method
		$reflection = new ReflectionClass( PFTemplate::class );
		$method = $reflection->getMethod( 'newFromName' );
		$method->setAccessible( true );

		$originalMethod = $method->getClosure();

		// Call the `create` method
		$templateInForm = PFTemplateInForm::create( 'TestTemplate' );

		// Assertions
		$this->assertInstanceOf( PFTemplateInForm::class, $templateInForm );
		$this->assertEquals( 'TestTemplate', $templateInForm->getTemplateName() );
		$this->assertNotEmpty( $mockTemplate->getTemplateFields() );
		$this->assertCount( 2, $mockTemplate->getTemplateFields() );
	}

	public function testSetFieldValuesFromSubmitSingleInstance(): void {
		global $wgRequest;

		// Mock request for a single-instance template.
		$wgRequest = $this->createMock( WebRequest::class );
		$wgRequest->method( 'getArray' )->willReturnCallback( static function ( $key ) {
			if ( $key === 'My_Template' ) {
				return [ 'field1' => 'value1', 'field2' => 'value2' ];
			}
			return null;
		} );

		$template = new PFTemplateInForm();
		$template->setTemplateName( 'My Template' );
		$template->setAllowsMultiple( false );

		$template->setFieldValuesFromSubmit();

		$this->assertEquals(
			[ 'field1' => 'value1', 'field2' => 'value2' ],
			$template->getValuesFromSubmit()
		);
	}

	public function testSetFieldValuesFromSubmitMultipleInstances(): void {
		global $wgRequest;

		// Mock request for multiple-instance template.
		$wgRequest = $this->createMock( WebRequest::class );
		$wgRequest->method( 'getArray' )->willReturnCallback( static function ( $key ) {
			if ( $key === 'My_Template' ) {
				return [
					'0' => [ 'field1' => 'value1', 'field2' => 'value2' ],
					'1' => [ 'field1' => 'value3', 'field2' => 'value4' ],
				];
			}
			return null;
		} );

		$template = new PFTemplateInForm();
		$template->setTemplateName( 'My Template' );
		$template->setAllowsMultiple( true );
		if ( version_compare( MW_VERSION, '1.39', '>=' ) ) {
			$template->setInstanceNum( 1 );
		}

		$template->setFieldValuesFromSubmit();

		$this->assertEquals(
			[ 'field1' => 'value3', 'field2' => 'value4' ],
			$template->getValuesFromSubmit()
		);
	}

	public function testSetFieldValuesFromSubmitNoData(): void {
		global $wgRequest;

		// Mock request with no data for the template.
		$wgRequest = $this->createMock( WebRequest::class );
		$wgRequest->method( 'getArray' )->willReturn( null );

		$template = new PFTemplateInForm();
		$template->setTemplateName( 'Nonexistent Template' );

		$template->setFieldValuesFromSubmit();

		$this->assertSame( [], $template->getValuesFromSubmit() );
	}

	public function testSingleTagExtraction(): void {
		$str = "This is some text <pre>unparsed content</pre> more text.";
		$replacements = [];

		$result = PFTemplateInForm::removeUnparsedText( $str, $replacements );

		// Assert the content within <pre> is replaced with a placeholder.
		$this->assertEquals( "This is some text \1" . "0" . "\2 more text.", $result );

		// Assert the content is stored in the replacements array.
		$this->assertEquals( [ "<pre>unparsed content</pre>" ], $replacements );
	}

	public function testMultipleTagsExtraction(): void {
		$str = "Text <pre>first</pre> middle <nowiki>second</nowiki> end.";
		$replacements = [];

		$result = PFTemplateInForm::removeUnparsedText( $str, $replacements );

		// Assert the placeholders are correctly inserted.
		$this->assertEquals( "Text \1" . "0" . "\2 middle \1" . "1" . "\2 end.", $result );

		// Assert the content is stored in the replacements array.
		$this->assertEquals(
			[ "<pre>first</pre>", "<nowiki>second</nowiki>" ],
			$replacements
		);
	}

	public function testNestedTags(): void {
		$str = "Nested <pre><ref>ignored</ref></pre> content.";
		$replacements = [];

		$result = PFTemplateInForm::removeUnparsedText( $str, $replacements );

		// Assert the placeholder replaces the entire <pre> tag with its content.
		$this->assertEquals( "Nested \1" . "0" . "\2 content.", $result );

		// Assert the replacements array includes the full content of <pre>.
		$this->assertEquals( [ "<pre><ref>ignored</ref></pre>" ], $replacements );
	}

	public function testUnclosedTagsAreIgnored(): void {
		$str = "This is <pre>unclosed text.";
		$replacements = [];

		$result = PFTemplateInForm::removeUnparsedText( $str, $replacements );

		// Assert the string remains unchanged since the tag is unclosed.
		$this->assertEquals( $str, $result );

		// Assert the replacements array remains empty.
		$this->assertSame( [], $replacements );
	}

	public function testSetFieldValuesFromPageSimpleTemplate() {
		$existingContent = "{{TemplateName|field1=value1|field2=value2}}";
		$template = new PFTemplateInForm();

		$template->setPregMatchTemplateStr( 'TemplateName' );
		$template->setSearchTemplateStr( 'TemplateName' );

		$template->setFieldValuesFromPage( $existingContent );

		$expectedValues = [
			'field1' => 'value1',
			'field2' => 'value2'
		];

		$this->assertEquals( $expectedValues[ 'field1' ], $template->getValuesFromPage()[ 'field1' ] );
		$this->assertEquals( $expectedValues[ 'field2' ], $template->getValuesFromPage()[ 'field2' ] );
	}

	public function testSetFieldValuesFromPageNestedBrackets() {
		$existingContent = "{{TemplateName|field1=[[Link|Display]]|field2=value2}}";
		$template = new PFTemplateInForm();

		$template->setPregMatchTemplateStr( 'TemplateName' );
		$template->setSearchTemplateStr( 'TemplateName' );

		$template->setFieldValuesFromPage( $existingContent );

		$expectedValues = [
			'field1' => '[[Link|Display]]',
			'field2' => 'value2'
		];

		$this->assertEquals( $expectedValues[ 'field1' ], $template->getValuesFromPage()[ 'field1' ] );
		$this->assertEquals( $expectedValues[ 'field2' ], $template->getValuesFromPage()[ 'field2' ] );
	}

	public function testSetPageRelatedInfo() {
		// Create a mock or instance of the class
		$template = new PFTemplateInForm();
		$template->setTemplateName( 'Example_Template' );
		$template->setInstanceNum( 1 );

		// Page content contains the template
		$existingPageContent = 'Some content {{Example Template|param1=value1}} more content';
		$template->setPageRelatedInfo( $existingPageContent );

		// Assertions for Case 1
		$this->assertEquals( 'Example Template', $template->getSearchTemplateStr() );
		$this->assertEquals( 'Example Template', $template->getPregMatchTemplateStr() );
		$this->assertNotNull( $template->pageCallsThisTemplate() );
		$this->assertEquals( 2, $template->numSeenInstancesOnThisPage() );
	}

}
