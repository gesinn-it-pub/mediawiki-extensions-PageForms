/**
 * Javascript Code to enable simple upload functionality using OOUI's SelectFileInputWidget
 * for "combobox" and "text" input types
 *
 * @author Nischay Nahata
 * @author Yaron Koren
 * @author Yash Varshney
 */

( function( $, mw) {
	/**
	 * Wrapper around an input field providing the gallery and the functionality to add a file after upload
	 *
	 * @param {jQuery} $sibling
	 * @return {{addFile: Function}}
	 */
	function inputFor($sibling) {
		const $parent = $sibling.parent();
		let $input = $parent.find('#' + $sibling.data('input-id'));
		if ($input.length === 0) {
			$input = $parent.find('[data-origid="' + $sibling.data('input-id') + '"]');
		}

		if ($input.length === 0) {
			return {
				addFile: function() {
					// eslint-disable-next-line no-console
					console.warn('Cannot add file, input not found');
				}
			};
		}

		const isTokenInput = $input.hasClass('pfTokens');

		const input = isTokenInput
			? {
				filenames: function(value) {
					return value.map(s => s.trim());
				},
				addFile: function(filename) {
					$input.append('<option value="' + filename + '">' + filename + '</option>');
					$input.val([ ...getFilenames(), filename ]);
					// refresh Select2 tokens
					(new pf.select2.tokens()).refresh($input);
				}
			}
			: {
				filenames: function(value) {
					return [ value ];
				},
				addFile: function(filename) {
					$input.val(filename);
				}
			};

		const getFilenames = function() {
			const value = $input.val();
			return value ? input.filenames(value) : [];
		};

		const handleFilenamesChanged = function() {
			$parent.find('div.simpleupload_prv').remove();
			const filenames = getFilenames();
			if ( filenames.length > 0 ) {
				const $container = $('<div class="simpleupload_prv" />');
				for (const filename of filenames) {
					const thumbnailURL =
						mw.config.get('wgArticlePath').replace('$1', 'Special:Redirect/file/' + encodeURIComponent(filename)) + '?width=150';
					$('<img src="' + thumbnailURL + '">').appendTo($container);
				}
				$container.prependTo($parent);
			}
		};

		const addFile = function(fileName) {
			input.addFile(fileName);
			handleFilenamesChanged();
		};

		handleFilenamesChanged();
		// Register for change event
		$input.change( function() {
			// Have to wait when removing a file in the pfTokens case
			setTimeout(() => handleFilenamesChanged(), 0);
		});

		return { addFile };
	}

	$.fn.initializeSimpleUpload = function() {
		const uploadWidget = new OO.ui.SelectFileWidget( {
			buttonOnly: true,
			button: {
				flags: [
					'progressive'
				],
				icon: 'upload',
				label: mw.message( 'pf-simpleupload' ).text()
			},
			classes: [ 'simpleUpload' ]
		} );

		const inputSpan = this.parent();
		const loadingImage = inputSpan.find('img.loading');
		// append a row of buttons for upload and remove
		inputSpan.find('span.simpleUploadInterface').append(uploadWidget.$element);

		const input = inputFor(this);
		uploadWidget.on('change', function(files) {
			const file = files[0];

			const formdata = new FormData(); // see https://developer.mozilla.org/en-US/docs/Web/API/FormData/Using_FormData_Objects
			formdata.append("action", "upload");
			formdata.append("format", "json");
			formdata.append("ignorewarnings", "true");
			formdata.append("filename", file.name);
			formdata.append("token", mw.user.tokens.get( 'csrfToken' ) );
			formdata.append("file", file);

			loadingImage.show();
			$.ajax( { // http://stackoverflow.com/questions/6974684/how-to-send-formdata-objects-with-ajax-requests-in-jquery
				url: mw.util.wikiScript( 'api' ), // url to api.php
				contentType:false,
				processData:false,
				type:'POST',
				data: formdata,
				success: function( data ) {
					if ( data.upload ) {
						input.addFile(data.upload.filename);
					} else {
						const error = (data.error && data.error.info) || mw.msg( 'pf-simpleupload-unspecified-upload-error' );
						window.alert("Error: " + error);
					}
					loadingImage.hide();
				},
				error: function( xhr,status, error ) {
					window.alert(mw.msg( 'pf-simpleupload-unspecified-upload-error' ));
					loadingImage.hide();
					mw.log(error);
				}
			});
		});

	};

}( jQuery, mediaWiki ) );
