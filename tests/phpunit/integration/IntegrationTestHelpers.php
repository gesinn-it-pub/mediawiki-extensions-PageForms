<?php

trait IntegrationTestHelpers {

	public function requireLanguageCodeEn(): void {
		if ( $GLOBALS['wgLanguageCode'] !== 'en' ) {
			$this->markTestSkipped( 'PageForms integration testing currently only supported for wgLanguageCode "en" !' );
		}
	}

}
