if (!global.tests_initialized) {
	const { TextEncoder, TextDecoder } = require('util');
	global.TextEncoder = TextEncoder;
	global.TextDecoder = TextDecoder;

	const jsdom = require('jsdom');
	global.jQuery = require('../../../../resources/lib/jquery/jquery.js')(new jsdom.JSDOM().window);

	global.mediaWiki = {};
	global.pageforms = {};

	global.tests_initialized = true;
}

QUnit.module('pf.js');
QUnit.test('no test yet', assert => assert.ok(1));
