<?php

/**
 * Integration test class for the PFRunQuery special page.
 * This uses MediaWikiIntegrationTestCase, designed for testing in the MediaWiki framework.
 *
 * @covers \PFRunQuery
 * 
 * @group Database
 *
 * @author gesinn-it-ilm
 */
class PFRunQueryTest extends MediaWikiIntegrationTestCase {

    protected function setUp(): void {
        parent::setUp();

        if ( !defined( 'PF_NS_FORM' ) ) {
            define( 'PF_NS_FORM', 106 );
        }
    }

    public function testExecuteWithoutQuery() {
        // Create a special page instance
        $specialPage = new PFRunQuery();
        
        // Create a faux request object with no form query
        $request = new FauxRequest(
            [
                'form' => ''
            ],
            true
        );

        // Create a RequestContext and set the faux request
        $context = new RequestContext();
        $context->setRequest( $request );

        // Inject the context into the special page
        $specialPage->setContext( $context );

        // Capture the output
        $outputPage = $context->getOutput();
        
        // Execute the special page
        $specialPage->execute( '' );

        // Capture the output after execution
        $output = $outputPage->getHTML();
        
        // Check if the output contains an error message
        $this->assertStringContainsString( 'error', $output );
    }

    public function testExecuteWithNonexistentForm() {
        // Create a special page instance
        $specialPage = new PFRunQuery();

        // Create a request object with a non-existent form name
        $request = new FauxRequest(
            [
                'form' => 'NonExistentForm'
            ]
        );

        // Create a RequestContext and set the faux request
        $context = new RequestContext();
        $context->setRequest( $request );

        // Inject the context into the special page
        $specialPage->setContext( $context );

        // Capture the output
        $outputPage = $context->getOutput();
        
        // Execute the special page
        $specialPage->execute( '' );

        // Capture the output after execution
        $output = $outputPage->getHTML();

        // Check if the output contains part of the error message
        $this->assertStringContainsString(
            'Error: No form was found on page',
            $output
        );
    }

    public function testExecuteWithValidForm() {
        // Create a special page instance
        $specialPage = new PFRunQuery();

        // Mock the request with a valid form
        $request = new FauxRequest(
            [
                'form' => 'ValidForm'
            ]
        );

        $context = new RequestContext();
        $context->setRequest( $request );

        // Mock form title and form content
        $formTitle = Title::newFromText( 'ValidForm', PF_NS_FORM );
        $this->insertPage( $formTitle, 'Form content here' );

        // Inject the context into the special page
        $specialPage->setContext( $context );

        // Capture the output
        $outputPage = $context->getOutput();

        // Execute the special page
        $specialPage->execute( 'ValidForm' );

        // Capture the output after execution
        $output = $outputPage->getHTML();

        $this->assertStringContainsString( 'Form content here', $output );
    }

    public function testSubmitFormQuery() {
        // Create a special page instance
        $specialPage = new PFRunQuery();

        // Mock the request with valid form data and a _run parameter
        $request = new FauxRequest(
            [
                'form' => 'ValidForm',
                '_run' => 'true',
                'wpTextbox1' => 'Some content for query'
            ]
        );

        $context = new RequestContext();
        $context->setRequest( $request );

        // Inject the context into the special page
        $specialPage->setContext( $context );

        // Mock form title and form content
        $formTitle = Title::newFromText( 'ValidForm', PF_NS_FORM );
        $this->insertPage( $formTitle, 'Form content here' );

        // Capture the output
        $outputPage = $context->getOutput();

        // Execute the special page
        $specialPage->execute( 'ValidForm' );

        // Capture the output after execution
        $output = $outputPage->getHTML();

        $this->assertStringContainsString( 'Some content for query', $output );
    }
}
