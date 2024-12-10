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

	public function testSetMappedValuesTemplate() {
		// Create the PFFormField object
		$formField = PFFormField::create( $this->mockTemplateField );

		// Set the mapping type to 'template'
		$formField->setFieldArg( 'mapping template', 'TestTemplate' );
		$formField->setPossibleValues( [ 'val1' => 'val1', 'val2' => 'val2' ] );

		// Call the method under test
		$formField->setMappedValues( 'template' );

		// Assertions to ensure correct method calls
		$this->assertEquals( [
			'val1' => 'val1',
			'val2' => 'val2'
		], $formField->getPossibleValues() );
	}

	public function testSetMappedValuesProperty() {
		// Create the PFFormField object
		$formField = PFFormField::create( $this->mockTemplateField );

		// Set the mapping type to 'property'
		$formField->setFieldArg( 'mapping property', 'TestProp' );
		$formField->setPossibleValues( [ 'val1' => 'val1', 'val2' => 'val2' ] );

		// Call the method under test
		$formField->setMappedValues( 'property' );

		// Assertions to ensure correct method calls
		$this->assertEquals( [
			'val1' => 'val1',
			'val2' => 'val2'
		], $formField->getPossibleValues() );
	}

	public function testValueStringToLabels() {
		// Create the PFFormField object
		$formField = PFFormField::create( $this->mockTemplateField );
		$formField->setPossibleValues( [ 'val1' => 'Label 1', 'val2' => 'Label 2' ] );

		// Empty string
		$result = $formField->valueStringToLabels( '', ',' );
		$this->assertSame( '', $result );

		// String with only spaces
		$result = $formField->valueStringToLabels( '    ', ',' );
		$this->assertEquals( '    ', $result );

		// Test case 2: Null valueString
		$result = $formField->valueStringToLabels( null, ',' );
		$this->assertNull( $result );

		// Test case 3: Value exists in mPossibleValues
		$result = $formField->valueStringToLabels( 'val1', ',' );
		$this->assertEquals( 'Label 1', $result );

		// Test case 4: Value does not exist in mPossibleValues
		$result = $formField->valueStringToLabels( 'val3', ',' );
		$this->assertEquals( 'val3', $result );

		// Test case 5: Multiple values, some exist in mPossibleValues, others do not
		$result = $formField->valueStringToLabels( 'val1,val3', ',' );
		$this->assertEquals( [ 'Label 1', 'val3' ], $result );

		// Test case 6: Delimiter is null
		$result = $formField->valueStringToLabels( 'val1,val2', null );
		$this->assertEquals( 'val1,val2', $result );

		// Test case 7: Multiple labels and values exist
		$result = $formField->valueStringToLabels( 'val1,val2', ',' );
		$this->assertEquals( [ 'Label 1', 'Label 2' ], $result );
	}

	public function testAdditionalHTMLForInput() {
		// Create the PFFormField object
		$field = PFFormField::create( $this->mockTemplateField );

		// Mock values for $field
		$field->setHoldsTemplate( true );
		$field->setIsDisabled( true );
		$field->setInputName( 'input_field' );
		$field->setFieldArg( 'delimiter', ',' );
		$field->setFieldArg( 'mapping template', 'template_name' );
		$field->setFieldArg( 'unique', true );
		$field->setFieldArg( 'unique_for_category', 'Category1' );
		$field->setFieldArg( 'unique_for_namespace', 'Namespace1' );

		// Call the method to test
		$cur_value = 'some_value';
		$field_name = 'some_field';
		$template_name = 'template_example';
		$result = $field->additionalHTMLForInput( $cur_value, $field_name, $template_name );

		// Assertions for template-related hidden fields
		$this->assertStringContainsString( 'type="hidden" value="true" name="template_example[map_field][some_field]"', $result );
		$this->assertStringContainsString( 'type="hidden" value="some_value" name="input_field"', $result );

		// Assertions for unique-related hidden fields
		$this->assertStringContainsString( 'type="hidden" value="Category1" name="input__unique_for_category"', $result );
		$this->assertStringContainsString( 'type="hidden" value="Namespace1" name="input__unique_for_namespace"', $result );
	}

	public function testGetCurrentValue() {
		// Mock TemplateField
		$this->mockTemplateField->method( 'getFieldName' )->willReturn( 'TestField' );
		$this->mockTemplateField->mFieldName = 'TestField';

		// Create the PFFormField object
		$field = PFFormField::create( $this->mockTemplateField );
		$field->setFieldArg( 'delimiter', ',' );
		$field->setFieldArg( 'translatable', true );

		// Mock TemplateInForm
		$this->mockTemplateInForm->method( 'getTemplateName' )->willReturn( 'TestTemplate' );

		// Simulate values from query
		$values_from_query = [
			'TestField' => 'value1,value2'
		];

		// Call getCurrentValue()
		$val_modifier = null;
		$result = $field->getCurrentValue( $values_from_query, true, false, true, $val_modifier );

		// Assertions
		$this->assertEquals( 'value1,value2', $result, 'Concatenated values should match expected result.' );
	}

	public function testGetCurrentValue_WithAppending() {
		// Mock TemplateField
		$this->mockTemplateField->method( 'getFieldName' )->willReturn( 'TestField' );
		$this->mockTemplateField->mFieldName = 'TestField';

		// Create the PFFormField object
		$field = PFFormField::create( $this->mockTemplateField );
		$field->setFieldArg( 'delimiter', ',' );
		$field->setFieldArg( 'translatable', true );

		// Mock TemplateInForm
		$this->mockTemplateInForm->method( 'getTemplateName' )->willReturn( 'TestTemplate' );

		// Define test cases for appending
		$template_instance_query_values = [
			'TestField+' => 'AppendedValue'
		];

		// Appending scenario
		$resultAppend = $field->getCurrentValue( $template_instance_query_values, true, false, true );
		$this->assertEquals( 'AppendedValue', $resultAppend, 'Appended value should be handled correctly' );
	}

	public function testGetCurrentValue_WithPrepending() {
		// Mock TemplateField
		$this->mockTemplateField->method( 'getFieldName' )->willReturn( 'TestField' );
		$this->mockTemplateField->mFieldName = 'TestField';

		// Create the PFFormField object
		$field = PFFormField::create( $this->mockTemplateField );
		$field->setFieldArg( 'delimiter', ',' );
		$field->setFieldArg( 'translatable', true );

		// Mock TemplateInForm
		$this->mockTemplateInForm->method( 'getTemplateName' )->willReturn( 'TestTemplate' );

		// Define test cases for prepending
		$template_instance_query_values = [
			'TestField-' => 'PrependedValue'
		];

		// Prepending scenario
		$field->setFieldArg( 'field_name', 'TestField-' );
		$resultPrepend = $field->getCurrentValue( $template_instance_query_values, true, false, true );
		$this->assertEquals( 'PrependedValue', $resultPrepend, 'Prepended value should be handled correctly' );
	}
}
