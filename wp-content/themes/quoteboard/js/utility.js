/*
 *	QUOTEBOARD
 *	Add your comments here
 */

// closure, so we can use $ shorthand
(function ($) {
	// 'use strict';

	// set global vars
	qb = {
		// global autocomplete options
		autocomplete : {
			limit: 5,
			minChars: 2
		},
		// most fancyboxes are the same
		fancybox : {
			closeBtn : false,
			margin: 10,
			maxWidth : 460,
			minWidth: '98%',
			padding : 0
		},
		// all form submissions have same action and a nonce
		formData : {
			action	: 'ajax-submit',
			nonce 	: qb_ajax.ajaxNonce
		},
		minPasswordLength : 6,
		quoteLimit : 750,
		viewport : $(window)
	},

	// utility functions
	qb.utility = {

		init : function() {
			this.chosen();
			this.fancybox();
			// this.lazyload();
			this.onWindowLoad();
			this.onWindowResize();
			this.tabs();
			this.timeago();
		},


		/*
		 *	Generates an autocomplete box for the "quote source" input
		 *	The "qb_autocomp_sources" JSON object is created when the
		 *	"Add Quote" lightbox is opened.
		 *
		 *	The "qb_autocomp_following" JSON object is created when the
		 *	"Invite Friends" lightbox is opened.
		 */
		autocomplete : function() {
			$('[name="quote_source"]').autocomplete({
				lookup: qb_autocomp_sources,
				lookupLimit: qb.autocomplete.limit,
				minChars: qb.autocomplete.minChars,
				onSelect: function (suggestion) {
					$('[name="quote_source_id"]').val(suggestion.data);
				}
			});
		},


		/*
		 *	Vertically centers the homepage hero area after window load
		 *	The 15 is half height of the footer.
		 *
		 *	DEPRECATED
		 */
		centerHero : function() {
			var hero 		= $( '#hero' ),
				heroOffset 	= ( qb.viewport.height() - hero.height() - 15 ) / 2;

			if ( qb.viewport.width() > 767 ) {
				hero.css( 'margin-top', heroOffset );
			} else {
				hero.css( 'margin-top', 0 );
			}

			hero.fadeIn();
			$( '.home footer' ).fadeIn();
		},


		/*
		 *	Fires off AJAX request to a script that checks the wp_usermeta table
		 *	to see if a user's Facebook ID already exists (which indicates the
		 *	user has previously signed into the site with Facebook).
		 */
		checkFacebookID : function( token, id, form ) {
			qb.formData['form_name']	= 'check-facebook-id';
			qb.formData['token'] 		= token;
			qb.formData['user_id'] 		= id;

			// dim signup form
			var loadingBox = $('.sso-loading'),
				loadingForm = loadingBox.closest('form');

			loadingForm.addClass('loading');
			loadingBox.html('<span>Connecting to Facebook...</span>');

			//console.log('token used: ' + token)
			//console.log('fb user id: ' + id)
			$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
				data = $.parseJSON( data );
				//console.log('connecting...')
				//console.log(data);

				// if user found, log them in
				if ( data.user_id ) {
					//console.log('user id found: ' + data.user_id)
					//console.log('token used to log in: ' + token)

					qb.utility.loginFacebookUser( token, data.user_id );

				// if user not found in WP user table, fetch user details from Facebook for new registration
				} else {
					if ( form === 'login' ) {
						// RB: removing confirmation since it seems unnecessary - 03/22/15
						//if ( confirm( 'This looks like your first time signing into Quoteboard with Facebook. Do you want us to automatically create a Quoteboard account for you using your Facebook profile?' ) ) {
							qb.utility.registerFacebookUser( token );
						//} else {
							//loadingForm.removeClass('loading');
						//}
					} else {
						qb.utility.registerFacebookUser( token );
					}
				}

				delete qb.formData['form_name'];
			} );
		},


		/*
		 *	Checks for a specific email and returns true if it exists
		 *	in the wp_users table. Fired on blur of the email field
		 *	in the "Join" form.
		 */
		checkRegisterEmail : function (input, email, fn) {
			var errorBox = input.closest('div').find('.error-inline'),
				icon = input.closest('div').find('.ico');

			input.addClass('loading');

			qb.formData['form_name']  	= 'check-email';
			qb.formData['email']  		= email;

			$.ajax({
				type: 'POST',
				url: qb_ajax.ajaxURL,
				data: qb.formData,
				success: function( data ) {
					data = $.parseJSON( data );

					input.removeClass();
					icon.removeClass('error');

					delete qb.formData['form_name'];
					delete qb.formData['email'];

					if ( data.errors ) {
						alert( data.errors );
					} else {
						errorBox.hide();

						if ( data.result == 'unique' ) {
							input.addClass('success');
							icon.addClass('success');
						} else if ( data.result === 'invalid' ) {
							input.addClass('invalid');
						} else {
							input.addClass('error');
							icon.addClass('error');
							errorBox.show();
						}
					}

					fn(data);
				},
				async: false
			});
		},


		/*
		 *	Checks that password is a minimum length. Fired on blur of the
		 *	password field in the "Join" form.
		 */
		checkRegisterPassword : function (input, password) {
			var errorBox = input.closest('div').find('.error-inline'),
				icon = input.closest('div').find('.ico');

			//$('.toggle-pw').addClass('shifted');

			if ( password.length < qb.minPasswordLength ) {
				input.addClass('error');
				icon.addClass('error');
				errorBox.show();
				return false;
			} else {
				input.addClass('success');
				icon.addClass('success');
				errorBox.hide();
				return true;
			}
		},


		/*
		 *	Checks for a specific username and returns true if it exists
		 *	in the wp_users table. Fired on blur of the username field
		 *	in the "Join" form.
		 */
		checkRegisterUsername : function (input, username, fn) {
			var errorBox = input.closest('div').find('.error-inline'),
				icon = input.closest('div').find('.ico');

			input.addClass('loading');

			qb.formData['form_name']  	= 'check-username';
			qb.formData['username']  	= username;

			$.ajax({
				type: 'POST',
				url: qb_ajax.ajaxURL,
				data: qb.formData,
				success: function( data ) {
					data = $.parseJSON( data );

					input.removeClass();
					icon.removeClass('error');

					delete qb.formData['form_name'];
					delete qb.formData['username'];

					if ( data.errors ) {
						alert( data.errors );
					} else {
						errorBox.hide();

						if ( data.result == 'unique' ) {
							input.addClass('success');
							icon.addClass('success');
						} else if ( data.result === 'invalid' ) {
							input.addClass('invalid');
						} else {
							input.addClass('error');
							icon.addClass('error');

							if ( data.result === 'too short' ) {
								errorBox.show();
							} else {
								errorBox.text('Drat! This username is unavailable!').show();
							}

						}
					}

					fn(data);
				},
				async: false
			} );
		},


		/*
		 *	Checks the length of text in a textarea and updates the
		 *	"characters remaining" count
		 */
		checkTextareaLength : function( textarea ) {
			var userInput 	= textarea.val(),
				charsLeft	= qb.quoteLimit - userInput.length,
				countWrap	= textarea.closest( 'form' ).find('.charcount');

			// once user hits limit, trim first char over the limit and prevent further typing
			if ( userInput.length > qb.quoteLimit ) {
				textarea.val( userInput.substr( 0, qb.quoteLimit ) );
				return false;
			}

			// if fewer than 50 characters remaining, add class for color change
			if (charsLeft < 50) {
				countWrap.addClass('dwindling');
			} else {
				countWrap.removeClass('dwindling');
			}

			// special case for 1 character left
			if (charsLeft === 1) {
				countWrap.html('<span>1</span> character left');
			} else {
				countWrap.html('<span>' + charsLeft + '</span> characters left');
			}

			// update character counter
			countWrap.find('span').text(charsLeft);
		},


		/*
		 *	Chosen (fancy dropdowns)
		 */
		chosen : function() {
			$( 'select' ).chosen( {
				disable_search_threshold : 6
			} );
		},


		/*
		 *	Delay function, for preventing events like keyup
		 *	from firing too frequently
		 *
		 *	NOT BEING USED - RB, 3/28/14
		 *
		delay : ( function() {
			var timer = 0;
			return function( callback, ms ) {
				clearTimeout( timer );
				timer = setTimeout( callback, ms );
			};
		} )(),
		*/


		/*
		 *	Called from qb_init_facebook_sdk() in functions.php only when
		 *	a user is not logged into the site.
		 *
		 *	Authenticates with Facebook, and either logs user in or creates
		 *	a new WP account tied to the user's Facebook ID.
		 *
		 *	Note that "Quoteboard App" refers to the Facebook app and NOT
		 *	to the website. I do not believe it matters whether the person
		 *	is logged into the Facebook app.
		 */
		facebookLogin : function( form ) {

			FB.login(function (response) {
				if (response.authResponse) {
					qb.utility.checkFacebookID(response.authResponse.accessToken, response.authResponse.userID, form);
				} else {
					console.log('User cancelled login or did not fully authorize.');
				}
			}, {
				scope: 'public_profile, email',
				return_scopes: true
			} );

			/*
			 *	FB.getLoginStatus() gets the state of the person visiting this page and can
			 *	return one of three states to the callback. They can be:
			 *
			 *		1. Logged into your app ('connected')
			 *		2. Logged into Facebook, but not your app ('not_authorized')
			 *		3. Not logged into Facebook and can't tell if they are logged into
			 *		   your app or not.
			 *

			 // not sure how this plays in anymore...
			FB.getLoginStatus(function(response) {
				// user is logged into both Facebook and the Quoteboard app
				if (response.status === 'connected') {
					var accessToken	= response.authResponse.accessToken,
						fbUserID 	= response.authResponse.userID;

					// check if user has an account on the site
					qb.utility.checkFacebookID(accessToken, fbUserID);

				// user is logged into Facebook, but not the Quoteboard app
				} else if (response.status === 'not_authorized') {
					console.log('Please log into this app.');

				// user is not logged into Facebook; not sure if logged into Quoteboard app
				} else {
					console.log('Please log into Facebook.');

					// subscribe to the event 'auth.authResponseChange' and wait for the user to authenticate
					FB.Event.subscribe('auth.authResponseChange', function(response) {
						window.location.reload();
					}, true);
				}
			});
			*/
		},


		/*
		 *	Fancybox
		 */
		fancybox : function() {
			if ( $.isFunction( $.fn.fancybox ) ) {
				// default fancybox
				$( '.fancybox' ).fancybox( {
					afterShow: function () {
						// setTimeout avoids a bug in Chrome where field never gains focus
						// see: http://stackoverflow.com/questions/17384464/jquery-focus-not-working-in-chrome
						setTimeout( function() {
							$( 'h2 + div' ).find( ':input' ).focus();
						}, 1 );
					},
					closeBtn : qb.fancybox.closeBtn,
					margin: qb.fancybox.margin,
					maxWidth : qb.fancybox.maxWidth,
					minWidth: qb.fancybox.minWidth,
					padding : qb.fancybox.padding,
					scrolling	: 'visible'
				} );
			}
		},


		/*
		 *	TO DO: abstract the next 3 methods into a single method
		 *
		 *	Retrieves a list of mutual followers (people the current user
		 *	is following that are also following the current user).
		 *	Used to populate the "who said it" dropdown. Called when the
		 *	"Add Quote" or "Invite Friends" lightbox is opened (or when inline
		 *	add quote form gains focus)
		 */
		getAutocompleteAuthors : function() {
			qb.formData['form_name'] = 'get-authors';
			
			if ( !$( '#autocomplete-authors' ).length ) {
				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					data = $.parseJSON( data );

					// console.log(data.json_response)
					
					if ( data.errors ) {
						alert( data.errors );
					} else {
						$( 'body' ).append( '<div id="autocomplete-authors" />' );
						$( '#autocomplete-authors' ).html( '<script>var qb_autocomp_authors = ' + data.json_response + ';</script>' );
						
						// pulled from qb.utility.autocomplete method
						$('[name="quote_author"]').autocomplete({
							lookup: qb_autocomp_authors,
							lookupLimit: qb.autocomplete.limit,
							minChars: qb.autocomplete.minChars,
							onSelect: function (suggestion) {
								$('[name="quote_author_id"]').val(suggestion.data);
								//alert('You selected:' + suggestion.value +',' + suggestion.data);
							}
						});
					}

					// unset formData to prevent issues with later submissions
					delete qb.formData['form_name'];
				} );
			}

			// unset formData to prevent issues with later submissions
			delete qb.formData['form_name'];
		},


		/*
		 *	Retrieves a list of people the current user is following.
		 *	Used to populate the "Invite People to Board" form.
		 *	Called when the "Invite" form is opened. Almost identical
		 *	to the previous function for getting author autocomplete data.
		 */
		getAutocompleteFollowing : function() {
			qb.formData['form_name'] = 'get-following';
			
			if ( !$( '#autocomplete-following' ).length ) {
				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					data = $.parseJSON( data );

					if ( data.errors ) {
						alert( data.errors );
					} else {
						$( 'body' ).append( '<div id="autocomplete-following" />' );
						$( '#autocomplete-following' ).html( '<script>var qb_autocomp_following = ' + data.json_response + ';</script>' );
						
						// autocomplete - pulled from qb.utility.autcomplete()
						$('[name="invite_email"]').autocomplete({
							lookup: qb_autocomp_following,
							lookupLimit: qb.autocomplete.limit,
							minChars: qb.autocomplete.minChars,
							onSelect: function (suggestion) {
								$('.btn.add-invite').attr('data-id', suggestion.data);
							}
						});
					}

					// unset formData to prevent issues with later submissions
					delete qb.formData['form_name'];
				} );
			}

			// unset formData to prevent issues with later submissions
			delete qb.formData['form_name'];
		},


		/*
		 *	Retrieves a list of quote sources. Used to populate the
		 *	"where was it said" input. Called when the "Add Quote"
		 *	or "Edit Quote" forms are opened. Almost identical to the
		 *	previous function for getting following autocomplete data.
		 */
		getAutocompleteSources : function() {
			qb.formData['form_name'] = 'get-sources';
			console.log('get autocomplete] sources');
			
			if ( !$( '#autocomplete-sources' ).length ) {
				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					data = $.parseJSON( data );

					if ( data.errors ) {
						alert( data.errors );
					} else {
						$( 'body' ).append( '<div id="autocomplete-sources" />' );
						$( '#autocomplete-sources' ).html( '<script>var qb_autocomp_sources = ' + data.json_response + ';</script>' );
						qb.utility.autocomplete();
					}

					// unset formData to prevent issues with later submissions
					delete qb.formData['form_name'];
				} );
			}

			// unset formData to prevent issues with later submissions
			delete qb.formData['form_name'];
		},


		/**
		 *	Infinite Scrolling / Lazy Load
		 *
		 *	Loads results via AJAX instead of displaying pagination
		 */

		lazyload : function() {
			var loader 			= $('#lazyloader'),
				nextPageNumber	= 0,
				nextPageURL		= $('a.page-numbers:last-child').attr( 'href'),
				scrollPosition	= 0,
				timeoutID;

			// only fire scroll event once every half second
			qb.viewport.scroll(function () {
				clearTimeout(timeoutID);
				timeoutID = setTimeout(doneScrolling, 500);
			});

			// this is the # of the last page
			console.log('last page: ' + $('.page-numbers.next').prev().text());

			function doneScrolling() {
				// recalculate
				nextPageNumber	= nextPageURL.slice(0, -1).split('/').pop();
				scrollPosition 	= qb.viewport.scrollTop() + qb.viewport.height();

				// if scrolled to bottom of page
				if ( scrollPosition >= $(document).height() ) {
					// if there is a next page...
					if ( nextPageURL ) {
						// show loading animation
						loader.show();

						console.log(nextPageNumber);
						// ...get contents of next page
						$.get( nextPageURL, function( data ) {
							// filter down to articles and insert before loading animation
							$(data).find('article.box').insertBefore(loader);

							// hide loading animation
							loader.hide();

							// increment "next page" number
							//updated = 
							$('.page-numbers.next').attr('href', updated);

							// grab next page URL from hidden "next page" link
							nextPageURL = $(data).find('#page-nav a:last-of-type').attr('href');
						} );
					}
				}
			}
		},


		/*
		 *	Loads a specific modal and contents via AJAX
		 */
		loadAjaxModal : function( clicked ) {
			var modalType = clicked.attr( 'data-type' ),
				modalElement = $( '#' + modalType );

			//console.log(modalType + ' - ' + clicked.attr( 'data-id' ))

			// set constant data
			qb.formData['form_name'] 		= modalType;
			qb.formData['modal_id'] 		= clicked.attr( 'data-id' );

			// set unique data
			switch ( modalType ) {
				case 'modal-board-edit':
				case 'modal-user-edit':
					qb.formData['description'] 		= clicked.attr( 'data-description' );
					qb.formData['name_or_title'] 	= clicked.attr( 'data-name' );
					qb.formData['thumbnail_src'] 	= clicked.attr( 'data-thumb' );
					qb.formData['background_src'] 	= clicked.attr( 'data-background' );
				break;

				case 'modal-board-profile':
				case 'modal-user-profile':
					qb.formData['quote_count'] 		= clicked.attr( 'data-quotes' );
					qb.formData['follower_count'] 	= clicked.attr( 'data-followers' );
					qb.formData['thumbnail_src'] 	= clicked.attr( 'data-thumb' );
					qb.formData['background_src'] 	= clicked.attr( 'data-background' );
				break;

				case 'modal-user-profile':
					qb.formData['following_count'] 	= clicked.attr( 'data-following' );
				break;

				default:
				break;
			}

			// check for modal skeleton; append if needed
			if ( !modalElement.length ) {
				$( 'body' ).append( '<section class="hidden ' + modalType + '" id="' + modalType + '" />' );
				modalElement = $( '#' + modalType );

				// add content to modal
				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					//console.log(data)
					data = $.parseJSON( data );

					if ( data.errors ) {
						alert( data.errors );
					} else {
						modalElement.html( data.html );
						launchFancybox( modalElement, clicked );
					}

					$.fancybox.hideLoading();
				} );
			} else {
				launchFancybox( modalElement, clicked );
			}

			delete qb.formData['form_name'];
			delete qb.formData['modalId'];

			function launchFancybox( modalElement, clicked ) {
				modalElement.find('form').show();

				$.fancybox.open(
					modalElement, {
						afterLoad: function () {
							// setTimeout avoids a bug in Chrome where field never gains focus
							// see: http://stackoverflow.com/questions/17384464/jquery-focus-not-working-in-chrome
							setTimeout( function() {
								$( 'h2 + div' ).find( ':input' ).focus();

								if ( modalType == 'modal-board-add' || modalType == 'modal-board-edit' ) {
									modalElement.find('select').chosen();

								}
							}, 1 );

							if ( modalType == 'modal-board-invite' ) {
								qb.utility.getAutocompleteFollowing();
							}
						},
						closeBtn 	: qb.fancybox.closeBtn,
						margin 		: qb.fancybox.margin,
						maxWidth 	: qb.fancybox.maxWidth,
						minWidth 	: qb.fancybox.minWidth,
						padding 	: qb.fancybox.padding,
						scrolling	: 'visible'
					}
				);
			}
		},


		/*
		 *	Called once a user is authenticated and signed into Facebook;
		 *	simply logs the user into the site
		 */
		loginFacebookUser : function( token, id ) {
			var ssoLoading = $('#login-form, #register-form').find('.sso-loading');

			qb.formData['form_name']	= 'login-facebook';
			qb.formData['token']		= token;
			qb.formData['user_id'] 		= id;

			ssoLoading.html('<span>Reticulating splines...</span>');

			$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
				data = $.parseJSON( data );
				ssoLoading.html('<span>Signing you in...</span>');

				window.location.href = data.redirect_to;

				delete qb.formData['form_name'];
			} );
		},


		/*
		 *	Executes stuff when the window is ready
		 */
		onWindowLoad : function() {
			qb.viewport.load( function() {
				//qb.utility.centerHero();
				qb.utility.scrollDownCoverPhoto();

				$('[data-columns]').animate({opacity: 1}); // temp
			} );
		},


		/*
		 *	Executes stuff when the window is resized, after a short delay
		 */
		onWindowResize : function() {
			var timeoutID;

			qb.viewport.resize( function() {
				clearTimeout( timeoutID );
				timeoutID = setTimeout( doneResizing, 250 );
			} );

			function doneResizing() {
				//qb.utility.centerHero();
			}
		},


		/*
		 *	Populates the Edit Quote form after it's been opened in a Fancybox.
		 *	Takes the quote information in the bubble that was clicked and
		 *	copies it to the form. This was the most efficient method, I think.
		 */
		populateQuoteEditForm : function (clicked) {
			var editWrapper		= $('#quote-edit'),
				textarea		= editWrapper.find('textarea'),
				boardSelectMenu = editWrapper.find('select'),
				quoteWrapper	= clicked.closest('article'),
				quoteText 		= quoteWrapper.find('q'),
				quoteAuthor 	= quoteWrapper.find('cite').text(),
				quoteSource 	= quoteWrapper.find('.quote-source').text();
				boardID 		= quoteWrapper.attr('data-board'),
				quoteID			= quoteWrapper.attr('data-id');
				authorID 		= quoteWrapper.attr('data-author'),

			// remove contributor name
			quoteText.find('b').remove();

			// text
			textarea.val(quoteText.text().replace(/ +/g, " "));
			// author
			editWrapper.find( '#edit-quote-author' ).val( quoteAuthor );
			editWrapper.find( '[name="quote_author_id"]' ).val( authorID );
			//source
			editWrapper.find('[name="quote_source"]').val(quoteSource);
			// board
			boardSelectMenu.find( 'option[value="' + boardID + '"]' ).attr( 'selected', 'selected' );
			boardSelectMenu.trigger( 'chosen:updated' );
			editWrapper.find('[name="quote_board"]').val(boardID);
			// quote ID
			editWrapper.find('[name="qid"]').val(quoteID);

			qb.utility.checkTextareaLength(textarea);
		},


		/*
		 *	Populates the Requote form after it's been opened in a Fancybox.
		 *	Takes the quote information in the bubble that was clicked and
		 *	copies it to the form. This was the most efficient method, I think.
		 */
		populateRequoteForm : function( clicked ) {
			var quoteWrapper 	= clicked.closest( 'article' ),
				quoteText 		= quoteWrapper.find( 'q' ).clone(),
				quoteAuthor 	= quoteWrapper.find( 'cite' ).clone(),
				quoteID 		= quoteWrapper.attr( 'data-id' );

			$( '#requote-this' ).html( quoteText ).append( quoteAuthor );
			$( '[name="reqid"]' ).val( quoteID );

			$.fancybox.update();
		},


		/*
		 *	Use the FileReader API to show the selected image prior to upload
		 */
		readFileBeforeUpload : function( fileInput, fileData ) {
			if ( fileData.type.match( /image.*/ ) ) {
				var reader = new FileReader();

				reader.onload = function() {
					var img = new Image();
					img.src = reader.result;

					fileInput.closest('.photo').find('img').attr('src', img.src);
					fileInput.closest('label').find('span').text(fileData.name + ' ready for upload');
				}

				reader.readAsDataURL( fileData );
			} else {
				alert( 'Please choose an image of type GIF, JPG, or PNG' );
			}
		},


		/*
		 *	Creates a new WordPress user account for someone signing into Quoteboard
		 *	for the first time with the Facebook Login plugin.
		 */
		registerFacebookUser: function( token ) {
			var ssoLoading = $('#register-form, #login-form').find('.sso-loading');

			ssoLoading.html('<span>Creating new account...</span>').fadeIn();
			console.log('creating...')

			// logged into the QB app and Facebook - fetch user details from Facebook
			FB.api('/me?fields=cover,email,first_name,last_name,link,name', function(response) {
				console.log('response:');
				console.log(response);

				var fbCover = 'http://www.quoteboard.com/wp-content/uploads/2014/11/hero.jpg';

				if (response.cover) {
					fbCover = response.cover.source;
				}

				var fbEmail 	= response.email,
					fbFirstName = response.first_name,
					fbLastName 	= response.last_name,
					fbLink		= response.link;
					fbName 		= response.name;
					fbUserID 	= response.id;

				// grab profile image (can't figure out how to include these params in the API call above)
				FB.api('/me/picture', {
					'redirect' 	: false,
					'height' 	: 400,
					'type' 		: 'normal',
					'width'		: 400
				}, function (response) {
					if (response && !response.error) {
						qb.formData['fb_photo']	= response.data.url;
					}

					// set form data for AJAX call
					qb.formData['form_name']	= 'register-facebook';
					qb.formData['token']		= token;
					qb.formData['user_id'] 		= fbUserID;
					qb.formData['cover']		= fbCover;
					qb.formData['email']		= fbEmail;
					qb.formData['first_name']	= fbFirstName;
					qb.formData['last_name']	= fbLastName;
					qb.formData['fb_url']		= fbLink;
					qb.formData['full_name']	= fbName;

					/* debug *
					console.log('email: ' + fbEmail);
					console.log('first: ' + fbFirstName);
					console.log('last: ' + fbLastName);
					console.log('fb page: ' + fbLink);
					console.log('full name: ' + fbName);
					console.log('user ID: ' + fbUserID);
					console.log('token: ' + token);
					console.log(response);
					/**/

					// register and sign in user
					$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
						data = $.parseJSON( data );
						ssoLoading.html('<span>Almost done...</span>').fadeIn();

						if ( data.errors ) {
							alert( data.errors );
							ssoLoading.fadeOut();
						} else {
							window.location.href = data.redirect_to;
						}

						delete qb.formData['form_name'];
					} );
				} );
			} );
		},


		/*
		 *	Removes a profile photo (really we just replace it with the default)
		 */
		removePhoto : function(clicked) {
			if ( confirm( 'Are you sure you want to remove this photo?' ) ) {
				var formWrapper 	 = clicked.closest('form'),
					photoWrapper 	 = clicked.closest('.photo'),
					loadingAnimation = photoWrapper.find('.loading'),
					profilePhoto 	 = photoWrapper.find('img'),
					board_id, user_id;

				clicked.addClass('hidden');
				loadingAnimation.show();

				// set values for WordPress AJAX submit
				board_id 	= formWrapper.find('input[name="bid"]').val();
				user_id		= formWrapper.find('input[name="uid"]').val();

				// 'bid' is undefined when submitting a user form; 'uid' undefined when submitting a board form
				if (user_id === undefined) {
					user_id = false;
				}

				if (board_id === undefined) {
					board_id = false;
				}

				qb.formData['bid'] 			= board_id;
				qb.formData['uid'] 			= user_id;
				qb.formData['form_name'] 	= 'remove-photo';

				// set flag if user is removing a background image
				if (clicked.hasClass('bg')) {
					qb.formData['bg'] = 1;
				} else {
					qb.formData['bg'] = 0;
				}

				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					data = $.parseJSON(data);
					console.log(data)

					if ( data.errors ) {
						alert( data.errors );
					} else {
						profilePhoto.attr( 'src', data.thumbnail );

						// if user removed bg, update page background to default
						if (data.remove_bg === 1) {
							$('#hero').attr('style', 'background: url(' + data.full_img + ') center no-repeat;');
						// else, update profile photo to default
						} else {
							$('#profile').find('img').attr('src', data.full_img).css('width', 200);
						}
					}

					loadingAnimation.hide();

					// unset formData to prevent issues with later submissions (e.g., board edit)
					delete qb.formData['form_name'];
					delete qb.formData['bg'];
					delete qb.formData['bid'];
					delete qb.formData['uid'];
				} );
			}
		},


		/*
		 *	On pages with a cover photo, scrolls down so cover photo
		 *	doesn't take up so much of the fold
		 */
		scrollDownCoverPhoto : function () {
			var hero = $('#hero');

			if (!$('body.home').length) {
				if (hero.length) {
					qb.viewport.scrollTop(hero.height()*0.7);
				}
			}
		},


		/*
		 *	Hand-rolled tabs
		 */
		tabs: function () {
			$('.tabs').find('a').click(function(e) {
				var clicked = $(this);

				e.preventDefault();
				
				$('.tabs').find('a').removeClass('active')
				clicked.addClass('active');

				$('.pane').hide();
				$('.pane[id="' + clicked.attr('href').substring(1) + '"]').show();
			})
		},


		/*
		 *	Converts absolute timestamps to relative time (e.g., 2 days ago)
		 */
		timeago : function() {
			$( '.timeago' ).timeago();
		},


		/*
		 *	Updates photo upload fields after a successful submit
		 *	These fields are located in the board and user profile forms
		 */
		updatePhotoFieldsAfterSubmit : function (json, form) {
			form.find('.photo input').val('');
			form.find('.photo label span').text('Tap here to upload a new photo');
			if (json.profile_photo) {
				form.find('.photo .pp').removeClass('default');
			}

			if (json.bg_photo) {
				form.find('.photo .bg').removeClass('default');
			}
		},


		/*
		 *	Live updates board or user profile after successful submit
		 */
		updateProfileAfterSubmit : function (json) {
			var profileWrapper = $('#profile');

			profileWrapper.find('p').remove();
			profileWrapper.find('h3').text(json.name).after(json.description);

			if (json.profile_photo) {
				profileWrapper.find('img').attr('src', json.profile_photo);
			}
		}
	}


	// TO DO: move to separate events.js file
	// TO DO: move stuff into separate functions vs. having it all in a giant init() function
	qb.events = {
		init : function() {
			var self = this;

			// submit forms through AJAX
			$( 'body' ).on( 'click', '[type="submit"]', function() {
				if ( !$( this ).hasClass( 'no-ajax' ) ) {
					self.submitForm( $( this ) );
				}
			} );

			// select change (When choosing a board, e.g.)
			$('select').change(function (e, params) {
				console.log(params)
				$(this).closest('form').find('[name="quote_board"]').val(params.selected);
			});

			// when adding a quote from a board page, selects the current board
			$( 'a.from-board' ).click( function() {
				self.selectCurrentBoard( $( this ) );
			} );

			// toggles password field type between "password" and "text"
			$( '.toggle-pw' ).click( function() {
				self.togglePasswordVisibility( $( this ) );
			} );

			// hides the "show/hide" password option
			$( '[type="password"]' ).blur( function() {
				//self.hidePasswordToggleLink( $( this ) );
			} );

			// shows the "show/hide" password option
			$( '[type="password"]' ).focus( function() {
				self.showPasswordToggleLink( $( this ) );
			} );

			// close fancyboxes (ignore when closing the "Add Board" on-the-fly form launched from the "Add Quote" fancybox)
			$( 'body' ).on( 'click', '.close', function() {
				//if ( $( this ).closest( 'form' ).attr( 'id' ) !== 'board-create-new-inline' ) {
					$.fancybox.close();
				//}
			} );

			// when users choose "Create new board" from "Add Quote" form, open "Add Board" on-the-fly form
			$('body').on('click', '.choose-board .chosen-results li[data-option-array-index="1"], #menu-item-625, #menu-item-719', function () {
				self.openAddBoardInlineFancybox($(this));
			} );

			// open add quote fancybox
			$('#menu-item-624, #menu-item-718').click(function () {
				self.openAddQuoteFancybox();
				qb.utility.getAutocompleteAuthors();
				qb.utility.getAutocompleteSources();
			});

			// when users close the "Add Board" on-the-fly fancybox, re-open the fancybox the users clicked from
			$( '#board-create-new-inline' ).find( '.close' ).click( function() {
				self.openFancyboxAfterAddingBoardInline( $( this ) );
			} );

			// add quote to favorites
			$( 'body' ).on( 'click', '.fave', function() {
				self.faveQuote( $( this ) );
			} );

			// delete quote
			$('.delete-quote').click(function (e) {
				e.preventDefault();
				self.eraseQuote($(this));
			} );

			// resubmit a from within a lightbox, after successful submit -- see, e.g., createQuote()
			$( 'body' ).on( 'click', '.another', function() {
				var form = $(this).closest('form');

				form.find('.hidden.success').fadeOut(250, function() {
					$(this).slideUp();
					form.find('.ajax-wrapper').fadeIn();
				} );
			} );

			// follow or unfollow a user or a board
			$( 'body' ).on( 'click', '.follow, .collab', function (e) {
				e.preventDefault();
				if ( !$( this ).hasClass( 'disabled' ) ) {
					self.followSomething( $( this ) );
				}
			} );

			// leave a board
			$( '#leave-board' ).click( function (e) {
				e.preventDefault();
				self.leaveBoard( $( this ) );
			} );

			// erase (delete) a board
			$( 'body' ).on( 'click', '.erase-board', function (e) {
				e.preventDefault();
				self.eraseBoard( $( this ) );
			} );

			// add user or email to board invite list (on click)
			$( 'body' ).on( 'click', '.add-invite', function (e) {
				e.preventDefault();
				self.addUserToInviteList( $( this ) );
			} );

			// add user or email to board invite list ("enter" key)
			$( 'body' ).on( 'keypress', '#invite-email', function (e) {
				var code = ( e.keyCode ? e.keyCode : e.which );
				if ( code == 13 ) {
					self.addUserToInviteList( $( this ).closest( 'div' ).find( '.add-invite' ) );
					e.preventDefault();
				}
			} );

			// load modals and forms via AJAX
			$( '.ajax-modal' ).click( function (e) {
				e.preventDefault();
				$.fancybox.showLoading();
				qb.utility.loadAjaxModal( $( this ) );
			} );

			// remove user from board invite list
			$( 'body' ).on( 'click', '.remove-invitee', function (e) {
				e.preventDefault();
				self.removeUserFromInviteList( $( this ) );
			} );

			// remove user as collaborator
			$('body').on('click', '.remove-collab', function (e) {
				e.preventDefault();
				self.removeCollaborator($(this));
			});

			// remove pending collaborator
			$('body').on('click', '.remove-pending-collab', function (e) {
				e.preventDefault();
				self.removePendingCollaborator($(this));
			});

			// change text on hover of "Following" button
			$('#profile').on('mouseenter', '.btn.following', function () {
				$(this).text('Unfollow');
			});

			$('#profile').on('mouseleave', '.btn.following', function () {
				$(this).text('Following');
			});

			// change text on hover of "Collaborating" button
			$('#profile').on('mouseenter', '.btn.collaborating', function () {
				$(this).text('Stop Collab');
			});

			$('#profile').on('mouseleave', '.btn.collaborating', function () {
				$(this).text('Collaborating');
			});

			// remove profile photo
			$( 'body' ).on( 'click', '.remove-photo', function (e) {
				e.preventDefault();
				qb.utility.removePhoto($(this));
			} );

			// when a file is selected for upload
			$( 'body' ).on( 'change', ':file', function() {
				qb.utility.readFileBeforeUpload( $(this), this.files[0] );
			} );

			/**
			 *	Clicking on a box that has this attribute takes people to the link -
			 *	allows us to link boxes that should not otherwise be wrapped in anchor tags
			 */
			$('[data-link]').click(function () {
				var clickedArea = $(this);

				// don't redirect if box is a quote on a single quote page
				if (clickedArea.closest('.box').hasClass('quote')) {
					if (!clickedArea.closest('body').hasClass('single-quote')) {
						window.location.href = $(this).attr('data-link');
					}
				} else {
					window.location.href = $(this).attr('data-link');
				}
			});

			// when user clicks anywhere in a quote/board/member bubble, direct to the permalink (since no anchor is present)
			// $('.main').on('click', '.bubble', function () {
			// 	var box = $(this).closest('.box');

			// 	// quotes should be clickable on board permalink pages, but not on quote permalink pages
			// 	if ((box.hasClass('quote') && !box.closest('body').hasClass('single-quote')) || box.hasClass('board') || box.hasClass('member')) {
			// 		window.location.href = $(this).attr('data-link');
			// 	}
			// });

			// slide down textarea
			$('.expandable').focus(function() {
				$(this).animate({ 'height' : 150 }, 250, function () {
					$(this).css({'margin-bottom': 20, 'overflow': 'auto', 'padding-bottom': 24, 'padding-left': 16, 'padding-right': 16, 'padding-top': 16});
				});
				$(this).parent().siblings('.author, .submit').slideDown();
				$(this).next().fadeIn();
				qb.utility.getAutocompleteAuthors();
			});

			// slide down extra form fields
			$('.more-options').click(function (e) {
				e.preventDefault();
				var extraFields = $(this).closest('form').find('.extra-fields');

				if (extraFields.is(':visible')) {
					extraFields.slideUp(function() {
						//$.fancybox.reposition();
					});
					$('span:first-child', this).text('Add more details');
					$('.ico', this).removeClass('arrow-up').addClass('arrow-down');
				} else {
					extraFields.slideDown(function() {
						$.fancybox.reposition();
					});
					$('span:first-child', this).text('Hide more details');
					$('.ico', this).removeClass('arrow-down').addClass('arrow-up');
				}
			});

			// open quote edit form
			$('.edit-quote').click(function (e) {
				e.preventDefault();
				qb.utility.populateQuoteEditForm($(this));
			});

			// load autocomplete options for edit form when user clicks "More Options"
			// $('form').find('.more-options').click(function () {
			// 	qb.utility.getAutocompleteSources();
			// });

			// signed out homepage - slide down to quotes
			$('.explore').click(function (e) {
				e.preventDefault();

				$('html, body').stop().animate({
					'scrollTop' : $('#hero').height()
				}, 750, 'swing');
			});


			/////// everything below here is an unorganized mess ///////

			// check quote length
			// TO DO: set delay so this doesn't fire with *each* keystroke(?)
			$('body').on('keyup', 'textarea', function () {
				qb.utility.checkTextareaLength($(this));
			});

			$('textarea').each(function () {
				qb.utility.checkTextareaLength($(this));
			});


			// show side drawer menu on click of main logo
			$('#logo').click(function (e) {
				e.preventDefault();

				var logo = $(this),
					drawer = $('body > nav'),
					sideNav = drawer.find('ul');

				if (sideNav.is(':visible')) {
					sideNav.fadeOut(function () {
						logo.animate({ 'margin-left' : 0 }, 250);
						drawer.animate({ 'left' : -240 }, 250);
					})
				} else {
					logo.animate({ 'margin-left' : 240 }, 250);
					drawer.animate({ 'left' : 0 }, 250, function () {
						sideNav.fadeIn();
					});
				}
			} );
		}, // init


		/*
		 *	From the "Invite Friends" to board lightbox, adds the selected
		 *	user or email to the invite queue. Also verifies that you are
		 *	adding a mutual follower or valid email address.
		 */
		addUserToInviteList : function( clicked ) {
			var emailNameField 	= $( '#invite-email' ),
				emptyFieldError = $( '.error.invite' ),
				boardID 		= clicked.closest( 'form' ).find( 'input[name="board_id"]' ).val();
				submitButton 	= clicked.closest( 'form' ).find( 'button[type="submit"]' );
			
			// make sure email/name field isn't empty
			if ( emailNameField.val().trim() === '' ) {
				emptyFieldError.show();
				emailNameField.focus();
			} else {
				qb.formData['form_name']  	= 'invite-member-add';
				qb.formData['user_input'] 	= emailNameField.val();
				qb.formData['invite_id']  	= clicked.attr( 'data-id' );
				qb.formData['board_id']		= boardID;

				clicked.addClass( 'loading' );
				emptyFieldError.hide();

				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					data = $.parseJSON( data );

					if ( data.errors ) {
						alert( data.errors );
					} else {
						// reset fields
						clicked.attr( 'data-id', '' );

						// make sure invitee ins't already on the list
						if ( $( '.stacked.invitees' ).find( 'li[data-email="' + data.email + '"]' ).length ) {
							alert( 'This person is already on your invite list' );
						} else {
							// enable submit
							submitButton.removeAttr( 'disabled' );

							// add invited user to list
							$( '.stacked.invitees' ).find('li:first-child').after( data.html );

							// add user email as hidden field
							submitButton.before( '<input name="emails[]" type="hidden" value="' + data.email + '" />')

							// center Fancybox
							$.fancybox.update();

							// TO DO: reposition autocomplete
						}
					}
					
					emailNameField.val( '' );
					emailNameField.focus();

					clicked.removeClass( 'loading' );

					// unset formData to prevent issues with later submissions
					delete qb.formData['form_name'];
					delete qb.formData['user_input'];
					delete qb.formData['invite_id'];
					delete qb.formData['board_id'];
				} );
			}
		},


		/*
		 *	Deletes a quote
		 */
		eraseQuote : function( clicked ) {
			if ( confirm( "Are you sure you want to delete this quote? This action cannot be undone." ) ) {
				clicked.text('Deleting...');

				var quoteBox = clicked.closest('article'),
					mainWrap = quoteBox.closest('main');

				mainWrap.prepend('<div class="overlay" />');

				// set values for WordPress AJAX submit
				qb.formData['quote_id'] = quoteBox.attr('data-id');
				qb.formData['form_name'] = 'erase-quote';

				$.post( qb_ajax.ajaxURL, qb.formData, function (data) {
					data = $.parseJSON(data);

					if (data.errors) {
						alert(data.errors);
					} else {
						// slide up and remove quote and all boxes, show success message
						quoteBox.siblings('.box').slideUp(function () {
							quoteBox.slideUp(function () {
								quoteBox.remove();
								mainWrap.prepend('<p class="message shown success">Quote deleted successfully.</p>');
							});
						});
					}

					mainWrap.find('.overlay').remove();

					delete qb.formData['quote_id'];
					delete qb.formData['form_name'];
				} );
			}
		},


		/*
		 *	Erases (deletes) a board. Only executable by the user
		 *	who created the board (the board author/curator/admin)
		 */
		eraseBoard : function( clicked ) {
			if ( confirm( "Are you sure you want to erase this board? All of this board's members will be kicked out, and all of this board's quotes will be erased. This action cannot be undone." ) ) {
				qb.formData['form_name'] = 'erase-board';
				qb.formData['board_id']	 = clicked.attr( 'data-id' );

				clicked.slideUp();
				clicked.closest('form')
					.find('button')
					.attr( 'disabled', 'disabled' )
					.text( 'Erasing...' );

				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					data = $.parseJSON( data );

					if ( data.errors ) {
						alert( data.errors );
					} else {
						window.location.href = data.redirect_to;
					}

					//clicked.removeClass( 'loading' ).slideUp();

					// unset formData to prevent issues with later submissions
					delete qb.formData['form_name'];
				} );
			}
		},


		/*
		 *	Adds or removes a quote from favorites
		 */
		faveQuote : function( faveIcon ) {
			faveIcon.addClass( 'loading' );

			// set values for WordPress AJAX submit
			qb.formData['qid'] = faveIcon.closest( 'article' ).attr( 'data-id' );
			qb.formData['form_name'] = 'fave-quote';

			$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
				data = $.parseJSON( data );

				if ( data.errors ) {
					alert( data.errors );
				} else {
					// update styling on heart icon
					faveIcon.toggleClass( 'faved' );
					faveIcon.toggleAttr( 'title', 'Unfave', 'Fave' );

					if (data.fave_count < 1) {
						faveIcon.find('span').text('');
					} else {
						faveIcon.find('span').text(data.fave_count);						
					}
				}

				faveIcon.removeClass( 'loading' );

				// unset formData to prevent issues with later submissions
				delete qb.formData['qid'];
				delete qb.formData['form_name'];
			} );
		},


		/*
		 *	Follows a board or a user, depending on what was clicked
		 */
		followSomething : function (followButton) {
			var buttonText = followButton.text();

			// ignore clicks while processing
			if (!followButton.hasClass('loading')) {
				followButton.addClass('loading');

				// set values for WordPress AJAX submit
				if ( followButton.hasClass('board')) {
					qb.formData['form_name'] = 'follow-board';
					
					if (followButton.hasClass('collab')) {
						qb.formData['is_public'] = true;
					}
				} else {
					qb.formData['form_name'] = 'follow-user';
				}

				qb.formData['id'] = followButton.attr('data-id');

				$.post(qb_ajax.ajaxURL, qb.formData, function (data) {
					data = $.parseJSON(data);

					if (data.errors) {
						alert(data.errors);
					} else {
						// handle normal follow
						if (followButton.hasClass('follow')) {
							followButton.toggleClass('following');
							followButton.toggleAttr('title', 'Following', 'Follow');

							if (followButton.hasClass('btn')) {
								if (followButton.hasClass('following')) {
									followButton.text('Following');
								} else {
									followButton.text('Follow');
								}
							}

						// handle collaboration
						} else {
							followButton.toggleClass('collaborating');
							followButton.toggleAttr('title', 'Collaborating', 'Collaborate');

							// only adjust text on buttons; we don't want text showing for icons in list view
							if (followButton.hasClass('btn')) {
								if (followButton.hasClass('collaborating')) {
									followButton.text('Collaborating');
								} else {
									followButton.text('Collaborate');
								}
							}
						}
					}

					followButton.removeClass('loading');

					// unset formData to prevent issues with later submissions
					delete qb.formData['id'];
					delete qb.formData['form_name'];
				} );
			}
		},


		/*
		 *	Shows the "Show/Hide" password toggle link when the password field has focus
		 */
		hidePasswordToggleLink : function( passwordField ) {
			passwordField.closest( 'div' ).find( '.toggle-pw' ).hide();
		},


		/*
		 *	Removes a user from a board
		 */
		leaveBoard : function( clicked ) {
			if ( confirm( 'Are you sure you want to leave this board?' ) ) {
				qb.formData['form_name'] = 'leave-board';
				qb.formData['board_id']	 = clicked.attr( 'data-id' );

				clicked.addClass( 'loading' );

				$.post( qb_ajax.ajaxURL, qb.formData, function( data ) {
					data = $.parseJSON( data );

					if ( data.errors ) {
						alert( data.errors );
					} else {
						window.location.href = data.redirect_to;
					}

					clicked.removeClass( 'loading' ).slideUp();

					// unset formData to prevent issues with later submissions
					delete qb.formData['form_name'];
				} );
			}
		},


		/*
		 *	Opens the "Add Board" (inline/on-the-fly) fancybox. Since this can be opened
		 *	from multiple places (e.g., "Add Quote", "Edit Quote", and "Requote"), we must
		 *	determine from which form the inline board fancybox was opened, so that on close
		 *	the appropriate fancybox can be reopened.
		 */
		openAddBoardInlineFancybox : function( firstDropdownOption ) {
			var formID = firstDropdownOption.closest('form').attr('id'),
				addBoardForm = $('#board-create-new');

			if (firstDropdownOption.attr('id') === 'menu-item-625' || firstDropdownOption.attr('id') === 'menu-item-719') {
				addBoardForm.find('input[name="form_name"]').val('add-board');
			} else {
				addBoardForm.find('input[name="form_name"]').val('add-board-inline');
				addBoardForm.attr('id', 'board-create-new-inline');
			}

			$.fancybox.open(
				addBoardForm, {
					afterShow: function () {
						setTimeout( function() {
							$( 'h2 + div', addBoardForm ).find( ':input' ).focus();
						}, 1 );
					},
					closeBtn : qb.fancybox.closeBtn,
					helpers: { // prevent jump to top of page
						overlay: {
							locked: false
						}
					},
					margin: qb.fancybox.margin,
					maxWidth : qb.fancybox.maxWidth,
					minWidth: qb.fancybox.minWidth,
					padding : qb.fancybox.padding,
					wrapCSS: formID
				}
			);
		},


		/*
		 *	Opens the "Add Quote" fancybox
		 */
		openAddQuoteFancybox : function () {
			var addQuoteForm = $('#quote-create-new');

			$.fancybox(
				addQuoteForm, {
					afterShow: function () {
						setTimeout( function() {
							addQuoteForm.find('textarea').focus();
						}, 1 );
					},
					closeBtn : qb.fancybox.closeBtn,
					margin: qb.fancybox.margin,
					maxWidth : qb.fancybox.maxWidth,
					minWidth: qb.fancybox.minWidth,
					padding : qb.fancybox.padding
				}
			);
		},


		/*
		 *	Fired when a user closes the "Add Board" on the fly fancybox.
		 *	Determines which fancybox to open after close. For example,
		 *	if the user added a board on the fly from the "Add Quote" fancybox.
		 *	then this function reopens the "Add Quote" fancybox.
		 *
		 *	User can get to the "Add Board" on the fly box in at least 3 ways
		 *	("Add Quote", "Edit Quote", and "Requote"), hence the need for this.
		 */
		openFancyboxAfterAddingBoardInline : function( button ) {
			var fancyboxWrapper = button.closest( '.fancybox-wrap' );
			var boardSelectMenu = $( '.choose-board' ).find( 'select' );

			// reopen "Add Quote"
			if ( fancyboxWrapper.hasClass( 'quote-create-new' ) ) {
				qb.events.openAddQuoteFancybox( boardSelectMenu );

			// reopen "Edit Quote"
			} else if ( fancyboxWrapper.hasClass( 'quote-edit' ) ) {
				$.fancybox.open(
					$( '#quote-edit' ), {
						afterShow: function () {
							setTimeout( function() {
								boardSelectMenu.trigger( 'chosen:activate' );
							}, 1 );
						},
						closeBtn : false,
						margin : 10,
						maxWidth : qb.fancybox.maxWidth,
						minWidth: qb.fancybox.minWidth,
						padding : 0,
						wrapCSS: 'quote-edit'
					}
				);

			// reopen "Requote"
			} else if ( fancyboxWrapper.hasClass( 'requote-form' ) ) {
				$.fancybox.open(
					$( '#requote-form' ), {
						afterShow: function () {
							setTimeout( function() {
								//boardSelectMenu.trigger( 'chosen:activate' );
							}, 1 );
						},
						closeBtn : false,
						margin : 10,
						padding : 0,
						wrapCSS: 'requote-form quote-details'
					}
				);

			// on page 
			} else {
				$.fancybox.close();
			}
		},


		/**
		 *	Removes a collaborator from a board (demotes to follower)
		 */
		removeCollaborator : function (clicked) {
			var parentListItem = clicked.closest('li');

			if (confirm('Are you sure you want to remove this person as a collaborator? This person will still follow this board, but will be unable to add or edit quotes on this board.')) {
				qb.formData['form_name'] = 'demote-member';
				qb.formData['user_id']	 = parentListItem.attr('data-id');
				qb.formData['board_id']	 = clicked.closest('ul').attr('data-board');

				clicked.addClass('loading');

				$.post(qb_ajax.ajaxURL, qb.formData, function (data) {
					data = $.parseJSON(data);

					if (data.errors) {
						alert(data.errors);
					} else {
						// hide and remove collaborator from list
						parentListItem.slideUp(250, function () {
							$(this).remove();
							$.fancybox.update(); // center Fancybox
						});
					}

					clicked.removeClass( 'loading' );

					// unset formData to prevent issues with later submissions
					delete qb.formData['form_name'];
				} );
			}
		},


		/**
		 *	Removed a pending collaborator from a board (deletes invite code from DB)
		 *	NOTE: This is deferred to later; for now, you can't delete pending invites
		 */
		removePendingCollaborator : function (clicked) {
			// later
		},


		/*
		 *	Removes a user from a board invite list
		 */
		removeUserFromInviteList : function( clicked ) {
			var parentListItem 	= clicked.closest( 'li' ),
				parentForm		= clicked.closest( 'form' ),
				inviteeCount 	= parentListItem.siblings().length,
				emailToRemove 	= parentListItem.attr( 'data-email' );

			// hide and remove invitee from list
			parentListItem.slideUp( 250, function() {
				$( this ).remove();
				$.fancybox.update(); // center Fancybox
			} );

			// remove corresponding hidden form field
			parentForm.find( 'input[value="' + emailToRemove + '"]' ).remove();

			// disable submit button if no invitees left
			if ( inviteeCount < 1 ) {
				parentForm.find( '[type="submit"]' ).attr( 'disabled', 'disabled' );
			}
		},


		/*
		 *	When adding a quote from a board page, this function
		 *	selects the current board in the "Add Quote" form.
		 *
		 *	TO DO: does not currently do this if a user clicks the
		 *	"Add quote" button at the bottom of the screen.
		 */
		selectCurrentBoard : function( addQuoteButton ) {
			var boardID = addQuoteButton.attr( 'data-bid' );
			var boardSelectMenu = $( '#quote-create-new' ).find( '[name="quote_board"]' );

			boardSelectMenu.find( 'option[value="' + boardID + '"]' ).attr( 'selected', 'selected' );
			boardSelectMenu.trigger( 'chosen:updated' );
		},


		/*
		 *	Shows the "Show/Hide" password toggle link when the password field has focus
		 */
		showPasswordToggleLink : function( element ) {
			element.closest( 'div' ).find( '.toggle-pw' ).show();
		},


		/*
		 *	Submits a form via AJAX through the jQuery Form plugin
		 *	Docs: http://jquery.malsup.com/form
		 */
		submitForm : function( submitButton ) {
			// cache selectors
			var form = submitButton.closest( 'form' ),
				buttonText = submitButton.html(),
				returnValue = true;

			// set jQuery Form plugin options
			var options = {
				beforeSubmit : prepareRequest,
				data 		 : qb.formData,
				error 		 : showErrors,
				success 	 : showResponse,
				url 		 : qb_ajax.ajaxURL
			}

			// submit the form
			form.ajaxForm( options );

			function prepareRequest( formData, jqForm, options ) {
				submitButton
					.attr( 'disabled', 'disabled' )
					.text( 'Please wait...' );

				if ( jqForm.attr('id') === 'login-form') {
					jqForm.addClass('loading');
					jqForm.find('.sso-loading').html('<span>Please wait...</span>');
				}

				if ( jqForm.attr('id') === 'register-form') {
					var emailInput = $( '#register-email' ),
						passwordInput = $( '#register-password' ),
						usernameInput = $( '#register-username' );

					jqForm.addClass('loading');
					jqForm.find('.sso-loading').html('<span>Please wait...</span>');

					qb.utility.checkRegisterEmail( emailInput, emailInput.val(), function ( data ) {
						if ( data.result !== 'unique' ) {
							submitButton.removeAttr( 'disabled' ).html( buttonText );
							jqForm.removeClass('loading');
							returnValue = false;
						} else {
							if ( !qb.utility.checkRegisterPassword( passwordInput, passwordInput.val() ) ) {
								submitButton.removeAttr( 'disabled' ).html( buttonText );
								jqForm.removeClass('loading');
								returnValue = false;
							} else {
								qb.utility.checkRegisterUsername( usernameInput, usernameInput.val(), function ( data ) {
									if ( data.result !== 'unique' ) {
										submitButton.removeAttr( 'disabled' ).html( buttonText );
										jqForm.removeClass('loading');
										returnValue = false;
									}
								} );
							}
						}
					} );
				}

				return returnValue;
			}

			function showErrors() {
				alert( 'There was a problem with your submission; please try again later' );
			}

			function showResponse( responseText, statusText, xhr, form ) {
				console.log('response: ' + responseText);
				//console.log('status: ' + statusText)
				//console.log(xhr)
				//console.log(form)
				//console.log(form[0].id)

				var responseText = $.parseJSON( responseText );

				// output any errors
				if ( responseText.errors ) {
					alert( responseText.errors );
					submitButton.removeAttr( 'disabled' ).html( buttonText );
					form.removeClass('loading');
					form.find('.sso-loading').hide();

				// load various success functions
				} else {
					switch( form[0].id ) {
						case 'board-create-new':
							qb.success.createBoard( responseText, form );
							break;

						case 'board-create-new-inline':
							qb.success.createBoardInline( responseText, form, submitButton, buttonText );
							break;

						case 'board-invite':
							qb.success.sendBoardInvites( responseText, form, submitButton, buttonText );
							break;

						case 'board-profile-edit':
							qb.success.updateBoardProfile( responseText, form, submitButton, buttonText );
							break;

						case 'comment-form':
							qb.success.addComment( responseText, form, submitButton, buttonText );
							break;

						case 'feedback-form':
							qb.success.sendFeedback( responseText, form, submitButton, buttonText );
							break;

						case 'login-form':
							qb.success.loginUser( responseText );
							break;

						case 'register-form':
							qb.success.registerUser( responseText );
							break;

						case 'requote-form':
							qb.success.requote( responseText, form, submitButton, buttonText );
							break;

						case 'quote-create-new':
						case 'quote-create-new-inline':
							qb.success.createQuote( responseText, form, submitButton, buttonText );
							break;

						case 'quote-edit':
							qb.success.updateQuote( responseText, form, submitButton, buttonText );
							break;

						case 'user-profile-edit':
							qb.success.updateUserProfile( responseText, form, submitButton, buttonText );
							break;
					}
				}
			}
		},


		/*
		 *	Toggles password visibility on, e.g., the register form
		 */
		togglePasswordVisibility : function( element ) {
			element.toggleClass( 'revealed' );
			element.toggleAttr( 'title', 'Hide password', 'Reveal password' );
			element.closest( 'div' ).find( 'input' ).toggleAttr( 'type', 'text', 'password' );

			if ( element.hasClass( 'revealed' ) ) {
				element.text( 'Hide' );
			} else {
				element.text( 'Show' );
			}
		}
	}, // qb.events

	// functions to handle successful form submissions
	qb.success = {

		/*
		 *	Fired after a comment is added to a quote. Live inserts the comment,
		 *	displays a success message, and resets comment form.
		 */

		addComment : function( json, form, submitButton, buttonText ) {
			var commentWrapper = $('#comment-list'),
				successMessage = form.find('.message.success');

			// insert comment and update relative time
			commentWrapper.append( json.comment_html );
			$('.timeago').timeago();

			// show success message
			successMessage.show();

			// reset form
			form.find('textarea').val('');
			submitButton.removeClass('updated').removeAttr('disabled').text(buttonText);

			// hide success message
			setTimeout(hideMessage, 8000);

			function hideMessage() {
				successMessage.slideUp();
			}
		},


		/*
		 *	Redirects user to a newly created board permalink
		 */
		createBoard : function( json, form ) {
			window.location.href = json.permalink + '?new';
		},


		/*
		 *	Reopens the lightbox ("Add Quote", "Edit Quote", or "Requote", inserts newly
		 *	added board into the Chosen dropdown, and selects it
		 */
		createBoardInline : function( json, form, submitButton, buttonText ) {
			var boardSelectMenu = $( '.choose-board' ).find( 'select' );
			
			boardSelectMenu.append( '<option selected value="' + json.board_id + '">' + json.board_name + '</option>' );
			boardSelectMenu.trigger( 'chosen:updated' );

			// re-open the appropriate fancybox, based on from where user clicked
			//alert(form[0].id)
			qb.events.openFancyboxAfterAddingBoardInline( submitButton );

			// reset "add board" inline form
			form.find( '[type="text"]' ).val( '' );
			form.find( 'select' ).find( 'option:first-child' ).attr( 'selected', 'selected' );
			form.find( 'select' ).trigger( 'chosen:updated' );

			// update submit button on "add board" inline form
			submitButton.removeClass( 'updated' ).removeAttr( 'disabled' ).text( buttonText );
		},


		/*
		 *	After adding quote from inline form, resets form and slides down
		 *	a success message.
		 */
		createQuote : function (json, form, submitButton, buttonText) {
			// reset form

			form.find('textarea').val('')
			form.find('[name="quote_author"]').val('');
			form.find('[name="quote_author_id"]').val('');
			form.find('[name="quote_source"]').val('');
			form.find('[name="quote_source_id"]').val('');
			form.find('.charcount span').text(qb.quoteLimit);
			form.find('.form-options').find('.ico').removeClass('arrow-up').addClass('arrow-down');
			form.find('.extra-fields').slideUp();
			form.find('.more-options').find('span:first-child').text('Add more details');

			// update submit button
			submitButton.removeClass('updated').removeAttr('disabled').text( buttonText );

			
			/**
			 *	Inline quote success.
			 */
			if (form.attr('id') === 'quote-create-new-inline') {
				// reset form
				form.find('.author, .submit').slideUp();
				form.find('textarea').animate({'height' : 40, 'margin-bottom': 0, 'padding': 10});
				form.find('.charcount').hide();

				// privacy
				if (json.privacy === 'private') {
					privacy = '<span class="status private" title="Private Quote"></span>';
				} else {
					privacy = '';
				}

				// insert quote
				form.closest('.box').after('<article class="quote box yours new hidden" data-author="' + json.added_by + '" data-board="' + json.board_id + '" data-id="' + json.quote_id + '"><a class="avatar" href="' + json.home_url + '" title="Posted by You">' + json.avatar + '</a><div><div class="bubble" data-link="' + json.quote_url + '"><h4>' + privacy + 'You <span>@' + json.username + '</span></h4><time class="timeago" datetime="' + json.datetime + '">less than a minute ago</time><q>' + json.quote + '</q></div><menu><span class="ico fave" title="Fave"></span></menu></div></article>');
				
				// show quote
				setTimeout(function () {
					$('.quote.new').slideDown();
				}, 1000);

				// remove highlighting
				setTimeout(function () {
					form.closest('main').find('.quote.new').removeClass('new')
				}, 5000);
			}


			/**
			 *	Lightbox quote success
			 */
			else if (form.attr('id') === 'quote-create-new') {
				// show success message and link to new quote
				form.find('.ajax-wrapper').fadeOut(250, function () {
					$(this).slideUp();
					if (json.permalink) {
						form.find('.hidden.success').find('span').html(json.permalink);
						form.find('.hidden.success').fadeIn();
						form.find('.flex').find('a').attr('href', json.quote_url);
					} else {
						form.find('.hidden.error').fadeIn();
					}
				} );
			}
		},


		/*
		 *	Actions performed after a user attempts to sign in
		 */
		loginUser : function( json ) {
			if ( json.result === true ) {
				$('.sso-loading').html('<span>Signing you in...</span>');
				window.location.href = json.redirect_to;
			} else {
				// TO DO: error handling (this will only happen if something went wrong in the PHP script)
			}
		},


		/*
		 *	Actions performed after a user attempts to register
		 */
		registerUser : function( json ) {
			if ( json.result === true ) {
				$('.sso-loading').html('<span>Finishing registration...</span>');
				window.location.href = json.redirect_to;
			} else {
				// TO DO: error handling (this will only happen if something went wrong in the PHP script)
			}
		},


		/*
		 *	Actions performed after a user requotes a quote.
		 *	Show success message, reset form, and remove the board just added
		 *	from the available options (to discourage duplicate requotes)
		 */
		requote : function( json, form, submitButton, buttonText ) {
			var successMessage = form.find( '.success' ),
				boardSelectMenu = form.find( 'select' );

			boardSelectMenu.find( 'option[value="' + json.board_id + '"]' ).remove();
			boardSelectMenu.trigger( 'chosen:updated' );

			successMessage.slideDown();

			setTimeout( hideMessage, 5000 );
			function hideMessage() {
				successMessage.slideUp();
			}

			submitButton.removeClass( 'updated' ).removeAttr( 'disabled' ).text( buttonText );
		},


		/*
		 *	Updates "Invite Peeps" fancybox after board invitations are sent
		 *	and a response comes back from the Mandrill API.
		 *
		 *	For more, see: includes/invite-member-send.php
		 */
		sendBoardInvites : function( json, form, submitButton, buttonText ) {
			var status = '';

			if ( json.result[0][0] ) {
				status = json.result[0][0].status;
			} else {
				status = 'error';
			}

			form.find ('.inner-btn, .who-can-add, div:last-of-type').animate({'opacity' : 0}, 250, function () {
				$(this).slideUp();
				if (status === 'sent') {
					form.find('.hidden.success').fadeIn(250, function() {
						$.fancybox.update();
					});
				} else {
					form.find('.hidden.error').fadeIn(250, function() {
						$.fancybox.update();
					});
				}
			} );

			/*
			console.log(json.result[0][0].email);
			console.log(json.result[0][0].status);
			console.log(json.result[0][0]._id);
			console.log(json.result[0][0].reject_reason);
			*/
		},


		/*
		 *	Updates the "Send Feedback" fancybox after beta tester feedback is sent
		 *	and a response comes back from the Mandrill API.
		 *
		 *	For more, see: includes/feedback.php
		 */
		sendFeedback : function( json, form, submitButton, buttonText ) {
			var status = '';

			if ( json.result[0][0] ) {
				status = json.result[0][0].status;
			} else {
				status = 'error';
			}

			if ( status === 'sent' ) {
				form.find('.message.success').slideDown();
			} else {
				form.find('.message.error').slideDown();
			}

			// reset form
			form.find( 'textarea' ).val( '' );
			form.find( '.charcount span' ).text( qb.quoteLimit );

			// update submit button
			submitButton.removeClass( 'updated' ).removeAttr( 'disabled' ).text( buttonText );
		},


		/*
		 *	Updates board profile modal (needs to be modified to also update inline profile for larger device sizes)
		 */
		updateBoardProfile : function( json, form, submitButton, buttonText ) {
			// reset photo fields
			qb.utility.updatePhotoFieldsAfterSubmit(json, form);

			// update profile on page, in real time
			qb.utility.updateProfileAfterSubmit(json);

			// update background image
			if (json.bg_photo) {
				$('#hero').attr('style', 'background: url(' + json.bg_photo + ') center no-repeat;');
			}

			// update submit button
			qb.success.updateSubmitButton( submitButton, buttonText );
		},


		/*
		 *	Updates edit form after successful quote edit
		 */
		updateQuote : function (json, form, submitButton, buttonText) {
			var updatedQuoteBox = $('.quote.box[data-id="' + json.quote_id + '"]'),
				successMessage = $('.quote-edit.success');

			// reset form
			submitButton.removeClass('updated').removeAttr('disabled').text(buttonText);

			// update quote on page
			updatedQuoteBox.find('q').html(json.quote_text);
			updatedQuoteBox.find('cite').html(json.quote_author);
			updatedQuoteBox.attr('data-author', json.quote_author_id);
			updatedQuoteBox.attr('data-board', json.quote_board_id);

			// if quote is private, add lock icon if it's not already there
			if (json.quote_status === 'private' && !updatedQuoteBox.find('.private').length) {
				updatedQuoteBox.find('h4').prepend('<span class="status private" title="Private Quote"></span>');
				updatedQuoteBox.find('footer').append('<span class="private" title="This is a private quote">Private</span>');
			} else {
				updatedQuoteBox.find('.private').remove();
			}

			// update source if one is present
			if (updatedQuoteBox.find('.quote-source').length) {
				updatedQuoteBox.find('.quote-source').html(json.quote_source);
			} else {
				updatedQuoteBox.find('cite').after('<span class="box-meta quote-source">' + json.quote_source + '</span>');
			}

			// update original board
			$('.boards.box').find('li:first-child').html(json.quote_board_img);

			$.fancybox.close();
			
			successMessage.slideDown();
			setTimeout(function () {
				successMessage.slideUp();
			}, 8000);
		},


		/*
		 *	Updates submit button text after successful form submit
		 *	Presently, this is specific to board/user profile updates
		 */
		updateSubmitButton : function( button, originalText ) {
			button.addClass( 'updated' ).text( 'Updated!' );
			setTimeout( function() {
				button.removeClass( 'updated' ).removeAttr( 'disabled' ).text( originalText );
				$.fancybox.close();
			}, 2500 );
		},


		/*
		 *	Updates user profile modal
		 */
		updateUserProfile : function(json, form, submitButton, buttonText) {
			// reset photo fields
			qb.utility.updatePhotoFieldsAfterSubmit(json, form);

			// update on page profile, in real time
			qb.utility.updateProfileAfterSubmit(json);

			// update fixed header avatar
			$('#dropdown-avatar').find('img').attr('src', json.profile_photo).attr('width', 48).attr('height', 48);

			// update background image
			if (json.bg_photo) {
				$('#hero').attr('style', 'background: url(' + json.bg_photo + ') center no-repeat;');
			}

			// update submit button
			qb.success.updateSubmitButton( submitButton, buttonText );
		}
	}
} )(jQuery);



// toggle attribute plugin
// TO DO: move this somewhere more sensible
jQuery.fn.toggleAttr = function( attr, attr1, attr2 ) {
	return this.each( function() {
		var self = jQuery( this );
		
		if ( self.attr( attr ) == attr1 ) {
			self.attr( attr, attr2 );
		} else {
			self.attr( attr, attr1 );
		}
	} )
};