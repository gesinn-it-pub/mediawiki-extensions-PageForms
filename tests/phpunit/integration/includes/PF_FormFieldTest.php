<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers PFFormField
 *
 * @author gesinn-it-ilm
 */
class PFFormFieldTest extends TestCase {

	public function testCreateFormFieldWithEmptyValues() {
		// Mock the PFTemplateField class
		$mockTemplateField = $this->createMock( PFTemplateField::class );

		// Create the PFFormField object
		$formField = PFFormField::create( $mockTemplateField );

		// Verify the object is an instance of PFFormField
		$this->assertInstanceOf( PFFormField::class, $formField );
		$this->assertSame( $mockTemplateField, $formField->template_field );

		$this->assertNull( $formField->getInputType() );
		$this->assertFalse( $formField->isMandatory() );
		$this->assertFalse( $formField->isHidden() );
		$this->assertFalse( $formField->isRestricted() );
		$this->assertNull( $formField->getPossibleValues() );
		$this->assertFalse( $formField->getUseDisplayTitle() );
		$this->assertSame( [], $formField->getFieldArgs() );
	}

	public function testCreateFormFieldWithTemplateFieldProps() {
		// Mock the PFTemplateField class
		$mockTemplateField = $this->createMock( PFTemplateField::class );

		// Set expectations for the mocked methods or properties
		$mockTemplateField->method( 'getFieldName' )->willReturn( 'MockedFieldName' );
		$mockTemplateField->method( 'getLabel' )->willReturn( 'Mocked Label' );
		$mockTemplateField->method( 'getSemanticProperty' )->willReturn( 'MockedSemanticProperty' );
		$mockTemplateField->method( 'isList' )->willReturn( true );
		$mockTemplateField->method( 'getDelimiter' )->willReturn( ';' );
		$mockTemplateField->method( 'getDisplay' )->willReturn( 'MockedDisplay' );

		// Create the PFFormField object
		$formField = PFFormField::create( $mockTemplateField );

		// Verify the object is an instance of PFFormField
		$this->assertInstanceOf( PFFormField::class, $formField );

		// Assert that the template field is set correctly
		$this->assertSame( $mockTemplateField, $formField->template_field );

		// Assert that properties were set correctly based on the mocked PFTemplateField
		$this->assertSame( 'MockedFieldName', $formField->template_field->getFieldName() );
		$this->assertSame( $mockTemplateField, $formField->getTemplateField() );
		$this->assertSame( 'MockedSemanticProperty', $formField->template_field->getSemanticProperty() );
		$this->assertSame( ';', $formField->template_field->getDelimiter() );
		$this->assertSame( 'MockedDisplay', $formField->template_field->getDisplay() );
	}

	public function testDelimiterHandling() {
		// Mock the PFTemplateField class
		$mockTemplateFieldOne = $this->createMock( PFTemplateField::class );
		$mockTemplateFieldTwo = $this->createMock( PFTemplateField::class );
		$mockTemplateFieldOne->method( 'getDelimiter' )->willReturn( ',' );
		$mockTemplateFieldTwo->method( 'getDelimiter' )->willReturn( ';' );

		$mockTemplateFieldThree = $this->createMock( PFTemplateField::class );
		$mockTemplateFieldThree->method( 'getDelimiter' )->willReturn( '' );

		// Create the PFFormField objects
		$fieldOne = PFFormField::create( $mockTemplateFieldOne );
		$fieldTwo = PFFormField::create( $mockTemplateFieldTwo );
		$fieldThree = PFFormField::create( $mockTemplateFieldThree );

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
}
