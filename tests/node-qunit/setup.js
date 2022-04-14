if (!global.pf_tests_initialized) {
	const { TextEncoder, TextDecoder } = require('util');
	global.TextEncoder = TextEncoder;
	global.TextDecoder = TextDecoder;

	const jsdom = require('jsdom');
	global.jQuery = require('../../../../resources/lib/jquery/jquery.js')(new jsdom.JSDOM().window);

	global.mediaWiki = {};
	global.pageforms = {};
	require('../../libs/originalValueLookup.js');

	global.pf_tests_initialized = true;
}
