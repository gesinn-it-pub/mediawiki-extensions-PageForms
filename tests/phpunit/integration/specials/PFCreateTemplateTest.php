<?php

/**
 * @covers \PFCreateTemplate
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
		return new PFCreateTemplate();
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

		$this->assertStringContainsString( '<form id="editform" name="editform" method="post" action="/index.php?title=Template:Thing&amp;action=submit"><input type="hidden" value="&lt;noinclude&gt;&#10;{{#template_params:Name (property=Foaf =&gt;name)}}&#10;&lt;/noinclude&gt;&lt;includeonly&gt;{| class=&quot;wikitable&quot;&#10;! Name&#10;| [[Foaf =&gt;name::{{{Name|}}}]]&#10;|-&#10;! &#10;|{{#ask:[[Foaf =&gt;homepage::{{SUBJECTPAGENAME}}]]|format=list}}&#10;|}&#10;&#10;[[Category:Thing]]&#10;&lt;/includeonly&gt;&#10;" name="wpTextbox1"/><input type="hidden" value="ℳ𝒲♥𝓊𝓃𝒾𝒸ℴ𝒹ℯ" name="wpUnicodeCheck"/><input type="hidden" name="wpSummary"/><input type="hidden" value="+\" name="wpEditToken"/><input type="hidden" name="wpSave"/><input type="hidden" value="1" name="wpUltimateParam"/></form>', $output->mBodytext );
	}
}
