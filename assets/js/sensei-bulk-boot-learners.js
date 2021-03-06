/*! Sensei Bulk Boot Learners - v0.1.1
 * http://zao.is
 * Copyright (c) 2017; * Licensed GPL-2.0+ */
window.SenseiBoot = window.SenseiBoot || {};

( function( window, document, $, app, undefined ) {
	'use strict';

	app.processSize = 2;
	app.processed = 0;

	app.init = function() {
		$( document.body ).on( 'click', '.senseiboot-button', app._triggerProcessing );
		$( '.wp_list_table_learners_mains .alternate' ).removeClass( 'alternate' ); // because WP styles by odd/even.
	};

	app._triggerProcessing = function( evt ) {
		evt.preventDefault();

		var $this = $( this );
		var courseId = $this.data( 'bootfrom' );

		if ( $this.hasClass( 'disabled' ) ) {
			return;
		}

		if ( app.xhr ) {
			app.xhr.abort();
		}

		app.removeLoadingRows();

		app.triggerProcessing( $this.parents( 'tr' ), courseId );
	};

	app.triggerProcessing = function( $tr, courseId, left ) {
		if ( ! $tr.length || ! courseId ) {
			return false;
		}

		var $loadRow = $tr.next();
		var processed = app.processed + app.processSize;
		var origCount, width, turtle;

		if ( ! $loadRow.hasClass( 'boot-loading-row' ) ) {
			origCount = $tr.find( '.column-num_learners' ).text();

			if ( origCount < 1 ) {
				$tr.find( '.senseiboot-button' ).addClass( 'disabled' );
				return false;
			}

			app.turtle = app.turtle ? '<em>🐢</em>' : '';
			if ( app.turtle ) {
				console.warn( 'WHEEEEeeeeee!! 🐢 🐢 🐢 🐢' );
			}
			$loadRow = $( '<tr class="boot-loading-row" data-origcount="'+ origCount +'"><td colspan="'+ ( $tr.find( '> *' ).length - 1 ) +'"><div class="progress-bar"><span class="progress-indicator">'+ app.turtle +'</span></div></td><td class="info">'+ app.l10n.processing.replace( '%1$d', '<span class="processed">'+ processed +'</span>' ).replace( '%2$d', '<span class="count">'+ origCount +'</span>' ) +'<div class="spinner is-active"></div></td></tr>' );

			$tr.after( $loadRow );

		} else if ( left ) {
			origCount = $loadRow.data( 'origcount' );

			$loadRow.find( '.processed' ).text( processed );

			width = ( processed / origCount ) * 100;
			$loadRow.find( '.progress-bar span' ).css({ width: ( width > 100 ? 100 : width ) + '%' });

			$tr.find( '.column-num_learners' ).text( left );
		}

		if ( 'undefined' !== typeof left && ! left ) {
			return app.successfulBoot( $tr, $loadRow );
		}

		app.xhr = $.post( app.ajaxurl, {
			'action'       : 'boot_from_course',
			'nonce'        : app.nonce,
			'to-process'   : app.processSize,
			'total'        : origCount,
			'processed'    : processed,
			'process-size' : app.processSize,
			'boot-from'    : courseId
		}, function( response ) {
			if ( ! response.success ) {
				app.failBoot( app.l10n.boot_error );
			}

			if ( response.data ) {
				app.processed = processed;

				if ( 0 === processed % 12 ) {
					console.warn( 'Let the server breathe...' );
					window.setTimeout( function() {
						console.warn( 'Ok, continue...' );
						// Keep going.
						app.triggerProcessing( $tr, courseId, response.data );
					}, 5000 );
				} else {
					// Keep going.
					app.triggerProcessing( $tr, courseId, response.data );
				}
			} else {
				// Ok, we're done.
				app.successfulBoot( $tr, $loadRow );
			}

		} )
		.fail( function() {
			app.failBoot( app.l10n.ajax_error );
		} );

	};

	app.successfulBoot = function( $tr, $loadRow ) {
		$loadRow.find( '.progress-bar span' ).css({ width: '100%' });
		$loadRow.find( 'td.info' ).html( '<strong>'+ app.l10n.success +'</strong>' );
		$tr.find( '.column-num_learners' ).text( 0 );
		$tr.find( '.senseiboot-button' ).addClass( 'disabled' );
		window.setTimeout( function() {
			$loadRow.fadeOut( 400, function() {
				app.removeLoadingRows();
			} );
		}, 4000 );
	};

	app.failBoot = function( msg ) {
		app.removeLoadingRows();
		window.alert( msg );
	};

	app.removeLoadingRows = function( msg ) {
		$( '.boot-loading-row' ).remove();
	};

	$( app.init );

} )( window, document, jQuery, window.SenseiBoot );
