<?php

use OOUI\BlankTheme;

/**
 * @covers \PFRadioButtonInput
 * @group Database
 *
 * @author Mark A. Hershberger <mah@nichework.com>
 */
class PFRadioButtonInputTest extends MediaWikiIntegrationTestCase {

	/**
	 * Set up the environment
	 */
	protected function setUp(): void {
		\OOUI\Theme::setSingleton( new BlankTheme() );

		parent::setUp();
	}

	/**
	 * Formats a single radio button's HTML representation for test validation.
	 *
	 * Purpose:
	 * This helper method generates the expected HTML structure of a radio button,
	 * which is used in test cases to verify the output of the `PFRadioButtonInput::getHTML` method.
	 * By centralizing the HTML format in one method, it ensures consistency across multiple test scenarios.
	 *
	 * @param string $name The name attribute for the radio button input (usually prefixed).
	 * @param string $value The value attribute of the radio button.
	 * @param string|null $label The text label displayed next to the radio button. Defaults to the value if not specified.
	 * @param string|null $checked Adds the 'checked' attribute if the radio button is selected.
	 * @param string|null $class Additional CSS class(es) to add to the `<label>` element.
	 * @param string|null $append Additional attributes or content appended to the input element.
	 * @param string|null $disabled Adds the 'disabled' attribute if the radio button is disabled.
	 * @param string|null $originalValue The original value for data tracking, added as a 'data-original-value' attribute.
	 *
	 * @return string The formatted HTML string for the radio button and label.
	 */
	private function radioButtonFormat(
		$name, $value, $label = null, $checked = null, $class = null,
		$append = null, $disabled = null, $originalValue = null
	) {
		return "\t" . sprintf(
			'<label class="radioButtonItem%s">'
			. '<input id="input_\d+" tabindex="\d+" data-original-value="%s"%s%s%s type="radio" value="%s" '
			. 'name="TestTemplate123\[%s\]"/>&nbsp;%s</label>',
			( $class !== null ? " $class" : '' ),
			( $originalValue !== null ? $originalValue : '\d' ),
			( $append !== null ? " $append" : '' ),
			( $disabled !== null ? ' disabled=""' : '' ),
			( $checked !== null ? ' checked=""' : '' ),
			$value, $name,
			( $label !== null ? $label : $value )
		) . "\n";
	}

	/**
	 * Test method for validating the HTML output of radio button input elements.
	 *
	 * Purpose:
	 * This test ensures that the `PFRadioButtonInput::getHTML` method generates the correct HTML output for radio buttons.
	 * It leverages a data provider (`radioButtonDataProvider`) to supply various test cases, checking different configurations
	 * of the radio button (e.g., labels, values, default selections, etc.). The method compares the generated HTML against
	 * the expected output to confirm correctness.
	 *
	 * Test Behavior:
	 * - The test executes `PFRadioButtonInput::getHTML` with different argument setups, including dynamic values like
	 *   the radio button's name, value, label, and other attributes.
	 * - The test checks that the generated HTML matches the expected HTML for each case.
	 *
	 * @dataProvider radioButtonDataProvider
	 * @param array $setup An associative array containing the test setup values for the radio button generation.
	 * @param array $expected An associative array containing the expected HTML output for comparison.
	 */
	public function testRadioButtons( $setup, $expected ) {
		if ( version_compare( MW_VERSION, '1.41', '>=' ) ) {
			$this->markTestSkipped( 'JSONScript tests covers Radiobutton functionality. Consider removing this test completely' );
		}

		$args = $setup['args'];
		$args[1] = "TestTemplate123[{$args[1]}]";
		$result = call_user_func_array(
			[ 'PFRadioButtonInput', 'getHTML' ], $args
		);
		if ( version_compare( MW_VERSION, '1.40', '<' ) ) {
			$this->assertRegexp(
				'#' . $expected['expected_html'] . '#',
				$result,
				'asserts that getHTML() returns the correct HTML text'
			);
		} else {
			$this->assertMatchesRegularExpression(
				'#' . $expected['expected_html'] . '#',
				$result,
				'asserts that getHTML() returns the correct HTML text'
			);
		}
	}

	/**
	 * Data provider method for supplying test cases for radio button input generation.
	 *
	 * Purpose:
	 * This method generates multiple sets of test data used by the `testRadioButtons` function. Each data set includes the
	 * parameters required to test the HTML generation of radio button inputs in different scenarios. The data is structured as
	 * an array of arrays, where each sub-array contains:
	 * - **args**: A set of arguments passed to the `PFRadioButtonInput::getHTML` method.
	 * - **expected_html**: The expected HTML output that should match the result of the `getHTML` method for the provided `args`.
	 *
	 * The `args` array in each test case includes the following parameters:
	 * - `$cur_value`: The current value of the radio button, representing the initially selected option.
	 * - `$input_name`: The name of the radio button input field, used for grouping multiple radio buttons together.
	 * - `$is_mandatory`: A boolean indicating whether the field is mandatory (i.e., required to be selected).
	 * - `$is_disabled`: A boolean indicating whether the radio button should be disabled.
	 * - `$other_args`: An associative array containing additional arguments that control the behavior and appearance of the radio buttons.
	 *     - **'possible_values'**: An array of possible values for the radio buttons.
	 *     - **'property_type'**: A string specifying the type of property (e.g., `'_boo'` for boolean).
	 *     - **'value_labels'**: An array of custom labels for the radio button values.
	 *     - **'show on select'**: A configuration for elements that should be shown when a specific option is selected.
	 *     - **'class'**: An optional CSS class for styling the radio buttons and labels.
	 *
	 * @return array The data provider array, where each element contains a set of arguments (`args`) and the expected HTML (`expected_html`).
	 */
	public function radioButtonDataProvider() {
		$provider = [];
		$label = "froot_loops";

		// data set #0 radiobutton definition without other parameters
		//
		// FIXME: This seems like it shouldn't be possible
		$provider[] = [ [
			'args' => [ 999, $label, false, false, [] ],
		], [
			'expected_html' => '<span id="span_\d+" '
			. 'class="radioButtonSpan">' . "\n"

			. $this->radioButtonFormat( $label, '', 'None', 'checked' )

			. "</span>"
		] ];

		// data set #1 form definition with only 'property_type' => '_boo'
		$provider[] = [ [
			'args' => [ 999, $label, false, false, [
				'property_type' => '_boo'
			] ] ], [
				'expected_html' => '<span id="span_\d+" '
				. 'class="radioButtonSpan">' . "\n"

				. $this->radioButtonFormat( $label, '', 'None', 'checked' )
				. $this->radioButtonFormat( $label, 'Yes' )
				. $this->radioButtonFormat( $label, 'No' )

				. "</span>"
			] ];

		// data set #2 form definition with only 'possible_values' =>
		// [ 'one', 'deux', 'drei' ]
		$provider[] = [ [
			'args' => [ 999, $label, false, false, [
				'possible_values' => [ 'one', 'deux', 'drei' ]
			] ] ], [
				'expected_html' => '<span id="span_\d+" '
				. 'class="radioButtonSpan">' . "\n"

				. $this->radioButtonFormat( $label, '', 'None', 'checked' )
				. $this->radioButtonFormat( $label, 'one' )
				. $this->radioButtonFormat( $label, 'deux' )
				. $this->radioButtonFormat( $label, 'drei' )

				. "</span>"
				] ];

		// data set #3 - if this is a mandatory field,
		// make sure mandatoryFieldSpan class is added.
		$provider[] = [ [
			'args' => [
				999, $label, true, false, [
					'possible_values' => [ 'one', 'deux', 'drei' ]
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

					. $this->radioButtonFormat( $label, 'one' )
					. $this->radioButtonFormat( $label, 'deux' )
					. $this->radioButtonFormat( $label, 'drei' )

					. "</span>"
				] ];

		// data set #4
		$provider[] = [ [
			'args' => [
				'drei', $label, true, false, [
					'possible_values' => [ 'one', 'deux', 'drei' ]
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

					. $this->radioButtonFormat( $label, 'one' )
					. $this->radioButtonFormat( $label, 'deux' )
					. $this->radioButtonFormat(
						$label, 'drei', null, "checked"
					)

					. "</span>"
				] ];

		// data set #5 - if null is the current value provided on a
		// mandatory field, none of the options should be selected.
		$provider[] = [ [
			'args' => [
				null, $label, true, false, [
					'possible_values' => [ 'one', 'deux', 'drei' ]
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

					. $this->radioButtonFormat( $label, 'one' )
					. $this->radioButtonFormat( $label, 'deux' )
					. $this->radioButtonFormat( $label, 'drei' )

					. "</span>"
				] ];

		// data set #6 - if null is the current value provided on a
		// non-mandatory field, make sure 'None' is selected.
		$provider[] = [ [
			'args' => [
				null, $label, false, false, [
					'possible_values' => [ 'one', 'deux', 'drei' ]
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan">' . "\n"

					. $this->radioButtonFormat( $label, '', 'None', 'checked' )
					. $this->radioButtonFormat( $label, 'one' )
					. $this->radioButtonFormat( $label, 'deux' )
					. $this->radioButtonFormat( $label, 'drei' )

					. "</span>"
				] ];

		// data set #7 - if null is the current value provided on a
		// _boo, make sure 'None' is available.
		$provider[] = [ [
			'args' => [
				null, $label, false, false, [
					'property_type' => '_boo'
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan">' . "\n"

					. $this->radioButtonFormat( $label, '', 'None', 'checked' )
					. $this->radioButtonFormat( $label, 'Yes' )
					. $this->radioButtonFormat( $label, 'No' )

					. "</span>",
				] ];

		// data set #8 Add CSS class(es)
		$provider[] = [ [
			'args' => [
				999, $label, false, false, [
					"possible_values" => [ "one", "deux", "drei" ],
					'class' => 'testME'
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan testME">' . "\n"

					. $this->radioButtonFormat( $label, '', 'None', 'checked', 'testME' )
					. $this->radioButtonFormat( $label, 'one', null, null, 'testME' )
					. $this->radioButtonFormat( $label, 'deux', null, null, 'testME' )
					. $this->radioButtonFormat( $label, 'drei', null, null, 'testME' )

					. "</span>"
				] ];

		// data set #9 origName attribute
		$provider[] = [ [
			'args' => [
				999, $label, false, false, [
					'property_type' => '_boo',
					'origName' => 'testME'
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan">' . "\n"

					. $this->radioButtonFormat(
						$label, '', 'None', 'checked', null, 'origname="testME"'
					)
					. $this->radioButtonFormat(
						$label, 'Yes', null, null, null, 'origname="testME"'
					)
					. $this->radioButtonFormat(
						$label, 'No', null, null, null, 'origname="testME"'
					)

					. "</span>"
				] ];

		// data set #10 is_disabled is true
		// FIXME: I can see an argument for using None here, but,
		// still seems wonky.
		$provider[] = [ [
			'args' => [
				999, $label, false, true, [
					'possible_values' => [ 'Yes', 'No' ],
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan">' . "\n"

					. $this->radioButtonFormat(
						$label, '', 'None', 'checked', null, null, true
					)
					. $this->radioButtonFormat(
						$label, 'Yes', null, null, null, null, true
					)
					. $this->radioButtonFormat(
						$label, 'No', null, null, null, null, true
					)

					. "</span>"
				] ];

		// data set #11 is_disabled is true and is_mandatory is true
		// FIXME: shouldn't this fail instead of forcing true?
		$provider[] = [ [
			'args' => [
				999, $label, true, true, [
					'property_type' => '_boo',
					'origName' => 'testME'
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

					. $this->radioButtonFormat(
						$label, 'Yes', null, null, null,
						'origname="testME"', true
					)
					. $this->radioButtonFormat(
						$label, 'No', null, null, null, 'origname="testME"',
						true
					)

					. "</span>"
				] ];

		// data set #12 - Ensure value_labels are used when provided.
		$provider[] = [ [
			'args' => [
				null, $label, true, false, [
					'possible_values' => [ 'one', 'deux', 'drei' ],
					'value_labels' => [
						'deux' => 'two', 'drei' => 'three'
					] ] ] ], [
						'expected_html' => '<span id="span_\d+" '
						. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

						. $this->radioButtonFormat( $label, 'one' )
						. $this->radioButtonFormat( $label, 'deux', 'two' )
						. $this->radioButtonFormat( $label, 'drei', 'three' )

						. "</span>"
					] ];

		// data set #13 - Ensure value_labels are escaped properly
		$provider[] = [ [
			'args' => [
				null, $label, true, false, [
					'possible_values' => [ 'one', 'deux', 'drei' ],
					'value_labels' => [
						'deux' => '&2&', 'drei' => '<3>'
					] ] ] ], [
						'expected_html' => '<span id="span_\d+" '
						. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

						. $this->radioButtonFormat( $label, 'one' )
						. $this->radioButtonFormat( $label, 'deux', '&amp;2&amp;' )
						. $this->radioButtonFormat( $label, 'drei', '&lt;3&gt;' )

						. "</span>"
					] ];

		// data set #14 - Show on Select handling
		// FIXME: We're only dealing with the CSS class here
		$provider[] = [ [
			'args' => [
				null, $label, true, false, [
					'show on select' => [],
					'property_type' => '_boo'
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan mandatoryFieldSpan '
					. 'pfShowIfChecked">' . "\n"

					. $this->radioButtonFormat( $label, 'Yes' )
					. $this->radioButtonFormat( $label, 'No' )

					. "</span>"
				] ];

		// data set #15 - mandatory boolean with default value
		$provider[] = [ [
			'args' => [
				'No', $label, true, false, [
					'property_type' => '_boo'
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan mandatoryFieldSpan">'
					. "\n"

					. $this->radioButtonFormat( $label, 'Yes' )
					. $this->radioButtonFormat( $label, 'No', null, 'checked' )

					. "</span>"
				] ];

		// data set #16 - original value data attribute is set
		$provider[] = [ [
			'args' => [
				'No', $label, true, false, [
					'possible_values' => [ 'eins' => 'one', 'zwei' => 'deux', 'drei' => 'drei' ],
				] ] ], [
					'expected_html' => '<span id="span_\d+" '
					. 'class="radioButtonSpan mandatoryFieldSpan">'
					. "\n"

					. $this->radioButtonFormat( $label, 'one', null, null, null, null, null, 'eins' )
					. $this->radioButtonFormat( $label, 'deux', 'deux', null, null, null, null, 'zwei' )
					. $this->radioButtonFormat( $label, 'drei', 'drei', null, null, null, null, 'drei' )

					. "</span>"
				] ];

		return $provider;
	}

	/**
	 * @dataProvider radioButtomFromWikitextDataProvider
	 */
	public function testRadioButtonsFromWikitext( $setup, $expected ) {
		if ( version_compare( MW_VERSION, '1.41', '>=' ) ) {
			$this->markTestSkipped( 'JSONScript tests covers Radiobutton functionality. Consider removing this test completely' );
		}
		if ( !isset( $expected['skip'] ) ) {
			global $wgPageFormsFormPrinter, $wgOut;

			$wgOut->getContext()->setTitle( $this->getTitle() );

			if ( isset( $setup['form_definition'] ) ) {
				// We have to specify a template name
				$form_definition = "{{{for template|TestTemplate123}}}\n{$setup['form_definition']}\n{{{end template}}}\n{{{standard input|save}}}";
				[ $form_text, $page_text, $form_page_title, $generated_page_name ]
					= $wgPageFormsFormPrinter->formHTML(
						$form_definition, true, false, null, null,
						'TestStringForFormPageTitle', null,
						false, false, false, [], self::getTestUser()->getUser()
					);
			} else {
				$this->markTestSkipped( "No form to test!" );
				return;
			}

			// use assertMatchesRegularExpression instead of deprecated assertRegexp in MW higher then 1.39
			if ( isset( $expected['expected_form_text'] ) && version_compare( MW_VERSION, '1.40', '<' ) ) {
				$this->assertRegexp(
					'#' . $expected['expected_form_text'] . '#',
					$form_text,
					'asserts that formHTML() returns the correct HTML text for the form'
				);
			} elseif ( isset( $expected['expected_form_text'] ) ) {
				$this->assertMatchesRegularExpression(
					'#' . $expected['expected_form_text'] . '#',
					$form_text,
					'asserts that formHTML() returns the correct HTML text for the form'
				);
			}
			if ( isset( $expected['expected_page_text'] ) && version_compare( MW_VERSION, '1.40', '<' ) ) {
				$this->assertRegexp(
					'#' . $expected['expected_page_text'] . '#',
					$page_text,
					'assert that formHTML() returns the correct text for the page created'
				);
			} elseif ( isset( $expected['expected_page_text'] ) ) {
				$this->assertMatchesRegularExpression(
					'#' . $expected['expected_page_text'] . '#',
					$page_text,
					'assert that formHTML() returns the correct text for the page created'
				);
			}
			if (
				!isset( $expected['expected_form_text'] ) &&
				!isset( $expected['expected_page_text'] )
			) {
				$this->markTestSkipped( "No results to check!" );
			}
		} else {
			$this->markTestSkipped( "Skipping: $expected[skip]" );
		}
	}

	/**
	 * Data provider method
	 */
	public function radioButtomFromWikitextDataProvider() {
		$provider = [];

		$label = "field_radiobutton";

		/**
		 * data set #0 radiobutton definition without other parameters
		 *
		 * FIXME: This seems like it shouldn't be possible
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton}}}"
		], [
			'expected_form_text' => $this->radioButtonFormat( $label, '', 'None', 'checked' )
		] ];

		/**
		 * data set #1 form definition with only 'property_type' => '_boo'
		 */
		$provider[] = [ [
		], [
			'expected_form_test' =>
			$this->radioButtonFormat( $label, '', 'None', 'checked' )
			. $this->radioButtonFormat( $label, 'Yes' )
			. $this->radioButtonFormat( $label, 'No' ),
			'skip' => 'How to do SMW?'
		] ];

		/**
		 * data set #2 form definition with only 'possible_values' =>
		 *             [ 'one', 'deux', 'drei' ]
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label| input type=radiobutton| values=one, "
			. "deux, drei}}}"
		], [
			'expected_form_test' =>
			$this->radioButtonFormat( $label, '', 'None', 'checked' )
			. $this->radioButtonFormat( $label, 'one' )
			. $this->radioButtonFormat( $label, 'deux' )
			. $this->radioButtonFormat( $label, 'drei' )
		] ];

		/**
		 * data set #3 - mandatory field
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label| input type=radiobutton| values=one, "
			. "deux, drei|mandatory}}}"
		], [
			'expected_form_text' => $this->radioButtonFormat(
				$label, 'one', null, 'checked'
			)
			. $this->radioButtonFormat( $label, 'deux' )
			. $this->radioButtonFormat( $label, 'drei' ),
			'skip' => 'Mandatory does not seem to work, see also #16 in this set'
		] ];

		/**
		 * data set #4 - mandatory field with default value
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label| input type=radiobutton| values=one, "
			. "deux, drei|mandatory|default=drei}}}"
		], [
			'expected_form_text'  => '<span id="span_\d+" '
			. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

			. $this->radioButtonFormat( $label, 'one' )
			. $this->radioButtonFormat( $label, 'deux' )
			. $this->radioButtonFormat(
				$label, 'drei', null, "checked"
			)

			. "</span>",
			'skip' => "This should pass, but doesn't see 'code, default value' above"
		] ];

		/*
		 * data set #5 - if null is the current value provided on a
		 *               mandatory field, make sure nothing is selected
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei|mandatory}}}"
		], [
			'expected_form_text' => $this->radioButtonFormat( $label, 'one' )
			. $this->radioButtonFormat( $label, 'deux' )
			. $this->radioButtonFormat( $label, 'drei' ),
			'skip' => 'Works with parameters (see above), but wikitext parsing fails'
		] ];

		/**
		 * data set #6 - if null is the current value provided on a
		 *               non-mandatory field, make sure 'None' is
		 *               selected.
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei}}}"
		], [
			'expected_form_text' => $this->radioButtonFormat( $label, '', 'None', 'checked' )
			. $this->radioButtonFormat( $label, 'one' )
			. $this->radioButtonFormat( $label, 'deux' )
			. $this->radioButtonFormat( $label, 'drei' )
		] ];

		/**
		 * data set #7 - if null is the current value provided on a
		 *               mandatory boolean field, make sure 'None' is
		 *               not available.
		 */
		$provider[] = [ [
		], [
			'expected_form_text' => $this->radioButtonFormat( $label, 'one' )
			. $this->radioButtonFormat( $label, 'deux' )
			. $this->radioButtonFormat( $label, 'drei' ),
			'skip' => 'no SMW'
		] ];

		/**
		 * data set #8 - Add CSS if specified
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei|class=testME}}}"
		], [
			'expected_form_text' =>
			$this->radioButtonFormat( $label, '', 'None', 'checked', 'testME' )
			. $this->radioButtonFormat( $label, 'one', null, null, 'testME' )
			. $this->radioButtonFormat( $label, 'deux', null, null, 'testME' )
			. $this->radioButtonFormat( $label, 'drei', null, null, 'testME' )
		] ];

		/**
		 * data set #9 - No tests for origName in wikitext yet
		 */
		$provider[] = [ [], [
			'skip' => 'No tests for origName in wikitext yet'
		] ];

		/**
		 * data set #10 - restricted
		 */
		$provider[] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei|restricted}}}"
		], [
			'expected_form_text' =>
			$this->radioButtonFormat( $label, '', 'None', 'checked', null, null, true )
			. $this->radioButtonFormat( $label, 'one', null, null, null, null, true )
			. $this->radioButtonFormat( $label, 'deux', null, null, null, null, true )
			. $this->radioButtonFormat( $label, 'drei', null, null, null, null, true )
		] ];

		/**
		 * restricted, but mandatory
		 *
		 * FIXME: This should flag permission denied for the whole form
		 */
		$provider["wikitext, restricted, mandatory"] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei|restricted|mandatory}}}"
		], [
			'expected_form_text' => '<span id="span_\d+" '
			. 'class="radioButtonSpan mandatoryFieldSpan">' . "\n"

			. $this->radioButtonFormat( $label, 'one', null, null, null, null, true )
			. $this->radioButtonFormat( $label, 'deux', null, null, null, null, true )
			. $this->radioButtonFormat( $label, 'drei', null, null, null, null, true )

			. "</span>"
		] ];

		/**
		 * Ensure value labels are used when provided
		 */
		$provider["wikitext, value labels"] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei}}}"
		], [
			'skip' => "How to do this in wikitext?"
		] ];

		/**
		 * Ensure proper escaping for labels
		 */
		$provider["wikitext, value labels, escaping"] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei|label=deux=>&2&;drei=><3>}}}"
		], [
			'expected_form_text' => $this->radioButtonFormat( $label, '', 'None', 'checked' )
			. $this->radioButtonFormat( $label, 'one' )
			. $this->radioButtonFormat( $label, 'deux', '&amp;2&amp;' )
			. $this->radioButtonFormat( $label, 'drei', '&lt;3&gt;' ),
			'skip' => 'This looks wrong, but I need to understand the code'
		] ];

		/**
		 * Show on select handling
		 *
		 * FIXME: This is only the CSS classes
		 */
		$provider["wikitext, show on select"] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|values=one,"
			. "deux,drei|show on select=one=>blah}}}"
		], [
			'expected_form_text' => '<span id="span_\d+" class="radioButtonSpan '
			. 'pfShowIfChecked">' . "\n"
			. $this->radioButtonFormat( $label, '', 'None', 'checked' )
			. $this->radioButtonFormat( $label, 'one' )
			. $this->radioButtonFormat( $label, 'deux' )
			. $this->radioButtonFormat( $label, 'drei' ),
		] ];

		/**
		 * https://www.mediawiki.org/wiki/Extension:Page_Forms/Input_types#radiobutton
		 *
		 * By default, the first radiobutton value is "None", which
		 * lets the user choose a blank value. To prevent "None" from
		 * showing up, you must make the field "mandatory", as well as
		 * making one of the allowed values the field's "default="
		 * value.
		 */
		$provider['wikitext, smw no none'] = [ [
			'form_definition' => "{{{field|$label|input type=radiobutton|"
		], [
			'skip' => "No SMW test yet!",
			'expected_form_text' => $this->radioButtonFormat( $label, 'Yes' )
					. $this->radioButtonFormat( $label, 'No', null, 'checked' )
		] ];

		return $provider;
	}

	/**
	 * Returns a mock Title for test
	 * @return Title
	 */
	private function getTitle() {
		$mockTitle = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$mockTitle->expects( $this->any() )
			->method( 'getDBkey' )
			->willReturn( 'Sometitle' );

		$mockTitle->expects( $this->any() )
			->method( 'getNamespace' )
			->willReturn( PF_NS_FORM );

		return $mockTitle;
	}
}
