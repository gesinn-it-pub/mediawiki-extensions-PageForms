<?php

namespace PF\Tests\Integration\JSONScript;

use ExtensionRegistry;
use SMW\Tests\Integration\JSONScript\JSONScriptTestCaseRunnerTest;

// Putting the following in JsonTestCaseScriptRunnerTest::setUpBeforeClass didn't work:
define( "TEST_NAMESPACE", 3000 );
$wgExtraNamespaces[TEST_NAMESPACE] = "Test_Namespace";

/**
 * @group PF
 * @group SMWExtension
 */
class JsonTestCaseScriptRunnerTest extends JSONScriptTestCaseRunnerTest {

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
