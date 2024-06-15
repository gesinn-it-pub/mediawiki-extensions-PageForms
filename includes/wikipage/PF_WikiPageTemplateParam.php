<?php
/**
 * @author Yaron Koren
 * @file
 * @ingroup PF
 */

/**
 * Represents a single parameter (name and value) within a template call
 * in a wiki page.
 */
class PFWikiPageTemplateParam {
	private $mName;
	private $mValue;

	public function __construct( $name, $value ) {
		$this->mName = $name;
		$this->mValue = $value;
	}

	public function getName() {
		return $this->mName;
	}

	public function getValue() {
		return $this->mValue;
	}

	public function setValue( $value ) {
		$this->mValue = $value;
	}
}
