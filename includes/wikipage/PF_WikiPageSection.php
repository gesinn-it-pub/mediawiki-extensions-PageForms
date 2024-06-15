<?php
/**
 * @author Yaron Koren
 * @file
 * @ingroup PF
 */

/**
 * Represents a section (header and contents) in a wiki page.
 */
class PFWikiPageSection {
	private $mHeader;
	private $mHeaderLevel;
	private $mText;
	private $mHideIfEmpty;

	public function __construct( $sectionName, $headerLevel, $sectionText, $sectionOptions ) {
		$this->mHeader      = $sectionName;
		$this->mHeaderLevel = $headerLevel;
		$this->mText        = $sectionText;
		$this->mHideIfEmpty = $sectionOptions['hideIfEmpty'];
	}

	/**
	 * @return bool
	 */
	public function isHideIfEmpty() {
		return $this->mHideIfEmpty;
	}

	public function getHeader() {
		return $this->mHeader;
	}

	public function getHeaderLevel() {
		return $this->mHeaderLevel;
	}

	public function getText() {
		return $this->mText;
	}
}
