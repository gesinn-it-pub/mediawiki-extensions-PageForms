<?php

define( 'PF_VERSION', '' );
define( 'SMW_NS_CONCEPT', 0 );
define( 'SMW_NS_PROPERTY', 0 );
define( 'SMW_OUTPUT_WIKI', 0 );

require_once '../SemanticMediaWiki/src/RequestOptions.php';
class_alias( \SMW\RequestOptions::class, 'SMWRequestOptions' );

require_once '../SemanticMediaWiki/src/DataValueFactory.php';
class_alias( \SMW\DataValueFactory::class, 'SMWDataValueFactory' );

require_once '../SemanticMediaWiki/src/Query/PrintRequest.php';
class_alias( \SMW\Query\PrintRequest::class, 'SMWPrintRequest' );

require_once '../SemanticMediaWiki/src/Query/Language/Description.php';
require_once '../SemanticMediaWiki/src/Query/Language/ConceptDescription.php';
class_alias( \SMW\Query\Language\ConceptDescription::class, 'SMWConceptDescription' );
