<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers PFFormField
 *
 * @author gesinn-it-ilm
 */
class PFFormFieldTest extends TestCase {

	private $mockTemplate;
	private $mockTemplateInForm;
	private $mockUser;
	private $mockTemplateField;

	protected function setUp(): void {
		parent::setUp();

		// Mocking the Parser object
		$this->mockParser = $this->createMock( Parser::class );
		$this->mockParser->method( 'recursivePreprocess' )
			->willReturnCallback( fn ( $input ) => $input );
		$this->mockParser->method( 'recursiveTagParse' )
			->willReturnCallback( fn ( $input ) => $input );

		// Mocking the User object
		$this->mockUser = $this->createMock( User::class );
		$this->mockUser->method( 'isAllowed' )
			->with( 'editrestrictedfields' )
			->willReturn( false );

		// Mocking the object being updated
		$this->f = new stdClass();
		$this->f->mFieldArgs = [];

		// Mocking the Template object
		$this->mockTemplate = $this->createMock( PFTemplate::class );

		// Mocking TemplateInForm object
		$this->mockTemplateInForm = $this->createMock( PFTemplateInForm::class );

		// Mock the PFTemplateField class
		$this->mockTemplateField = $this->createMock( PFTemplateField::class );
	}

	public function testCreateFormFieldWithEmptyValues() {
		// Create the PFFormField object
		$formField = PFFormField::create( $this->mockTemplateField );

		// Verify the object is an instance of PFFormField
		$this->assertInstanceOf( PFFormField::class, $formField );
		$this->assertSame( $this->mockTemplateField, $formField->template_field );

		$this->assertNull( $formField->getInputType() );
		$this->assertFalse( $formField->isMandatory() );
		$this->assertFalse( $formField->isHidden() );
		$this->assertFalse( $formField->isRestricted() );
		$this->assertNull( $formField->getPossibleValues() );
		$this->assertFalse( $formField->getUseDisplayTitle() );
		$this->assertSame( [], $formField->getFieldArgs() );
	}

	public function testCreateFormFieldWithTemplateFieldProps() {
		// Set expectations for the mocked methods or properties
		$this->mockTemplateField->method( 'getFieldName' )->willReturn( 'MockedFieldName' );
		$this->mockTemplateField->method( 'getLabel' )->willReturn( 'Mocked Label' );
		$this->mockTemplateField->method( 'getSemanticProperty' )->willReturn( 'MockedSemanticProperty' );
		$this->mockTemplateField->method( 'isList' )->willReturn( true );
		$this->mockTemplateField->method( 'getDelimiter' )->willReturn( ';' );
		$this->mockTemplateField->method( 'getDisplay' )->willReturn( 'MockedDisplay' );

		// Create the PFFormField object
		$formField = PFFormField::create( $this->mockTemplateField );

		// Verify the object is an instance of PFFormField
		$this->assertInstanceOf( PFFormField::class, $formField );

		// Assert that the template field is set correctly
		$this->assertSame( $this->mockTemplateField, $formField->template_field );

		// Assert that properties were set correctly based on the mocked PFTemplateField
		$this->assertSame( 'MockedFieldName', $formField->template_field->getFieldName() );
		$this->assertSame( $this->mockTemplateField, $formField->getTemplateField() );
		$this->assertSame( 'MockedSemanticProperty', $formField->template_field->getSemanticProperty() );
		$this->assertSame( ';', $formField->template_field->getDelimiter() );
		$this->assertSame( 'MockedDisplay', $formField->template_field->getDisplay() );
	}

	public function testDelimiterHandling() {
		// Mock the PFTemplateField class
		$this->mockTemplateFieldOne = $this->createMock( PFTemplateField::class );
		$this->mockTemplateFieldTwo = $this->createMock( PFTemplateField::class );
		$this->mockTemplateFieldOne->method( 'getDelimiter' )->willReturn( ',' );
		$this->mockTemplateFieldTwo->method( 'getDelimiter' )->willReturn( ';' );

		$this->mockTemplateFieldThree = $this->createMock( PFTemplateField::class );
		$this->mockTemplateFieldThree->method( 'getDelimiter' )->willReturn( '' );

		// Create the PFFormField objects
		$fieldOne = PFFormField::create( $this->mockTemplateFieldOne );
		$fieldTwo = PFFormField::create( $this->mockTemplateFieldTwo );
		$fieldThree = PFFormField::create( $this->mockTemplateFieldThree );

		$this->assertEquals( ',', $fieldOne->template_field->getDelimiter() );
		$this->assertEquals( ';', $fieldTwo->template_field->getDelimiter() );

		$delimiter = $fieldThree->template_field->getDelimiter();
		if ( $delimiter === '' ) {
			$fieldThree->setFieldArg( 'delimiter', ',' );
			$this->assertEquals( ',', $fieldThree->getFieldArgs()['delimiter'] );
		}
		$delimiter = $fieldTwo->template_field->getDelimiter();
		if ( $delimiter != '' ) {
			$fieldTwo->setFieldArg( 'delimiter', $fieldTwo->template_field->getDelimiter() );
			$this->assertEquals( ';', $fieldTwo->getFieldArgs()['delimiter'] );
		}
	}

	public function testNewFromFormFieldTag_FieldExistsInTemplate() {
		// Mock data for the tag_components
		$tagComponents = [ 'somePrefix', 'fieldName' ];

		// Mock the template to return a field
		$mockField = $this->createMock( PFTemplateField::class );
		$this->mockTemplate->method( 'getFieldNamed' )->willReturn( $mockField );

		// Mock the template_in_form to return a template name
		$this->mockTemplateInForm->method( 'getTemplateName' )->willReturn( 'TestTemplate' );
		$this->mockTemplateInForm->method( 'strictParsing' )->willReturn( false );

		// Call the method under test
		$formField = PFFormField::newFromFormFieldTag( $tagComponents, $this->mockTemplate, $this->mockTemplateInForm, false, $this->mockUser );

		// Assert that the template field was set
		$this->assertInstanceOf( PFTemplateField::class, $formField->template_field );
		$this->assertSame( $mockField, $formField->template_field );
	}

	public function testNewFromFormFieldTag_FieldDoesNotExistInTemplate_StrictParsing() {
		// Mock data for the tag_components
		$tagComponents = [ 'somePrefix', 'fieldName' ];

		// Mock the template to return null, meaning no field is found
		$this->mockTemplate->method( 'getFieldNamed' )->willReturn( null );

		// Mock the template_in_form to return a template name and enable strict parsing
		$this->mockTemplateInForm->method( 'getTemplateName' )->willReturn( 'TestTemplate' );
		$this->mockTemplateInForm->method( 'strictParsing' )->willReturn( true );

		// Create the PFFormField object
		$formField = PFFormField::create( $this->mockTemplateField );

		$this->assertSame( $this->mockTemplateField, $formField->template_field );

		if ( $this->mockTemplateInForm->strictParsing() ) {

			$formField->template_field = new PFTemplateField();
			$this->mockTemplateField->mIsList = false;

			// Assert that mIsList is set to false if strictParsing is set to TRUE
			$this->assertInstanceOf( PFTemplateField::class, $formField->template_field );
			$this->assertNull( $formField->template_field->isList() );
			$this->assertFalse( $this->mockTemplateField->mIsList );
		}
	}

	public function testMandatoryComponent() {
		$tag_components = [ '', 'test_field', 'mandatory' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertTrue( $formField->isMandatory() );
	}

	public function testHiddenComponent() {
		$tag_components = [ '', '', 'hidden' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertTrue( $formField->isHidden() );
	}

	public function testRestrictedComponent() {
		$tag_components = [ '', '', 'restricted' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertTrue( $formField->isRestricted() );
	}

	public function testKeyValueComponent() {
		$tag_components = [ '', '', 'autocapitalize=uppercase' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertEquals( 'uppercase', $formField->getAutocapitalize() );
	}

	public function testPropertyComponent() {
		$tag_components = [ '', 'test_field', 'property=TestProperty' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertEquals( 'TestProperty', $formField->getFieldArgs()['property'], 'The property should be set correctly' );
	}

	public function testUniqueComponent() {
		$tag_components = [ '', '', 'unique' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertTrue( $formField->getFieldArgs()['unique'] );
	}

	public function testLabelComponent() {
		$tag_components = [ '', '', 'label=TestField' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertEquals( 'TestField', $formField->getLabel() );
	}

	public function testMappingTypeWithMappingTemplate() {
		$mappingType = null;
		$tag_components = [ '', '', 'mapping template' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertTrue( $formField->getFieldArgs()['mapping template'] );

		if ( $formField->getFieldArgs()['mapping template'] != null ) {
			$mappingType = 'template';
			$this->assertSame( 'template', $mappingType );
		}
	}

	public function testMappingTypeWithMappingProperty() {
		$mappingType = null;
		$tag_components = [ '', '', 'mapping property' ];

		// Call the method
		$formField = PFFormField::newFromFormFieldTag(
			$tag_components,
			$this->mockTemplate,
			$this->mockTemplateInForm,
			false,
			$this->mockUser
		);

		$this->assertTrue( $formField->getFieldArgs()['mapping property'] );

		if ( $formField->getFieldArgs()['mapping property'] != null ) {
			$mappingType = 'property';
			$this->assertSame( 'property', $mappingType );
		}
	}
}
