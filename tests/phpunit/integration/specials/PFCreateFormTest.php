<?php

use MediaWiki\MediaWikiServices;

/**
 * @covers \PFCreateForm
 *
 * @author gesinn-it-wam
 */
class PFCreateFormTest extends SpecialPageTestBase {

	use IntegrationTestHelpers;

	public function setUp(): void {
		parent::setUp();
		$this->requireLanguageCodeEn();
	}

	/**
	 * Create an instance of the special page being tested.
	 *
	 * @return SpecialPage
	 */
	protected function newSpecialPage() {
		// Return an instance of PFCreateForm
		return MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'CreateForm' );
	}

	public function testGet() {
		$createForm = $this->newSpecialPage();

		$createForm->execute( null );

		$output = $createForm->getOutput();
		$this->assertStringStartsWith( "Create a form", $output->getPageTitle() );
	}

	public function testForm() {
        $createForm = $this->newSpecialPage();
        $context = new RequestContext();
        $createForm->setContext( $context );

        $values = [
            'form_name' => 'TestForm',
            'template_1' => 'TestTemplate',
            'label_1' => 'Test Label',
            'allow_multiple_1' => true,
            'csrf' => '+\\',
            'wpSave' => ''
        ];

        foreach ( $values as $k => $v ) {
            $context->getRequest()->setVal( $k, $v );
        }

        // Execute the special page with the given values
        $createForm->execute( null );
        $output = $createForm->getOutput();

        // Use DOM and XPath to assert form structure and content
        $dom = new DOMDocument();
        @$dom->loadHTML( $output->mBodytext ); 
        $xpath = new DOMXPath( $dom );

		// Check that the form uses the 'post' method and has the correct action URL
		$form = $xpath->query( "//form[@id='editform' and @method='post' and @action='/index.php?title=Form:TestForm&action=submit']" );
		$this->assertNotNull( $form->item( 0 ), "Form not found or incorrect method/action" );

		// Check for the wpSave hidden field
		$wpSave = $xpath->query( "//input[@name='wpSave' and @type='hidden']" );
		$this->assertNotNull( $wpSave->item( 0 ), "Save button hidden field not found" );
		
		// Check for the template_1
		$this->assertStringContainsString( 'TestTemplate', $output->mBodytext, 'TestTemplate not found' );

		// Check for the JavaScript that auto-submits the form
		$this->assertStringContainsString( "document.editform.submit();", $output->mBodytext, "Form auto-submit script not found" );
    }
}
