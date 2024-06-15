<?php
/**
 * @author Yaron Koren
 * @file
 * @ingroup PF
 */

/**
 * Represents a single template call within a wiki page.
 */
class PFWikiPageTemplate {
	private $mName;
	private $mParams = [];
	private $mAddUnhandledParams;

	public function __construct( $name, $addUnhandledParams ) {
		$this->mName = $name;
		$this->mAddUnhandledParams = $addUnhandledParams;
	}

	public function addParam( $paramName, $value ) {
		$this->mParams[] = new PFWikiPageTemplateParam( $paramName, $value );
	}

	public function addUnhandledParam( $paramName, $value ) {
		// See if there's already a value for this parameter, and
		// if it's blank, replace it.
		// This only happens if values are coming in from both the
		// page and the form submission, i.e. for #autoedit.
		foreach ( $this->mParams as $i => $param ) {
			if ( $param->getName() == $paramName ) {
				if ( $param->getValue() == '' ) {
					$this->mParams[$i]->setValue( $value );
				}
				return;
			}
		}

		// All other cases, probably.
		$this->addParam( $paramName, $value );
	}

	public function addUnhandledParams() {
		global $wgRequest;

		if ( !$this->mAddUnhandledParams ) {
			return;
		}

		$templateName = str_replace( ' ', '_', $this->mName );
		$prefix = '_unhandled_' . $templateName . '_';
		$prefixSize = strlen( $prefix );
		foreach ( $wgRequest->getValues() as $key => $value ) {
			if ( strpos( $key, $prefix ) === 0 ) {
				$paramName = urldecode( substr( $key, $prefixSize ) );
				$this->addUnhandledParam( $paramName, $value );
			}
		}
	}

	public function getName() {
		return $this->mName;
	}

	public function getParams() {
		return $this->mParams;
	}
}
