<?php
/**
 * @file
 * @ingroup PF
 */

/**
 * @ingroup PFFormInput
 */
class PFRadioButtonInput extends PFEnumInput {

	public static function getName(): string {
		return 'radiobutton';
	}

	public static function getHTML( $cur_value, $input_name, $is_mandatory, $is_disabled, array $other_args ) {
		global $wgPageFormsTabIndex, $wgPageFormsFieldNum;

		if ( array_key_exists( 'possible_values', $other_args ) && ( count( $other_args['possible_values'] ) > 0 ) ) {
			$possible_values = $other_args['possible_values'];
		} elseif (
			array_key_exists( 'property_type', $other_args ) &&
			$other_args['property_type'] == '_boo'
		) {
			// If it's a Boolean property, display 'Yes' and 'No'
			// as the values.
			$possible_values = [
				PFUtils::getWordForYesOrNo( true ),
				PFUtils::getWordForYesOrNo( false ),
			];
		} else {
			$possible_values = [];
		}

		// Add a "None" value at the beginning, unless this is a
		// mandatory field and there's a current value in place (either
		// through a default value or because we're editing an existing
		// page).
		if ( !$is_mandatory || $cur_value === '' ) {
			array_unshift( $possible_values, '' );
		}

		// If $cur_value is an invalid value (not null, and not one
		// of the allowed options), set it to blank, so it can show
		// up as "None" (if "None" is one of the options).
		if ( $cur_value !== null && !in_array( $cur_value, $possible_values ) ) {
			$cur_value = '';
		}

		$text = "\n";
		$itemClass = 'radioButtonItem';
		if ( array_key_exists( 'class', $other_args ) ) {
			$itemClass .= ' ' . $other_args['class'];
		}

		foreach ( $possible_values as $originalValue => $value ) {
			$wgPageFormsTabIndex++;
			$wgPageFormsFieldNum++;
			$input_id = "input_$wgPageFormsFieldNum";

			$radiobutton_attrs = [
				'value' => $value,
				'id' => $input_id,
				'tabindex' => $wgPageFormsTabIndex,
				'data-original-value' => $originalValue
			];
			if ( array_key_exists( 'origName', $other_args ) ) {
				$radiobutton_attrs['origname'] = $other_args['origName'];
			}
			$isChecked = ( $cur_value == $value );
			if ( $is_disabled ) {
				$radiobutton_attrs['disabled'] = true;
			}
			if ( $value === '' ) {
				// blank/"None" value
				$label = wfMessage( 'pf_formedit_none' )->text();
			} elseif (
				array_key_exists( 'value_labels', $other_args ) &&
				is_string( $other_args['value_labels'] ) ) {
				$other_args['value_labels'] = json_decode( $other_args['value_labels'], true );
				$label = htmlspecialchars( $other_args['value_labels'][$value] );
			} elseif (
				array_key_exists( 'value_labels', $other_args ) &&
				is_array( $other_args['value_labels'] ) &&
				array_key_exists( $value, $other_args['value_labels'] )
			) {
				$label = htmlspecialchars( $other_args['value_labels'][$value] );
			} else {
				$label = $value;
			}

			$itemAttrs = [ 'class' => $itemClass ];
			$text .= "\t" . Html::rawElement( 'label', $itemAttrs,
				Html::radio( $input_name, $isChecked, $radiobutton_attrs ) .
				'&nbsp;' . $label ) . "\n";
		}

		$spanClass = 'radioButtonSpan';
		if ( array_key_exists( 'class', $other_args ) ) {
			$spanClass .= ' ' . $other_args['class'];
		}
		if ( $is_mandatory ) {
			$spanClass .= ' mandatoryFieldSpan';
		}

		$spanID = "span_$wgPageFormsFieldNum";

		// Do the 'show on select' handling.
		if ( array_key_exists( 'show on select', $other_args ) ) {
			$spanClass .= ' pfShowIfChecked';
			PFFormUtils::setShowOnSelect( $other_args['show on select'], $spanID );
		}
		$spanAttrs = [
			'id' => $spanID,
			'class' => $spanClass
		];
		$text = Html::rawElement( 'span', $spanAttrs, $text );

		return $text;
	}

	/**
	 * Returns the HTML code to be included in the output page for this input.
	 * @return string
	 */
	public function getHtmlText(): string {
		return self::getHTML(
			$this->mCurrentValue,
			$this->mInputName,
			$this->mIsMandatory,
			$this->mIsDisabled,
			$this->mOtherArgs
		);
	}
}
