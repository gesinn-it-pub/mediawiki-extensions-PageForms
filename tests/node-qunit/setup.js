const fs = require('fs');
const path = require('path');
const resetDom = createDom();
const sinon = require('sinon');

// Define paths for both versions
const oojsJqueryPath = path.resolve(__dirname, '../../../../resources/lib/oojs/oojs.jquery.js');
const oojsPath = path.resolve(__dirname, '../../../../resources/lib/oojs/oojs.js');

const resetMediaWiki = prepareMediaWiki();

QUnit.hooks.beforeEach(assert => {
	sinon.assert.pass = message =>
		assert.pushResult({ result: true, expected: true, actual: true, message });
	sinon.assert.fail = message =>
		assert.pushResult({ result: false, expected: true, actual: false, message });
});

QUnit.hooks.afterEach(() => {
	resetDom();
	resetMediaWiki();
	sinon.restore();
});

/**
 * first attempt to provide a clean environment for each test
 *
 * @return {(function(): void)|*} a function to reset the DOM between tests
 */
function createDom() {
	// required by jsdom
	const { TextEncoder, TextDecoder } = require('util');
	global.TextEncoder = TextEncoder;
	global.TextDecoder = TextDecoder;
	global.FormData = function() {
		const data = Object.create(null);
		return {
			append: function(key, value) {
				data[key] = value;
			},
			get: function(key) {
				return data[key];
			}
		}
	};

	const { JSDOM } = require('jsdom');
	const dom = new JSDOM();
	global.window = dom.window;
	global.document = window.document;
	global.Node = window.Node;
	global.scroll = () => {};
	global.$ = global.jQuery = require('../../../../resources/lib/jquery/jquery.js');


	return () => {
		global.document.body.innerHTML = '';
	};
}

/**
 * setup MediaWiki globals: OO, mw, ...
 *
 * @return {(function(): void)|*} a function to reset mediaWiki between tests
 */
function prepareMediaWiki() {
	// Check if oojs.js exists (for MW 1.39+), otherwise use oojs.jquery.js (for MW 1.35)
	if (fs.existsSync(oojsPath)) {
		global.OO = require(oojsPath);
	} else if (fs.existsSync(oojsJqueryPath)) {
		global.OO = require(oojsJqueryPath);
	} else {
		throw new Error('Neither oojs.js nor oojs.jquery.js could be found.');
	}
	require('../../../../resources/lib/ooui/oojs-ui-core.js');
	require('../../../../resources/lib/ooui/oojs-ui-widgets.js');
	require('../../../../resources/lib/ooui/oojs-ui-wikimediaui.js');

	const resetMediaWiki = () => {
		if ( global.mw === undefined ) {
			global.mw = global.mediaWiki = {};
		}
		const mw = global.mw;

		for (const key in Object.keys(mw))
			delete mw[key];

		mw.message = () => ({
			text: () => {
			}
		});
		mw.hook = () => ({
			fire: () => {
			}
		});
		mw.user = {
			tokens: {
				get: () => ""
			}
		};
		mw.util = {};
	}

	resetMediaWiki();
	return resetMediaWiki;
}


/**
 * Test setup itself
 */
QUnit.module('setup');

QUnit.test('body is cleaned up between tests: 1', assert => {
	$('<div>', { id: 1 }).appendTo(document.body);
	assert.equal($('div').length, 1);
});

QUnit.test('body is cleaned up between tests: 2', assert => {
	$('<div>', { id: 2 }).appendTo(document.body);
	assert.equal($('div').length, 1);
});

[ 'click', 'mouseup', 'invalid-trigger' ].forEach(event => {
	QUnit.test('can trigger events with jQuery: ' + event, assert => {
		const triggered = sinon.fake();
		$('<div>').appendTo(document.body);
		const $target = $('div');
		$target.on(event, triggered);

		$target.trigger(event);

		assert.true(triggered.calledOnce);
	});
});

QUnit.test(':input selector works', assert => {
	document.body.innerHTML = '<div><input></div>';

	const $inputs = $('body').find(':input');

	assert.equal($inputs.length, 1);
});
