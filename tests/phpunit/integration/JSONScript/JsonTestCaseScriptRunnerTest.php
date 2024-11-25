<?php

namespace PF\Tests\Integration\JSONScript;

use ExtensionRegistry;
use Parser;
use PFDefaultForm;
use PFFormInputParserFunction;
use PFFormLink;
use PFFormRedLink;
use PFQueryFormLink;
use PFArrayMap;
use PFArrayMapTemplate;
use PFAutoEdit;
use PFAutoEditRating;
use PFTemplateParams;
use PFTemplateDisplay;
use MediaWiki\MediaWikiServices;
use SMW\Tests\Integration\JSONScript\JSONScriptTestCaseRunnerTest;

define( "TEST_NAMESPACE", 3000 );
$wgExtraNamespaces[TEST_NAMESPACE] = "Test_Namespace";

/**
 * @group PF
 * @group SMWExtension
 */
class JsonTestCaseScriptRunnerTest extends JSONScriptTestCaseRunnerTest {

    protected function setUp(): void {
        parent::setUp();

        // Register parser functions directly
        $parser = $this->getParser();
        $parser->setFunctionHook( 'default_form', [ PFDefaultForm::class, 'run' ] );
        $parser->setFunctionHook( 'forminput', [ PFFormInputParserFunction::class, 'run' ] );
        $parser->setFunctionHook( 'formlink', [ PFFormLink::class, 'run' ] );
        $parser->setFunctionHook( 'formredlink', [ PFFormRedLink::class, 'run' ] );
        $parser->setFunctionHook( 'queryformlink', [ PFQueryFormLink::class, 'run' ] );
        $parser->setFunctionHook( 'arraymap', [ PFArrayMap::class, 'run' ], Parser::SFH_OBJECT_ARGS );
        $parser->setFunctionHook( 'arraymaptemplate', [ PFArrayMapTemplate::class, 'run' ], Parser::SFH_OBJECT_ARGS );
        $parser->setFunctionHook( 'autoedit', [ PFAutoEdit::class, 'run' ] );
        $parser->setFunctionHook( 'autoedit_rating', [ PFAutoEditRating::class, 'run' ] );
        $parser->setFunctionHook( 'template_params', [ PFTemplateParams::class, 'run' ] );
        $parser->setFunctionHook( 'template_display', [ PFTemplateDisplay::class, 'run' ], Parser::SFH_OBJECT_ARGS );
    }

    protected function getParser(): Parser {
        // Assuming you can retrieve a Parser instance here
        return MediaWikiServices::getInstance()->getParser();
    }

	protected function getTestCaseLocation() {
		return __DIR__ . '/TestCases';
	}

	protected function getPermittedSettings() {
		return array_merge( parent::getPermittedSettings(), [
			'wgPageFormsAutocompleteOnAllChars',
			'wgAllowDisplayTitle',
			'wgRestrictDisplayTitle',
		] );
	}

	protected function getDependencyDefinitions() {
		return [
			'DisplayTitle' => static function ( $val, &$reason ) {
				if ( !ExtensionRegistry::getInstance()->isLoaded( 'DisplayTitle' ) ) {
					$reason = "Dependency: DisplayTitle as requirement for the test is not available!";
					return false;
				}
				return true;
			}
		];
	}
}
