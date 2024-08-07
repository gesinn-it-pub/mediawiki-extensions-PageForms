<?php
/**
 * Shows list of all forms on the site.
 *
 * @author Yaron Koren
 * @file
 * @ingroup PF
 */

/**
 * @ingroup PFSpecialPages
 */
class PFForms extends QueryPage {

	public function __construct( $name = 'Forms' ) {
		parent::__construct( $name );
	}

	public function isExpensive() {
		return false;
	}

	public function isSyndicated() {
		return false;
	}

	public function getPageHeader() {
		$header = Html::element( 'p', null, $this->msg( 'pf_forms_docu' )->text() );
		return $header;
	}

	public function getPageFooter() {
	}

	public function getQueryInfo() {
		return [
			'tables' => [ 'page' ],
			'fields' => [ 'page_title AS title', 'page_title AS value' ],
			'conds' => [ 'page_namespace' => PF_NS_FORM, 'page_is_redirect' => 0 ]
		];
	}

	public function sortDescending() {
		return false;
	}

	public function formatResult( $skin, $result ) {
		$pageName = $result->value;

		if ( PFUtils::ignoreFormName( $pageName ) ) {
			return null;
		}

		$title = Title::makeTitle( PF_NS_FORM, $pageName );
		return $this->getLinkRenderer()->makeKnownLink( $title, htmlspecialchars( $title->getText() ) );
	}

	protected function getGroupName() {
		return 'pf_group';
	}
}
