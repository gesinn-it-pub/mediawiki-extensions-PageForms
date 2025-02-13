<?php

use MediaWiki\MediaWikiServices;

/**
 * @covers \PFCreateTemplate
 * @group Database
 *
 * @author gesinn-it-wam
 */
class PFCreateTemplateTest extends SpecialPageTestBase {

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
		// Return an instance of PFCreateTemplate
		return MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'CreateTemplate' );
	}

	public function testGetCreatePage() {
		$createTemplate = $this->newSpecialPage();

		$createTemplate->execute( null );

		$output = $createTemplate->getOutput();
		$this->assertStringStartsWith( "Create a template", $output->getPageTitle() );
	}

	public function testCreateTemplate() {
		$createTemplate = $this->newSpecialPage();
		$context = new RequestContext();
		$createTemplate->setContext( $context );
		$values = [
			"title" => "Special:CreateTemplate",
			"template_name" => "Thing",
			"category" => "Thing",
			"name_1" => "Name",
			"label_1" => "Name",
			"property_name_1" => "Name",
			"semantic_property_1" => "Foaf =>name",
			"delimiter_1" => ",",
			"name_starter" => "",
			"label_starter" => "",
			"property_name_starter" => "",
			"semantic_property_starter" => "Foaf =>homepage",
			"delimiter_starter" => ",",
			"semantic_property_aggregation" => "Foaf =>homepage",
			"aggregation_label" => "",
			"template_format" => "standard",
			"csrf" => "+\\",
			"wpSave" => ""
		];
		foreach ( $values as $k => $v ) {
			$context->getRequest()->setVal( $k, $v );
		}
		$createTemplate->execute( null );

		$output = $createTemplate->getOutput();
		$this->assertStringStartsWith( "Create a template", $output->getPageTitle() );

		$expected = <<<EOF
		window.onload = function() {
			document.editform.submit();
		}
		</script>
		EOF;

		if ( version_compare( MW_VERSION, '1.41', '>=' ) ) {
			$this->assertStringContainsString( '<form id="editform" name="editform" method="post" action="/index.php?title=Template:Thing&amp;action=submit">', $output->mBodytext );
			$this->assertStringContainsString( '<input type="hidden" value="&lt;noinclude&gt;&#10;{{#template_params:Name (property=Foaf =&gt;name)}}&#10;&lt;/noinclude&gt;&lt;includeonly&gt;{| class=&quot;wikitable&quot;&#10;! Name&#10;| [[Foaf =&gt;name::{{{Name|}}}]]&#10;|-&#10;! &#10;|{{#ask:[[Foaf =&gt;homepage::{{SUBJECTPAGENAME}}]]|format=list}}&#10;|}&#10;&#10;[[Category:Thing]]&#10;&lt;/includeonly&gt;&#10;" name="wpTextbox1">', $output->mBodytext );
			$this->assertStringContainsString( '<input type="hidden" value="ℳ𝒲♥𝓊𝓃𝒾𝒸ℴ𝒹ℯ" name="wpUnicodeCheck">', $output->mBodytext );
			$this->assertStringContainsString( '<input type="hidden" name="wpSummary">', $output->mBodytext );
			$this->assertStringContainsString( '<input type="hidden" value="+\" name="wpEditToken">', $output->mBodytext );
			$this->assertStringContainsString( '<input type="hidden" name="wpSave">', $output->mBodytext );
			$this->assertStringContainsString( '<input type="hidden" value="1" name="wpUltimateParam"></form>', $output->mBodytext );
		} else {
			$this->assertStringContainsString( '<form id="editform" name="editform" method="post" action="/index.php?title=Template:Thing&amp;action=submit"><input type="hidden" value="&lt;noinclude&gt;&#10;{{#template_params:Name (property=Foaf =&gt;name)}}&#10;&lt;/noinclude&gt;&lt;includeonly&gt;{| class=&quot;wikitable&quot;&#10;! Name&#10;| [[Foaf =&gt;name::{{{Name|}}}]]&#10;|-&#10;! &#10;|{{#ask:[[Foaf =&gt;homepage::{{SUBJECTPAGENAME}}]]|format=list}}&#10;|}&#10;&#10;[[Category:Thing]]&#10;&lt;/includeonly&gt;&#10;" name="wpTextbox1"/><input type="hidden" value="ℳ𝒲♥𝓊𝓃𝒾𝒸ℴ𝒹ℯ" name="wpUnicodeCheck"/><input type="hidden" name="wpSummary"/><input type="hidden" value="+\" name="wpEditToken"/><input type="hidden" name="wpSave"/><input type="hidden" value="1" name="wpUltimateParam"/></form>', $output->mBodytext );
		}

		// Load the HTML with DOMDocument and DOMXPath
		$dom = new DomDocument;
		@$dom->loadHTML( $output->mBodytext );
		$xpath = new DomXPath( $dom );

		// Check if the form exists with the correct action
		$form = $xpath->query( '//form[contains(@action,"Template:Thing")]' )->item( 0 );
		$this->assertNotNull( $form, 'Form element not found' );

		// Debugging: Check if wpTextbox1 input exists in the DOM
		$templateInput = $xpath->query( '//input[@name="wpTextbox1"]' )->item( 0 );
		$this->assertNotNull( $templateInput, 'Template input field not found' );

		// Check for wpSave field
		$wpSave = $xpath->query( '//input[@name="wpSave"]' )->item( 0 );
		$this->assertNotNull( $wpSave, 'wpSave input field not found' );

		// Check for script tag that submits the form
		$script = $xpath->query( '//script[contains(text(),"document.editform.submit();")]' )->item( 0 );
		$this->assertNotNull( $script, 'Script for form submission not found' );
	}
}
